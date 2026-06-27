<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Role_model — roles + their permission assignments.
 */
class Role_model extends CI_Model {

    /**
     * All roles with permission count + user count (single grouped query,
     * no N+1).
     *
     * @return array
     */
    public function get_all()
    {
        return $this->db
            ->select('r.id, r.name, r.created_at')
            ->select('(SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) AS permission_count', FALSE)
            ->select('(SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) AS user_count', FALSE)
            ->from('roles r')
            ->order_by('r.id', 'ASC')
            ->get()->result();
    }

    /**
     * @param  int $id
     * @return object|null
     */
    public function get_by_id($id)
    {
        return $this->db
            ->select('id, name, created_at')
            ->from('roles')
            ->where('id', (int) $id)
            ->get()->row();
    }

    /**
     * Permission ids assigned to a role.
     *
     * @param  int $role_id
     * @return int[]
     */
    public function get_permission_ids($role_id)
    {
        $rows = $this->db
            ->select('permission_id')
            ->from('role_permissions')
            ->where('role_id', (int) $role_id)
            ->get()->result();

        return array_map(function ($r) { return (int) $r->permission_id; }, $rows);
    }

    /**
     * Every available permission (for the assignment checkboxes).
     *
     * @return array
     */
    public function get_all_permissions()
    {
        return $this->db
            ->select('id, key_name, label')
            ->from('permissions')
            ->order_by('id', 'ASC')
            ->get()->result();
    }

    /**
     * Create a role and assign permissions atomically.
     *
     * @param  array $data            ['name' => ...]
     * @param  int[] $permission_ids
     * @return int|false  new role id, or FALSE on failure
     */
    public function create($data, $permission_ids = array())
    {
        $this->db->trans_start();

        $this->db->insert('roles', array(
            'name'       => $data['name'],
            'created_at' => date('Y-m-d H:i:s'),
        ));
        $role_id = (int) $this->db->insert_id();

        $this->_sync_permissions($role_id, $permission_ids);

        $this->db->trans_complete();

        return ($this->db->trans_status() === FALSE) ? FALSE : $role_id;
    }

    /**
     * Update a role + re-sync its permissions atomically.
     *
     * @return bool
     */
    public function update($id, $data, $permission_ids = array())
    {
        $id = (int) $id;
        $this->db->trans_start();

        $this->db->where('id', $id)->update('roles', array(
            'name' => $data['name'],
        ));

        $this->_sync_permissions($id, $permission_ids);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Replace a role's permission rows with the given set.
     */
    protected function _sync_permissions($role_id, $permission_ids)
    {
        $this->db->where('role_id', (int) $role_id)->delete('role_permissions');

        $permission_ids = array_unique(array_map('intval', (array) $permission_ids));
        if (empty($permission_ids)) {
            return;
        }

        $batch = array();
        foreach ($permission_ids as $pid) {
            if ($pid > 0) {
                $batch[] = array('role_id' => (int) $role_id, 'permission_id' => $pid);
            }
        }
        if (! empty($batch)) {
            $this->db->insert_batch('role_permissions', $batch);
        }
    }

    /**
     * Delete a role (role_permissions cascade via FK).
     *
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('roles');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Is this role assigned to any user? (delete guard)
     *
     * @return bool
     */
    public function is_in_use($id)
    {
        return $this->db
            ->where('role_id', (int) $id)
            ->count_all_results('users') > 0;
    }

    /**
     * Case-insensitive name uniqueness check.
     *
     * @param  string   $name
     * @param  int|null $except_id
     * @return bool      TRUE if name already taken
     */
    public function name_exists($name, $except_id = NULL)
    {
        $this->db->from('roles')->where('name', $name);
        if ($except_id !== NULL) {
            $this->db->where('id !=', (int) $except_id);
        }
        return $this->db->count_all_results() > 0;
    }
}
