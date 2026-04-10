-- =============================================================================
-- WhatsApp CRM & Supervision System — Database Initialisation
-- =============================================================================
-- This script runs automatically on the FIRST boot of the PostgreSQL container
-- via docker-entrypoint-initdb.d. It will NOT re-run if the volume already
-- contains data.
-- =============================================================================

BEGIN;

-- ============================================================
-- 1. Tabel Identitas (Admin & Staff)
-- ============================================================
CREATE TABLE IF NOT EXISTS employees (
    id          SERIAL PRIMARY KEY,
    fullname    VARCHAR(100) NOT NULL,
    username    VARCHAR(50)  UNIQUE NOT NULL,
    password    TEXT         NOT NULL,
    role        VARCHAR(20)  CHECK (role IN ('owner', 'staff')),
    is_active   BOOLEAN      DEFAULT TRUE,
    created_at  TIMESTAMPTZ  DEFAULT NOW()
);

-- ============================================================
-- 2. Tabel Manajemen Sesi WAHA
-- ============================================================
CREATE TABLE IF NOT EXISTS wa_sessions (
    session_id    VARCHAR(50)  PRIMARY KEY,          -- Nama sesi unik dari WAHA
    employee_id   INT          REFERENCES employees(id) ON DELETE CASCADE,
    wa_number     VARCHAR(20)  UNIQUE,
    status        VARCHAR(20)  DEFAULT 'SCAN_QR',    -- SCAN_QR, CONNECTED, DISCONNECTED
    battery_level INT          DEFAULT 0,
    last_seen     TIMESTAMPTZ  DEFAULT NOW()
);

-- ============================================================
-- 3. Tabel Buku Telepon Digital (Klien)
-- ============================================================
CREATE TABLE IF NOT EXISTS wa_contacts (
    phone_number  VARCHAR(20)  PRIMARY KEY,          -- Format 628...
    fullname      VARCHAR(100) NOT NULL,
    company_name  VARCHAR(100),
    email         VARCHAR(100),
    address       TEXT,
    category      VARCHAR(50)  DEFAULT 'General',    -- VIP, Prospect, Complain
    notes         TEXT,
    assigned_to   INT          REFERENCES employees(id) ON DELETE SET NULL,
    created_at    TIMESTAMPTZ  DEFAULT NOW()
);

-- ============================================================
-- 4. Tabel Arsip Percakapan (Log Chat & Supervisi)
-- ============================================================
CREATE TABLE IF NOT EXISTS wa_messages (
    id             BIGSERIAL    PRIMARY KEY,
    waha_msg_id    VARCHAR(100) UNIQUE,              -- ID unik dari sistem WhatsApp
    session_id     VARCHAR(50)  REFERENCES wa_sessions(session_id) ON DELETE SET NULL,
    employee_id    INT          REFERENCES employees(id) ON DELETE SET NULL,
    contact_phone  VARCHAR(20)  REFERENCES wa_contacts(phone_number) ON DELETE CASCADE,
    direction      VARCHAR(5)   CHECK (direction IN ('IN', 'OUT')),
    message_type   VARCHAR(20)  DEFAULT 'text',
    body           TEXT         NOT NULL,
    media_url      TEXT,
    is_read        BOOLEAN      DEFAULT FALSE,
    created_at     TIMESTAMPTZ  DEFAULT NOW()
);

-- ============================================================
-- 5. Tabel Jejak Digital (Audit Trail)
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id           SERIAL       PRIMARY KEY,
    employee_id  INT          REFERENCES employees(id) ON DELETE CASCADE,
    action       VARCHAR(100) NOT NULL,              -- LOGIN, SEND_MESSAGE, EDIT_CONTACT
    description  TEXT,
    ip_address   VARCHAR(45),
    created_at   TIMESTAMPTZ  DEFAULT NOW()
);

-- ============================================================
-- Performance Indexes
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_messages_session     ON wa_messages(session_id);
CREATE INDEX IF NOT EXISTS idx_messages_contact     ON wa_messages(contact_phone);
CREATE INDEX IF NOT EXISTS idx_messages_created     ON wa_messages(created_at);
CREATE INDEX IF NOT EXISTS idx_messages_direction   ON wa_messages(direction);
CREATE INDEX IF NOT EXISTS idx_messages_employee    ON wa_messages(employee_id);
CREATE INDEX IF NOT EXISTS idx_audit_employee       ON audit_logs(employee_id);
CREATE INDEX IF NOT EXISTS idx_audit_created        ON audit_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_sessions_employee    ON wa_sessions(employee_id);

-- ============================================================
-- Seed Data — Default Owner Account
-- ============================================================
-- Password: admin123  (bcrypt hash)
-- IMPORTANT: Change this immediately in production!
INSERT INTO employees (fullname, username, password, role, is_active)
VALUES (
    'Administrator',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- admin123
    'owner',
    TRUE
)
ON CONFLICT (username) DO NOTHING;

COMMIT;
