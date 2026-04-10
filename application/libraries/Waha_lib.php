<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * =============================================================================
 * Waha_lib — WhatsApp HTTP API (WAHA) Client Library
 * =============================================================================
 *
 * A dedicated cURL wrapper for all interactions with the WAHA Docker container.
 * Controllers MUST use this library instead of making raw cURL calls.
 *
 * Usage in Controller:
 *   $this->load->library('waha_lib');
 *   $qr = $this->waha_lib->get_qr_code('my-session', 'base64');
 *   $result = $this->waha_lib->send_text('my-session', '6281234567890', 'Hello!');
 *
 * Configuration:
 *   Set WAHA_HOST and WAHA_API_KEY in the .env file.
 *
 * @package    WhatsApp CRM
 * @subpackage Libraries
 * @author     Adrian
 * =============================================================================
 */
class Waha_lib
{
    /**
     * Base URL of the WAHA API container (e.g., http://localhost:3000)
     * @var string
     */
    private $base_url;

    /**
     * API key for authenticating with WAHA
     * @var string
     */
    private $api_key;

    /**
     * Default cURL timeout in seconds
     * @var int
     */
    private $timeout = 30;

    /**
     * Stores the HTTP status code of the last request
     * @var int
     */
    private $last_http_code = 0;

    /**
     * Stores the raw response body of the last request
     * @var string
     */
    private $last_response = '';

    /**
     * Stores the JSON encoded payload of the last request
     * @var string
     */
    private $last_payload = '';

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /**
     * Initialise the library by reading config from environment variables.
     *
     * @param array $params Optional overrides: ['base_url' => '...', 'api_key' => '...']
     */
    public function __construct($params = array())
    {
        $this->base_url = isset($params['base_url'])
            ? rtrim($params['base_url'], '/')
            : rtrim(getenv('WAHA_HOST') ?: 'http://localhost:3000', '/');

        $this->api_key = isset($params['api_key'])
            ? $params['api_key']
            : (getenv('WAHA_API_KEY') ?: 'change_me_to_a_strong_secret');

        if (isset($params['timeout'])) {
            $this->timeout = (int) $params['timeout'];
        }

        // Log configuration for debugging (masking API key)
        $masked_key = $this->api_key ? substr($this->api_key, 0, 4) . '...' : 'NONE';
        log_message('debug', "Waha_lib Initialized: Base URL [{$this->base_url}], API Key [{$masked_key}]");
    }

    // =========================================================================
    // SESSION MANAGEMENT
    // =========================================================================

    /**
     * Create (or upsert) a WAHA session.
     *
     * @param  string      $name        Unique session name (e.g., 'staff-andi')
     * @param  string|null $webhook_url Optional per-session webhook URL
     * @return array|false Decoded JSON response or FALSE on failure
     */
    public function create_session($name, $webhook_url = null)
    {
        $data = array('name' => $name);

        // Attach per-session webhook if provided
        if ($webhook_url) {
            $data['config'] = array(
                'webhooks' => array(
                    array(
                        'url' => $webhook_url,
                        'events' => array('message', 'message.any', 'session.status'),
                    )
                )
            );
        }

        return $this->_request('POST', '/api/sessions', $data);
    }

    /**
     * Start an existing session (transitions from STOPPED → STARTING).
     *
     * @param  string $name Session name
     * @return array|false
     */
    public function start_session($name)
    {
        return $this->_request('POST', '/api/sessions/start', array('name' => $name));
    }

    /**
     * Stop a running session.
     *
     * @param  string $name Session name
     * @return array|false
     */
    public function stop_session($name)
    {
        return $this->_request('POST', '/api/sessions/stop', array('name' => $name));
    }

    /**
     * Get the current status of a session.
     *
     * Possible statuses: STARTING, SCAN_QR_CODE, WORKING, FAILED, STOPPED
     *
     * @param  string $name Session name
     * @return array|false  Contains 'status', 'config', etc.
     */
    public function get_session_status($name)
    {
        return $this->_request('GET', '/api/sessions/' . urlencode($name));
    }

    /**
     * List all sessions.
     *
     * @return array|false
     */
    public function list_sessions()
    {
        return $this->_request('GET', '/api/sessions');
    }

    /**
     * Get recent chats for a session.
     *
     * @param  string $session Session name
     * @param  int    $limit   Number of chats to fetch
     * @return array|false
     */
    public function get_recent_chats($session, $limit = 20)
    {
        return $this->_request('GET', '/api/' . urlencode($session) . '/chats?limit=' . (int)$limit);
    }

    // =========================================================================
    // AUTHENTICATION (QR CODE)
    // =========================================================================

    /**
     * Get the QR code for a session that is in SCAN_QR_CODE status.
     *
     * @param  string $name   Session name
     * @param  string $format 'image' (binary PNG), 'base64', or 'raw'
     * @return mixed  Binary image data (for 'image'), decoded JSON (for base64/raw), or FALSE
     */
    public function get_qr_code($name, $format = 'image')
    {
        $endpoint = '/api/' . urlencode($name) . '/auth/qr';
        $raw = $this->_request_raw('GET', $endpoint);
        
        if ($raw === FALSE) return FALSE;
        
        if ($format === 'base64') {
            return 'data:image/png;base64,' . base64_encode($raw);
        }
        return $raw;
    }

    // =========================================================================
    // MESSAGING
    // =========================================================================

    /**
     * Send a text message with explicit chatId suffix validation and detailed response.
     * 
     * @param  string $session Session name
     * @param  string $phone   Phone number
     * @param  string $text    Message body
     * @return array           ['status' => bool, 'response' => string, 'http_code' => int]
     */
    public function sendText($session, $phone, $text)
    {
        // 1. Format the phone number into a chatId if missing suffix
        $chatId = trim($phone);
        if (strpos($chatId, '@') === FALSE) {
            // Strip any non-numeric for cleanliness if it's a raw number
            $chatId = preg_replace('/[^0-9]/', '', $chatId) . '@c.us';
        }

        $data = array(
            'session' => $session,
            'chatId' => $chatId,
            'text' => $text,
        );

        // 2. Perform request
        $result = $this->_request('POST', '/api/sendText', $data);
        $code = $this->last_http_code;

        // 3. Status is true if code is 200 or 201
        $status = ($code === 200 || $code === 201);

        return array(
            'status' => $status,
            'response' => $this->last_response,
            'http_code' => $code
        );
    }

    /**
     * Legacy snake_case wrapper (calls new sendText)
     */
    public function send_text($session, $phone, $text)
    {
        $res = $this->sendText($session, $phone, $text);
        return $res['status'] ? json_decode($res['response'], true) : false;
    }

    /**
     * Send an image message with optional caption.
     *
     * @param  string $session Session name
     * @param  string $phone   Phone number
     * @param  string $url     URL of the image file
     * @param  string $caption Optional caption text
     * @return array|false
     */
    public function send_image($session, $phone, $url, $caption = '')
    {
        $data = array(
            'session' => $session,
            'chatId' => $this->_format_chat_id($phone),
            'file' => array('url' => $url),
            'caption' => $caption,
        );

        return $this->_request('POST', '/api/sendImage', $data);
    }

    /**
     * Get detailed contact information including profile picture and about status.
     *
     * @param  string $session Session name
     * @param  string $phone   Contact phone
     * @return array|false
     */
    public function get_contact_info($session, $phone)
    {
        return $this->_request('GET', '/api/' . urlencode($session) . '/contacts/about?contactId=' . urlencode($this->_format_chat_id($phone)));
    }

    /**
     * Get contact profile picture URL.
     * 
     * @param  string $session Session name
     * @param  string $phone   Contact phone
     * @return array|false
     */
    public function get_profile_picture($session, $phone)
    {
        return $this->_request('GET', '/api/' . urlencode($session) . '/contacts/profile-picture?contactId=' . urlencode($this->_format_chat_id($phone)));
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Execute a JSON API request via cURL.
     *
     * @param  string     $method   HTTP method (GET, POST, PUT, DELETE)
     * @param  string     $endpoint API path (e.g., '/api/sessions')
     * @param  array|null $data     Request body (will be JSON-encoded for POST/PUT)
     * @return array|false          Decoded JSON or FALSE on failure
     */
    private function _request($method, $endpoint, $data = null)
    {
        $url = $this->base_url . $endpoint;

        $ch = curl_init();

        // Common options
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );

        // Attach API key if configured
        if (!empty($this->api_key)) {
            $headers[] = 'X-Api-Key: ' . $this->api_key;
        }

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => FALSE,  // Local Docker — no SSL
        ));

        // Method-specific options
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, TRUE);
                if ($data !== null) {
                    $this->last_payload = json_encode($data);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->last_payload);
                }
                break;

            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;

            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            case 'GET':
            default:
                // GET is the default
                break;
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->last_http_code = $http_code;
        $this->last_response = $response;
        $error = curl_error($ch);

        curl_close($ch);

        // Handle cURL errors
        if ($response === FALSE) {
            log_message('error', "Waha_lib: cURL error on {$method} {$endpoint}: {$error} | Payload: {$this->last_payload}");
            return FALSE;
        }

        // Log non-2xx responses with full payload context
        if ($http_code < 200 || $http_code >= 300) {
            log_message('error', "Waha_lib: HTTP {$http_code} on {$method} {$endpoint} | Payload: {$this->last_payload} | Response: {$response}");
            return FALSE;
        }

        $decoded = json_decode($response, TRUE);

        if ($decoded !== null) return $decoded;
        
        // If response was successful (2xx) but empty, return an empty array 
        // to ensure it's "truthy" in the calling controller.
        if (trim($response) === '') {
            return array('status' => 'ok');
        }
        
        return $response;
    }

    /**
     * Execute a raw (non-JSON) request — used for binary responses like QR images.
     *
     * @param  string $method   HTTP method
     * @param  string $endpoint API path
     * @return string|false     Raw response body or FALSE on failure
     */
    private function _request_raw($method, $endpoint)
    {
        $url = $this->base_url . $endpoint;

        $ch = curl_init();

        $headers = array();

        if (!empty($this->api_key)) {
            $headers[] = 'X-Api-Key: ' . $this->api_key;
        }

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => FALSE,
        ));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === FALSE) {
            log_message('error', "Waha_lib: cURL error (raw) on {$method} {$endpoint}: {$error}");
            return FALSE;
        }

        if ($http_code < 200 || $http_code >= 300) {
            log_message('error', "Waha_lib: HTTP {$http_code} (raw) on {$method} {$endpoint}");
            return FALSE;
        }

        return $response;
    }

    /**
     * Format a phone number into a WhatsApp chatId.
     *
     * Strips leading '+' or '0' and appends '@c.us'.
     * Examples:
     *   '6281234567890'  → '6281234567890@c.us'
     *   '+6281234567890' → '6281234567890@c.us'
     *   '081234567890'   → '81234567890@c.us'  (caller should normalise first)
     *
     * @param  string $phone Phone number
     * @return string        WhatsApp chatId
     */
    private function _format_chat_id($phone)
    {
        // Remove any non-numeric characters except leading '+'
        $phone = preg_replace('/[^\d]/', '', $phone);

        // Ensure it doesn't already have @c.us
        if (strpos($phone, '@') !== FALSE) {
            return $phone;
        }

        return $phone . '@c.us';
    }
}
