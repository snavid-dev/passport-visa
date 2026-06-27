<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Task_model — the core entity (one passport batch → one vendor → Iran visas).
 */
class Task_model extends CI_Model {

    /**
     * Apply list filters to the active query builder.
     *
     * @param array $f  status, client_id, vendor_id, date_from, date_to, search
     */
    protected function _apply_filters($f)
    {
        if (! empty($f['status'])) {
            $this->db->where('t.status', $f['status']);
        }
        if (! empty($f['client_id'])) {
            $this->db->where('t.client_id', (int) $f['client_id']);
        }
        if (! empty($f['vendor_id'])) {
            $this->db->where('t.vendor_id', (int) $f['vendor_id']);
        }
        if (! empty($f['date_from'])) {
            $this->db->where('t.date >=', $f['date_from']);
        }
        if (! empty($f['date_to'])) {
            $this->db->where('t.date <=', $f['date_to']);
        }
        if (! empty($f['search'])) {
            $this->db->group_start()
                     ->like('c.name', $f['search'])
                     ->or_like('v.name', $f['search'])
                     ->or_like('t.visa_type', $f['search'])
                     ->group_end();
        }
    }

    /**
     * Filtered + paginated task list with client/vendor/service names and a
     * passport count (single grouped query — no N+1).
     *
     * @return array
     */
    public function get_filtered($f, $limit, $offset)
    {
        $this->db
            ->select('t.id, t.date, t.status, t.visa_type, t.destination,
                      t.fee_amount, t.fee_currency, t.vendor_cost_amount, t.vendor_cost_currency,
                      c.name AS client_name, v.name AS vendor_name, s.name AS service_name')
            ->select('(SELECT COUNT(*) FROM task_passports p WHERE p.task_id = t.id) AS passport_count', FALSE)
            ->from('tasks t')
            ->join('financial_accounts c', 'c.id = t.client_id', 'left')
            ->join('financial_accounts v', 'v.id = t.vendor_id', 'left')
            ->join('services s', 's.id = t.service_id', 'left');

        $this->_apply_filters($f);

        return $this->db
            ->order_by('t.date', 'DESC')
            ->order_by('t.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->result();
    }

    /** @return int */
    public function count_filtered($f)
    {
        $this->db->from('tasks t')
                 ->join('financial_accounts c', 'c.id = t.client_id', 'left')
                 ->join('financial_accounts v', 'v.id = t.vendor_id', 'left');
        $this->_apply_filters($f);
        return $this->db->count_all_results();
    }

    /** Single task with joined names. @return object|null */
    public function get_by_id($id)
    {
        return $this->db
            ->select('t.*, c.name AS client_name, v.name AS vendor_name, s.name AS service_name')
            ->from('tasks t')
            ->join('financial_accounts c', 'c.id = t.client_id', 'left')
            ->join('financial_accounts v', 'v.id = t.vendor_id', 'left')
            ->join('services s', 's.id = t.service_id', 'left')
            ->where('t.id', (int) $id)
            ->get()->row();
    }

    /** @return int|false  new task id */
    public function create($data)
    {
        $data['created_by'] = $this->session->userdata('user_id');
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->insert('tasks', $data)) {
            return (int) $this->db->insert_id();
        }
        return FALSE;
    }

    /** @return bool */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', (int) $id)->update('tasks', $data);
    }

    /**
     * Delete a task: reverse its payment ledger entries, then delete the task
     * (passports + payment logs cascade via FK). Transactional.
     *
     * @return bool
     */
    public function delete($id)
    {
        $id = (int) $id;
        $this->db->trans_start();

        // Remove ledger entries posted by this task's payments.
        $this->db->where_in('source', array('task_client_payment', 'task_vendor_payment'))
                 ->where('reference', (string) $id)
                 ->delete('ledger_entries');

        // Cascades task_passports + task_client_payments + task_vendor_payments.
        $this->db->where('id', $id)->delete('tasks');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
