<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Accounts — financial accounts management (gated by manage_accounts).
 * Create/edit happen in a modal via AJAX (JSON); delete via POST form.
 */
class Accounts extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('manage_accounts');
        $this->load->model('Account_model');
    }

    /** List all accounts. */
    public function index()
    {
        $data = array(
            'page_title'    => 'حساب‌ها',
            'accounts'      => $this->Account_model->get_all(),
            'account_types' => ACCOUNT_TYPES,
            'extra_js'      => '<script src="' . base_url('assets/js/crud-modal.js') . '"></script>',
        );
        $this->render('accounts/index', $data);
    }

    /** JSON: single account (used to populate the edit modal). */
    public function get($id)
    {
        $account = $this->Account_model->get_by_id($id);
        if (! $account) {
            $this->json_response(array('success' => FALSE, 'error' => 'not_found'), 404);
        }
        $this->json_response(array('success' => TRUE, 'data' => $account));
    }

    /** Create (AJAX JSON). */
    public function store()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->_validate()) {
            $this->json_response(array('success' => FALSE, 'errors' => $this->form_validation->error_array()), 422);
        }

        $new_id = $this->Account_model->create($this->_collect());
        if ($new_id) {
            $this->session->set_flashdata('success', 'حساب با موفقیت ایجاد شد.');
            $this->json_response(array('success' => TRUE));
        }
        $this->json_response(array('success' => FALSE, 'error' => 'create_failed'), 500);
    }

    /** Update (AJAX JSON). */
    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->Account_model->get_by_id($id)) {
            $this->json_response(array('success' => FALSE, 'error' => 'not_found'), 404);
        }
        if (! $this->_validate()) {
            $this->json_response(array('success' => FALSE, 'errors' => $this->form_validation->error_array()), 422);
        }

        $this->Account_model->update($id, $this->_collect());
        $this->session->set_flashdata('success', 'حساب با موفقیت بروزرسانی شد.');
        $this->json_response(array('success' => TRUE));
    }

    /** Delete (POST form, blocked if referenced). */
    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $account = $this->Account_model->get_by_id($id);
        if (! $account) {
            show_404();
        }
        if ($account->type === 'cash') {
            $this->session->set_flashdata('error', 'حساب صندوق قابل حذف نیست.');
            redirect('accounts');
        }
        if ($this->Account_model->is_in_use($id)) {
            $this->session->set_flashdata('error', 'این حساب در وظایف یا دفتر کل استفاده شده و قابل حذف نیست.');
            redirect('accounts');
        }

        $this->Account_model->delete($id);
        $this->session->set_flashdata('success', 'حساب حذف شد.');
        redirect('accounts');
    }

    // ------------------------------------------------------------------

    private function _validate()
    {
        $this->form_validation->set_rules('name', 'نام حساب', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('type', 'نوع حساب', 'required|in_list[' . implode(',', array_keys(ACCOUNT_TYPES)) . ']');
        $this->form_validation->set_rules('phone', 'تلفن', 'trim|max_length[30]');
        $this->form_validation->set_rules('note', 'یادداشت', 'trim|max_length[255]');
        return $this->form_validation->run();
    }

    private function _collect()
    {
        return array(
            'name'   => $this->input->post('name', TRUE),
            'type'   => $this->input->post('type', TRUE),
            'phone'  => $this->input->post('phone', TRUE),
            'note'   => $this->input->post('note', TRUE),
            'active' => (int) $this->input->post('active'),
        );
    }
}
