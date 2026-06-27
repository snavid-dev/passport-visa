<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Payment_model — task client + vendor payments.
 *
 * Every payment posts atomically to: (1) its payment log table,
 * (2) the cash account, (3) the counterparty account — per the ledger
 * posting pattern. reference = task_id so task/report queries group cleanly.
 * Deleting a payment reverses all three rows in one transaction.
 */
class Payment_model extends CI_Model {

    protected $cash_id = null;

    // ------------------------------------------------------------------
    // Client payments (money received FROM the client)
    //   cash:   debit  (cash up)
    //   client: credit (receivable down)
    // ------------------------------------------------------------------

    public function add_client_payment($task_id, $amount, $currency, $date, $note)
    {
        $task_id = (int) $task_id;
        $amount  = _bc_clean($amount);
        $client  = $this->_party($task_id, 'client_id');
        $cash    = $this->_cash_id();
        $now     = date('Y-m-d H:i:s');
        $user    = $this->session->userdata('user_id');

        $this->db->trans_start();

        $this->db->insert('task_client_payments', array(
            'task_id' => $task_id, 'amount' => $amount, 'currency' => $currency,
            'date' => $date, 'note' => $note, 'recorded_by' => $user, 'created_at' => $now,
        ));

        $this->db->insert('ledger_entries', array(
            'account_id' => $cash, 'currency' => $currency, 'debit' => $amount, 'credit' => '0.00',
            'date' => $date, 'source' => 'task_client_payment', 'reference' => (string) $task_id,
            'note' => $note, 'recorded_by' => $user, 'created_at' => $now,
        ));

        if ($client) {
            $this->db->insert('ledger_entries', array(
                'account_id' => $client, 'currency' => $currency, 'debit' => '0.00', 'credit' => $amount,
                'date' => $date, 'source' => 'task_client_payment', 'reference' => (string) $task_id,
                'note' => $note, 'recorded_by' => $user, 'created_at' => $now,
            ));
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete_client_payment($payment_id)
    {
        $p = $this->get_client_payment($payment_id);
        if (! $p) {
            return FALSE;
        }
        $client = $this->_party($p->task_id, 'client_id');
        $cash   = $this->_cash_id();

        $this->db->trans_start();
        $this->db->where('id', (int) $payment_id)->delete('task_client_payments');
        $this->_delete_one_ledger(array(
            'source' => 'task_client_payment', 'reference' => (string) $p->task_id,
            'account_id' => $cash, 'currency' => $p->currency, 'debit' => $p->amount,
        ));
        if ($client) {
            $this->_delete_one_ledger(array(
                'source' => 'task_client_payment', 'reference' => (string) $p->task_id,
                'account_id' => $client, 'currency' => $p->currency, 'credit' => $p->amount,
            ));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ------------------------------------------------------------------
    // Vendor payments (money paid TO the vendor)
    //   cash:   credit (cash down)
    //   vendor: debit  (payable down)
    // ------------------------------------------------------------------

    public function add_vendor_payment($task_id, $amount, $currency, $date, $note)
    {
        $task_id = (int) $task_id;
        $amount  = _bc_clean($amount);
        $vendor  = $this->_party($task_id, 'vendor_id');
        $cash    = $this->_cash_id();
        $now     = date('Y-m-d H:i:s');
        $user    = $this->session->userdata('user_id');

        $this->db->trans_start();

        $this->db->insert('task_vendor_payments', array(
            'task_id' => $task_id, 'amount' => $amount, 'currency' => $currency,
            'date' => $date, 'note' => $note, 'recorded_by' => $user, 'created_at' => $now,
        ));

        $this->db->insert('ledger_entries', array(
            'account_id' => $cash, 'currency' => $currency, 'debit' => '0.00', 'credit' => $amount,
            'date' => $date, 'source' => 'task_vendor_payment', 'reference' => (string) $task_id,
            'note' => $note, 'recorded_by' => $user, 'created_at' => $now,
        ));

        if ($vendor) {
            $this->db->insert('ledger_entries', array(
                'account_id' => $vendor, 'currency' => $currency, 'debit' => $amount, 'credit' => '0.00',
                'date' => $date, 'source' => 'task_vendor_payment', 'reference' => (string) $task_id,
                'note' => $note, 'recorded_by' => $user, 'created_at' => $now,
            ));
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete_vendor_payment($payment_id)
    {
        $p = $this->get_vendor_payment($payment_id);
        if (! $p) {
            return FALSE;
        }
        $vendor = $this->_party($p->task_id, 'vendor_id');
        $cash   = $this->_cash_id();

        $this->db->trans_start();
        $this->db->where('id', (int) $payment_id)->delete('task_vendor_payments');
        $this->_delete_one_ledger(array(
            'source' => 'task_vendor_payment', 'reference' => (string) $p->task_id,
            'account_id' => $cash, 'currency' => $p->currency, 'credit' => $p->amount,
        ));
        if ($vendor) {
            $this->_delete_one_ledger(array(
                'source' => 'task_vendor_payment', 'reference' => (string) $p->task_id,
                'account_id' => $vendor, 'currency' => $p->currency, 'debit' => $p->amount,
            ));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ------------------------------------------------------------------
    // Reads
    // ------------------------------------------------------------------

    public function get_client_payments($task_id)
    {
        return $this->db->select('id, task_id, amount, currency, date, note, created_at')
            ->from('task_client_payments')->where('task_id', (int) $task_id)
            ->order_by('date', 'DESC')->order_by('id', 'DESC')->get()->result();
    }

    public function get_vendor_payments($task_id)
    {
        return $this->db->select('id, task_id, amount, currency, date, note, created_at')
            ->from('task_vendor_payments')->where('task_id', (int) $task_id)
            ->order_by('date', 'DESC')->order_by('id', 'DESC')->get()->result();
    }

    public function get_client_payment($id)
    {
        return $this->db->select('id, task_id, amount, currency, date')
            ->from('task_client_payments')->where('id', (int) $id)->get()->row();
    }

    public function get_vendor_payment($id)
    {
        return $this->db->select('id, task_id, amount, currency, date')
            ->from('task_vendor_payments')->where('id', (int) $id)->get()->row();
    }

    /** Per-currency totals received from client. @return array currency=>sum */
    public function sum_client_by_currency($task_id)
    {
        return $this->_sum_by_currency('task_client_payments', $task_id);
    }

    /** Per-currency totals paid to vendor. @return array currency=>sum */
    public function sum_vendor_by_currency($task_id)
    {
        return $this->_sum_by_currency('task_vendor_payments', $task_id);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    protected function _sum_by_currency($table, $task_id)
    {
        $rows = $this->db->select('currency, COALESCE(SUM(amount),0) AS total', FALSE)
            ->from($table)->where('task_id', (int) $task_id)
            ->group_by('currency')->get()->result();
        $out = array();
        foreach (array_keys(CURRENCIES) as $cur) {
            $out[$cur] = '0.00';
        }
        foreach ($rows as $r) {
            $out[$r->currency] = bc_add($r->total, '0');
        }
        return $out;
    }

    protected function _cash_id()
    {
        if ($this->cash_id === null) {
            $row = $this->db->select('id')->from('financial_accounts')
                ->where('type', 'cash')->order_by('id', 'ASC')->limit(1)->get()->row();
            $this->cash_id = $row ? (int) $row->id : 0;
        }
        return $this->cash_id;
    }

    protected function _party($task_id, $column)
    {
        $row = $this->db->select($column)->from('tasks')->where('id', (int) $task_id)->get()->row();
        return $row ? (int) $row->$column : 0;
    }

    /**
     * Delete a single ledger row matching the criteria (LIMIT 1 via id lookup).
     * Identical duplicate payments yield identical rows, so removing either is
     * equivalent — the resulting ledger state is the same.
     */
    protected function _delete_one_ledger($criteria)
    {
        $this->db->select('id')->from('ledger_entries');
        foreach ($criteria as $k => $val) {
            $this->db->where($k, $val);
        }
        $row = $this->db->order_by('id', 'DESC')->limit(1)->get()->row();
        if ($row) {
            $this->db->where('id', (int) $row->id)->delete('ledger_entries');
        }
    }
}
