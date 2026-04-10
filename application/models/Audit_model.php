<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model {

    private $table = 'audit_logs';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function log_action($employee_id, $action, $description = '') {
        $insert = array(
            'employee_id' => $employee_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->input->ip_address(),
        );
        return $this->db->insert($this->table, $insert);
    }
    
    public function get_logs($limit = 100) {
        $sql = "SELECT a.*, e.fullname, e.role FROM {$this->table} a LEFT JOIN employees e ON a.employee_id = e.id ORDER BY a.created_at DESC LIMIT ?";
        $query = $this->db->query($sql, array((int)$limit));
        return $query->result_array();
    }
}
