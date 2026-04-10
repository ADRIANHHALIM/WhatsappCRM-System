<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * =============================================================================
 * Message_model — CRUD for wa_messages Table
 * =============================================================================
 *
 * Handles all database operations for WhatsApp messages:
 *   - Saving outgoing messages (with mandatory employee_id for accountability)
 *   - Saving incoming messages from the WAHA webhook
 *   - Retrieving conversations and managing read status
 *   - Deduplication via waha_msg_id
 *
 * All queries use Query Bindings (prepared statements) to prevent SQL Injection.
 *
 * @package    WhatsApp CRM
 * @subpackage Models
 * @author     Adrian
 * =============================================================================
 */
class Message_model extends CI_Model
{
    /**
     * Table name constant for easy refactoring.
     * @var string
     */
    private $table = 'wa_messages';

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =========================================================================
    // SAVE MESSAGES
    // =========================================================================

    /**
     * Save an outgoing (OUT) message sent by an employee.
     *
     * IMPORTANT: employee_id is REQUIRED for accountability/supervision.
     *
     * @param  array $data Associative array with keys:
     *   - waha_msg_id    (string)  WAHA message ID
     *   - session_id     (string)  WAHA session name
     *   - employee_id    (int)     REQUIRED — who sent the message
     *   - contact_phone  (string)  Recipient phone number
     *   - body           (string)  Message content
     *   - message_type   (string)  Optional, defaults to 'text'
     *   - media_url      (string)  Optional
     * @return int|false  Inserted message ID or FALSE on failure
     */
    public function save_outgoing($data)
    {
        $insert = array(
            'waha_msg_id'   => isset($data['waha_msg_id'])   ? $data['waha_msg_id']   : null,
            'session_id'    => isset($data['session_id'])    ? $data['session_id']    : null,
            'employee_id'   => empty($data['employee_id'])   ? null : (int) $data['employee_id'],
            'contact_phone' => $data['contact_phone'],
            'direction'     => 'OUT',
            'message_type'  => isset($data['message_type'])  ? $data['message_type']  : 'text',
            'body'          => $data['body'],
            'media_url'     => isset($data['media_url'])     ? $data['media_url']     : null,
            'is_read'       => TRUE,  // Outgoing messages are always "read"
        );
        if (isset($data['created_at'])) {
            $insert['created_at'] = $data['created_at'];
        }

        $this->db->insert($this->table, $insert);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        log_message('error', 'Message_model::save_outgoing — Insert failed: ' . $this->db->error()['message']);
        return FALSE;
    }

    /**
     * Save an incoming (IN) message received via the WAHA webhook.
     *
     * employee_id is NULL for incoming messages (the sender is a contact, not staff).
     * Performs deduplication via waha_msg_id.
     *
     * @param  array $data Associative array with keys:
     *   - waha_msg_id    (string)  WAHA message ID (used for deduplication)
     *   - session_id     (string)  WAHA session name
     *   - contact_phone  (string)  Sender phone number
     *   - body           (string)  Message content
     *   - message_type   (string)  Optional, defaults to 'text'
     *   - media_url      (string)  Optional
     * @return int|false  Inserted message ID, or FALSE if duplicate / failure
     */
    public function save_incoming($data)
    {
        // Deduplication: skip if this message was already saved
        if ( ! empty($data['waha_msg_id']) && $this->message_exists($data['waha_msg_id'])) {
            log_message('debug', 'Message_model::save_incoming — Duplicate waha_msg_id: ' . $data['waha_msg_id']);
            return FALSE;
        }

        $insert = array(
            'waha_msg_id'   => isset($data['waha_msg_id'])   ? $data['waha_msg_id']   : null,
            'session_id'    => isset($data['session_id'])     ? $data['session_id']    : null,
            'employee_id'   => null,  // Incoming — no employee sender
            'contact_phone' => $data['contact_phone'],
            'direction'     => 'IN',
            'message_type'  => isset($data['message_type'])  ? $data['message_type']  : 'text',
            'body'          => $data['body'],
            'media_url'     => isset($data['media_url'])      ? $data['media_url']     : null,
            'is_read'       => FALSE,
        );

        $this->db->insert($this->table, $insert);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        log_message('error', 'Message_model::save_incoming — Insert failed: ' . $this->db->error()['message']);
        return FALSE;
    }

    // =========================================================================
    // RETRIEVE MESSAGES
    // =========================================================================

    /**
     * Get the conversation (chat history) between a session and a contact.
     *
     * Results are ordered by created_at ASC (oldest first), suitable for
     * rendering a chat UI.
     *
     * @param  string $contact_phone Contact phone number
     * @param  string $session_id    WAHA session name
     * @param  int    $limit         Number of messages to fetch (default 50)
     * @param  int    $offset        Offset for pagination (default 0)
     * @return array                 Array of message rows
     */
    public function get_conversation($contact_phone, $session_id, $limit = 50, $offset = 0)
    {
        $sql = "SELECT m.*, e.fullname AS employee_name
                FROM {$this->table} m
                LEFT JOIN employees e ON e.id = m.employee_id
                WHERE m.contact_phone = ?
                  AND m.session_id = ?
                ORDER BY m.created_at ASC
                LIMIT ? OFFSET ?";

        $query = $this->db->query($sql, array(
            $contact_phone,
            $session_id,
            (int) $limit,
            (int) $offset,
        ));

        return $query->result_array();
    }

    /**
     * Get the latest conversations (chat list view).
     *
     * Returns one row per contact with the most recent message, ordered by
     * newest conversation first. Used for rendering the chat sidebar.
     *
     * @param  string   $session_id WAHA session name
     * @param  int      $limit      Number of conversations (default 50)
     * @return array                Array of rows with contact info + latest message
     */
    public function get_latest_conversations($session_id, $limit = 50)
    {
        $sql = "SELECT DISTINCT ON (m.contact_phone)
                    m.id,
                    m.contact_phone,
                    m.body AS last_message,
                    m.direction AS last_direction,
                    m.message_type,
                    m.created_at AS last_message_at,
                    m.is_read,
                    c.fullname AS contact_name,
                    c.category AS contact_category,
                    c.profile_pic_url,
                    (SELECT COUNT(*) FROM {$this->table}
                     WHERE contact_phone = m.contact_phone
                       AND session_id = ?
                       AND direction = 'IN'
                       AND is_read = FALSE
                    ) AS unread_count
                FROM {$this->table} m
                LEFT JOIN wa_contacts c ON c.phone_number = m.contact_phone
                WHERE m.session_id = ?
                ORDER BY m.contact_phone, m.created_at DESC";

        $wrapped_sql = "SELECT sub.*
                        FROM ({$sql}) sub
                        ORDER BY sub.last_message_at DESC
                        LIMIT ?";

        $query = $this->db->query($wrapped_sql, array(
            $session_id,
            $session_id,
            (int) $limit,
        ));

        return $query->result_array();
    }

    /**
     * Alias for AJAX sidebar endpoint — same query, returns array.
     */
    public function get_sidebar_conversations($session_id, $limit = 50)
    {
        return $this->get_latest_conversations($session_id, $limit);
    }

    // =========================================================================
    // READ STATUS
    // =========================================================================

    /**
     * Count unread incoming messages for a specific session.
     *
     * @param  string $session_id WAHA session name
     * @return int                Number of unread messages
     */
    public function get_unread_count($session_id)
    {
        $sql = "SELECT COUNT(*) AS cnt
                FROM {$this->table}
                WHERE session_id = ?
                  AND direction = 'IN'
                  AND is_read = FALSE";

        $query = $this->db->query($sql, array($session_id));
        $row   = $query->row_array();

        return (int) $row['cnt'];
    }

    /**
     * Mark all incoming messages from a contact as read.
     *
     * Typically called when an employee opens a conversation.
     *
     * @param  string $contact_phone Contact phone number
     * @param  string $session_id    WAHA session name
     * @return bool                  TRUE on success
     */
    public function mark_as_read($contact_phone, $session_id)
    {
        $sql = "UPDATE {$this->table}
                SET is_read = TRUE
                WHERE contact_phone = ?
                  AND session_id = ?
                  AND direction = 'IN'
                  AND is_read = FALSE";

        $this->db->query($sql, array($contact_phone, $session_id));

        return TRUE;
    }

    // =========================================================================
    // DEDUPLICATION
    // =========================================================================

    /**
     * Check if a message with the given WAHA message ID already exists.
     *
     * Used to prevent saving duplicate webhook payloads.
     *
     * @param  string $waha_msg_id WAHA message ID
     * @return bool                TRUE if exists, FALSE otherwise
     */
    public function message_exists($waha_msg_id)
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE waha_msg_id = ? LIMIT 1";

        $query = $this->db->query($sql, array($waha_msg_id));

        return $query->num_rows() > 0;
    }

    // =========================================================================
    // SUPERVISION (OWNER) QUERIES
    // =========================================================================

    /**
     * Get all messages sent by a specific employee (for Owner supervision).
     *
     * @param  int $employee_id Employee ID
     * @param  int $limit       Number of messages (default 100)
     * @param  int $offset      Offset for pagination (default 0)
     * @return array             Array of message rows
     */
    public function get_messages_by_employee($employee_id, $limit = 100, $offset = 0)
    {
        $sql = "SELECT m.*, c.fullname AS contact_name, e.fullname AS employee_name
                FROM {$this->table} m
                LEFT JOIN wa_contacts c ON c.phone_number = m.contact_phone
                LEFT JOIN employees e ON e.id = m.employee_id
                WHERE m.employee_id = ?
                ORDER BY m.created_at DESC
                LIMIT ? OFFSET ?";

        $query = $this->db->query($sql, array(
            (int) $employee_id,
            (int) $limit,
            (int) $offset,
        ));

        return $query->result_array();
    }

    /**
     * Search messages by body content (for Owner dashboard search).
     *
     * @param  string      $keyword    Search term
     * @param  string|null $session_id Optional session filter
     * @param  int         $limit      Number of results (default 50)
     * @return array                   Array of matching message rows
     */
    public function search_messages($keyword, $session_id = null, $limit = 50)
    {
        $params = array('%' . strtolower($keyword) . '%');

        $sql = "SELECT m.*, c.fullname AS contact_name, e.fullname AS employee_name
                FROM {$this->table} m
                LEFT JOIN wa_contacts c ON c.phone_number = m.contact_phone
                LEFT JOIN employees e ON e.id = m.employee_id
                WHERE LOWER(m.body) LIKE ?";

        if ($session_id !== null) {
            $sql .= " AND m.session_id = ?";
            $params[] = $session_id;
        }

        $sql .= " ORDER BY m.created_at DESC LIMIT ?";
        $params[] = (int) $limit;

        $query = $this->db->query($sql, $params);

        return $query->result_array();
    }
}
