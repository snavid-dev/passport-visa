<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permission library — reusable RBAC checks for controllers and views.
 *
 * Initialised once per request by MY_Controller with the already-loaded
 * current user, so views (e.g. the sidebar) can call:
 *     $this->permission->has('manage_tasks')
 * without re-querying the database.
 */
class Permission {

    /** @var array Flat list of permission key strings. */
    protected $permissions = array();

    /** @var object|null The current user object. */
    protected $user = null;

    /**
     * Seed the library with the current user (whose ->permissions is a
     * flat array of permission keys).
     */
    public function init($user)
    {
        $this->user = $user;
        $this->permissions = ($user && isset($user->permissions) && is_array($user->permissions))
            ? $user->permissions
            : array();
        return $this;
    }

    /**
     * Does the current user hold this permission key?
     */
    public function has($key)
    {
        return in_array($key, $this->permissions, TRUE);
    }

    /**
     * Does the user hold ANY of the given keys?
     */
    public function has_any(array $keys)
    {
        foreach ($keys as $key) {
            if ($this->has($key)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Halt with a 403 unless the user holds the key.
     */
    public function require_permission($key)
    {
        if (! $this->has($key)) {
            show_error('شما به این بخش دسترسی ندارید.', 403, 'دسترسی غیرمجاز');
        }
    }

    /**
     * Return all permission keys the user currently holds.
     */
    public function all()
    {
        return $this->permissions;
    }
}
