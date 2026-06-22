<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth — session login / logout.
 *
 * Extends CI_Controller directly (NOT MY_Controller) so the login page
 * is reachable without an authenticated session.
 */
class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set(APP_TIMEZONE);
        $this->load->helper(array('url', 'form'));
        $this->load->library(array('session', 'form_validation'));
        $this->load->model('User_model');
    }

    /** Default → login page. */
    public function index()
    {
        redirect('login');
    }

    /**
     * GET  → render the login form.
     * POST → validate credentials and start a session.
     */
    public function login()
    {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('username', 'نام کاربری', 'required|trim');
            $this->form_validation->set_rules('password', 'رمز عبور', 'required');
            $this->form_validation->set_error_delimiters('<div class="form-error mt-1">', '</div>');

            if ($this->form_validation->run() === TRUE) {
                $username = $this->input->post('username', TRUE);
                $password = $this->input->post('password', FALSE); // do not XSS-clean a password

                $user = $this->User_model->get_by_username($username);

                if ($user && password_verify($password, $user->password)) {
                    // Prevent session fixation.
                    $this->session->sess_regenerate(TRUE);
                    $this->session->set_userdata(array(
                        'user_id'   => (int) $user->id,
                        'user_name' => $user->name,
                        'role_id'   => (int) $user->role_id,
                    ));
                    $this->session->set_flashdata('success', 'خوش آمدید، ' . $user->name);
                    redirect('dashboard');
                }

                $this->session->set_flashdata('error', 'نام کاربری یا رمز عبور نادرست است.');
                redirect('login');
            }
        }

        $data['page_title'] = 'ورود';
        $data['content_view'] = 'auth/login';
        $this->load->view('_layouts/auth', $data);
    }

    /** Destroy the session and return to login. */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }
}
