<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'Dashboard';
        $data['role'] = $this->session->userdata('role');
        $data['fullname'] = $this->session->userdata('fullname');

        if ($data['role'] == 'owner') {
            redirect('whatsapp/owner_view');
        } else {
            redirect('whatsapp/chat_room');
        }
    }
}
