<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User_model
 *
 * Phase 1 scope: the lookups the auth guard + login need.
 * (Full user CRUD is added in Phase 2.)
 */
class User_model extends CI_Model {

    /**
     * Fetch an active user by id, with their role name and a flat
     * array of permission keys attached as ->permissions.
     *
     * @param  int $id
     * @return object|null
     */
    public function get_with_permissions($id)
    {
        $user = $this->db
            ->select('u.id, u.name, u.username, u.email, u.phone, u.role_id, u.active, r.name AS role_name')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', (int) $id)
            ->where('u.active', 1)
            ->get()->row();

        if (! $user) {
            return NULL;
        }

        $perms = $this->db
            ->select('p.key_name')
            ->from('role_permissions rp')
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->where('rp.role_id', (int) $user->role_id)
            ->get()->result();

        $user->permissions = array_map(function ($row) {
            return $row->key_name;
        }, $perms);

        return $user;
    }

    /**
     * Fetch an active user by username (for login verification).
     *
     * @param  string $username
     * @return object|null
     */
    public function get_by_username($username)
    {
        return $this->db
            ->select('id, name, username, password, role_id, active')
            ->from('users')
            ->where('username', $username)
            ->where('active', 1)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // CRUD (Phase 2)
    // ------------------------------------------------------------------

    /**
     * All users with their role name (single join, no N+1).
     *
     * @return array
     */
    public function get_all()
    {
        return $this->db
            ->select('u.id, u.name, u.username, u.email, u.phone, u.active, u.created_at, r.name AS role_name')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->order_by('u.id', 'ASC')
            ->get()->result();
    }

    /**
     * @param  int $id
     * @return object|null
     */
    public function get_by_id($id)
    {
        return $this->db
            ->select('id, name, username, email, phone, role_id, active, created_at')
            ->from('users')
            ->where('id', (int) $id)
            ->get()->row();
    }

    /**
     * Create a user. Password is hashed here.
     *
     * @param  array $data  must include plaintext 'password'
     * @return int|false    new user id
     */
    public function create($data)
    {
        $insert = array(
            'name'       => $data['name'],
            'username'   => $data['username'],
            'email'      => ! empty($data['email']) ? $data['email'] : NULL,
            'phone'      => ! empty($data['phone']) ? $data['phone'] : NULL,
            'password'   => password_hash($data['password'], PASSWORD_BCRYPT),
            'role_id'    => (int) $data['role_id'],
            'active'     => isset($data['active']) ? (int) $data['active'] : 1,
            'created_at' => date('Y-m-d H:i:s'),
        );

        if ($this->db->insert('users', $insert)) {
            return (int) $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update a user. Password is re-hashed only when a new one is provided.
     *
     * @return bool
     */
    public function update($id, $data)
    {
        $update = array(
            'name'    => $data['name'],
            'username'=> $data['username'],
            'email'   => ! empty($data['email']) ? $data['email'] : NULL,
            'phone'   => ! empty($data['phone']) ? $data['phone'] : NULL,
            'role_id' => (int) $data['role_id'],
            'active'  => isset($data['active']) ? (int) $data['active'] : 1,
        );

        if (! empty($data['password'])) {
            $update['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return $this->db->where('id', (int) $id)->update('users', $update);
    }

    /**
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('users');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Username uniqueness check.
     *
     * @param  string   $username
     * @param  int|null  $except_id
     * @return bool       TRUE if taken
     */
    public function username_exists($username, $except_id = NULL)
    {
        $this->db->from('users')->where('username', $username);
        if ($except_id !== NULL) {
            $this->db->where('id !=', (int) $except_id);
        }
        return $this->db->count_all_results() > 0;
    }

    /**
     * Count active users — used to block deleting/deactivating the last admin.
     *
     * @return int
     */
    public function count_active()
    {
        return $this->db->where('active', 1)->count_all_results('users');
    }
}
