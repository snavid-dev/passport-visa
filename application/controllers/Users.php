<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users — user management (gated by manage_users).
 */
class Users extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('manage_users');
        $this->load->model(array('User_model', 'Role_model'));
    }

    /** List all users. */
    public function index()
    {
        $data = array(
            'page_title' => 'کاربران',
            'users'      => $this->User_model->get_all(),
        );
        $this->render('users/index', $data);
    }

    /** Show the create form. */
    public function create()
    {
        $data = array(
            'page_title'  => 'افزودن کاربر',
            'user'        => NULL,
            'roles'       => $this->Role_model->get_all(),
            'form_action' => base_url('users/store'),
        );
        $this->render('users/form', $data);
    }

    /** Persist a new user. */
    public function store()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if (! $this->_validate(FALSE)) {
            return $this->_form_with_errors(NULL);
        }

        $username = $this->input->post('username', TRUE);
        if ($this->User_model->username_exists($username)) {
            $this->session->set_flashdata('error', 'این نام کاربری قبلاً ثبت شده است.');
            redirect('users/create');
        }

        $new_id = $this->User_model->create($this->_collect());

        if ($new_id) {
            $this->session->set_flashdata('success', 'کاربر با موفقیت ایجاد شد.');
            redirect('users');
        }

        $this->session->set_flashdata('error', 'خطا در ایجاد کاربر.');
        redirect('users/create');
    }

    /** Show the edit form. */
    public function edit($id)
    {
        $user = $this->User_model->get_by_id($id);
        if (! $user) {
            show_404();
        }

        $data = array(
            'page_title'  => 'ویرایش کاربر',
            'user'        => $user,
            'roles'       => $this->Role_model->get_all(),
            'form_action' => base_url('users/update/' . (int) $id),
        );
        $this->render('users/form', $data);
    }

    /** Persist an edited user. */
    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $user = $this->User_model->get_by_id($id);
        if (! $user) {
            show_404();
        }

        if (! $this->_validate(TRUE)) {
            return $this->_form_with_errors($user);
        }

        $username = $this->input->post('username', TRUE);
        if ($this->User_model->username_exists($username, $id)) {
            $this->session->set_flashdata('error', 'این نام کاربری قبلاً ثبت شده است.');
            redirect('users/edit/' . (int) $id);
        }

        // Guard: do not deactivate the last remaining active user.
        $active = (int) $this->input->post('active');
        if ($active === 0 && (int) $user->id === (int) $this->current_user->id) {
            $this->session->set_flashdata('error', 'نمی‌توانید حساب کاربری خود را غیرفعال کنید.');
            redirect('users/edit/' . (int) $id);
        }

        $this->User_model->update($id, $this->_collect());

        $this->session->set_flashdata('success', 'کاربر با موفقیت بروزرسانی شد.');
        redirect('users');
    }

    /** Delete a user (cannot delete self). */
    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $user = $this->User_model->get_by_id($id);
        if (! $user) {
            show_404();
        }

        if ((int) $user->id === (int) $this->current_user->id) {
            $this->session->set_flashdata('error', 'نمی‌توانید حساب کاربری خود را حذف کنید.');
            redirect('users');
        }

        $this->User_model->delete($id);
        $this->session->set_flashdata('success', 'کاربر حذف شد.');
        redirect('users');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * @param  bool $is_edit  password optional on edit
     * @return bool
     */
    private function _validate($is_edit)
    {
        $this->form_validation->set_rules('name', 'نام', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('username', 'نام کاربری', 'required|trim|alpha_dash|max_length[100]');
        $this->form_validation->set_rules('role_id', 'نقش', 'required|integer');
        $this->form_validation->set_rules('email', 'ایمیل', 'trim|valid_email|max_length[150]');
        $this->form_validation->set_rules('phone', 'تلفن', 'trim|max_length[30]');

        $pw_rules = $is_edit ? 'trim|min_length[6]' : 'required|trim|min_length[6]';
        $this->form_validation->set_rules('password', 'رمز عبور', $pw_rules);

        $this->form_validation->set_error_delimiters('<div class="form-error mt-1">', '</div>');
        return $this->form_validation->run();
    }

    /** Gather sanitized fields for the model. */
    private function _collect()
    {
        return array(
            'name'     => $this->input->post('name', TRUE),
            'username' => $this->input->post('username', TRUE),
            'email'    => $this->input->post('email', TRUE),
            'phone'    => $this->input->post('phone', TRUE),
            'role_id'  => (int) $this->input->post('role_id'),
            'active'   => (int) $this->input->post('active'),
            'password' => $this->input->post('password', FALSE),
        );
    }

    private function _form_with_errors($user)
    {
        $is_edit = ($user !== NULL);
        $data = array(
            'page_title'  => $is_edit ? 'ویرایش کاربر' : 'افزودن کاربر',
            'user'        => $user,
            'roles'       => $this->Role_model->get_all(),
            'form_action' => $is_edit ? base_url('users/update/' . (int) $user->id) : base_url('users/store'),
        );
        $this->render('users/form', $data);
    }
}
