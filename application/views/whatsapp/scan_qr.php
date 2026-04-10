<div class="container-fluid py-4 h-100">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-panel p-5 text-center">
                <h3 class="fw-bold mb-4">Pair WhatsApp Device</h3>
                <p class="text-muted mb-4">Scan the QR code below using your WhatsApp app to connect this session to the CRM.</p>
                
                <div class="bg-white p-4 rounded-4 mb-4 d-inline-block mx-auto" style="min-height: 250px; min-width: 250px;" id="qrContainer">
                    <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem; margin-top: 80px;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                
                <div>
                    <span class="badge bg-secondary p-2 px-3 fw-normal" id="statusBadge">Status: Checking...</span>
                </div>
                
                <hr class="border-secondary my-4">
                <div class="text-start">
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
    </div>
</div>

<script>
    const sessionId = '<?= $session_id ?>';
    let pollInterval = null;
    
    async function checkSessionStatus() {
        try {
            // Using standard fetch API
            const response = await fetch(`${BASE_URL}whatsapp/get_qr/${sessionId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Assuming the CodeIgniter endpoint returns a JSON containing connection status
            // Example structure: { "status": "CONNECTED", "qr": "..." }
            // According to waha, it might be in { status: 'SCAN_QR_CODE' }, etc.
            
            const container = document.getElementById('qrContainer');
            const badge = document.getElementById('statusBadge');
            
            if (data.status === 'CONNECTED' || data.status === 'WORKING') {
                // Device successfully paired!
                clearInterval(pollInterval);
                
                badge.className = 'badge bg-success p-2 px-3 fw-normal';
                badge.innerText = 'Status: Connected Successfully!';
                
                container.innerHTML = `
                    <div class="d-flex flex-column align-items-center justify-content-center text-success" style="height: 100%;">
                        <i class="bi bi-check-circle-fill" style="font-size: 5rem;"></i>
                        <h5 class="mt-3 fw-bold">Device Paired!</h5>
                        <p class="text-muted small">Redirecting to Chat Room...</p>
                    </div>
                `;
                
                // Seamlessly redirect to the chat room after 1.5 seconds
                setTimeout(() => {
                    window.location.href = `${BASE_URL}whatsapp/chat_room`;
                }, 1500);
            } 
            else if (data.qr) {
                // Device still waiting for QR scan
                badge.className = 'badge bg-warning p-2 px-3 fw-normal text-dark';
                badge.innerText = 'Status: Waiting for scan';
                
                if(data.qr.startsWith('data:image')) {
                    container.innerHTML = `<img src="${data.qr}" alt="QR Code" class="img-fluid" style="border-radius: 0.5rem;" />`;
                }
            } else {
                badge.className = 'badge bg-secondary p-2 px-3 fw-normal';
                badge.innerText = 'Status: Checking...';
            }
            
        } catch (error) {
            console.error('Error fetching WAHA session status:', error);
            
            const badge = document.getElementById('statusBadge');
            badge.className = 'badge bg-danger p-2 px-3 fw-normal';
            badge.innerText = 'Status: Network/API Error';
        }
    }

    // Initialize polling every 3 seconds (3000ms)
    document.addEventListener("DOMContentLoaded", () => {
        checkSessionStatus(); // Initial check
        pollInterval = setInterval(checkSessionStatus, 3000);
    });
</script>
