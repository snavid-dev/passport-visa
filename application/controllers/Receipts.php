<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Receipts — manual ledger adjustments (gated by manage_receipts).
 * Each receipt is one ledger entry. Create/edit via AJAX modal.
 */
class Receipts extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('manage_receipts');
        $this->load->model(array('Receipt_model', 'Account_model'));
    }

    public function index()
    {
        $data = array(
            'page_title' => 'رسیدها',
            'receipts'   => $this->Receipt_model->get_all(),
            'accounts'   => $this->Account_model->get_by_type(array_keys(ACCOUNT_TYPES)),
            'currencies' => CURRENCIES,
            'extra_js'   => '<script src="' . base_url('assets/js/crud-modal.js') . '"></script>',
        );
        $this->render('receipts/index', $data);
    }

    /** JSON for the edit modal — shaped into form fields. */
    public function get($id)
    {
        $r = $this->Receipt_model->get_by_id($id);
        if (! $r) {
            $this->json_response(array('success' => FALSE, 'error' => 'not_found'), 404);
        }

        $is_debit = bc_compare($r->debit, '0') > 0;
        $payload = array(
            'id'         => (int) $r->id,
            'account_id' => (int) $r->account_id,
            'currency'   => $r->currency,
            'direction'  => $is_debit ? 'debit' : 'credit',
            'amount'     => $is_debit ? $r->debit : $r->credit,
            'date'       => to_jalali($r->date),   // Shamsi for the input
            'note'       => $r->note,
        );
        $this->json_response(array('success' => TRUE, 'data' => $payload));
    }

    public function store()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->_validate()) {
            $this->json_response(array('success' => FALSE, 'errors' => $this->form_validation->error_array()), 422);
        }

        $data = $this->_collect();
        if ($data['date'] === NULL) {
            $this->json_response(array('success' => FALSE, 'errors' => array('date' => 'تاریخ نامعتبر است.')), 422);
        }

        if ($this->Receipt_model->create($data)) {
            $this->session->set_flashdata('success', 'رسید با موفقیت ثبت شد.');
            $this->json_response(array('success' => TRUE));
        }
        $this->json_response(array('success' => FALSE, 'error' => 'create_failed'), 500);
    }

    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->Receipt_model->get_by_id($id)) {
            $this->json_response(array('success' => FALSE, 'error' => 'not_found'), 404);
        }
        if (! $this->_validate()) {
            $this->json_response(array('success' => FALSE, 'errors' => $this->form_validation->error_array()), 422);
        }

        $data = $this->_collect();
        if ($data['date'] === NULL) {
            $this->json_response(array('success' => FALSE, 'errors' => array('date' => 'تاریخ نامعتبر است.')), 422);
        }

        $this->Receipt_model->update($id, $data);
        $this->session->set_flashdata('success', 'رسید با موفقیت بروزرسانی شد.');
        $this->json_response(array('success' => TRUE));
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->Receipt_model->get_by_id($id)) {
            show_404();
        }
        $this->Receipt_model->delete($id);
        $this->session->set_flashdata('success', 'رسید حذف شد.');
        redirect('receipts');
    }

    // ------------------------------------------------------------------

    private function _validate()
    {
        $this->form_validation->set_rules('account_id', 'حساب', 'required|integer');
        $this->form_validation->set_rules('currency', 'ارز', 'required|in_list[' . implode(',', array_keys(CURRENCIES)) . ']');
        $this->form_validation->set_rules('direction', 'نوع', 'required|in_list[debit,credit]');
        $this->form_validation->set_rules('amount', 'مبلغ', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('date', 'تاریخ', 'required|trim');
        return $this->form_validation->run();
    }

    private function _collect()
    {
        return array(
            'account_id' => (int) $this->input->post('account_id'),
            'currency'   => $this->input->post('currency', TRUE),
            'direction'  => $this->input->post('direction', TRUE),
            'amount'     => _bc_clean($this->input->post('amount', TRUE)),
            'date'       => from_jalali($this->input->post('date', TRUE)),  // → Gregorian or NULL
            'note'       => $this->input->post('note', TRUE),
        );
    }
}
