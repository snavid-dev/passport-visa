<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Passport_model — task_passports rows belonging to a task.
 *
 * Receives already-clean row arrays (dates Gregorian, scan_path resolved);
 * file handling lives in the controller/upload helper.
 */
class Passport_model extends CI_Model {

    protected $fields = array(
        'surname', 'given_name', 'passport_no', 'dob', 'place_of_birth',
        'issue_date', 'expiry_date', 'gender', 'scan_path',
    );

    /** @return array */
    public function get_by_task($task_id)
    {
        return $this->db
            ->select('id, task_id, surname, given_name, passport_no, dob, place_of_birth, issue_date, expiry_date, gender, scan_path')
            ->from('task_passports')
            ->where('task_id', (int) $task_id)
            ->order_by('id', 'ASC')
            ->get()->result();
    }

    /** @return object|null */
    public function get_by_id($id)
    {
        return $this->db
            ->select('id, task_id, surname, given_name, passport_no, scan_path')
            ->from('task_passports')
            ->where('id', (int) $id)
            ->get()->row();
    }

    /** Existing passport ids for a task. @return int[] */
    public function get_ids_by_task($task_id)
    {
        $rows = $this->db->select('id')->from('task_passports')
                         ->where('task_id', (int) $task_id)->get()->result();
        return array_map(function ($r) { return (int) $r->id; }, $rows);
    }

    /** @return int new id */
    public function insert($task_id, $row)
    {
        $data = $this->_filter($row);
        $data['task_id']    = (int) $task_id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('task_passports', $data);
        return (int) $this->db->insert_id();
    }

    /** @return bool */
    public function update($id, $row)
    {
        $data = $this->_filter($row);
        return $this->db->where('id', (int) $id)->update('task_passports', $data);
    }

    /**
     * Delete passport rows by id (scoped to a task) and return their
     * scan_path values so the caller can remove the files.
     *
     * @return string[]  scan paths of deleted rows
     */
    public function delete_by_ids(array $ids, $task_id)
    {
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return array();
        }

        $rows = $this->db->select('scan_path')->from('task_passports')
                         ->where('task_id', (int) $task_id)
                         ->where_in('id', $ids)->get()->result();
        $paths = array();
        foreach ($rows as $r) {
            if (! empty($r->scan_path)) {
                $paths[] = $r->scan_path;
            }
        }

        $this->db->where('task_id', (int) $task_id)->where_in('id', $ids)
                 ->delete('task_passports');

        return $paths;
    }

    /** All scan paths for a task (used when deleting the whole task). @return string[] */
    public function get_scan_paths($task_id)
    {
        $rows = $this->db->select('scan_path')->from('task_passports')
                         ->where('task_id', (int) $task_id)
                         ->where('scan_path IS NOT NULL', NULL, FALSE)
                         ->get()->result();
        return array_map(function ($r) { return $r->scan_path; }, $rows);
    }

    /** Keep only known columns. */
    protected function _filter($row)
    {
        $data = array();
        foreach ($this->fields as $f) {
            if (array_key_exists($f, $row)) {
                $data[$f] = ($row[$f] === '') ? NULL : $row[$f];
            }
        }
        return $data;
    }
}
