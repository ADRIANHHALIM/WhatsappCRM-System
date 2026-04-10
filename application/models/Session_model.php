<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Session_model extends CI_Model {

    private $table = 'wa_sessions';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function upsert_session($data) {
        // Find if exists
        $sql = "SELECT session_id FROM {$this->table} WHERE session_id = ?";
        $query = $this->db->query($sql, array($data['session_id']));
        
        if ($query->num_rows() > 0) {
            // Update
            $update = array(
                'status' => isset($data['status']) ? $data['status'] : 'CONNECTED',
                'last_seen' => date('Y-m-d H:i:s')
            );
            if (isset($data['wa_number'])) {
                $update['wa_number'] = $data['wa_number'];
            }
            if (isset($data['battery_level'])) {
                $update['battery_level'] = $data['battery_level'];
            }
            if (isset($data['employee_id'])) {
                $update['employee_id'] = $data['employee_id'];
            }
            
            $this->db->where('session_id', $data['session_id']);
            return $this->db->update($this->table, $update);
        } else {
            // Insert
            $insert = array(
                'session_id' => $data['session_id'],
                'status' => isset($data['status']) ? $data['status'] : 'SCAN_QR',
                'employee_id' => isset($data['employee_id']) ? $data['employee_id'] : NULL,
                'wa_number' => isset($data['wa_number']) ? $data['wa_number'] : NULL,
                'battery_level' => isset($data['battery_level']) ? (int) $data['battery_level'] : 0,
            );
            return $this->db->insert($this->table, $insert);
        }
    }

    public function get_session($session_id) {
        $sql = "SELECT * FROM {$this->table} WHERE session_id = ?";
        $query = $this->db->query($sql, array($session_id));
        return $query->row_array();
    }

    public function get_all_sessions() {
        $sql = "SELECT s.*, e.fullname AS employee_name FROM {$this->table} s LEFT JOIN employees e ON s.employee_id = e.id ORDER BY s.last_seen DESC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
