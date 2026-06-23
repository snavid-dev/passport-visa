<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard — landing page after login.
 * Shows live KPI cards, each gated by the viewer's permissions.
 */
class Dashboard extends MY_Controller {

    public function index()
    {
        $cards = array();

        if ($this->has_permission('manage_tasks')) {
            $this->load->model('Task_model');
            $cards[] = array(
                'label' => 'وظایف باز', 'icon' => 'fa-list-check',
                'value' => number_format($this->Task_model->count_filtered(array('status' => 'open'))),
                'grad'  => 'linear-gradient(135deg,#4f46e5,#818cf8)',
                'link'  => 'tasks?status=open',
            );
        }

        if ($this->has_permission('manage_accounts')) {
            $this->load->model('Account_model');
            $cards[] = array(
                'label' => 'حساب‌های فعال', 'icon' => 'fa-users',
                'value' => number_format($this->Account_model->count_active()),
                'grad'  => 'linear-gradient(135deg,#06b6d4,#22d3ee)',
                'link'  => 'accounts',
            );
        }

        if ($this->has_permission('view_balance_sheet')) {
            $this->load->model('Report_model');
            $afn = '0.00';
            foreach ($this->Report_model->cash_position() as $row) {
                $afn = bc_add($afn, $row->bal_AFN);
            }
            $cards[] = array(
                'label' => 'موجودی صندوق (افغانی)', 'icon' => 'fa-sack-dollar',
                'value' => format_money($afn, NULL, FALSE),
                'grad'  => 'linear-gradient(135deg,#10b981,#34d399)',
                'link'  => 'balance_sheet',
            );
        }

        if ($this->has_permission('view_reports')) {
            $this->load->model('Report_model');
            $cards[] = array(
                'label' => 'موارد معوق', 'icon' => 'fa-triangle-exclamation',
                'value' => number_format(count($this->Report_model->outstanding())),
                'grad'  => 'linear-gradient(135deg,#f59e0b,#fbbf24)',
                'link'  => 'reports/outstanding',
            );
        }

        $this->render('dashboard/index', array(
            'page_title' => 'داشبورد',
            'cards'      => $cards,
        ));
    }
}
