<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Account_model — financial accounts
 * (client / vendor / expense / income / individual / cash).
 */
class Account_model extends CI_Model {

    /** @return array */
    public function get_all()
    {
        return $this->db
            ->select('id, name, type, phone, note, active, created_at')
            ->from('financial_accounts')
            ->order_by('id', 'ASC')
            ->get()->result();
    }

    /** @return object|null */
    public function get_by_id($id)
    {
        return $this->db
            ->select('id, name, type, phone, note, active, created_at')
            ->from('financial_accounts')
            ->where('id', (int) $id)
            ->get()->row();
    }

    /**
     * Active accounts of a given type (for dropdowns in tasks/receipts).
     *
     * @param  string|array $type
     * @return array
     */
    public function get_by_type($type)
    {
        $this->db->select('id, name, type')->from('financial_accounts')->where('active', 1);
        if (is_array($type)) {
            $this->db->where_in('type', $type);
        } else {
            $this->db->where('type', $type);
        }
        return $this->db->order_by('name', 'ASC')->get()->result();
    }

    /** @return int|false */
    public function create($data)
    {
        $insert = array(
            'name'       => $data['name'],
            'type'       => $data['type'],
            'phone'      => ! empty($data['phone']) ? $data['phone'] : NULL,
            'note'       => ! empty($data['note']) ? $data['note'] : NULL,
            'active'     => isset($data['active']) ? (int) $data['active'] : 1,
            'created_at' => date('Y-m-d H:i:s'),
        );
        if ($this->db->insert('financial_accounts', $insert)) {
            return (int) $this->db->insert_id();
        }
        return FALSE;
    }

    /** @return bool */
    public function update($id, $data)
    {
        $update = array(
            'name'   => $data['name'],
            'type'   => $data['type'],
            'phone'  => ! empty($data['phone']) ? $data['phone'] : NULL,
            'note'   => ! empty($data['note']) ? $data['note'] : NULL,
            'active' => isset($data['active']) ? (int) $data['active'] : 1,
        );
        return $this->db->where('id', (int) $id)->update('financial_accounts', $update);
    }

    /** @return bool */
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('financial_accounts');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Is this account referenced by any task or ledger entry? (delete guard)
     *
     * @return bool
     */
    public function is_in_use($id)
    {
        $id = (int) $id;

        if ($this->db->where('account_id', $id)->count_all_results('ledger_entries') > 0) {
            return TRUE;
        }

        $task_refs = $this->db
            ->where('client_id', $id)
            ->or_where('vendor_id', $id)
            ->count_all_results('tasks');

        return $task_refs > 0;
    }
}
