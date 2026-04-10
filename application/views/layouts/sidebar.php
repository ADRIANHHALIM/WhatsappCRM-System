<aside class="premium-sidebar" id="appSidebar">
    <div class="sidebar-header">
        <div class="brand-wrapper">
            <div class="brand-icon">
                <i class="bi bi-whatsapp"></i>
            </div>
            <div class="brand-text">
                <h1 class="brand-title">CRM Hub</h1>
                <p class="brand-subtitle">Corporate Portal</p>
            </div>
        </div>
        <button class="toggle-btn" id="toggleSidebar" title="Toggle Sidebar">
            <i class="bi bi-chevron-left" id="toggleIcon"></i>
        </button>
    </div>

    <div class="sidebar-search">
        <div class="search-input-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search menu...">
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($this->session->userdata('role') === 'owner'): ?>
            <a href="<?= site_url('whatsapp/owner_view') ?>"
                class="nav-item <?= current_url() == site_url('whatsapp/owner_view') ? 'active' : '' ?>"
                title="Owner Dashboard">
                <div class="nav-icon"><i class="bi bi-speedometer2"></i></div>
                <div class="nav-text">
                    <span class="nav-label">Dashboard</span>
                    <span class="nav-desc">Supervision & Metrics</span>
                </div>
                <div class="active-indicator"></div>
            </a>
        <?php endif; ?>

        <a href="<?= site_url('whatsapp/chat_room') ?>"
            class="nav-item <?= current_url() == site_url('whatsapp/chat_room') ? 'active' : '' ?>" title="Chat Room">
            <div class="nav-icon"><i class="bi bi-chat-dots"></i></div>
            <div class="nav-text">
                <span class="nav-label">Chat Room</span>
                <span class="nav-desc">Live Client Messaging</span>
            </div>
            <div class="active-indicator"></div>
        </a>
    </nav>

    <div class="sidebar-role-badge">
        <div class="role-box">
            <div class="role-header">
                <i class="bi bi-shield-check"></i>
                <span>CURRENT ROLE</span>
            </div>
            <div class="role-name"><?= $this->session->userdata('role') ?></div>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile-btn" id="userMenuBtn">
            <div class="user-avatar">
                <?= strtoupper(substr($this->session->userdata('fullname'), 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= $this->session->userdata('fullname') ?></div>
                <div class="user-status">Online</div>
            </div>
            <i class="bi bi-chevron-up toggle-dropdown"></i>
        </div>

        <div class="user-popover" id="userPopover">
            <div class="popover-header">
                <div class="user-avatar">
                    <?= strtoupper(substr($this->session->userdata('fullname'), 0, 1)) ?>
                </div>
                <div>
                    <div class="popover-name"><?= $this->session->userdata('fullname') ?></div>
                    <div class="popover-role"><?= ucfirst($this->session->userdata('role')) ?> Account</div>
                </div>
            </div>
            <div class="popover-body">
                <a href="<?= site_url('auth/logout') ?>" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <div>
                        <span class="logout-label">Logout</span>
                        <span class="logout-desc">Sign out securely</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</aside>

<style>
    /* CSS Variables matching React Theme */
    :root {
        --sb-bg: rgba(15, 23, 42, 0.6);
        --sb-border: rgba(255, 255, 255, 0.08);
        --sb-text: #94a3b8;
        --sb-text-hover: #f8fafc;
        --sb-accent: #3b82f6;
        --sb-accent-bg: rgba(59, 130, 246, 0.15);
        --sb-width: 320px;
        --sb-width-collapsed: 90px;
        --sb-transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Floating Sidebar Core */
    .premium-sidebar {
        width: var(--sb-width);
        height: calc(100vh - 2rem);
        margin: 1rem 0 1rem 1rem;
        background: var(--sb-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--sb-border);
        border-radius: 1.5rem;
        display: flex;
        flex-direction: column;
        transition: var(--sb-transition);
        position: relative;
        z-index: 100;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    /* --- Collapsed State (Dengan Fix Tombol Toggle) --- */
    .premium-sidebar.collapsed {
        width: var(--sb-width-collapsed);
    }

    .premium-sidebar.collapsed .brand-text,
    .premium-sidebar.collapsed .search-input-wrapper input,
    .premium-sidebar.collapsed .nav-text,
    .premium-sidebar.collapsed .sidebar-role-badge,
    .premium-sidebar.collapsed .user-info,
    .premium-sidebar.collapsed .toggle-dropdown {
        display: none;
        opacity: 0;
    }

    .premium-sidebar.collapsed .sidebar-header {
        flex-direction: column;
        padding: 1.5rem 0.5rem;
        gap: 1.25rem;
    }

    .premium-sidebar.collapsed .toggle-btn {
        background: rgba(255, 255, 255, 0.05);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .premium-sidebar.collapsed .sidebar-search,
    .premium-sidebar.collapsed .user-profile-btn {
        justify-content: center;
        padding: 0.75rem;
    }

    .premium-sidebar.collapsed .nav-item {
        justify-content: center;
        padding: 0.75rem;
    }

    .premium-sidebar.collapsed .brand-wrapper {
        justify-content: center;
        width: 100%;
    }

    /* Header */
    .sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 1px solid var(--sb-border);
        transition: var(--sb-transition);
    }

    .brand-wrapper {
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: var(--sb-transition);
    }

    .brand-icon {
        width: 40px;
        height: 40px;
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        border: 1px solid rgba(34, 197, 94, 0.2);
        flex-shrink: 0;
    }

    .brand-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        white-space: nowrap;
    }

    .brand-subtitle {
        font-size: 0.75rem;
        color: var(--sb-text);
        margin: 0;
        white-space: nowrap;
    }

    .toggle-btn {
        background: transparent;
        border: none;
        color: var(--sb-text);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: 0.3s;
        flex-shrink: 0;
    }

    .toggle-btn:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    /* Search */
    .sidebar-search {
        padding: 1rem 1.25rem;
    }

    .search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--sb-border);
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        transition: 0.3s;
    }

    .search-input-wrapper:focus-within {
        border-color: var(--sb-accent);
        box-shadow: 0 0 0 2px var(--sb-accent-bg);
    }

    .search-input-wrapper i {
        color: var(--sb-text);
        font-size: 0.9rem;
    }

    .search-input-wrapper input {
        background: transparent;
        border: none;
        color: #fff;
        margin-left: 0.5rem;
        width: 100%;
        outline: none;
        font-size: 0.85rem;
    }

    .search-input-wrapper input::placeholder {
        color: #64748b;
    }

    /* Nav Menu */
    .sidebar-nav {
        flex: 1;
        padding: 0.5rem 1rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 1rem;
        text-decoration: none;
        border-radius: 12px;
        color: var(--sb-text);
        transition: 0.3s;
        position: relative;
        overflow: hidden;
    }

    .nav-item:hover {
        background: var(--sb-accent-bg);
        color: var(--sb-text-hover);
    }

    .nav-item.active {
        background: var(--sb-accent);
        color: #fff;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .nav-icon {
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        flex-shrink: 0;
    }

    .nav-text {
        display: flex;
        flex-direction: column;
        white-space: nowrap;
        overflow: hidden;
    }

    .nav-label {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .nav-desc {
        font-size: 0.65rem;
        opacity: 0.7;
    }

    .active-indicator {
        display: none;
        position: absolute;
        right: 10px;
        width: 4px;
        height: 20px;
        background: #fff;
        border-radius: 4px;
        opacity: 0.8;
    }

    .nav-item.active .active-indicator {
        display: block;
    }

    /* Role Badge */
    .sidebar-role-badge {
        padding: 1rem 1.25rem;
    }

    .role-box {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 12px;
        padding: 0.75rem 1rem;
    }

    .role-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--sb-accent);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 0.25rem;
    }

    .role-name {
        color: #fff;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: capitalize;
    }

    /* Footer & Popover */
    .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid var(--sb-border);
        position: relative;
    }

    .user-profile-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: 12px;
        cursor: pointer;
        transition: 0.3s;
        border: 1px solid transparent;
    }

    .user-profile-btn:hover,
    .user-profile-btn.open {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--sb-border);
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        background: var(--sb-accent);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.2);
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        overflow: hidden;
        white-space: nowrap;
    }

    .user-name {
        color: #fff;
        font-weight: 600;
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-status {
        color: #22c55e;
        font-size: 0.7rem;
        font-weight: 500;
    }

    .toggle-dropdown {
        color: var(--sb-text);
        transition: transform 0.3s;
        flex-shrink: 0;
    }

    .user-profile-btn.open .toggle-dropdown {
        transform: rotate(180deg);
        color: #fff;
    }

    /* The Popover Menu */
    .user-popover {
        position: absolute;
        bottom: calc(100% + 10px);
        left: 1rem;
        right: 1rem;
        background: #0f172a;
        border: 1px solid var(--sb-border);
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: 0.3s;
        z-index: 200;
        overflow: hidden;
    }

    .user-popover.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .popover-header {
        padding: 1rem;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(0, 0, 0, 0));
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid var(--sb-border);
    }

    .popover-name {
        color: #fff;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .popover-role {
        color: var(--sb-text);
        font-size: 0.75rem;
    }

    .popover-body {
        padding: 0.5rem;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: 10px;
        color: #f87171;
        text-decoration: none;
        transition: 0.3s;
    }

    .logout-btn:hover {
        background: rgba(248, 113, 113, 0.1);
        color: #fca5a5;
    }

    .logout-label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .logout-desc {
        display: block;
        font-size: 0.7rem;
        opacity: 0.7;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Sidebar Collapse Logic
        const sidebar = document.getElementById('appSidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const toggleIcon = document.getElementById('toggleIcon');

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            // Ubah icon chevron saat dilipat/dibuka
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.replace('bi-chevron-left', 'bi-chevron-right');
            } else {
                toggleIcon.classList.replace('bi-chevron-right', 'bi-chevron-left');
            }
        });

        // 2. User Menu Popover Logic
        const userBtn = document.getElementById('userMenuBtn');
        const popover = document.getElementById('userPopover');

        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userBtn.classList.toggle('open');
            popover.classList.toggle('show');
        });

        // Tutup popover jika user klik di area luar
        document.addEventListener('click', function (e) {
            if (!userBtn.contains(e.target) && !popover.contains(e.target)) {
                userBtn.classList.remove('open');
                popover.classList.remove('show');
            }
        });
    });
</script>

<div class="main-content">