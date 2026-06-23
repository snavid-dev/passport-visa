<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reports — 10 read-only reports (gated by view_reports).
 */
class Reports extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('view_reports');
        $this->load->model(array('Report_model', 'Account_model'));
    }

    /** Report hub. */
    public function index()
    {
        $this->render('reports/index', array('page_title' => 'گزارشات'));
    }

    // ---- 1. Balance sheet ----
    public function balance_sheet()
    {
        $rows = $this->Report_model->balance_sheet();
        $totals = $this->_currency_totals($rows);
        $this->_report('reports/balance_sheet', 'گزارش ترازنامه', array(
            'rows' => $rows, 'totals' => $totals,
        ));
    }

    // ---- 2. Account statement ----
    public function account_statement()
    {
        $r = $this->_range();
        $account_id = (int) $this->input->get('account_id');
        $currency   = $this->input->get('currency', TRUE);
        $currency   = in_array($currency, array_keys(CURRENCIES), TRUE) ? $currency : 'AFN';

        $rows = $opening = $account = NULL;
        $running = '0.00';
        if ($account_id) {
            $account = $this->Account_model->get_by_id($account_id);
            $opening = $this->Report_model->opening_balance($account_id, $r['from'], $currency);
            $rows    = $this->Report_model->account_statement($account_id, $r['from'], $r['to'], $currency);
        }

        $this->_report('reports/account_statement', 'صورتحساب حساب', array(
            'accounts'    => $this->Account_model->get_all(),
            'account_id'  => $account_id,
            'account'     => $account,
            'currency'    => $currency,
            'rows'        => $rows,
            'opening'     => $opening,
        ));
    }

    // ---- 3. Cash position ----
    public function cash_position()
    {
        $rows = $this->Report_model->cash_position();
        $this->_report('reports/cash_position', 'وضعیت نقدینگی', array(
            'rows'   => $rows,
            'totals' => $this->_currency_totals($rows),
        ));
    }

    // ---- 4. Income vs expense ----
    public function income_expense()
    {
        $r = $this->_range();
        $this->_report('reports/income_expense', 'درآمد و مصارف', array(
            'data' => $this->Report_model->income_expense($r['from'], $r['to']),
        ));
    }

    // ---- 5. Tasks report ----
    public function tasks()
    {
        $r = $this->_range();
        $this->_report('reports/tasks', 'گزارش وظایف', array(
            'summary'  => $this->Report_model->tasks_summary($r['from'], $r['to']),
            'statuses' => TASK_STATUSES,
        ));
    }

    // ---- 6. Profit ----
    public function profit()
    {
        $r = $this->_range();
        $this->_report('reports/profit', 'گزارش سود', array(
            'rows'  => $this->Report_model->profit($r['from'], $r['to']),
            'tasks' => $this->Report_model->profit_tasks($r['from'], $r['to']),
        ));
    }

    // ---- 7. Outstanding ----
    public function outstanding()
    {
        $this->_report('reports/outstanding', 'مانده‌های معوق', array(
            'rows' => $this->Report_model->outstanding(),
        ));
    }

    // ---- 8. Client report ----
    public function client()
    {
        $r = $this->_range();
        $this->_report('reports/client', 'گزارش مشتریان', array(
            'map' => $this->Report_model->client_report($r['from'], $r['to']),
        ));
    }

    // ---- 9. Vendor report ----
    public function vendor()
    {
        $r = $this->_range();
        $this->_report('reports/vendor', 'گزارش فروشندگان', array(
            'map' => $this->Report_model->vendor_report($r['from'], $r['to']),
        ));
    }

    // ---- 10. Volume ----
    public function volume()
    {
        $r = $this->_range();
        $this->_report('reports/volume', 'حجم پاسپورت / نوع ویزا', array(
            'rows' => $this->Report_model->volume($r['from'], $r['to']),
        ));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** Parse a Jalali date range from GET into Gregorian + keep raw for the form. */
    private function _range()
    {
        return array(
            'from'     => from_jalali($this->input->get('date_from', TRUE)),
            'to'       => from_jalali($this->input->get('date_to', TRUE)),
            'from_raw' => $this->input->get('date_from', TRUE),
            'to_raw'   => $this->input->get('date_to', TRUE),
        );
    }

    /** Sum bal_AFN/bal_USD/bal_TOMAN columns across rows. */
    private function _currency_totals($rows)
    {
        $totals = array();
        foreach (array_keys(CURRENCIES) as $cur) { $totals[$cur] = '0.00'; }
        foreach ($rows as $row) {
            foreach (array_keys(CURRENCIES) as $cur) {
                $field = 'bal_' . $cur;
                if (isset($row->$field)) { $totals[$cur] = bc_add($totals[$cur], $row->$field); }
            }
        }
        return $totals;
    }

    /** Render a report view with shared bits (title, currencies, date range). */
    private function _report($view, $title, $data)
    {
        $r = $this->_range();
        $data = array_merge(array(
            'page_title' => $title,
            'currencies' => CURRENCIES,
            'date_from'  => $r['from_raw'],
            'date_to'    => $r['to_raw'],
        ), $data);
        $this->render($view, $data);
    }
}
