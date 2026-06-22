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
}
