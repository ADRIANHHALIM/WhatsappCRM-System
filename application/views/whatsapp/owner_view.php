<div class="container-fluid py-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Supervision Dashboard</h2>
            <p class="text-muted">Global overview of all operations and employee interactions.</p>
        </div>
        <button class="btn btn-outline-light"><i class="bi bi-download me-2"></i>Export Report</button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="glass-panel p-4 d-flex align-items-center shadow-sm">
                <div class="bg-primary bg-opacity-25 p-3 rounded-3 text-primary me-3">
                    <i class="bi bi-chat-square-dots fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">1,245</h3>
                    <div class="text-muted small text-uppercase fw-semibold mt-1">Total Messages</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-panel p-4 d-flex align-items-center shadow-sm">
                <div class="bg-success bg-opacity-25 p-3 rounded-3 text-success me-3">
                    <i class="bi bi-phone fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0"><?= count($sessions) ?></h3>
                    <div class="text-muted small text-uppercase fw-semibold mt-1">Active Sessions</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-panel p-4 d-flex align-items-center shadow-sm">
                <div class="bg-info bg-opacity-25 p-3 rounded-3 text-info me-3">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">89</h3>
                    <div class="text-muted small text-uppercase fw-semibold mt-1">Clients Active</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-panel p-4 d-flex align-items-center shadow-sm">
                <div class="bg-warning bg-opacity-25 p-3 rounded-3 text-warning me-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0">2m 4s</h3>
                    <div class="text-muted small text-uppercase fw-semibold mt-1">Avg Response</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sessions Info -->
        <div class="col-lg-4">
            <div class="glass-panel p-0 h-100 shadow-sm d-flex flex-column">
                <div class="p-4 border-bottom border-secondary">
                    <h5 class="fw-bold mb-0">Connected Devices</h5>
                </div>
                <div class="p-3">
                    <?php if (empty($sessions)): ?>
                        <p class="text-muted">No active sessions.</p>
                    <?php else: ?>
                        <?php foreach ($sessions as $sess): ?>
                            <div class="p-3 mb-3 border border-secondary rounded-3 bg-dark">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 text-truncate"><i
                                            class="bi bi-hdd-network me-2 text-primary"></i><?= htmlspecialchars($sess['session_id']) ?>
                                    </h6>
                                    <span
                                        class="badge <?= $sess['status'] == 'CONNECTED' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $sess['status'] ?></span>
                                </div>
                                <div class="small text-muted mb-1">Registered to: <span
                                        class="text-white"><?= htmlspecialchars($sess['employee_name'] ?: 'None') ?></span>
                                </div>
                                <div class="small text-muted mb-1">Phone: <span
                                        class="text-white"><?= htmlspecialchars($sess['wa_number'] ?: 'Unknown') ?></span></div>
                                <div class="small text-muted">Battery: <?= $sess['battery_level'] ?>% • Last Seen:
                                    <?= date('d M, H:i', strtotime($sess['last_seen'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Audit Trail -->
        <div class="col-lg-8">
            <div class="glass-panel p-0 h-100 shadow-sm flex-column d-flex">
                <div class="p-4 border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Global Audit Log</h5>
                    <div class="input-group" style="width: 250px;">
                        <input type="text" class="form-control form-control-sm border-secondary bg-dark text-white"
                            placeholder="Search logs...">
                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="table-responsive flex-grow-1 p-3">
                    <table class="table table-dark table-hover table-borderless align-middle mb-0">
                        <thead class="text-muted small text-uppercase">
                            <tr>
                                <th scope="col" class="fw-medium pb-3">Time</th>
                                <th scope="col" class="fw-medium pb-3">Employee</th>
                                <th scope="col" class="fw-medium pb-3">Action</th>
                                <th scope="col" class="fw-medium pb-3">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($audit_logs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No logs recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($audit_logs as $log): ?>
                                    <tr>
                                        <td class="text-muted small"><?= date('H:i - M d', strtotime($log['created_at'])) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                                    style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                    <?= substr($log['fullname'], 0, 1) ?>
                                                </div>
                                                <span><?= htmlspecialchars($log['fullname']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($log['action'] == 'LOGIN'): ?>
                                                <span
                                                    class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2">LOGIN</span>
                                            <?php elseif ($log['action'] == 'LOGOUT'): ?>
                                                <span
                                                    class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 px-2">LOGOUT</span>
                                            <?php elseif ($log['action'] == 'SEND_MESSAGE'): ?>
                                                <span
                                                    class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-2">MSG
                                                    OUT</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-2"><?= htmlspecialchars($log['action']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-truncate" style="max-width: 250px;"><small class="text-muted"
                                                title="<?= htmlspecialchars($log['description']) ?>"><?= htmlspecialchars($log['description']) ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>