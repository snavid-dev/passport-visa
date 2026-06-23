<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Services — visa service types (gated by manage_services).
 * Create/edit via AJAX modal; delete via POST form.
 */
class Services extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('manage_services');
        $this->load->model('Service_model');
    }

    public function index()
    {
        $data = array(
            'page_title' => 'خدمات',
            'services'   => $this->Service_model->get_all(),
            'currencies' => CURRENCIES,
            'extra_js'   => '<script src="' . base_url('assets/js/crud-modal.js') . '"></script>',
        );
        $this->render('services/index', $data);
    }

    public function get($id)
    {
        $service = $this->Service_model->get_by_id($id);
        if (! $service) {
            $this->json_response(array('success' => FALSE, 'error' => 'not_found'), 404);
        }
        $this->json_response(array('success' => TRUE, 'data' => $service));
    }

    public function store()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->_validate()) {
            $this->json_response(array('success' => FALSE, 'errors' => $this->form_validation->error_array()), 422);
        }
        if ($this->Service_model->create($this->_collect())) {
            $this->session->set_flashdata('success', 'خدمت با موفقیت ایجاد شد.');
            $this->json_response(array('success' => TRUE));
        }
        $this->json_response(array('success' => FALSE, 'error' => 'create_failed'), 500);
    }

    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->Service_model->get_by_id($id)) {
            $this->json_response(array('success' => FALSE, 'error' => 'not_found'), 404);
        }
        if (! $this->_validate()) {
            $this->json_response(array('success' => FALSE, 'errors' => $this->form_validation->error_array()), 422);
        }
        $this->Service_model->update($id, $this->_collect());
        $this->session->set_flashdata('success', 'خدمت با موفقیت بروزرسانی شد.');
        $this->json_response(array('success' => TRUE));
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $service = $this->Service_model->get_by_id($id);
        if (! $service) {
            show_404();
        }
        if ($this->Service_model->is_in_use($id)) {
            $this->session->set_flashdata('error', 'این خدمت در وظایف استفاده شده و قابل حذف نیست.');
            redirect('services');
        }
        $this->Service_model->delete($id);
        $this->session->set_flashdata('success', 'خدمت حذف شد.');
        redirect('services');
    }

    // ------------------------------------------------------------------

    private function _validate()
    {
        $this->form_validation->set_rules('name', 'نام خدمت', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('default_fee', 'هزینه پیش‌فرض', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('default_currency', 'ارز', 'required|in_list[' . implode(',', array_keys(CURRENCIES)) . ']');
        $this->form_validation->set_rules('visa_type', 'نوع ویزا', 'trim|max_length[100]');
        return $this->form_validation->run();
    }

    private function _collect()
    {
        return array(
            'name'             => $this->input->post('name', TRUE),
            'default_fee'      => _bc_clean($this->input->post('default_fee', TRUE)),
            'default_currency' => $this->input->post('default_currency', TRUE),
            'visa_type'        => $this->input->post('visa_type', TRUE),
            'active'           => (int) $this->input->post('active'),
        );
    }
}
