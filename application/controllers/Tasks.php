<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tasks — the core feature (passport batches → vendor → Iran visas).
 * Gated by manage_tasks. The main form is a normal multipart POST (file
 * uploads); payments inside the task view are AJAX.
 */
class Tasks extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('manage_tasks');
        $this->load->model(array('Task_model', 'Passport_model', 'Payment_model',
                                 'Account_model', 'Service_model'));
        $this->load->helper('upload');
        $this->load->library('pagination');
    }

    // ------------------------------------------------------------------
    // List
    // ------------------------------------------------------------------

    public function index()
    {
        $filters = array(
            'status'    => $this->input->get('status', TRUE),
            'client_id' => $this->input->get('client_id', TRUE),
            'vendor_id' => $this->input->get('vendor_id', TRUE),
            'date_from' => from_jalali($this->input->get('date_from', TRUE)),
            'date_to'   => from_jalali($this->input->get('date_to', TRUE)),
            'search'    => $this->input->get('search', TRUE),
        );

        $per_page = PER_PAGE;
        $offset   = (int) $this->input->get('per_page');
        $total    = $this->Task_model->count_filtered($filters);

        $this->_init_pagination(base_url('tasks'), $total, $per_page);

        $data = array(
            'page_title'    => 'وظایف',
            'tasks'         => $this->Task_model->get_filtered($filters, $per_page, $offset),
            'total'         => $total,
            'filters'       => $filters,
            'clients'       => $this->Account_model->get_by_type('client'),
            'vendors'       => $this->Account_model->get_by_type('vendor'),
            'statuses'      => TASK_STATUSES,
            'pagination_links' => $this->pagination->create_links(),
            'extra_js'      => '<script src="' . base_url('assets/js/task-form.js') . '"></script>',
        );
        $this->render('tasks/index', $data);
    }

    // ------------------------------------------------------------------
    // Create / Edit
    // ------------------------------------------------------------------

    public function create()
    {
        $this->_render_form(NULL);
    }

    public function store()
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        if (! $this->_validate()) {
            return $this->_render_form(NULL);
        }

        $task_id = $this->Task_model->create($this->_collect());
        if (! $task_id) {
            $this->session->set_flashdata('error', 'خطا در ایجاد وظیفه.');
            redirect('tasks/create');
        }

        $this->_save_passport_rows($task_id, FALSE);
        $this->session->set_flashdata('success', 'وظیفه با موفقیت ایجاد شد.');
        redirect('tasks/view/' . $task_id);
    }

    public function edit($id)
    {
        $task = $this->Task_model->get_by_id($id);
        if (! $task) {
            show_404();
        }
        $this->_render_form($task);
    }

    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $task = $this->Task_model->get_by_id($id);
        if (! $task) {
            show_404();
        }
        if (! $this->_validate()) {
            return $this->_render_form($task);
        }

        $this->Task_model->update($id, $this->_collect());
        $this->_save_passport_rows($id, TRUE);
        $this->session->set_flashdata('success', 'وظیفه با موفقیت بروزرسانی شد.');
        redirect('tasks/view/' . (int) $id);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }
        $task = $this->Task_model->get_by_id($id);
        if (! $task) {
            show_404();
        }

        // Remove scan files first, then the task (DB rows cascade).
        foreach ($this->Passport_model->get_scan_paths($id) as $p) {
            delete_passport_scan($p);
        }
        @rmdir(passports_dir($id));
        $this->Task_model->delete($id);

        $this->session->set_flashdata('success', 'وظیفه حذف شد.');
        redirect('tasks');
    }

    // ------------------------------------------------------------------
    // Detail
    // ------------------------------------------------------------------

    public function view($id)
    {
        $task = $this->Task_model->get_by_id($id);
        if (! $task) {
            show_404();
        }

        $data = array(
            'page_title'       => 'جزئیات وظیفه #' . (int) $id,
            'task'             => $task,
            'passports'        => $this->Passport_model->get_by_task($id),
            'client_payments'  => $this->Payment_model->get_client_payments($id),
            'vendor_payments'  => $this->Payment_model->get_vendor_payments($id),
            'client_paid'      => $this->Payment_model->sum_client_by_currency($id),
            'vendor_paid'      => $this->Payment_model->sum_vendor_by_currency($id),
            'currencies'       => CURRENCIES,
            'statuses'         => TASK_STATUSES,
            'extra_js'         => '<script src="' . base_url('assets/js/task-payments.js') . '"></script>',
        );
        $this->render('tasks/view', $data);
    }

    // ------------------------------------------------------------------
    // Payments (AJAX)
    // ------------------------------------------------------------------

    public function add_client_payment($task_id)
    {
        $this->_add_payment($task_id, 'client');
    }

    public function add_vendor_payment($task_id)
    {
        $this->_add_payment($task_id, 'vendor');
    }

    public function delete_client_payment($payment_id)
    {
        if ($this->input->method() !== 'post') { show_404(); }
        $this->Payment_model->delete_client_payment($payment_id);
        $this->session->set_flashdata('success', 'پرداخت حذف و دفتر کل اصلاح شد.');
        $this->json_response(array('success' => TRUE));
    }

    public function delete_vendor_payment($payment_id)
    {
        if ($this->input->method() !== 'post') { show_404(); }
        $this->Payment_model->delete_vendor_payment($payment_id);
        $this->session->set_flashdata('success', 'پرداخت حذف و دفتر کل اصلاح شد.');
        $this->json_response(array('success' => TRUE));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function _add_payment($task_id, $side)
    {
        if ($this->input->method() !== 'post') { show_404(); }
        $task = $this->Task_model->get_by_id($task_id);
        if (! $task) {
            $this->json_response(array('success' => FALSE, 'error' => 'not_found'), 404);
        }

        $this->form_validation->set_rules('amount', 'مبلغ', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('currency', 'ارز', 'required|in_list[' . implode(',', array_keys(CURRENCIES)) . ']');
        $this->form_validation->set_rules('date', 'تاریخ', 'required|trim');
        if ($this->form_validation->run() === FALSE) {
            $this->json_response(array('success' => FALSE, 'errors' => $this->form_validation->error_array()), 422);
        }

        $date = from_jalali($this->input->post('date', TRUE));
        if ($date === NULL) {
            $this->json_response(array('success' => FALSE, 'errors' => array('date' => 'تاریخ نامعتبر است.')), 422);
        }

        $amount   = _bc_clean($this->input->post('amount', TRUE));
        $currency = $this->input->post('currency', TRUE);
        $note     = $this->input->post('note', TRUE);

        $ok = ($side === 'client')
            ? $this->Payment_model->add_client_payment($task_id, $amount, $currency, $date, $note)
            : $this->Payment_model->add_vendor_payment($task_id, $amount, $currency, $date, $note);

        if ($ok) {
            $this->session->set_flashdata('success', 'پرداخت ثبت و در دفتر کل درج شد.');
            $this->json_response(array('success' => TRUE));
        }
        $this->json_response(array('success' => FALSE, 'error' => 'post_failed'), 500);
    }

    private function _validate()
    {
        $this->form_validation->set_rules('client_id', 'مشتری', 'required|integer');
        $this->form_validation->set_rules('date', 'تاریخ', 'required|trim');
        $this->form_validation->set_rules('status', 'وضعیت', 'required|in_list[' . implode(',', array_keys(TASK_STATUSES)) . ']');
        $this->form_validation->set_rules('fee_amount', 'مبلغ فیس', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('fee_currency', 'ارز فیس', 'required|in_list[' . implode(',', array_keys(CURRENCIES)) . ']');
        $this->form_validation->set_rules('vendor_cost_amount', 'هزینه فروشنده', 'numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('vendor_cost_currency', 'ارز هزینه فروشنده', 'in_list[' . implode(',', array_keys(CURRENCIES)) . ']');
        $this->form_validation->set_error_delimiters('<div class="form-error mt-1">', '</div>');

        $valid = $this->form_validation->run();
        if ($valid && from_jalali($this->input->post('date', TRUE)) === NULL) {
            $this->form_validation->set_error_delimiters('', '');
            $this->session->set_flashdata('error', 'تاریخ وظیفه نامعتبر است.');
            return FALSE;
        }
        return $valid;
    }

    private function _collect()
    {
        $vendor_id  = (int) $this->input->post('vendor_id');
        $service_id = (int) $this->input->post('service_id');
        return array(
            'client_id'            => (int) $this->input->post('client_id'),
            'vendor_id'            => $vendor_id ?: NULL,
            'service_id'           => $service_id ?: NULL,
            'visa_type'            => $this->input->post('visa_type', TRUE),
            'destination'          => $this->input->post('destination', TRUE) ?: 'Iran',
            'date'                 => from_jalali($this->input->post('date', TRUE)),
            'status'               => $this->input->post('status', TRUE),
            'fee_amount'           => _bc_clean($this->input->post('fee_amount', TRUE)),
            'fee_currency'         => $this->input->post('fee_currency', TRUE),
            'vendor_cost_amount'   => _bc_clean($this->input->post('vendor_cost_amount', TRUE)),
            'vendor_cost_currency' => $this->input->post('vendor_cost_currency', TRUE) ?: 'AFN',
            'note'                 => $this->input->post('note', TRUE),
        );
    }

    /**
     * Insert/update/delete passport rows + handle per-row scan uploads.
     */
    private function _save_passport_rows($task_id, $is_edit)
    {
        $rows = $this->input->post('passport_rows');
        if (! is_array($rows)) {
            $rows = array();
        }

        $existing = $is_edit ? $this->Passport_model->get_ids_by_task($task_id) : array();
        $kept     = array();

        foreach ($rows as $i => $r) {
            $surname  = isset($r['surname']) ? trim($r['surname']) : '';
            $given    = isset($r['given_name']) ? trim($r['given_name']) : '';
            $passport = isset($r['passport_no']) ? trim($r['passport_no']) : '';

            $scan = process_passport_scan('scan_' . $i, $task_id);
            $has_new_file = ! empty($scan['path']);

            // Skip entirely blank rows.
            if ($surname === '' && $given === '' && $passport === '' && ! $has_new_file
                && empty($r['existing_scan']) && empty($r['id'])) {
                continue;
            }

            $row = array(
                'surname'        => $surname,
                'given_name'     => $given,
                'passport_no'    => $passport,
                'dob'            => from_jalali(isset($r['dob']) ? $r['dob'] : ''),
                'place_of_birth' => isset($r['place_of_birth']) ? trim($r['place_of_birth']) : '',
                'issue_date'     => from_jalali(isset($r['issue_date']) ? $r['issue_date'] : ''),
                'expiry_date'    => from_jalali(isset($r['expiry_date']) ? $r['expiry_date'] : ''),
                'gender'         => (isset($r['gender']) && in_array($r['gender'], array('male', 'female'), TRUE)) ? $r['gender'] : NULL,
            );

            if ($has_new_file) {
                $row['scan_path'] = $scan['path'];
                if (! empty($r['existing_scan'])) {
                    delete_passport_scan($r['existing_scan']);
                }
            } elseif (! empty($r['existing_scan'])) {
                $row['scan_path'] = $r['existing_scan'];
            }

            $id = isset($r['id']) ? (int) $r['id'] : 0;
            if ($id && in_array($id, $existing, TRUE)) {
                $this->Passport_model->update($id, $row);
                $kept[] = $id;
            } else {
                $kept[] = $this->Passport_model->insert($task_id, $row);
            }
        }

        // Delete rows removed in the form (+ their files).
        $to_delete = array_diff($existing, $kept);
        if (! empty($to_delete)) {
            foreach ($this->Passport_model->delete_by_ids($to_delete, $task_id) as $p) {
                delete_passport_scan($p);
            }
        }
    }

    private function _render_form($task)
    {
        $passports = $task ? $this->Passport_model->get_by_task($task->id) : array();
        $data = array(
            'page_title' => $task ? 'ویرایش وظیفه' : 'افزودن وظیفه',
            'task'       => $task,
            'passports'  => $passports,
            'clients'    => $this->Account_model->get_by_type('client'),
            'vendors'    => $this->Account_model->get_by_type('vendor'),
            'services'   => $this->Service_model->get_active(),
            'currencies' => CURRENCIES,
            'statuses'   => TASK_STATUSES,
            'form_action'=> $task ? base_url('tasks/update/' . (int) $task->id) : base_url('tasks/store'),
            'extra_js'   => '<script src="' . base_url('assets/js/task-form.js') . '"></script>',
        );
        $this->render('tasks/form', $data);
    }

    private function _init_pagination($base, $total, $per_page)
    {
        $config = array(
            'base_url'    => $base,
            'total_rows'  => $total,
            'per_page'    => $per_page,
            'page_query_string' => TRUE,
            'query_string_segment' => 'per_page',
            'reuse_query_string'   => TRUE,
            'use_page_numbers'     => FALSE,
            'full_tag_open'  => '<ul class="pagination">',
            'full_tag_close' => '</ul>',
            'attributes'     => array('class' => 'page-link'),
            'first_link' => 'اول', 'last_link' => 'آخر',
            'next_link'  => '›',   'prev_link' => '‹',
            'cur_tag_open'  => '<li class="page-item active"><span class="page-link">',
            'cur_tag_close' => '</span></li>',
            'num_tag_open'  => '<li class="page-item">', 'num_tag_close' => '</li>',
            'next_tag_open' => '<li class="page-item">', 'next_tag_close' => '</li>',
            'prev_tag_open' => '<li class="page-item">', 'prev_tag_close' => '</li>',
            'first_tag_open'=> '<li class="page-item">', 'first_tag_close'=> '</li>',
            'last_tag_open' => '<li class="page-item">', 'last_tag_close' => '</li>',
        );
        $this->pagination->initialize($config);
    }
}
