<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Message_model');
        $this->load->model('Contact_model');
        $this->load->model('Session_model');
        log_message('debug', 'Webhook Controller Initialized');
    }

    public function index() {
        $raw_json = file_get_contents('php://input');
        
        if (empty($raw_json)) {
            http_response_code(200);
            echo "OK";
            return;
        }

        log_message('debug', 'Webhook Payload: ' . $raw_json);
        $payload = json_decode($raw_json, true);

        if (!$payload) {
            http_response_code(400);
            return;
        }

        $event = isset($payload['event']) ? $payload['event'] : '';
        $session_id = isset($payload['session']) ? $payload['session'] : '';

        // Handle message events
        if ($event === 'message') {
            $data = isset($payload['payload']) ? $payload['payload'] : null;
            if ($data) {
                $from_dirty = isset($data['from']) ? $data['from'] : '';
                $contact_phone = preg_replace('/[^0-9]/', '', explode('@', $from_dirty)[0]);
                
                $id = isset($data['id']) ? $data['id'] : '';
                $body = isset($data['body']) ? $data['body'] : '';
                $type = isset($data['type']) ? $data['type'] : 'text';

                // Skip status replies or internal broadcasts if needed
                if ($from_dirty === 'status@broadcast') {
                    http_response_code(200);
                    return;
                }

                // 1. Check or Insert Contact (Auto Discovery)
                $pushName = isset($data['_data']['notifyName']) ? $data['_data']['notifyName'] : (isset($data['notifyName']) ? $data['notifyName'] : 'Unknown Contact');
                $this->Contact_model->upsert_contact($contact_phone, array('fullname' => $pushName));

                // 2. Insert Incoming Message
                $msg_insert = array(
                    'waha_msg_id' => $id,
                    'session_id' => $session_id,
                    'contact_phone' => $contact_phone,
                    'body' => $body,
                    'message_type' => $type
                );
                
                $this->Message_model->save_incoming($msg_insert);
            }
        } else if ($event === 'session.status') {
             $data = isset($payload['payload']) ? $payload['payload'] : null;
             if ($data && isset($data['status'])) {
                 $this->Session_model->upsert_session(array(
                     'session_id' => $session_id,
                     'status' => $data['status']
                 ));
             }
        }

        http_response_code(200);
        echo json_encode(["status" => "ok"]);
    }
}
