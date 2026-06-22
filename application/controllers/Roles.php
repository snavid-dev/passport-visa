<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Roles — RBAC role management (gated by manage_roles).
 */
class Roles extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('manage_roles');
        $this->load->model('Role_model');
    }

    /** List all roles. */
    public function index()
    {
        $data = array(
            'page_title' => 'نقش‌ها',
            'roles'      => $this->Role_model->get_all(),
        );
        $this->render('roles/index', $data);
    }

    /** Show the create form. */
    public function create()
    {
        $data = array(
            'page_title'      => 'افزودن نقش',
            'role'            => NULL,
            'permissions'     => $this->Role_model->get_all_permissions(),
            'assigned'        => array(),
            'form_action'     => base_url('roles/store'),
        );
        $this->render('roles/form', $data);
    }

    /** Persist a new role. */
    public function store()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (! $this->_validate()) {
            return $this->create_with_errors();
        }

        $name = $this->input->post('name', TRUE);
        if ($this->Role_model->name_exists($name)) {
            $this->session->set_flashdata('error', 'نقشی با این نام از قبل وجود دارد.');
            redirect('roles/create');
        }

        $perms  = (array) $this->input->post('permissions');
        $new_id = $this->Role_model->create(array('name' => $name), $perms);

        if ($new_id) {
            $this->session->set_flashdata('success', 'نقش با موفقیت ایجاد شد.');
            redirect('roles');
        }

        $this->session->set_flashdata('error', 'خطا در ایجاد نقش.');
        redirect('roles/create');
    }

    /** Show the edit form. */
    public function edit($id)
    {
        $role = $this->Role_model->get_by_id($id);
        if (! $role) {
            show_404();
        }

        $data = array(
            'page_title'  => 'ویرایش نقش',
            'role'        => $role,
            'permissions' => $this->Role_model->get_all_permissions(),
            'assigned'    => $this->Role_model->get_permission_ids($id),
            'form_action' => base_url('roles/update/' . (int) $id),
        );
        $this->render('roles/form', $data);
    }

    /** Persist an edited role. */
    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $role = $this->Role_model->get_by_id($id);
        if (! $role) {
            show_404();
        }

        if (! $this->_validate()) {
            return $this->edit_with_errors($id);
        }

        $name = $this->input->post('name', TRUE);
        if ($this->Role_model->name_exists($name, $id)) {
            $this->session->set_flashdata('error', 'نقشی با این نام از قبل وجود دارد.');
            redirect('roles/edit/' . (int) $id);
        }

        $perms = (array) $this->input->post('permissions');
        $this->Role_model->update($id, array('name' => $name), $perms);

        $this->session->set_flashdata('success', 'نقش با موفقیت بروزرسانی شد.');
        redirect('roles');
    }

    /** Delete a role (blocked if assigned to any user). */
    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $role = $this->Role_model->get_by_id($id);
        if (! $role) {
            show_404();
        }

        if ($this->Role_model->is_in_use($id)) {
            $this->session->set_flashdata('error', 'این نقش به کاربرانی اختصاص یافته و قابل حذف نیست.');
            redirect('roles');
        }

        $this->Role_model->delete($id);
        $this->session->set_flashdata('success', 'نقش حذف شد.');
        redirect('roles');
    }

    // ------------------------------------------------------------------
    // Validation helpers
    // ------------------------------------------------------------------

    private function _validate()
    {
        $this->form_validation->set_rules('name', 'نام نقش', 'required|trim|max_length[100]');
        $this->form_validation->set_error_delimiters('<div class="form-error mt-1">', '</div>');
        return $this->form_validation->run();
    }

    private function create_with_errors()
    {
        $data = array(
            'page_title'  => 'افزودن نقش',
            'role'        => NULL,
            'permissions' => $this->Role_model->get_all_permissions(),
            'assigned'    => array_map('intval', (array) $this->input->post('permissions')),
            'form_action' => base_url('roles/store'),
        );
        $this->render('roles/form', $data);
    }

    private function edit_with_errors($id)
    {
        $data = array(
            'page_title'  => 'ویرایش نقش',
            'role'        => $this->Role_model->get_by_id($id),
            'permissions' => $this->Role_model->get_all_permissions(),
            'assigned'    => array_map('intval', (array) $this->input->post('permissions')),
            'form_action' => base_url('roles/update/' . (int) $id),
        );
        $this->render('roles/form', $data);
    }
}
