# Master Document: Sistem Integrasi WAHA & Manajemen Client (CRM)
**Proyek:** Sistem Monitoring WhatsApp Perusahaan & CRM
**Developer:** Adrian
**Pemilik Bisnis:** Ka Roseuphoria
**Stack Teknologi:** PHP (CodeIgniter), PostgreSQL, Docker, WAHA API (Core)

---

## BAB 1: Product Requirements Document (PRD)

### 1.1 Visi Produk
Membangun sistem komunikasi WhatsApp terpusat bagi perusahaan. Sistem ini memberikan kontrol penuh kepada Owner untuk memantau interaksi pegawai dengan klien secara *real-time*, sekaligus menjaga kepemilikan data (arsip *chat* dan kontak) di *server* internal perusahaan.

### 1.2 Hak Akses & Peran (User Roles)
* **Owner (Super Admin):**
    * Memiliki akses "Global Monitor" untuk melihat seluruh riwayat obrolan dari semua sesi WA.
    * Memantau kecepatan dan kualitas respon pegawai terhadap klien.
    * Mengelola data master klien dan melihat rekam jejak digital pegawai (*Audit Log*).
* **Employee (Staff):**
    * Melakukan *pairing* WhatsApp dengan cara *scan* QR Code di dalam CMS.
    * Berkomunikasi dengan klien melalui antarmuka web.
    * Mengelola dan memperbarui profil klien yang mereka tangani.

### 1.3 Kebutuhan Fungsional (Functional Requirements)
1. **Multi-Session WAHA:** Sistem mampu menangani beberapa nomor WhatsApp secara bersamaan dalam satu *dashboard* CMS.
2. **Owner Supervision:** Sistem mencatat identitas pegawai (`employee_id`) pada setiap pesan keluar, sehingga Owner bisa memfilter riwayat pesan berdasarkan siapa yang mengirimnya.
3. **Auto-Contact Discovery:** Jika ada nomor baru yang mengirim pesan, sistem otomatis membuat profil klien baru di *database*.
4. **Audit Trail:** Mencatat aktivitas krusial pegawai seperti *Login*, *Logout*, Mengirim Pesan, dan Mengubah Data Klien.
5. **Decoupled Storage:** Seluruh riwayat obrolan tersimpan mandiri di PostgreSQL, mencegah kehilangan data jika aplikasi WhatsApp di HP terhapus atau akun diblokir.

---

## BAB 2: Spesifikasi Database (ERD PostgreSQL)

### 2.1 Konsep Arsitektur
Sistem ini menggunakan relasi ketat (*strict relational*) untuk memastikan **Akuntabilitas**. Setiap aksi yang dilakukan di CMS harus memiliki jejak yang mengarah ke tabel `employees`.

### 2.2 Struktur Tabel (Data Definition Language)

```sql
-- 1. Tabel Identitas (Admin & Staff)
CREATE TABLE employees (
    id SERIAL PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role VARCHAR(20) CHECK (role IN ('owner', 'staff')),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Tabel Manajemen Sesi WAHA
CREATE TABLE wa_sessions (
    session_id VARCHAR(50) PRIMARY KEY, -- Nama sesi unik dari WAHA
    employee_id INT REFERENCES employees(id) ON DELETE CASCADE,
    wa_number VARCHAR(20) UNIQUE,
    status VARCHAR(20) DEFAULT 'SCAN_QR', -- SCAN_QR, CONNECTED, DISCONNECTED
    battery_level INT DEFAULT 0,
    last_seen TIMESTAMPTZ DEFAULT NOW()
);

-- 3. Tabel Buku Telepon Digital (Klien)
CREATE TABLE wa_contacts (
    phone_number VARCHAR(20) PRIMARY KEY, -- ID Unik: Format 628...
    fullname VARCHAR(100) NOT NULL,
    company_name VARCHAR(100),
    email VARCHAR(100),
    address TEXT,
    category VARCHAR(50) DEFAULT 'General', -- VIP, Prospect, Complain
    notes TEXT, -- Catatan khusus tentang klien
    assigned_to INT REFERENCES employees(id) ON DELETE SET NULL, -- Penanggung jawab
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. Tabel Arsip Percakapan (Log Chat & Supervisi)
CREATE TABLE wa_messages (
    id BIGSERIAL PRIMARY KEY,
    waha_msg_id VARCHAR(100) UNIQUE, -- ID unik dari sistem WhatsApp
    session_id VARCHAR(50) REFERENCES wa_sessions(session_id) ON DELETE SET NULL,
    employee_id INT REFERENCES employees(id) ON DELETE SET NULL, -- Penanda akuntabilitas (Siapa yang kirim)
    contact_phone VARCHAR(20) REFERENCES wa_contacts(phone_number) ON DELETE CASCADE,
    direction VARCHAR(5) CHECK (direction IN ('IN', 'OUT')),
    message_type VARCHAR(20) DEFAULT 'text',
    body TEXT NOT NULL,
    media_url TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. Tabel Jejak Digital (Audit Trail)
CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    employee_id INT REFERENCES employees(id) ON DELETE CASCADE,
    action VARCHAR(100) NOT NULL, -- Contoh: 'LOGIN', 'SEND_MESSAGE', 'EDIT_CONTACT'
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMPTZ DEFAULT NOW()
);