<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Uploads — serve passport scans through an authenticated, permission-checked
 * gate. Files live in /uploads (denied at the webserver via .htaccess), so the
 * only way to read them is through here.
 */
class Uploads extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('manage_tasks');
    }

    /**
     * GET /uploads/passports/{task_id}/{file}
     */
    public function passport($task_id, $file)
    {
        $task_id = (int) $task_id;
        $file    = basename($file);                 // strip any path traversal
        if ($task_id <= 0 || $file === '' || strpos($file, '..') !== FALSE) {
            show_404();
        }

        $path = FCPATH . 'uploads/passports/' . $task_id . '/' . $file;
        if (! is_file($path)) {
            show_404();
        }

        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $types = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                       'png' => 'image/png', 'pdf' => 'application/pdf');
        if (! isset($types[$ext])) {
            show_404();
        }

        // Stream the file.
        header('Content-Type: ' . $types[$ext]);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . $file . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=600');
        readfile($path);
        exit;
    }
}
