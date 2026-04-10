# WhatsApp CRM & Supervision Hub

A high-performance corporate portal built for efficient WhatsApp-based customer relations and team supervision. This system integrates seamlessly with the **WAHA (WhatsApp HTTP API)** to provide real-time messaging capabilities within a professional CRM environment.

## 🚀 Key Features

- **Omnichannel Chat Room**: A real-time unified interface for live client messaging.
- **Owner Dashboard**: Strategic overview with supervision metrics and team performance tracking.
- **Role-Based Access Control**: Granular permissions for `Owner` and `Staff` roles.
- **Webhook Integration**: Instant message synchronization and status updates via WAHA webhooks.
- **Premium UI/UX**: Modern glassmorphism design with a fully responsive and interactive sidebar.
- **Docker-Ready**: Containerized infrastructure for easy deployment and scaling.

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+ (CodeIgniter 3 Framework)
- **Database**: PostgreSQL 16
- **Messaging Gateway**: [WAHA (WhatsApp HTTP API)](https://waha.devlikeapro.com/)
- **Frontend**: Vanilla JS (ES6+), Bootstrap Icons, Custom CSS3 Utilities
- **Infrastructure**: Docker & Docker Compose

## 📋 Prerequisites

- Docker and Docker Compose installed.
- WhatsApp account for WAHA session.

## ⚙️ Installation & Setup

1. **Clone the Project**
   ```bash
   git clone https://github.com/ADRIANHHALIM/WhatsappCRM-System.git
   cd WhatsappCRM-System
   ```

2. **Configure Environment Variables**
   Create a `.env` file in the root directory:
   ```env
   # Application
   APP_BASE_URL=http://localhost:8080

   # Database
   DB_HOST=localhost
   DB_NAME=waha_crm
   DB_USER=waha_user
   DB_PASS=your_secure_password
   DB_PORT=5432

   # WAHA API
   WAHA_API_KEY=your_waha_secret
   ```

3. **Launch Infrastructure**
   ```bash
   docker-compose up -d
   ```

4. **Initialize Database**
   Import the `db_seed.sql` file into your PostgreSQL instance to create initial roles and users.
   - Default Owner: `owner` / `admin123`
   - Default Staff: `adrian` / `admin123`

## 🏗️ Architecture

The system operates as a middle layer between the user interface and the WhatsApp network:
1. **User** sends a message via the **CRM Portal**.
2. **CodeIgniter** forwards the request to the **WAHA API**.
3. **WAHA** executes the message delivery via the WhatsApp protocol.
4. **Incoming messages** are captured by **WAHA Webhooks** and pushed back to the **CRM Webhook Controller**.

## 🛡️ Security

- **Bcrypt Hashing**: All passwords are encrypted using modern bcrypt standards.
- **Environment Isolation**: Sensitive credentials are managed via `.env` and excluded from source control.
- **Session Security**: Secure session handling for corporate users.

---
Developed by [ADRIAN HALIM](https://github.com/ADRIANHHALIM)
