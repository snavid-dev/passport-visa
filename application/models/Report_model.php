<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Report_model — read-only reporting queries.
 *
 * Currencies (AFN/USD/TOMAN) are independent and never converted. Balance
 * sheet style queries pivot per currency with conditional SUM. Heavier report
 * caching is deferred to Phase 6 hardening.
 */
class Report_model extends CI_Model {

    /** Conditional per-currency net-balance SELECT fragment (debit - credit). */
    protected function _currency_balance_select($expr = 'le.debit - le.credit')
    {
        $parts = array();
        foreach (array_keys(CURRENCIES) as $cur) {
            $parts[] = "COALESCE(SUM(CASE WHEN le.currency = '" . $cur . "' THEN " . $expr . " ELSE 0 END), 0) AS bal_" . $cur;
        }
        return implode(', ', $parts);
    }

    // ============================================================
    // 1. Balance sheet — every account × 3 currencies
    // ============================================================
    public function balance_sheet($only_active = FALSE)
    {
        $this->db->select('a.id, a.name, a.type')
                 ->select($this->_currency_balance_select(), FALSE)
                 ->from('financial_accounts a')
                 ->join('ledger_entries le', 'le.account_id = a.id', 'left');
        if ($only_active) {
            $this->db->where('a.active', 1);
        }
        return $this->db->group_by('a.id')->order_by('a.type')->order_by('a.name')->get()->result();
    }

    // ============================================================
    // 2. Account statement — one account, date range (+ optional currency)
    // ============================================================
    public function account_statement($account_id, $from = NULL, $to = NULL, $currency = NULL)
    {
        $this->db->select('le.id, le.currency, le.debit, le.credit, le.date, le.source, le.reference, le.note')
                 ->from('ledger_entries le')
                 ->where('le.account_id', (int) $account_id);
        if ($from)     { $this->db->where('le.date >=', $from); }
        if ($to)       { $this->db->where('le.date <=', $to); }
        if ($currency) { $this->db->where('le.currency', $currency); }
        return $this->db->order_by('le.date', 'ASC')->order_by('le.id', 'ASC')->get()->result();
    }

    /** Opening balance before a date for an account (per currency or one). */
    public function opening_balance($account_id, $before_date, $currency)
    {
        $this->db->select('COALESCE(SUM(debit - credit),0) AS bal', FALSE)
                 ->from('ledger_entries')
                 ->where('account_id', (int) $account_id)
                 ->where('currency', $currency);
        if ($before_date) { $this->db->where('date <', $before_date); }
        $row = $this->db->get()->row();
        return $row ? $row->bal : '0.00';
    }

    // ============================================================
    // 3. Cash position — cash accounts per currency
    // ============================================================
    public function cash_position()
    {
        return $this->db->select('a.id, a.name')
            ->select($this->_currency_balance_select(), FALSE)
            ->from('financial_accounts a')
            ->join('ledger_entries le', 'le.account_id = a.id', 'left')
            ->where('a.type', 'cash')
            ->group_by('a.id')->order_by('a.name')->get()->result();
    }

    // ============================================================
    // 4. Income vs expense — per currency, date range
    //    income  = credits on income-type accounts
    //    expense = debits  on expense-type accounts
    // ============================================================
    public function income_expense($from = NULL, $to = NULL)
    {
        $out = array();
        foreach (array_keys(CURRENCIES) as $cur) {
            $out[$cur] = array('income' => '0.00', 'expense' => '0.00');
        }

        // Income
        $this->db->select('le.currency, COALESCE(SUM(le.credit),0) AS total', FALSE)
                 ->from('ledger_entries le')
                 ->join('financial_accounts a', 'a.id = le.account_id')
                 ->where('a.type', 'income');
        if ($from) { $this->db->where('le.date >=', $from); }
        if ($to)   { $this->db->where('le.date <=', $to); }
        foreach ($this->db->group_by('le.currency')->get()->result() as $r) {
            if (isset($out[$r->currency])) { $out[$r->currency]['income'] = bc_add($r->total, '0'); }
        }

        // Expense
        $this->db->select('le.currency, COALESCE(SUM(le.debit),0) AS total', FALSE)
                 ->from('ledger_entries le')
                 ->join('financial_accounts a', 'a.id = le.account_id')
                 ->where('a.type', 'expense');
        if ($from) { $this->db->where('le.date >=', $from); }
        if ($to)   { $this->db->where('le.date <=', $to); }
        foreach ($this->db->group_by('le.currency')->get()->result() as $r) {
            if (isset($out[$r->currency])) { $out[$r->currency]['expense'] = bc_add($r->total, '0'); }
        }

        return $out;
    }

    // ============================================================
    // 5. Tasks report — counts + fee/cost sums, date range
    // ============================================================
    public function tasks_summary($from = NULL, $to = NULL)
    {
        $apply = function () use ($from, $to) {
            if ($from) { $this->db->where('date >=', $from); }
            if ($to)   { $this->db->where('date <=', $to); }
        };

        $status = array();
        $this->db->select('status, COUNT(*) AS n')->from('tasks');
        $apply();
        foreach ($this->db->group_by('status')->get()->result() as $r) {
            $status[$r->status] = (int) $r->n;
        }

        $fees = array(); $costs = array();
        $this->db->select('fee_currency AS cur, COALESCE(SUM(fee_amount),0) AS total', FALSE)->from('tasks');
        $apply();
        foreach ($this->db->group_by('fee_currency')->get()->result() as $r) { $fees[$r->cur] = bc_add($r->total, '0'); }

        $this->db->select('vendor_cost_currency AS cur, COALESCE(SUM(vendor_cost_amount),0) AS total', FALSE)->from('tasks');
        $apply();
        foreach ($this->db->group_by('vendor_cost_currency')->get()->result() as $r) { $costs[$r->cur] = bc_add($r->total, '0'); }

        $this->db->from('tasks'); $apply();
        $total_tasks = $this->db->count_all_results();

        $this->db->select('COUNT(*) AS n')->from('task_passports p')->join('tasks t', 't.id = p.task_id');
        $apply();
        $prow = $this->db->get()->row();

        return array(
            'total_tasks' => $total_tasks,
            'by_status'   => $status,
            'fees'        => $fees,
            'costs'       => $costs,
            'passports'   => $prow ? (int) $prow->n : 0,
        );
    }

    // ============================================================
    // 6. Profit report — only tasks where fee & cost share a currency
    // ============================================================
    public function profit($from = NULL, $to = NULL)
    {
        $this->db->select('fee_currency AS cur,
                           COALESCE(SUM(fee_amount),0) AS fee,
                           COALESCE(SUM(vendor_cost_amount),0) AS cost,
                           COALESCE(SUM(fee_amount - vendor_cost_amount),0) AS profit', FALSE)
                 ->from('tasks')
                 ->where('fee_currency = vendor_cost_currency', NULL, FALSE)
                 ->where('status !=', 'cancelled');
        if ($from) { $this->db->where('date >=', $from); }
        if ($to)   { $this->db->where('date <=', $to); }
        return $this->db->group_by('fee_currency')->get()->result();
    }

    /** Per-task profit list (same-currency tasks only). */
    public function profit_tasks($from = NULL, $to = NULL)
    {
        $this->db->select('t.id, t.date, t.fee_amount, t.fee_currency, t.vendor_cost_amount,
                           (t.fee_amount - t.vendor_cost_amount) AS profit, c.name AS client_name', FALSE)
                 ->from('tasks t')
                 ->join('financial_accounts c', 'c.id = t.client_id', 'left')
                 ->where('t.fee_currency = t.vendor_cost_currency', NULL, FALSE)
                 ->where('t.status !=', 'cancelled');
        if ($from) { $this->db->where('t.date >=', $from); }
        if ($to)   { $this->db->where('t.date <=', $to); }
        return $this->db->order_by('t.date', 'DESC')->get()->result();
    }

    // ============================================================
    // 7. Outstanding balances — per task, client & vendor side
    // ============================================================
    public function outstanding()
    {
        // Client side: fee (in fee_currency) minus client payments in that currency.
        $sql = "
            SELECT t.id, t.date, t.fee_currency AS currency,
                   c.name AS client_name, v.name AS vendor_name,
                   t.fee_amount AS due,
                   COALESCE((SELECT SUM(amount) FROM task_client_payments p
                             WHERE p.task_id = t.id AND p.currency = t.fee_currency), 0) AS paid,
                   'client' AS side
            FROM tasks t
            LEFT JOIN financial_accounts c ON c.id = t.client_id
            LEFT JOIN financial_accounts v ON v.id = t.vendor_id
            WHERE t.status != 'cancelled' AND t.fee_amount > 0
            HAVING due - paid > 0.005

            UNION ALL

            SELECT t.id, t.date, t.vendor_cost_currency AS currency,
                   c.name AS client_name, v.name AS vendor_name,
                   t.vendor_cost_amount AS due,
                   COALESCE((SELECT SUM(amount) FROM task_vendor_payments p
                             WHERE p.task_id = t.id AND p.currency = t.vendor_cost_currency), 0) AS paid,
                   'vendor' AS side
            FROM tasks t
            LEFT JOIN financial_accounts c ON c.id = t.client_id
            LEFT JOIN financial_accounts v ON v.id = t.vendor_id
            WHERE t.status != 'cancelled' AND t.vendor_cost_amount > 0
            HAVING due - paid > 0.005

            ORDER BY date DESC
        ";
        return $this->db->query($sql)->result();
    }

    // ============================================================
    // 8. Client report — per client: billed vs paid, per currency
    // ============================================================
    public function client_report($from = NULL, $to = NULL)
    {
        return $this->_party_report('client', 'client_id', 'task_client_payments', $from, $to);
    }

    // ============================================================
    // 9. Vendor report — per vendor: cost vs paid, per currency
    // ============================================================
    public function vendor_report($from = NULL, $to = NULL)
    {
        return $this->_party_report('vendor', 'vendor_id', 'task_vendor_payments', $from, $to);
    }

    /**
     * Shared client/vendor aggregation: per party + currency, billed vs paid.
     */
    protected function _party_report($type, $task_col, $pay_table, $from, $to)
    {
        $amount_col   = ($type === 'client') ? 't.fee_amount'   : 't.vendor_cost_amount';
        $currency_col = ($type === 'client') ? 't.fee_currency' : 't.vendor_cost_currency';
        $date_cond    = '';
        if ($from) { $date_cond .= " AND t.date >= " . $this->db->escape($from); }
        if ($to)   { $date_cond .= " AND t.date <= " . $this->db->escape($to); }

        // Billed per party + currency.
        $billed_sql = "
            SELECT a.id AS account_id, a.name, {$currency_col} AS currency,
                   COALESCE(SUM({$amount_col}),0) AS billed
            FROM tasks t
            JOIN financial_accounts a ON a.id = t.{$task_col}
            WHERE t.status != 'cancelled' {$date_cond}
            GROUP BY a.id, currency";
        $billed = $this->db->query($billed_sql)->result();

        // Paid per party + currency (join payments → task → party).
        $pay_date = '';
        if ($from) { $pay_date .= " AND p.date >= " . $this->db->escape($from); }
        if ($to)   { $pay_date .= " AND p.date <= " . $this->db->escape($to); }
        $paid_sql = "
            SELECT a.id AS account_id, p.currency AS currency,
                   COALESCE(SUM(p.amount),0) AS paid
            FROM {$pay_table} p
            JOIN tasks t ON t.id = p.task_id
            JOIN financial_accounts a ON a.id = t.{$task_col}
            WHERE 1=1 {$pay_date}
            GROUP BY a.id, p.currency";
        $paid_rows = $this->db->query($paid_sql)->result();

        // Merge into account → currency → [billed, paid].
        $map = array();
        foreach ($billed as $b) {
            $map[$b->account_id]['name'] = $b->name;
            $map[$b->account_id]['cur'][$b->currency] = array('billed' => bc_add($b->billed, '0'), 'paid' => '0.00');
        }
        foreach ($paid_rows as $p) {
            if (! isset($map[$p->account_id])) {
                $nm = $this->db->select('name')->from('financial_accounts')->where('id', $p->account_id)->get()->row();
                $map[$p->account_id]['name'] = $nm ? $nm->name : '';
            }
            if (! isset($map[$p->account_id]['cur'][$p->currency])) {
                $map[$p->account_id]['cur'][$p->currency] = array('billed' => '0.00', 'paid' => '0.00');
            }
            $map[$p->account_id]['cur'][$p->currency]['paid'] = bc_add($p->paid, '0');
        }
        return $map;
    }

    // ============================================================
    // 10. Passport / visa-type volume — counts by visa type
    // ============================================================
    public function volume($from = NULL, $to = NULL)
    {
        $this->db->select("COALESCE(NULLIF(t.visa_type,''),'(نامشخص)') AS visa_type,
                           COUNT(DISTINCT t.id) AS task_count,
                           COUNT(p.id) AS passport_count", FALSE)
                 ->from('tasks t')
                 ->join('task_passports p', 'p.task_id = t.id', 'left');
        if ($from) { $this->db->where('t.date >=', $from); }
        if ($to)   { $this->db->where('t.date <=', $to); }
        return $this->db->group_by('visa_type')->order_by('passport_count', 'DESC')->get()->result();
    }
}
