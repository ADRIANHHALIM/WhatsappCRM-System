<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_model extends CI_Model {

    private $table = 'wa_contacts';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function upsert_contact($phone_number, $data) {
        $sql = "SELECT phone_number FROM {$this->table} WHERE phone_number = ?";
        $query = $this->db->query($sql, array($phone_number));
        
        if ($query->num_rows() > 0) {
            // Filter out empty arrays to not overwrite existing name if we don't have it
            $update = array();
            if(!empty($data['fullname'])) $update['fullname'] = $data['fullname'];
            if(isset($data['category'])) $update['category'] = $data['category'];
            if(isset($data['profile_pic_url'])) $update['profile_pic_url'] = $data['profile_pic_url'];
            
            if(!empty($update)) {
                $this->db->where('phone_number', $phone_number);
                return $this->db->update($this->table, $update);
            }
            return true;
        } else {
            $insert = array(
                'phone_number' => $phone_number,
                'fullname' => !empty($data['fullname']) ? $data['fullname'] : 'Unknown Contact',
                'category' => isset($data['category']) ? $data['category'] : 'General',
                'assigned_to' => isset($data['assigned_to']) ? $data['assigned_to'] : NULL,
                'profile_pic_url' => isset($data['profile_pic_url']) ? $data['profile_pic_url'] : NULL,
            );
            return $this->db->insert($this->table, $insert);
        }
    }

    public function get_contact($phone_number) {
        $sql = "SELECT * FROM {$this->table} WHERE phone_number = ?";
        $query = $this->db->query($sql, array($phone_number));
        return $query->row_array();
    }
}
