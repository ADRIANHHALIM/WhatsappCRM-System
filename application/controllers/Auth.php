<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Employee_model');
        $this->load->model('Audit_model');
        $this->load->helper('url');
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function login() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $user = $this->Employee_model->get_by_username($username);

        if ($user && password_verify($password, $user['password'])) { 
            $userdata = array(
                'employee_id' => $user['id'],
                'fullname' => $user['fullname'],
                'role' => $user['role'],
                'logged_in' => TRUE
            );
            $this->session->set_userdata($userdata);
            
            // Audit Trail
            $this->Audit_model->log_action($user['id'], 'LOGIN', 'Employee logged into the system.');
            
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Invalid username or password');
            redirect('auth');
        }
    }

    public function logout() {
        if ($this->session->userdata('logged_in')) {
            $this->Audit_model->log_action($this->session->userdata('employee_id'), 'LOGOUT', 'Employee logged out.');
        }
        $this->session->sess_destroy();
        redirect('auth');
    }
}
