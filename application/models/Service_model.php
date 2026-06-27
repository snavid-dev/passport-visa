<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Service_model — named visa service types with default per-passport fee.
 */
class Service_model extends CI_Model {

    /** @return array */
    public function get_all()
    {
        return $this->db
            ->select('id, name, default_fee, default_currency, visa_type, active, created_at')
            ->from('services')
            ->order_by('id', 'ASC')
            ->get()->result();
    }

    /** @return object|null */
    public function get_by_id($id)
    {
        return $this->db
            ->select('id, name, default_fee, default_currency, visa_type, active, created_at')
            ->from('services')
            ->where('id', (int) $id)
            ->get()->row();
    }

    /** Active services (for the task form dropdown). @return array */
    public function get_active()
    {
        return $this->db
            ->select('id, name, default_fee, default_currency, visa_type')
            ->from('services')
            ->where('active', 1)
            ->order_by('name', 'ASC')
            ->get()->result();
    }

    /** @return int|false */
    public function create($data)
    {
        $insert = array(
            'name'             => $data['name'],
            'default_fee'      => $data['default_fee'],
            'default_currency' => $data['default_currency'],
            'visa_type'        => ! empty($data['visa_type']) ? $data['visa_type'] : NULL,
            'active'           => isset($data['active']) ? (int) $data['active'] : 1,
            'created_at'       => date('Y-m-d H:i:s'),
        );
        if ($this->db->insert('services', $insert)) {
            return (int) $this->db->insert_id();
        }
        return FALSE;
    }

    /** @return bool */
    public function update($id, $data)
    {
        $update = array(
            'name'             => $data['name'],
            'default_fee'      => $data['default_fee'],
            'default_currency' => $data['default_currency'],
            'visa_type'        => ! empty($data['visa_type']) ? $data['visa_type'] : NULL,
            'active'           => isset($data['active']) ? (int) $data['active'] : 1,
        );
        return $this->db->where('id', (int) $id)->update('services', $update);
    }

    /** @return bool */
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('services');
        return $this->db->affected_rows() > 0;
    }

    /** Is this service referenced by any task? (delete guard) @return bool */
    public function is_in_use($id)
    {
        return $this->db->where('service_id', (int) $id)->count_all_results('tasks') > 0;
    }
}
