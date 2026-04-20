<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('waha_lib');
        $this->load->model('Message_model');
        $this->load->model('Contact_model');  // BUG 4 — was missing
        $this->load->model('Session_model');
        $this->load->model('Employee_model');
        $this->load->model('Audit_model');
        $this->load->helper('url');

        // BUG 5 — set timezone before any date() / strtotime() call
        date_default_timezone_set('Asia/Jakarta');

        if (!$this->session->userdata('logged_in')) {
            // Allow get_qr and get_session_status through without auth check
            $excluded = ['get_qr', 'webhook_status'];
            if (!in_array($this->router->fetch_method(), $excluded)) {
                redirect('auth');
            }
        }
    }

    public function scan_qr() {
        $session_id = 'default'; // Let's use a default session name for now
        $data['title'] = 'Scan WhatsApp QR';
        $data['session_id'] = $session_id;
        
        // Start session via WAHA
        $res = $this->waha_lib->start_session($session_id);
        
        // Save to DB
        $this->Session_model->upsert_session(array(
            'session_id' => $session_id,
            'employee_id' => $this->session->userdata('employee_id')
        ));
        
        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('whatsapp/scan_qr', $data);
        $this->load->view('layouts/footer');
    }

    public function get_qr($session_id) {
        $status_data = $this->waha_lib->get_session_status($session_id);
        
        // Auto-initialize if session doesn't exist, is stopped, or has FAILED
        $status = isset($status_data['status']) ? $status_data['status'] : 'STOPPED';
        
        if (!$status_data || in_array($status, ['STOPPED', 'FAILED'])) {
            // If it failed, try to stop it first to clean up any stuck processes
            if ($status === 'FAILED') {
                log_message('error', "Whatsapp::get_qr — Session [{$session_id}] is FAILED. Attempting recovery...");
                $this->waha_lib->stop_session($session_id);
                sleep(1); // Give it a second to clean up
            }
            
            $this->waha_lib->create_session($session_id);
            $this->waha_lib->start_session($session_id);
            $status_data = $this->waha_lib->get_session_status($session_id);
            $status = isset($status_data['status']) ? $status_data['status'] : 'STARTING';
        }

        $status = isset($status_data['status']) ? $status_data['status'] : 'STARTING';
        $response = ['status' => $status];

        // Only fetch the QR image if the engine provides it 
        if ($status === 'SCAN_QR_CODE') {
            $response['qr'] = $this->waha_lib->get_qr_code($session_id, 'base64');
        }

        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        echo json_encode($response);
        exit;
    }

    public function chat_room() {
        $session_id = 'default';
        $data['title'] = 'Chat Room';
        $data['session_id'] = $session_id;

        // Ensure session exists in DB
        $this->Session_model->upsert_session([
            'session_id'  => $session_id,
            'employee_id' => $this->session->userdata('employee_id')
        ]);

        // Sync messages from WAHA directly (fallback for when webhooks can't reach localhost)
        $this->_sync_conversations_from_waha($session_id);

        $data['conversations'] = $this->Message_model->get_latest_conversations($session_id);

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('whatsapp/chat_room', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * Sync recent conversations from WAHA API into the database.
     * This is the fallback mechanism when Docker webhooks can't POST to localhost.
     */
    private function _sync_conversations_from_waha($session_id) {
        $waha_status = $this->waha_lib->get_session_status($session_id);
        if (!$waha_status || $waha_status['status'] !== 'WORKING') return;

        $chats = $this->waha_lib->get_recent_chats($session_id, 20);
        if (!$chats || !is_array($chats)) return;

        foreach ($chats as $chat) {
            // Skip groups
            if (isset($chat['id']['server']) && $chat['id']['server'] === 'g.us') continue;
            if (empty($chat['lastMessage'])) continue;

            $raw_jid = isset($chat['id']['user']) ? $chat['id']['user'] : '';
            if (empty($raw_jid)) continue;
            $phone = preg_replace('/[^0-9]/', '', $raw_jid);

            // Upsert contact
            $name = isset($chat['name']) ? $chat['name'] : $phone;
            
            // Optimization: Only fetch profile picture if we don't have it yet
            $existing_contact = $this->Contact_model->get_contact($phone);
            $profile_pic = isset($existing_contact['profile_pic_url']) ? $existing_contact['profile_pic_url'] : null;
            
            if (!$profile_pic) {
                $pic_data = $this->waha_lib->get_profile_picture($session_id, $phone);
                if ($pic_data && isset($pic_data['link'])) {
                    $profile_pic = $pic_data['link'];
                }
            }

            $this->Contact_model->upsert_contact($phone, [
                'fullname' => $name,
                'profile_pic_url' => $profile_pic
            ]);

            // Get the last message data
            $last = $chat['lastMessage'];
            $msg_id  = isset($last['id']['id']) ? $last['id']['id'] : (isset($last['id']['_serialized']) ? $last['id']['_serialized'] : '');
            $body    = isset($last['body']) ? $last['body'] : '';
            $from_me = isset($last['fromMe']) ? $last['fromMe'] : false;

            if (empty($msg_id)) continue;

            // Check if already stored
            $existing = $this->db->where('waha_msg_id', $msg_id)->get('wa_messages')->row_array();
            if ($existing) continue;

            $raw_ts = isset($last['timestamp']) ? $last['timestamp'] : null;
            $ts_val = null;
            if ($raw_ts) {
                // If timestamp > 10^12, it is likely milliseconds (current epoch in s is ~1.7e9)
                $seconds = ($raw_ts > 10000000000) ? floor($raw_ts / 1000) : $raw_ts;
                $ts_val = date('Y-m-d H:i:s', $seconds);
            }

            if ($from_me) {
                $this->Message_model->save_outgoing([
                    'waha_msg_id'  => $msg_id,
                    'session_id'   => $session_id,
                    'employee_id'  => null,
                    'contact_phone'=> $phone,
                    'body'         => $body ?: '[Media]',
                    'message_type' => isset($last['type']) ? $last['type'] : 'text',
                    'created_at'   => $ts_val,
                ]);
            } else {
                $this->Message_model->save_incoming([
                    'waha_msg_id'  => $msg_id,
                    'session_id'   => $session_id,
                    'contact_phone'=> $phone,
                    'body'         => $body ?: '[Media]',
                    'message_type' => isset($last['type']) ? $last['type'] : 'text',
                    'created_at'   => $ts_val,
                ]);
            }
        }
    }

    // =========================================================================
    // AJAX ENDPOINTS (called by JS fetch — return JSON only)
    // =========================================================================

    /**
     * GET  whatsapp/ajax_get_sidebar
     * Returns ordered conversation list for the left sidebar.
     */
    public function ajax_get_sidebar() {
        // Re-sync from WAHA on every poll so incoming messages appear without webhooks
        $this->_sync_conversations_from_waha('default');

        $rows = $this->Message_model->get_sidebar_conversations('default', 50);

        // Format timestamps for JS
        foreach ($rows as &$row) {
            $row['ts_formatted'] = $this->_format_ts($row['last_message_at']);
        }
        unset($row);

        $this->_json(['status' => 'ok', 'data' => $rows]);
    }

    /**
     * GET  whatsapp/ajax_get_chat_history/{phone}
     * Returns full message history for one contact.
     */
    public function ajax_get_chat_history($phone = null) {
        if (empty($phone)) { $this->_json(['status' => 'error', 'message' => 'No phone'], 400); }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        $msgs  = $this->Message_model->get_conversation($phone, 'default', 100);

        // Mark as read now that the employee opened the chat
        $this->Message_model->mark_as_read($phone, 'default');

        // Format timestamps
        foreach ($msgs as &$m) {
            $m['ts_formatted'] = $this->_format_ts($m['created_at']);
        }
        unset($m);

        $this->_json(['status' => 'ok', 'data' => $msgs]);
    }

    /**
     * GET whatsapp/ajax_get_contact_info/{phone}
     * Returns Bio/About and other metadata for the right panel.
     */
    public function ajax_get_contact_info($phone = null) {
        if (empty($phone)) $this->_json(['status' => 'error', 'message' => 'No phone'], 400);

        $session_id = 'default';
        $contact = $this->Contact_model->get_contact($phone);
        $waha_info = $this->waha_lib->get_contact_info($session_id, $phone);

        $data = [
            'phone' => $phone,
            'name' => isset($contact['fullname']) ? $contact['fullname'] : $phone,
            'avatar' => isset($contact['profile_pic_url']) ? $contact['profile_pic_url'] : null,
            'about' => (isset($waha_info['about'])) ? $waha_info['about'] : 'Busy',
            'category' => isset($contact['category']) ? $contact['category'] : 'General',
        ];

        $this->_json(['status' => 'ok', 'data' => $data]);
    }

    /**
     * POST whatsapp/ajax_send_message
     * BUG 1 FIX: Only saves to DB after WAHA confirms delivery (HTTP 200/201).
     */
    public function ajax_send_message() {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        $phone_raw   = $this->input->post('phone_number');
        $body        = trim($this->input->post('body'));
        $employee_id = $this->session->userdata('employee_id');

        if (empty($phone_raw) || empty($body)) {
            $this->_json(['status' => 'error', 'message' => 'phone_number and body are required'], 422);
        }

        // Strip everything except digits — sendText() will append @c.us
        $to = preg_replace('/[^0-9]/', '', $phone_raw);

        log_message('debug', "ajax_send_message: to=[{$to}] body_len=[" . strlen($body) . "]");

        $res = $this->waha_lib->sendText('default', $to, $body);

        // BUG 1: Only insert into DB when WAHA returns 200 or 201
        if ($res['status'] === true) {
            $waha_resp = json_decode($res['response'], true);
            $msg_id    = (is_array($waha_resp) && isset($waha_resp['id'])) ? $waha_resp['id'] : null;

            $this->Message_model->save_outgoing([
                'waha_msg_id'   => $msg_id,
                'session_id'    => 'default',
                'employee_id'   => $employee_id,
                'contact_phone' => $to,
                'body'          => $body,
                'message_type'  => 'text',
            ]);

            $this->Audit_model->log_action($employee_id, 'SEND_MESSAGE', "Sent to {$to}");
            $this->_json(['status' => 'ok']);
        } else {
            log_message('error', "ajax_send_message FAILED: HTTP {$res['http_code']} | {$res['response']}");
            $this->_json([
                'status'  => 'error',
                'message' => 'WAHA rejected the send request (HTTP ' . $res['http_code'] . ')',
            ], 502);
        }
    }

    // =========================================================================
    // Keep old ajax_get_messages for backwards compat (re-routes internally)
    // =========================================================================
    public function ajax_get_messages($phone_number) {
        $this->ajax_get_chat_history($phone_number);
    }

    /**
     * GET  whatsapp/proxy_avatar?url=...
     * BUG 3 FIX: Proxies WAHA profile images server-side so the browser never
     * needs to reach "http://localhost:3000" directly.
     */
    public function proxy_avatar() {
        $url = $this->input->get('url');

        if (empty($url)) {
            http_response_code(400);
            exit('Missing url parameter');
        }

        // Only allow proxying from the configured WAHA host (prevents open redirect)
        $waha_host = rtrim(getenv('WAHA_HOST') ?: 'http://localhost:3000', '/');
        if (strpos($url, $waha_host) !== 0 && strpos($url, 'http://localhost:3000') !== 0) {
            http_response_code(403);
            exit('Forbidden origin');
        }

        $binary = $this->waha_lib->fetch_image_binary($url);

        if ($binary === FALSE) {
            http_response_code(404);
            exit;
        }

        // Detect content type from magic bytes
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->buffer($binary) ?: 'image/jpeg';

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400'); // Cache for 1 day
        echo $binary;
        exit;
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /** Emit a JSON response and exit. */
    private function _json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * BUG 2 FIX: Format a DB timestamp into a human-friendly string.
     * Relies on date_default_timezone_set('Asia/Jakarta') having been called
     * in __construct(), so date() and strtotime() both work in local time.
     *
     * PostgreSQL stores timestamps as TIMESTAMPTZ (UTC). When PHP reads them
     * via the pgsql driver they arrive as strings like "2026-04-10 09:23:45+00".
     * strtotime() correctly parses the +00 offset and converts to local time
     * once the PHP timezone is set to Asia/Jakarta.
     *
     *   - Today      → "15:23"
     *   - Yesterday  → "Yesterday"
     *   - This week  → "Mon"
     *   - Older      → "09/04"
     */
    private function _format_ts($ts) {
        if (empty($ts)) return '';

        $dt = strtotime($ts);
        if ($dt === FALSE || $dt <= 0) return '';

        $now       = time();
        $today     = strtotime('today midnight');
        $yesterday = strtotime('yesterday midnight');

        if ($dt >= $today)           return date('H:i', $dt);
        if ($dt >= $yesterday)       return 'Yesterday';
        if ($now - $dt < 604800)     return date('D', $dt);   // within 7 days
        return date('d/m', $dt);
    }


    public function send_message() {
        $phone_number = $this->input->post('phone_number');
        $body = $this->input->post('body');
        $session_id = 'default';
        $employee_id = $this->session->userdata('employee_id');

        if(empty($phone_number) || empty($body)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Empty fields']);
            exit;
        }

        // Format number to ensure digits only
        $to = preg_replace('/[^0-9]/', '', $phone_number);

        // Send to WAHA API
        $res = $this->waha_lib->sendText($session_id, $to, $body);

        header('Content-Type: application/json');
        if ($res['status'] === true) {
            $waha_response = json_decode($res['response'], true);
            $msg_data = array(
                'waha_msg_id' => isset($waha_response['id']) ? $waha_response['id'] : null,
                'session_id' => $session_id,
                'employee_id' => $employee_id,
                'contact_phone' => $to,
                'body' => $body,
                'message_type' => 'text'
            );
            $this->Message_model->save_outgoing($msg_data);
            
            // Audit
            $this->Audit_model->log_action($employee_id, 'SEND_MESSAGE', "Sent message to {$to}");

            echo json_encode(['status' => 'success', 'data' => $msg_data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Failed to send via WAHA (HTTP {$res['http_code']})"]);
        }
        exit;
    }

    public function owner_view() {
        if ($this->session->userdata('role') !== 'owner') {
            redirect('dashboard');
        }

        $data['title'] = 'Owner Supervision Dashboard';
        $data['audit_logs'] = $this->Audit_model->get_logs(50);
        $data['sessions'] = $this->Session_model->get_all_sessions();

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('whatsapp/owner_view', $data);
        $this->load->view('layouts/footer');
    }
}
