<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Balance_sheet — real-time per-currency balances for every account
 * (gated by view_balance_sheet).
 */
class Balance_sheet extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('view_balance_sheet');
        $this->load->model('Report_model');
        // Cache the balance-sheet aggregate; invalidated on any POST mutation.
        $this->db->cache_on();
    }

    public function index()
    {
        $rows = $this->Report_model->balance_sheet();

        // Column totals per currency.
        $totals = array();
        foreach (array_keys(CURRENCIES) as $cur) {
            $totals[$cur] = '0.00';
        }
        foreach ($rows as $r) {
            foreach (array_keys(CURRENCIES) as $cur) {
                $field = 'bal_' . $cur;
                $totals[$cur] = bc_add($totals[$cur], $r->$field);
            }
        }

        $data = array(
            'page_title'    => 'ترازنامه',
            'rows'          => $rows,
            'totals'        => $totals,
            'currencies'    => CURRENCIES,
            'account_types' => ACCOUNT_TYPES,
        );
        $this->render('balance_sheet/index', $data);
    }
}
