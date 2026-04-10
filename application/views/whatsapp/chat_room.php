<!-- ============================================================
     WhatsApp Web CRM — Chat Room
     Real-time SPA powered by Vanilla JS (no jQuery)
     ============================================================ -->

<!-- ── QR / Connection Overlay ─────────────────────────────── -->
<div id="connectionOverlay"
    class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
    style="background: var(--app-bg); z-index: 1050;">
    <div class="glass-panel p-5 text-center shadow-lg" style="max-width: 500px; width: 90%;">
        <h3 class="fw-bold mb-4">Connecting to WhatsApp</h3>
        <div class="bg-white p-4 rounded-4 mb-4 d-inline-flex align-items-center justify-content-center mx-auto"
            style="min-height: 250px; min-width: 250px;" id="qrContainer">
            <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div>
            <span class="badge bg-secondary p-2 px-3 fw-normal" id="statusBadge" style="font-size: 0.9rem;">
                Status: Checking connection...
            </span>
        </div>
        <hr class="border-secondary my-4 opacity-25">
        <div class="text-start pe-3">
            <h6 class="fw-bold">Instructions:</h6>
            <ol class="text-muted small ps-3 mb-0">
                <li>Open WhatsApp on your phone</li>
                <li>Tap <b>Menu</b> or <b>Settings</b> and select <b>Linked Devices</b></li>
                <li>Tap on <b>Link a Device</b></li>
                <li>Point your phone to this screen to capture the code</li>
            </ol>
        </div>
    </div>
</div>

<!-- ── Styles ───────────────────────────────────────────────── -->
<style>
    /* ── Layout ──────────────────────────────────────────────── */
    .premium-chat-wrapper {
        display: flex;
        flex: 1;
        width: 100%;
        height: calc(100vh - 2rem);
        margin: 1rem 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    /* ── Sidebar (contact list) ──────────────────────────────── */
    .premium-contact-list {
        width: 380px;
        flex-shrink: 0;
        background: rgba(15, 23, 42, 0.6);
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .premium-search-header {
        padding: 1.5rem;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.4) 100%);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        flex-shrink: 0;
    }

    .glass-search-input {
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 1rem !important;
        color: #fff !important;
        padding: 0.75rem 1.25rem !important;
        transition: all 0.3s ease !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    }

    .glass-search-input:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: rgba(59, 130, 246, 0.5) !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    }

    .glass-search-input::placeholder {
        color: rgba(255, 255, 255, 0.3) !important;
    }

    #contactListBody {
        overflow-y: auto;
        flex: 1;
    }

    .premium-contact-item {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 0.85rem;
        position: relative;
        min-width: 0;
    }

    .premium-contact-item:hover {
        background: rgba(255, 255, 255, 0.05);
        transform: translateX(3px);
    }

    .premium-contact-item.active {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.18) 0%, rgba(59, 130, 246, 0.04) 100%);
        border-left: 3px solid #3b82f6;
        padding-left: calc(1.5rem - 3px);
    }

    /* ── Avatars ─────────────────────────────────────────────── */
    .premium-avatar {
        width: 50px;
        height: 50px;
        border-radius: 1rem;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    }

    .premium-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: inherit;
    }

    .premium-avatar.sm {
        width: 44px;
        height: 44px;
        font-size: 1rem;
        border-radius: 0.75rem;
    }

    /* ── Chat area ───────────────────────────────────────────── */
    .premium-chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        background-color: #0b141a;
        background-blend-mode: overlay;
        position: relative;
        min-width: 0;
    }

    .premium-chat-header {
        height: 72px;
        padding: 0 1.5rem;
        background: rgba(15, 23, 42, 0.88);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        z-index: 10;
        flex-shrink: 0;
    }

    .premium-chat-header.d-none {
        display: none !important;
    }

    #chatAreaBody {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem 2rem 9rem;
        display: flex;
        flex-direction: column;
    }

    /* ── Floating input ──────────────────────────────────────── */
    .premium-chat-input-wrapper {
        padding: 1.25rem 2rem 1.5rem;
        background: linear-gradient(0deg, rgba(11, 20, 26, 0.97) 0%, transparent 100%);
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }

    .premium-chat-input-wrapper.d-none {
        display: none !important;
    }

    .premium-chat-input-pill {
        background: rgba(30, 41, 59, 0.85);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2rem;
        padding: 0.45rem 0.45rem 0.45rem 1.25rem;
        display: flex;
        align-items: center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
        pointer-events: all;
    }

    .premium-chat-input-pill:focus-within {
        border-color: rgba(59, 130, 246, 0.5);
        background: rgba(30, 41, 59, 0.97);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6), 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .premium-message-input {
        background: transparent !important;
        border: none !important;
        color: white !important;
        box-shadow: none !important;
        font-size: 0.97rem;
        flex: 1;
    }

    .premium-message-input:focus {
        box-shadow: none !important;
    }

    .premium-message-input::placeholder {
        color: rgba(255, 255, 255, .35);
    }

    .premium-send-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
        flex-shrink: 0;
    }

    .premium-send-btn:hover {
        transform: scale(1.1) rotate(6deg);
        box-shadow: 0 6px 18px rgba(34, 197, 94, 0.6);
    }

    .premium-send-btn:active {
        transform: scale(0.94);
    }

    /* ── Message bubbles ─────────────────────────────────────── */
    .premium-bubble {
        max-width: 68%;
        padding: 0.8rem 1.1rem 0.55rem;
        margin-bottom: 0.5rem;
        position: relative;
        font-size: 0.95rem;
        line-height: 1.5;
        word-break: break-word;
        animation: popIn 0.22s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes popIn {
        from {
            opacity: 0;
            transform: scale(0.92) translateY(8px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .premium-message-in {
        background: rgba(32, 44, 57, 0.92);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255, 255, 255, 0.04);
        align-self: flex-start;
        border-radius: 0 1.25rem 1.25rem 1.25rem;
    }

    .premium-message-out {
        background: linear-gradient(135deg, #054d44, #065f52);
        border: 1px solid rgba(34, 197, 94, 0.15);
        align-self: flex-end;
        border-radius: 1.25rem 0 1.25rem 1.25rem;
    }

    .bubble-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 3px;
        margin-top: 4px;
        font-size: 0.67rem;
        color: rgba(255, 255, 255, 0.45);
        line-height: 1;
    }

    .bubble-meta .tick {
        font-size: 0.85rem;
    }

    .tick-sent {
        color: rgba(255, 255, 255, 0.5);
    }

    .tick-delivered {
        color: rgba(255, 255, 255, 0.5);
    }

    .tick-read {
        color: #4fc3f7;
    }

    .sender-label {
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        color: #6ee7b7;
        margin-bottom: 3px;
    }

    /* Date divider */
    .date-divider {
        text-align: center;
        margin: 1rem 0 0.5rem;
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.35);
    }

    .date-divider span {
        background: rgba(255, 255, 255, 0.06);
        padding: 0.25rem 0.9rem;
        border-radius: 2rem;
    }

    /* ── Empty states ────────────────────────────────────────── */
    .chat-empty-state,
    .sidebar-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: rgba(255, 255, 255, 0.35);
    }

    .sidebar-empty-state {
        height: 60%;
    }

    .chat-empty-state {
        flex: 1;
        padding-bottom: 4rem;
    }

    .empty-glow-icon {
        font-size: 5rem;
        background: linear-gradient(135deg, #22c55e, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulseGlow 3s infinite alternate;
        filter: drop-shadow(0 10px 20px rgba(34, 197, 94, 0.15));
    }

    @keyframes pulseGlow {
        from {
            filter: drop-shadow(0 8px 16px rgba(34, 197, 94, 0.1));
            transform: scale(1);
        }

        to {
            filter: drop-shadow(0 18px 36px rgba(34, 197, 94, 0.35));
            transform: scale(1.04);
        }
    }

    /* ── Scrollbars ──────────────────────────────────────────── */
    #contactListBody::-webkit-scrollbar,
    #chatAreaBody::-webkit-scrollbar {
        width: 4px;
    }

    #contactListBody::-webkit-scrollbar-track,
    #chatAreaBody::-webkit-scrollbar-track {
        background: transparent;
    }

    #contactListBody::-webkit-scrollbar-thumb,
    #chatAreaBody::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    /* ── Info Panel (Right Sidebar) ────────────────────────── */
    .premium-info-panel {
        width: 0;
        min-width: 0;
        background: rgba(15, 23, 42, 0.98);
        border-left: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .premium-info-panel.active {
        width: 320px;
        min-width: 320px;
    }

    .info-header {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .info-body {
        padding: 2rem 1.5rem;
        text-align: center;
        overflow-y: auto;
    }

    .info-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        border: 4px solid rgba(255, 255, 255, 0.05);
        background: var(--glass-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
        color: white;
        object-fit: cover;
    }

    .info-section {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        text-align: left;
    }

    .info-label {
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
        display: block;
    }

    .info-value {
        color: white;
        font-weight: 500;
        word-break: break-all;
    }
</style>

<!-- ── Main Chat Interface ───────────────────────────────── -->
<div class="premium-chat-wrapper">

    <!-- Left: Contact / Conversation List -->
    <div class="premium-contact-list">
        <div class="premium-search-header">
            <h5 class="mb-3 fw-bold text-white" style="letter-spacing:0.3px;">Messages</h5>
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-white-50"></i>
                <input type="text" id="sidebarSearch" class="form-control glass-search-input ps-5"
                    placeholder="Search conversations...">
            </div>
        </div>
        <!-- JS renders contacts here -->
        <div id="contactListBody">
            <div class="sidebar-empty-state py-5">
                <div class="spinner-border text-secondary" style="width:2rem;height:2rem;" role="status"></div>
                <small class="mt-3 d-block">Loading conversations...</small>
            </div>
        </div>
    </div>

    <!-- Right: Active Chat Area -->
    <div class="premium-chat-area">

        <!-- Chat Header (hidden until contact selected) -->
        <div class="premium-chat-header d-none" id="chatHeaderPanel" style="cursor: pointer;"
            onclick="toggleContactInfo()">
            <div class="premium-avatar sm" id="activeChatAvatar"></div>
            <div class="flex-grow-1 min-width-0">
                <div class="fw-bold text-white text-truncate" id="activeChatName"
                    style="font-size:1rem;letter-spacing:0.2px;"></div>
                <small class="text-white-50" id="activeChatPhone" style="font-size:0.78rem;"></small>
            </div>
            <div class="d-flex gap-1 ms-2">
                <button class="btn btn-link text-white-50 px-2" title="Search"><i
                        class="bi bi-search fs-5"></i></button>
                <button class="btn btn-link text-white-50 px-2" title="More"><i
                        class="bi bi-three-dots-vertical fs-5"></i></button>
            </div>
        </div>

        <!-- Message Area -->
        <div id="chatAreaBody">
            <!-- Default empty state -->
            <div class="chat-empty-state" id="chatEmptyState">
                <i class="bi bi-whatsapp empty-glow-icon"></i>
                <h4 class="mt-4 fw-bold text-white">WhatsApp Web CRM</h4>
                <p class="fs-6 mt-1" style="max-width:280px;">Select a conversation from the left to start messaging</p>
                <span class="badge mt-3 fw-normal rounded-pill px-3 py-2"
                    style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.45);">
                    <i class="bi bi-shield-lock me-1"></i> End-to-end encrypted
                </span>
            </div>
        </div>

        <!-- Floating Input Bar (hidden until contact selected) -->
        <div class="premium-chat-input-wrapper d-none" id="chatInputArea">
            <div class="premium-chat-input-pill">
                <button type="button" class="btn btn-link text-white-50 p-0 me-3" title="Emoji">
                    <i class="bi bi-emoji-smile fs-5"></i>
                </button>
                <button type="button" class="btn btn-link text-white-50 p-0 me-2" title="Attach">
                    <i class="bi bi-paperclip fs-5"></i>
                </button>
                <form id="sendForm" class="d-flex align-items-center flex-grow-1 m-0 p-0" autocomplete="off">
                    <input type="hidden" id="currentPhone">
                    <input type="text" id="messageBody" class="form-control premium-message-input"
                        placeholder="Type a message..." autocomplete="off">
                    <button type="submit" class="premium-send-btn ms-2" id="sendBtn" title="Send">
                        <i class="bi bi-send-fill" style="margin-left:-1px;margin-top:1px;"></i>
                    </button>
                </form>
            </div>
        </div>

    </div><!-- /.premium-chat-area -->

    <!-- Right: Contact Info Panel -->
    <div class="premium-info-panel" id="contactInfoPanel">
        <div class="info-header">
            <button class="btn btn-link text-white-50 p-0" onclick="toggleContactInfo()">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
            <span class="fw-bold text-white">Contact Info</span>
        </div>
        <div class="info-body" id="infoPanelContent">
            <div class="info-avatar-large" id="infoAvatar"></div>
            <h4 class="text-white fw-bold mb-1" id="infoName"></h4>
            <p class="text-white-50 mb-4" id="infoPhone"></p>

            <div class="info-section">
                <span class="info-label">About</span>
                <div class="info-value" id="infoAbout">...</div>
            </div>

            <div class="info-section">
                <span class="info-label">Category</span>
                <div class="info-value"><span class="badge bg-primary fw-normal" id="infoCategory">General</span></div>
            </div>
        </div>
    </div>
</div><!-- /.premium-chat-wrapper -->

<!-- Toast for errors / feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1090;">
    <div id="uiToast" class="toast align-items-center text-white border-0" role="alert" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">An error occurred.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- ============================================================
     VANILLA JS SPA ENGINE
     ============================================================ -->
<script>
    'use strict';

    // ── Config ──────────────────────────────────────────────────────────
    const SESSION_ID = '<?= $session_id ?>';
    const SIDEBAR_POLL_MS = 4000;   // Re-poll sidebar every 4 s
    const CHAT_POLL_MS = 3000;   // Re-poll open chat every 3 s
    const QR_POLL_MS = 3000;   // QR status check interval

    // ── State ───────────────────────────────────────────────────────────
    let activePhone = null;   // Currently open contact phone
    let lastMsgCount = 0;      // Tracks rendered message count for DOM diffing
    let autoScroll = true;   // Auto-scroll lock flag
    let sidebarPollTimer = null;
    let chatPollTimer = null;
    let qrPollTimer = null;
    let sidebarData = [];     // Latest sidebar payload (for search filter)
    let isSending = false;  // Debounce send button

    // ── DOM refs (lazily resolved once) ─────────────────────────────────
    const $ = id => document.getElementById(id);

    // ══════════════════════════════════════════════════════════════════════
    //  QR / CONNECTION OVERLAY
    // ══════════════════════════════════════════════════════════════════════

    async function checkSessionStatus() {
        try {
            const res = await fetch(`${BASE_URL}whatsapp/get_qr/${SESSION_ID}?t=${Date.now()}`, {
                credentials: 'include'
            });
            // If we got redirected to login, just stop — don't spam console
            if (!res.ok || res.redirected) { clearInterval(qrPollTimer); return; }

            let data;
            try { data = await res.json(); } catch (e) { return; }
            if (!data) return;

            const badge = $('statusBadge');
            const overlay = $('connectionOverlay');

            if (data.status === 'WORKING' || data.status === 'CONNECTED') {
                // ── Connected — dismiss overlay and boot the SPA ──
                clearInterval(qrPollTimer);
                badge.className = 'badge bg-success p-2 px-3 fw-normal';
                badge.textContent = '✓ Connected';
                overlay.style.transition = 'opacity 0.5s ease';
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.classList.remove('d-flex');
                    overlay.classList.add('d-none');
                }, 500);
                bootApp();                     // <-- starts sidebar + chat polling
            } else if (data.qr && data.qr.startsWith('data:image')) {
                badge.className = 'badge bg-warning p-2 px-3 fw-normal text-dark';
                badge.textContent = 'Scan the QR code';
                $('qrContainer').innerHTML =
                    `<img src="${data.qr}" alt="QR Code" style="width:240px;height:240px;border-radius:0.5rem;">`;
            } else {
                badge.className = 'badge bg-secondary p-2 px-3 fw-normal';
                badge.textContent = `Status: ${data.status || 'Starting…'}`;
            }
        } catch (err) {
            /* silent — network blip is OK */
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  BOOT — runs immediately on DOMContentLoaded (not gated on QR status)
    // ══════════════════════════════════════════════════════════════════════

    let appBooted = false; // guard against double-call

    function bootApp() {
        if (appBooted) return;
        appBooted = true;

        // Start sidebar polling immediately
        fetchSidebar();
        sidebarPollTimer = setInterval(fetchSidebar, SIDEBAR_POLL_MS);

        // ── Event delegation for sidebar clicks ──────────────────────────
        // Much more reliable than inline onclick — survives innerHTML repaints
        $('contactListBody').addEventListener('click', e => {
            const item = e.target.closest('.premium-contact-item');
            if (item && item.dataset.phone) openChat(item.dataset.phone);
        });

        // Wire up search
        $('sidebarSearch').addEventListener('input', e => {
            renderSidebar(filterSidebar(e.target.value));
        });

        // Wire up send form
        $('sendForm').addEventListener('submit', handleSend);

        // Auto-scroll detection
        $('chatAreaBody').addEventListener('scroll', function () {
            autoScroll = (this.scrollHeight - this.scrollTop - this.clientHeight) < 120;
        });
    }

    // ── Toggle Right Info Panel ─────────────────────────────
    let infoPanelActive = false;

    async function toggleContactInfo() {
        if (!activePhone) return;

        const panel = $('contactInfoPanel');
        infoPanelActive = !infoPanelActive;
        panel.classList.toggle('active', infoPanelActive);

        if (infoPanelActive) {
            await loadContactInfo(activePhone);
        }
    }

    async function loadContactInfo(phone) {
        try {
            const res = await fetch(`${BASE_URL}whatsapp/ajax_get_contact_info/${phone}`, { credentials: 'include' });
            const json = await res.json();
            if (json.status === 'ok') {
                const d = json.data;
                $('infoName').textContent = d.name;
                $('infoPhone').textContent = '+' + d.phone;
                $('infoAbout').textContent = d.about;
                $('infoCategory').textContent = d.category;

                const avatarEl = $('infoAvatar');
                if (d.avatar) {
                    avatarEl.innerHTML = `<img src="${escHtml(d.avatar)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
                } else {
                    avatarEl.textContent = getInitial(d.name);
                    applyAvatarGradient(avatarEl, d.phone);
                }
            }
        } catch (err) {
            console.error('Failed to load contact info', err);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  SIDEBAR
    // ══════════════════════════════════════════════════════════════════════

    async function fetchSidebar() {
        try {
            const res = await fetch(`${BASE_URL}whatsapp/ajax_get_sidebar`, { credentials: 'include' });
            if (!res.ok) return;
            const json = await res.json();
            if (json.status !== 'ok') return;

            sidebarData = json.data;                         // cache for search
            const query = $('sidebarSearch').value;
            renderSidebar(query ? filterSidebar(query) : sidebarData);
        } catch (err) { /* silent */ }
    }

    function filterSidebar(query) {
        const q = query.toLowerCase();
        return sidebarData.filter(c =>
            (c.contact_name || '').toLowerCase().includes(q) ||
            (c.contact_phone || '').includes(q) ||
            (c.last_message || '').toLowerCase().includes(q)
        );
    }

    function renderSidebar(rows) {
        const el = $('contactListBody');

        if (!rows || rows.length === 0) {
            el.innerHTML = `
            <div class="sidebar-empty-state py-5">
                <i class="bi bi-chat-square-dots fs-1 mb-3" style="color:rgba(255,255,255,0.2);"></i>
                <p class="mb-0" style="font-size:0.85rem;">No conversations yet</p>
            </div>`;
            return;
        }

        // Build HTML for all items
        // IMPORTANT: data-phone stores the RAW phone number (not HTML-escaped)
        // so that dataset.phone matches sidebarData entries exactly.
        // All display text (name, snippet) goes through escHtml separately.
        let html = '';
        rows.forEach(conv => {
            const rawPhone = conv.contact_phone || '';          // raw — for dataset
            const safePhone = rawPhone.replace(/"/g, '&quot;'); // safe for HTML attr only
            const name = escHtml(conv.contact_name || rawPhone);
            const snippet = escHtml(trimStr(conv.last_message || 'Media', 38));
            const ts = escHtml(conv.ts_formatted || '');
            const unread = parseInt(conv.unread_count, 10) || 0;
            const isActive = (rawPhone === activePhone);

            const avatarHtml = buildAvatar(conv, 'premium-avatar');
            const tickHtml = conv.last_direction === 'OUT'
                ? `<i class="bi bi-check2-all tick tick-read me-1"></i>` : '';
            const badgeHtml = unread > 0
                ? `<span class="badge rounded-pill ms-2 flex-shrink-0"
                style="background:#25d366;font-size:0.65rem;padding:0.3em 0.6em;">${unread}</span>` : '';

            // NO inline onclick — event delegation on #contactListBody handles clicks
            html += `
        <div class="premium-contact-item${isActive ? ' active' : ''}" data-phone="${safePhone}">
            ${avatarHtml}
            <div class="flex-grow-1 overflow-hidden">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold text-truncate text-white" style="font-size:0.93rem;">${name}</span>
                    <small class="text-white-50 flex-shrink-0 ms-2" style="font-size:0.68rem;font-weight:500;">${ts}</small>
                </div>
                <div class="d-flex align-items-center justify-content-between" style="min-width:0;">
                    <small class="text-truncate" style="font-size:0.78rem;color:rgba(255,255,255,0.45);flex:1;">
                        ${tickHtml}${snippet}
                    </small>
                    ${badgeHtml}
                </div>
            </div>
        </div>`;
        });

        el.innerHTML = html;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  OPEN CHAT
    // ══════════════════════════════════════════════════════════════════════

    async function openChat(phone) {
        if (activePhone === phone) return; // Already open

        activePhone = phone;
        lastMsgCount = 0;
        autoScroll = true;

        // Update header
        const conv = sidebarData.find(c => c.contact_phone === phone) || {};
        const name = conv.contact_name || phone;

        $('currentPhone').value = phone;
        $('activeChatName').textContent = name;
        $('activeChatPhone').textContent = `+${phone}`;
        $('activeChatAvatar').innerHTML = '';
        $('activeChatAvatar').appendChild(buildAvatarEl(conv, 44));

        // Show header + input
        $('chatHeaderPanel').classList.remove('d-none');
        $('chatInputArea').classList.remove('d-none');

        // Highlight active sidebar item
        document.querySelectorAll('.premium-contact-item').forEach(el => {
            el.classList.toggle('active', el.dataset.phone === phone);
        });

        // Show loading spinner in chat area
        $('chatAreaBody').innerHTML = `
        <div class="chat-empty-state">
            <div class="spinner-border text-success" role="status" style="width:2rem;height:2rem;"></div>
        </div>`;

        // Fetch and render immediately
        await fetchChatHistory(true);

        // If info panel is open, refresh it for the new contact
        if (infoPanelActive) {
            loadContactInfo(phone);
        }

        // Start chat poll
        if (chatPollTimer) clearInterval(chatPollTimer);
        chatPollTimer = setInterval(() => {
            if (activePhone) fetchChatHistory(false);
        }, CHAT_POLL_MS);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  FETCH & RENDER MESSAGES
    // ══════════════════════════════════════════════════════════════════════

    async function fetchChatHistory(forceScroll = false) {
        if (!activePhone) return;
        try {
            const res = await fetch(
                `${BASE_URL}whatsapp/ajax_get_chat_history/${activePhone}`,
                { credentials: 'include' }
            );
            if (!res.ok) return;
            const json = await res.json();
            if (json.status !== 'ok') return;

            renderMessages(json.data, forceScroll);

            // Update sidebar badge immediately (mark as read)
            const conv = sidebarData.find(c => c.contact_phone === activePhone);
            if (conv) { conv.unread_count = 0; }

        } catch (err) { /* silent */ }
    }

    function renderMessages(messages, forceScroll) {
        // DOM diffing — skip repaint if count is unchanged
        if (!forceScroll && messages.length === lastMsgCount) return;
        lastMsgCount = messages.length;

        const body = $('chatAreaBody');

        if (messages.length === 0) {
            body.innerHTML = `
            <div class="chat-empty-state">
                <i class="bi bi-chat-dots fs-1 mb-3" style="color:rgba(255,255,255,0.15);"></i>
                <p style="font-size:0.85rem;">No messages yet. Say hi!</p>
            </div>`;
            return;
        }

        let html = '';
        let lastDateLabel = '';

        messages.forEach(msg => {
            const isOut = msg.direction === 'OUT';
            const dateLabel = formatDateLabel(msg.created_at);

            // Date divider
            if (dateLabel !== lastDateLabel) {
                html += `<div class="date-divider"><span>${escHtml(dateLabel)}</span></div>`;
                lastDateLabel = dateLabel;
            }

            const senderHtml = (isOut && msg.employee_name)
                ? `<div class="sender-label">${escHtml(msg.employee_name)}</div>` : '';

            const tick = isOut ? buildTick(msg.is_read) : '';

            html += `
        <div class="premium-bubble ${isOut ? 'premium-message-out' : 'premium-message-in'}">
            ${senderHtml}
            <div>${escHtml(msg.body)}</div>
            <div class="bubble-meta">
                <span>${escHtml(msg.ts_formatted || formatTime(msg.created_at))}</span>
                ${tick}
            </div>
        </div>`;
        });

        body.innerHTML = html;

        if (forceScroll || autoScroll) scrollToBottom();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  SEND MESSAGE
    // ══════════════════════════════════════════════════════════════════════

    async function handleSend(e) {
        e.preventDefault();
        if (isSending) return;

        const input = $('messageBody');
        const text = input.value.trim();
        if (!text || !activePhone) return;

        // Optimistic UI — show immediately  
        input.value = '';
        appendOptimisticBubble(text);

        isSending = true;
        $('sendBtn').disabled = true;

        try {
            const fd = new FormData();
            fd.append('phone_number', activePhone);
            fd.append('body', text);

            const res = await fetch(`${BASE_URL}whatsapp/ajax_send_message`, {
                method: 'POST',
                body: fd,
                credentials: 'include'
            });

            const json = await res.json();

            if (json.status === 'ok') {
                // Replace optimistic bubble with real data
                await fetchChatHistory(true);
            } else {
                showToast(`Send failed: ${json.message || 'Unknown error'}`);
            }
        } catch (err) {
            showToast('Network error — message may not have sent.');
            console.error('Send error:', err);
        } finally {
            isSending = false;
            $('sendBtn').disabled = false;
            input.focus();
        }
    }

    function appendOptimisticBubble(text) {
        const body = $('chatAreaBody');

        // Remove empty-state if present
        const empty = body.querySelector('.chat-empty-state');
        if (empty) empty.remove();

        const time = formatTime(new Date().toISOString());
        const bubble = document.createElement('div');
        bubble.className = 'premium-bubble premium-message-out';
        bubble.style.opacity = '0.65';
        bubble.dataset.optimistic = '1';
        bubble.innerHTML = `
        <div>${escHtml(text)}</div>
        <div class="bubble-meta">
            <span>${time}</span>
            <i class="bi bi-clock tick" style="font-size:0.75rem;opacity:0.6;"></i>
        </div>`;
        body.appendChild(bubble);
        autoScroll = true;
        scrollToBottom();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  AVATAR HELPERS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Generate an avatar element — real image if profile_pic_url exists,
     * otherwise a gradient initial tile.
     */
    function buildAvatarEl(contact, size = 50) {
        const div = document.createElement('div');
        div.className = 'premium-avatar';
        div.style.width = size + 'px';
        div.style.height = size + 'px';
        div.style.fontSize = Math.round(size * 0.42) + 'px';

        if (contact.profile_pic_url) {
            const img = document.createElement('img');
            img.src = contact.profile_pic_url;
            img.alt = '';
            img.onerror = () => {
                // Fallback to initials on broken image
                div.removeChild(img);
                div.textContent = getInitial(contact.contact_name || contact.contact_phone);
                applyAvatarGradient(div, contact.contact_phone || '');
            };
            div.appendChild(img);
        } else {
            div.textContent = getInitial(contact.contact_name || contact.contact_phone);
            applyAvatarGradient(div, contact.contact_phone || '');
        }
        return div;
    }

    /** Returns HTML string for avatar (used in sidebar innerHTML) */
    function buildAvatar(contact, cls = 'premium-avatar') {
        const initial = escHtml(getInitial(contact.contact_name || contact.contact_phone));
        const gradient = getGradient(contact.contact_phone || '');

        if (contact.profile_pic_url) {
            return `<div class="${cls}" style="background:${gradient};">
            <img src="${escHtml(contact.profile_pic_url)}" alt=""
                 onerror="this.parentElement.textContent='${initial}'"
                 style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
        </div>`;
        }
        return `<div class="${cls}" style="background:${gradient};">${initial}</div>`;
    }

    function getInitial(name) {
        return (name || '?').trim().charAt(0).toUpperCase();
    }

    /** Deterministic gradient from phone number string */
    function getGradient(seed) {
        const palettes = [
            ['#3b82f6', '#8b5cf6'], ['#ec4899', '#f43f5e'], ['#22c55e', '#16a34a'],
            ['#f59e0b', '#ef4444'], ['#06b6d4', '#3b82f6'], ['#8b5cf6', '#ec4899'],
            ['#14b8a6', '#06b6d4'], ['#f97316', '#f59e0b'],
        ];
        let hash = 0;
        for (let i = 0; i < seed.length; i++) hash = seed.charCodeAt(i) + ((hash << 5) - hash);
        const pair = palettes[Math.abs(hash) % palettes.length];
        return `linear-gradient(135deg, ${pair[0]}, ${pair[1]})`;
    }

    function applyAvatarGradient(el, seed) {
        el.style.background = getGradient(seed);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  TICK / READ RECEIPT HELPERS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * is_read is a boolean from the DB. For sent messages:
     *   true  = read (blue double tick)
     *   false = delivered (grey double tick)
     */
    function buildTick(isRead) {
        if (isRead === true || isRead === '1' || isRead === 't') {
            return `<i class="bi bi-check2-all tick tick-read"></i>`;
        }
        return `<i class="bi bi-check2-all tick tick-delivered"></i>`;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  TIMESTAMP HELPERS
    // ══════════════════════════════════════════════════════════════════════

    function formatTime(ts) {
        if (!ts) return '';
        const d = new Date(ts);
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function formatDateLabel(ts) {
        if (!ts) return '';
        const d = new Date(ts);
        const now = new Date();
        const diffDays = Math.floor((now - d) / 86400000);

        if (diffDays === 0 && now.getDate() === d.getDate()) return 'Today';
        if (diffDays <= 1 && now.getDate() !== d.getDate()) return 'Yesterday';
        if (diffDays < 7) return d.toLocaleDateString([], { weekday: 'long' });
        return d.toLocaleDateString([], { day: '2-digit', month: 'short', year: 'numeric' });
    }

    // ══════════════════════════════════════════════════════════════════════
    //  SCROLL
    // ══════════════════════════════════════════════════════════════════════

    function scrollToBottom() {
        const el = $('chatAreaBody');
        el.scrollTop = el.scrollHeight;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  TOAST
    // ══════════════════════════════════════════════════════════════════════

    function showToast(msg, type = 'danger') {
        const el = $('uiToast');
        el.className = `toast align-items-center text-white bg-${type} border-0`;
        $('toastMessage').textContent = msg;
        bootstrap.Toast.getOrCreateInstance(el, { delay: 4000 }).show();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  UTILS
    // ══════════════════════════════════════════════════════════════════════

    function escHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function trimStr(str, len) {
        return str.length > len ? str.slice(0, len) + '…' : str;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  INIT
    // ══════════════════════════════════════════════════════════════════════

    document.addEventListener('DOMContentLoaded', () => {
        // ── Boot the SPA immediately — do NOT wait for QR check ──
        // The user is already authenticated (they're on this page).
        // The QR overlay visually blocks the UI; the JS engine runs in parallel.
        bootApp();

        // ── QR / connection status check (independent of bootApp) ────────
        checkSessionStatus();
        qrPollTimer = setInterval(checkSessionStatus, QR_POLL_MS);
    });
</script>