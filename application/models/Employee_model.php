<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_model extends CI_Model {

    private $table = 'employees';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_username($username) {
        $sql = "SELECT id, fullname, username, password, role, is_active FROM {$this->table} WHERE LOWER(username) = LOWER(?) LIMIT 1";
        $query = $this->db->query($sql, array($username));
        return $query->row_array();
    }

    public function get_all() {
        $sql = "SELECT id, fullname, username, role, is_active, created_at FROM {$this->table} ORDER BY fullname ASC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
