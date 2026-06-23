<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ledger_model — the single source of truth for ledger_entries.
 *
 * Every monetary movement (receipts, task client/vendor payments) is
 * recorded here as one or more rows. Currencies are independent and never
 * converted. All multi-row posting is transactional.
 */
class Ledger_model extends CI_Model {

    /**
     * Post one or more ledger entries atomically.
     *
     * @param  array $entries  list of associative rows; each may set:
     *   account_id, currency, debit, credit, date, source, reference,
     *   note, recorded_by
     * @return bool
     */
    public function post_entries(array $entries)
    {
        if (empty($entries)) {
            return TRUE;
        }

        $now  = date('Y-m-d H:i:s');
        $user = $this->session->userdata('user_id');
        $rows = array();

        foreach ($entries as $e) {
            $rows[] = array(
                'account_id'  => (int) $e['account_id'],
                'currency'    => $e['currency'],
                'debit'       => isset($e['debit'])  ? _bc_clean($e['debit'])  : '0.00',
                'credit'      => isset($e['credit']) ? _bc_clean($e['credit']) : '0.00',
                'date'        => $e['date'],
                'source'      => $e['source'],
                'reference'   => isset($e['reference']) ? $e['reference'] : NULL,
                'note'        => isset($e['note']) ? $e['note'] : NULL,
                'recorded_by' => isset($e['recorded_by']) ? $e['recorded_by'] : $user,
                'created_at'  => $now,
            );
        }

        $this->db->trans_start();
        $this->db->insert_batch('ledger_entries', $rows);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Delete every entry tied to a source + reference (used to reverse a
     * payment or receipt). Transactional.
     *
     * @return bool
     */
    public function delete_by_source($source, $reference)
    {
        $this->db->trans_start();
        $this->db->where('source', $source)->where('reference', (string) $reference)
                 ->delete('ledger_entries');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /** @return object|null */
    public function get_by_id($id)
    {
        return $this->db
            ->select('id, account_id, currency, debit, credit, date, source, reference, note, recorded_by, created_at')
            ->from('ledger_entries')
            ->where('id', (int) $id)
            ->get()->row();
    }

    /**
     * Net balance (debit − credit) for one account in one currency,
     * computed with bcmath.
     *
     * @return string  decimal string, scale 2
     */
    public function get_account_balance($account_id, $currency)
    {
        $row = $this->db
            ->select('COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c', FALSE)
            ->from('ledger_entries')
            ->where('account_id', (int) $account_id)
            ->where('currency', $currency)
            ->get()->row();

        return bc_subtract($row ? $row->d : '0', $row ? $row->c : '0');
    }

    /**
     * Per-currency balances for one account.
     *
     * @return array  ['AFN' => '0.00', 'USD' => ...]
     */
    public function get_account_balances($account_id)
    {
        $rows = $this->db
            ->select('currency, COALESCE(SUM(debit),0) AS d, COALESCE(SUM(credit),0) AS c', FALSE)
            ->from('ledger_entries')
            ->where('account_id', (int) $account_id)
            ->group_by('currency')
            ->get()->result();

        $balances = array();
        foreach (array_keys(CURRENCIES) as $cur) {
            $balances[$cur] = '0.00';
        }
        foreach ($rows as $r) {
            $balances[$r->currency] = bc_subtract($r->d, $r->c);
        }
        return $balances;
    }
}
