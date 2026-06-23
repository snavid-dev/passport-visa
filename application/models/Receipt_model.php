<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Receipt_model — manual ledger adjustments.
 *
 * A "receipt" is a single manual ledger entry (debit OR credit) recorded
 * against any account, stored in ledger_entries with source = 'receipt'.
 */
class Receipt_model extends CI_Model {

    const SOURCE = 'receipt';

    /**
     * All receipts with their account name (single join, no N+1).
     *
     * @return array
     */
    public function get_all()
    {
        return $this->db
            ->select('l.id, l.account_id, a.name AS account_name, a.type AS account_type,
                      l.currency, l.debit, l.credit, l.date, l.note, l.created_at')
            ->from('ledger_entries l')
            ->join('financial_accounts a', 'a.id = l.account_id', 'left')
            ->where('l.source', self::SOURCE)
            ->order_by('l.date', 'DESC')
            ->order_by('l.id', 'DESC')
            ->get()->result();
    }

    /** @return object|null */
    public function get_by_id($id)
    {
        return $this->db
            ->select('id, account_id, currency, debit, credit, date, note')
            ->from('ledger_entries')
            ->where('id', (int) $id)
            ->where('source', self::SOURCE)
            ->get()->row();
    }

    /**
     * Create a receipt = one ledger entry.
     *
     * @param  array $data  account_id, currency, direction(debit|credit),
     *                      amount, date, note
     * @return int|false  new ledger entry id
     */
    public function create($data)
    {
        list($debit, $credit) = $this->_split($data['direction'], $data['amount']);

        $insert = array(
            'account_id'  => (int) $data['account_id'],
            'currency'    => $data['currency'],
            'debit'       => $debit,
            'credit'      => $credit,
            'date'        => $data['date'],
            'source'      => self::SOURCE,
            'reference'   => NULL,
            'note'        => ! empty($data['note']) ? $data['note'] : NULL,
            'recorded_by' => $this->session->userdata('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
        );

        if ($this->db->insert('ledger_entries', $insert)) {
            return (int) $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update an existing receipt entry.
     *
     * @return bool
     */
    public function update($id, $data)
    {
        list($debit, $credit) = $this->_split($data['direction'], $data['amount']);

        $update = array(
            'account_id' => (int) $data['account_id'],
            'currency'   => $data['currency'],
            'debit'      => $debit,
            'credit'     => $credit,
            'date'       => $data['date'],
            'note'       => ! empty($data['note']) ? $data['note'] : NULL,
        );

        return $this->db
            ->where('id', (int) $id)
            ->where('source', self::SOURCE)
            ->update('ledger_entries', $update);
    }

    /**
     * Delete a receipt entry (guarded to source = 'receipt').
     *
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->where('source', self::SOURCE)
                 ->delete('ledger_entries');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Map a direction + amount to (debit, credit) columns.
     *
     * @return array [debit, credit]
     */
    private function _split($direction, $amount)
    {
        $amount = _bc_clean($amount);
        return ($direction === 'debit')
            ? array($amount, '0.00')
            : array('0.00', $amount);
    }
}
