<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller — base controller for all authenticated pages.
 *
 * Provides:
 *   - session auth guard (redirect to login, or JSON 401 for AJAX)
 *   - current user + role + permission loading
 *   - RBAC helpers: has_permission(), require_permission()
 *   - layout rendering: render(), render_auth()
 *   - JSON response helper for AJAX endpoints
 *
 * Auth (login/logout) extends CI_Controller directly, NOT this class.
 */
class MY_Controller extends CI_Controller {

    /** @var object|null Current authenticated user with ->permissions array */
    protected $current_user = null;

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set(APP_TIMEZONE);
        $this->load->helper(array('url', 'html', 'form', 'jalali', 'money'));

        // ---- Authentication guard -------------------------------------
        if (! $this->session->userdata('user_id')) {
            if ($this->_is_ajax()) {
                $this->json_response(array('success' => FALSE, 'error' => 'unauthorized'), 401);
            }
            redirect('login');
        }

        // ---- Load current user + permissions --------------------------
        $this->load->model('User_model');
        $this->current_user = $this->User_model->get_with_permissions(
            $this->session->userdata('user_id')
        );

        // Session points to a missing/disabled user — force re-login.
        if (! $this->current_user) {
            $this->session->sess_destroy();
            if ($this->_is_ajax()) {
                $this->json_response(array('success' => FALSE, 'error' => 'unauthorized'), 401);
            }
            redirect('login');
        }

        // Make permission checks available to controllers + views.
        $this->load->library('permission');
        $this->permission->init($this->current_user);
    }

    // ------------------------------------------------------------------
    // RBAC
    // ------------------------------------------------------------------

    /**
     * Gate a controller action to a permission key. Halts with 403 if denied.
     */
    protected function require_permission($key)
    {
        if (! $this->has_permission($key)) {
            if ($this->_is_ajax()) {
                $this->json_response(array('success' => FALSE, 'error' => 'forbidden'), 403);
            }
            show_error('شما به این بخش دسترسی ندارید.', 403, 'دسترسی غیرمجاز');
        }
    }

    /**
     * Check whether the current user holds a permission key.
     */
    protected function has_permission($key)
    {
        if (! $this->current_user) {
            return FALSE;
        }
        $perms = isset($this->current_user->permissions) ? $this->current_user->permissions : array();
        return in_array($key, $perms, TRUE);
    }

    // ------------------------------------------------------------------
    // Rendering
    // ------------------------------------------------------------------

    /**
     * Render a module view inside the main (sidebar) layout.
     */
    protected function render($view, $data = array())
    {
        $data['current_user'] = $this->current_user;
        $data['content_view'] = $view;
        $this->load->view('_layouts/main', $data);
    }

    /**
     * Render a view inside the minimal auth layout (login page).
     */
    protected function render_auth($view, $data = array())
    {
        $data['content_view'] = $view;
        $this->load->view('_layouts/auth', $data);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Emit a JSON response and stop execution.
     */
    protected function json_response($payload, $status = 200)
    {
        $this->output
             ->set_status_header($status)
             ->set_content_type('application/json', 'utf-8')
             ->set_output(json_encode($payload));
        // Stop further processing immediately.
        exit;
    }

    /**
     * Is the current request an AJAX (XHR) call?
     */
    protected function _is_ajax()
    {
        return $this->input->is_ajax_request();
    }
}
