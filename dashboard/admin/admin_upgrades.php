<?php
session_start();
require '../../config/config.php';
require '../../config/mailer.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$msg = "";

// Check CSRF for state-changing actions
if (isset($_GET['action']) && in_array($_GET['action'], ['approve_upgrade', 'reject_upgrade'])) {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
}

// 2. Approve Package Upgrade Request
if (isset($_GET['action']) && $_GET['action'] === 'approve_upgrade' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtGetUpgrade = $pdo->prepare("SELECT name, email, pending_upgrade_plan, upgrade_slip FROM users WHERE id = ?");
    $stmtGetUpgrade->execute([$u_id]);
    $upgradeData = $stmtGetUpgrade->fetch();

    if ($upgradeData && !empty($upgradeData['pending_upgrade_plan'])) {
        $parts = explode('|', $upgradeData['pending_upgrade_plan']);
        $target_package = $parts[0] ?? 'standard';
        $target_gallery = intval($parts[1] ?? 0);
        
        $stmtOldSlip = $pdo->prepare("SELECT payment_slip FROM users WHERE id = ?");
        $stmtOldSlip->execute([$u_id]);
        $oldSlipFile = $stmtOldSlip->fetchColumn();
        if (!empty($oldSlipFile) && file_exists('../../' . $oldSlipFile)) {
            unlink('../../' . $oldSlipFile);
        }

        $stmtUpdatePkg = $pdo->prepare("UPDATE users SET package = ?, has_guest_gallery = ?, payment_slip = upgrade_slip, upgrade_slip = NULL, pending_upgrade_plan = NULL WHERE id = ?");
        if ($stmtUpdatePkg->execute([$target_package, $target_gallery, $u_id])) {
            if (function_exists('send_upgrade_success_mail')) {
                $new_plan_readable = ucfirst($target_package) . ($target_gallery ? " + Guest Gallery" : "");
                send_upgrade_success_mail($upgradeData['email'], $upgradeData['name'], $new_plan_readable);
            }
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Upgrade approved! Couple promoted to <strong>" . ucfirst($target_package) . " Plan</strong> and notified.</div>";
        }
    }
}

// 3. Reject Package Upgrade Request
if (isset($_GET['action']) && $_GET['action'] === 'reject_upgrade' && isset($_GET['uid'])) {
    $u_id = intval($_GET['uid']);
    
    $stmtGetUpgrade = $pdo->prepare("SELECT upgrade_slip FROM users WHERE id = ?");
    $stmtGetUpgrade->execute([$u_id]);
    $upgradeSlipFile = $stmtGetUpgrade->fetchColumn();

    if (!empty($upgradeSlipFile) && file_exists('../../' . $upgradeSlipFile)) {
        unlink('../../' . $upgradeSlipFile);
    }

    $stmtReject = $pdo->prepare("UPDATE users SET upgrade_slip = NULL, pending_upgrade_plan = NULL WHERE id = ?");
    if ($stmtReject->execute([$u_id])) {
        $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Upgrade request rejected and slip file deleted.</div>";
    }
}

// AJAX: Live Upgrade Requests Table Update
if (isset($_GET['action']) && $_GET['action'] === 'live_upgrades') {
    header('Content-Type: application/json');

    $stmtLiveList = $pdo->prepare("
        SELECT id, name, email, package, has_guest_gallery, pending_upgrade_plan, upgrade_slip 
        FROM users 
        WHERE role = 'couple' AND pending_upgrade_plan IS NOT NULL AND upgrade_slip IS NOT NULL
        ORDER BY id DESC
    ");
    $stmtLiveList->execute();
    $liveUsersList = $stmtLiveList->fetchAll();

    $liveUpgradeFormatted = [];
    foreach ($liveUsersList as $upg) {
        $parts = explode('|', $upg['pending_upgrade_plan']);
        $req_pkg = $parts[0] ?? 'standard';
        $req_gal = intval($parts[1] ?? 0);
        $req_text = ucfirst($req_pkg) . ($req_gal ? " + Guest Gallery" : "");

        $liveUpgradeFormatted[] = [
            'id' => (int) $upg['id'],
            'name' => htmlspecialchars($upg['name']),
            'email' => htmlspecialchars($upg['email']),
            'package' => ucfirst($upg['package'] ?? 'Basic'),
            'has_guest_gallery' => !empty($upg['has_guest_gallery']),
            'req_text' => htmlspecialchars($req_text),
            'upgrade_slip' => !empty($upg['upgrade_slip']) ? htmlspecialchars('../../' . $upg['upgrade_slip']) : null,
            'upgrade_slip_is_pdf' => !empty($upg['upgrade_slip']) && strtolower(pathinfo($upg['upgrade_slip'], PATHINFO_EXTENSION)) === 'pdf',
        ];
    }

    echo json_encode(['upgrade_requests' => $liveUpgradeFormatted]);
    exit();
}

$stmtUsers = $pdo->prepare("
    SELECT id, name, email, package, has_guest_gallery, pending_upgrade_plan, upgrade_slip 
    FROM users 
    WHERE role = 'couple' AND pending_upgrade_plan IS NOT NULL AND upgrade_slip IS NOT NULL
    ORDER BY id DESC
");
$stmtUsers->execute();
$upgradeRequests = $stmtUsers->fetchAll();

require '../layouts/header.php';
?>

<style>
/* ============================================================
   ADMIN UPGRADES PANEL — PREMIUM REDESIGN
   ============================================================ */

:root {
    --ap-gold:        #c9a96e;
    --ap-gold-light:  rgba(201,169,110,0.12);
    --ap-gold-glow:   rgba(201,169,110,0.25);
    --ap-emerald:     #10b981;
    --ap-emerald-bg:  rgba(16,185,129,0.10);
    --ap-amber:       #f59e0b;
    --ap-amber-bg:    rgba(245,158,11,0.10);
    --ap-red:         #ef4444;
    --ap-red-bg:      rgba(239,68,68,0.10);
    --ap-blue:        #6366f1;
    --ap-blue-bg:     rgba(99,102,241,0.10);
    --ap-border-soft: #e8ecf0;
    --ap-surface:     #ffffff;
    --ap-text:        #1e293b;
    --ap-muted:       #64748b;
    --ap-faint:       #94a3b8;
    --ap-radius:      16px;
    --ap-radius-sm:   10px;
    --ap-shadow:      0 4px 24px rgba(15,15,26,0.06);
    --ap-shadow-lg:   0 12px 40px rgba(15,15,26,0.10);
}

.flash { display:flex; align-items:center; gap:12px; padding:14px 20px; border-radius:var(--ap-radius-sm); font-size:.875rem; font-weight:500; margin-bottom:28px; animation:slideDown .4s ease; }
@keyframes slideDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
.flash i { font-size:1rem; flex-shrink:0; }
.flash-success { background:var(--ap-emerald-bg); border:1px solid rgba(16,185,129,.25); color:#059669; }
.flash-error   { background:var(--ap-red-bg);     border:1px solid rgba(239,68,68,.25);  color:#dc2626; }

.admin-page-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:28px; }
.admin-page-header-left h1 { font-size:1.5rem; font-weight:800; color:var(--ap-text); margin:0; letter-spacing:-.3px; }
.admin-page-header-left p  { font-size:.82rem; color:var(--ap-muted); margin:4px 0 0; }
.live-dot { display:inline-flex; align-items:center; gap:7px; background:var(--ap-emerald-bg); border:1px solid rgba(16,185,129,.2); border-radius:20px; padding:6px 14px; font-size:.75rem; font-weight:600; color:var(--ap-emerald); }
.live-dot::before { content:''; width:7px; height:7px; border-radius:50%; background:var(--ap-emerald); animation:pulse-dot 1.6s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.3)} }

.panel-card { background:var(--ap-surface); border:1px solid var(--ap-border-soft); border-radius:var(--ap-radius); box-shadow:var(--ap-shadow); margin-bottom:24px; overflow:hidden; }
.panel-card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; padding:18px 24px; border-bottom:1px solid #f1f5f9; background:#fafbfc; }
.panel-card-header--alert { background:linear-gradient(135deg,rgba(99,102,241,.04),rgba(99,102,241,.01)); border-bottom-color:rgba(99,102,241,.1); }
.panel-card-title { display:flex; align-items:center; gap:10px; font-size:.95rem; font-weight:700; color:var(--ap-text); margin:0; }
.title-icon { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.85rem; }
.title-icon--blue { background:var(--ap-blue-bg);    color:var(--ap-blue); }

.ap-table-wrap { overflow-x:auto; }
.ap-table { width:100%; border-collapse:collapse; min-width:700px; }
.ap-table thead tr { background:#f8fafc; }
.ap-table th { padding:11px 18px; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.9px; color:var(--ap-faint); border-bottom:1px solid #e8ecf0; text-align:left; white-space:nowrap; }
.ap-table td { padding:14px 18px; border-bottom:1px solid #f1f5f9; font-size:.855rem; color:var(--ap-muted); vertical-align:middle; }
.ap-table tbody tr { transition:background .15s; }
.ap-table tbody tr:hover td { background:#f8fafb; }
.ap-table tbody tr:last-child td { border-bottom:none; }

.couple-name   { font-weight:700; color:var(--ap-text); font-size:.88rem; }
.couple-email  { font-size:.76rem; color:var(--ap-faint); margin-top:2px; }

.badge-pill    { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 9px; font-size:.68rem; font-weight:700; letter-spacing:.2px; }
.badge-pkg     { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.badge-gallery { background:rgba(16,185,129,.08); color:#059669; border:1px solid rgba(16,185,129,.15); font-size:.65rem; }

.slip-thumb    { width:42px; height:42px; border-radius:9px; object-fit:cover; border:2px solid #e8ecf0; cursor:pointer; transition:transform .2s,border-color .2s,box-shadow .2s; }
.slip-thumb:hover { transform:scale(1.1); border-color:var(--ap-gold); box-shadow:0 4px 12px rgba(201,169,110,.3); }
.slip-pdf-link { display:inline-flex; align-items:center; gap:6px; font-size:.78rem; color:var(--ap-gold); text-decoration:none; font-weight:600; padding:5px 10px; background:var(--ap-gold-light); border-radius:8px; border:1px solid var(--ap-gold-glow); transition:background .2s; }
.slip-pdf-link:hover { background:rgba(201,169,110,.2); color:var(--ap-gold); }

.actions-cell { display:flex; align-items:center; gap:6px; flex-wrap:nowrap; }
.btn-ap { display:inline-flex; align-items:center; gap:5px; border-radius:var(--ap-radius-sm); padding:7px 13px; font-size:.74rem; font-weight:700; text-decoration:none; transition:all .2s cubic-bezier(.4,0,.2,1); cursor:pointer; border:1px solid transparent; white-space:nowrap; letter-spacing:.1px; }
.btn-ap:hover { transform:translateY(-1px); }
.btn-approve { background:var(--ap-emerald-bg); color:#059669; border-color:rgba(16,185,129,.2); }
.btn-approve:hover { background:var(--ap-emerald); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,.3); }
.btn-reject  { background:var(--ap-red-bg); color:var(--ap-red); border-color:rgba(239,68,68,.2); }
.btn-reject:hover { background:var(--ap-red); color:#fff; box-shadow:0 4px 14px rgba(239,68,68,.3); }

.empty-state { text-align:center; padding:52px 24px; color:var(--ap-faint); }
.empty-state i { font-size:2.5rem; margin-bottom:12px; display:block; opacity:.35; }
.empty-state p { font-size:.88rem; margin:0; }

.upgrade-alert-banner { display:flex; align-items:center; gap:10px; padding:12px 24px; background:linear-gradient(90deg,rgba(99,102,241,.07),transparent); border-bottom:1px solid rgba(99,102,241,.1); font-size:.8rem; color:var(--ap-blue); font-weight:600; }

.lightbox { display:none; position:fixed; inset:0; background:rgba(10,10,20,.94); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); z-index:9999; align-items:center; justify-content:center; animation:lbFadeIn .2s ease; }
@keyframes lbFadeIn { from{opacity:0} to{opacity:1} }
.lightbox.open { display:flex; }
.lightbox-inner { position:relative; max-width:90vw; max-height:88vh; animation:lbScaleIn .25s cubic-bezier(.34,1.56,.64,1); }
@keyframes lbScaleIn { from{opacity:0;transform:scale(.85)} to{opacity:1;transform:scale(1)} }
.lightbox-inner img { max-width:90vw; max-height:85vh; border-radius:18px; object-fit:contain; box-shadow:0 30px 80px rgba(0,0,0,.6); border:1px solid rgba(255,255,255,.06); display:block; }
.lightbox-close { position:absolute; top:-14px; right:-14px; width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); color:#fff; font-size:1rem; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:background .2s,transform .2s; }
.lightbox-close:hover { background:rgba(239,68,68,.7); transform:rotate(90deg); }

@media(max-width:767px){
    .ap-table,
    .ap-table thead,.ap-table tbody,.ap-table th,.ap-table td,.ap-table tr{display:block}
    .ap-table thead tr{position:absolute;top:-9999px;left:-9999px}
    .ap-table tbody tr{border:1px solid var(--ap-border-soft);border-radius:12px;margin:12px;padding:4px 0;box-shadow:0 2px 8px rgba(15,24,40,.04)}
    .ap-table td{border-bottom:1px dashed #f1f5f9;padding:10px 14px;display:flex;align-items:flex-start;gap:10px}
    .ap-table td:last-child{border-bottom:none}
    .ap-table td::before{content:attr(data-label);font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--ap-faint);flex:0 0 90px;padding-top:2px}
    .actions-cell{flex-wrap:wrap}
    .panel-card-header{flex-direction:column;align-items:flex-start}
}
</style>

<div class="admin-page-header">
    <div class="admin-page-header-left">
        <h1><i class="fas fa-arrow-up-right-dots" style="color:var(--ap-blue);margin-right:10px;font-size:1.3rem;"></i>Upgrade Requests</h1>
        <p>Manage package upgrade requests from couples</p>
    </div>
    <div class="live-dot">Live Sync Active</div>
</div>

<?php if ($msg) echo $msg; ?>

<div class="panel-card" id="upgrade-requests-card">
    <div class="panel-card-header panel-card-header--alert">
        <h5 class="panel-card-title">
            <span class="title-icon title-icon--blue"><i class="fas fa-arrow-up-right-dots"></i></span>
            Pending Package Upgrade Reviews
        </h5>
    </div>
    <div class="upgrade-alert-banner">
        <i class="fas fa-info-circle"></i>
        Review the upgrade slip and approve or reject each request below.
    </div>
    <div class="ap-table-wrap">
        <table class="ap-table">
            <thead>
                <tr>
                    <th>Couple Info</th>
                    <th>Current Plan</th>
                    <th>Requested Plan</th>
                    <th>Payment Slip</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="upgrade-requests-tbody">
                <?php if (count($upgradeRequests) > 0): ?>
                    <?php foreach ($upgradeRequests as $upg):
                        $parts   = explode('|', $upg['pending_upgrade_plan']);
                        $req_pkg = $parts[0] ?? 'standard';
                        $req_gal = intval($parts[1] ?? 0);
                        $req_text = ucfirst($req_pkg) . ($req_gal ? " + Guest Gallery" : "");
                    ?>
                    <tr>
                        <td data-label="Couple Info">
                            <div class="couple-name"><?php echo htmlspecialchars($upg['name']); ?></div>
                            <div class="couple-email"><?php echo htmlspecialchars($upg['email']); ?></div>
                        </td>
                        <td data-label="Current Plan">
                            <span class="badge-pill badge-pkg"><?php echo ucfirst($upg['package'] ?? 'Basic'); ?></span>
                            <?php if (!empty($upg['has_guest_gallery'])): ?>
                                <br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Requested Plan">
                            <span style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:var(--ap-blue);font-size:0.85rem;">
                                <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i>
                                <?php echo htmlspecialchars($req_text); ?>
                            </span>
                        </td>
                        <td data-label="Payment Slip">
                            <?php if (!empty($upg['upgrade_slip'])): ?>
                                <?php $ext_upg = strtolower(pathinfo($upg['upgrade_slip'], PATHINFO_EXTENSION)); ?>
                                <?php if ($ext_upg === 'pdf'): ?>
                                    <a href="../../<?php echo htmlspecialchars($upg['upgrade_slip']); ?>" target="_blank" class="slip-pdf-link">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                <?php else: ?>
                                    <img src="../../<?php echo htmlspecialchars($upg['upgrade_slip']); ?>"
                                         class="slip-thumb"
                                         onclick="openLightbox(this.src)"
                                         alt="Upgrade receipt">
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <div class="actions-cell">
                                <a href="admin_upgrades.php?action=approve_upgrade&uid=<?php echo $upg['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                   class="btn-ap btn-approve"
                                   onclick="return confirm('Approve package upgrade to <?php echo $req_text; ?> for <?php echo addslashes($upg['name']); ?>?');">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                                <a href="admin_upgrades.php?action=reject_upgrade&uid=<?php echo $upg['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                   class="btn-ap btn-reject"
                                   onclick="return confirm('Reject upgrade request for <?php echo addslashes($upg['name']); ?>? This will delete the slip receipt.');">
                                    <i class="fas fa-xmark"></i> Reject
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-check-circle" style="color: var(--ap-emerald);"></i>
                                <p>No pending upgrade requests at the moment.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <div class="lightbox-inner" onclick="event.stopPropagation()">
        <div class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-xmark"></i></div>
        <img src="" id="lightbox-img" alt="Payment slip">
    </div>
</div>

<script>
let csrfTokenJS = "<?php echo $csrf_token; ?>";
let lastUpgradeSnapshot = null;
let adminPollPaused = false;
let pollingInterval = 5000;
let consecutiveErrors = 0;
let adminStatsTimer = null;

document.addEventListener('mousedown', function (e) {
    if (e.target.closest('#upgrade-requests-tbody')) {
        adminPollPaused = true;
        setTimeout(() => { adminPollPaused = false; }, 3000);
    }
});

function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
    setTimeout(() => { document.getElementById('lightbox-img').src = ''; }, 300);
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

function esc(str) {
    return String(str).replace(/'/g, "\\'");
}

function buildUpgradeRow(upg) {
    const galleryNote = upg.has_guest_gallery ? `<br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>` : '';
    let slipHtml = '';
    if (upg.upgrade_slip) {
        slipHtml = upg.upgrade_slip_is_pdf
            ? `<a href="${upg.upgrade_slip}" target="_blank" class="slip-pdf-link"><i class="fas fa-file-pdf"></i> View PDF</a>`
            : `<img src="${upg.upgrade_slip}" class="slip-thumb" onclick="openLightbox(this.src)" alt="Upgrade Receipt">`;
    }

    return `<tr>
        <td data-label="Couple Info">
            <div class="couple-name">${upg.name}</div>
            <div class="couple-email">${upg.email}</div>
        </td>
        <td data-label="Current Plan"><span class="badge-pill badge-pkg">${upg.package}</span>${galleryNote}</td>
        <td data-label="Requested Plan">
            <span style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:var(--ap-blue);font-size:0.85rem;">
                <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i> ${upg.req_text}
            </span>
        </td>
        <td data-label="Payment Slip">${slipHtml}</td>
        <td data-label="Actions">
            <div class="actions-cell">
                <a href="admin_upgrades.php?action=approve_upgrade&uid=${upg.id}&csrf_token=${csrfTokenJS}" class="btn-ap btn-approve" onclick="return confirm('Approve package upgrade to ${esc(upg.req_text)} for ${esc(upg.name)}?');"><i class="fas fa-check"></i> Approve</a>
                <a href="admin_upgrades.php?action=reject_upgrade&uid=${upg.id}&csrf_token=${csrfTokenJS}" class="btn-ap btn-reject" onclick="return confirm('Reject upgrade request for ${esc(upg.name)}? This will delete the slip receipt.');"><i class="fas fa-xmark"></i> Reject</a>
            </div>
        </td>
    </tr>`;
}

function fetchAdminLiveStats() {
    if (adminPollPaused || document.hidden) return;

    fetch('admin_upgrades.php?action=live_upgrades')
        .then(r => r.json())
        .then(data => {
            consecutiveErrors = 0;
            if (data.error) return;

            const upgradeSnapshot = JSON.stringify(data.upgrade_requests);
            if (upgradeSnapshot !== lastUpgradeSnapshot) {
                lastUpgradeSnapshot = upgradeSnapshot;
                const upgradeTbody = document.getElementById('upgrade-requests-tbody');
                if (upgradeTbody && data.upgrade_requests) {
                    if (data.upgrade_requests.length > 0) {
                        upgradeTbody.innerHTML = data.upgrade_requests.map(buildUpgradeRow).join('');
                    } else {
                        upgradeTbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><i class="fas fa-check-circle" style="color: var(--ap-emerald);"></i><p>No pending upgrade requests at the moment.</p></div></td></tr>`;
                    }
                }
            }

            if (pollingInterval > 5000) {
                pollingInterval = 5000;
                resetAdminStatsTimer();
            }
        })
        .catch(err => {
            console.error('Admin live stats error:', err);
            consecutiveErrors++;
            if (consecutiveErrors > 2) {
                pollingInterval = Math.min(60000, pollingInterval * 2);
                resetAdminStatsTimer();
            }
        });
}

function resetAdminStatsTimer() {
    if (adminStatsTimer) clearInterval(adminStatsTimer);
    adminStatsTimer = setInterval(fetchAdminLiveStats, pollingInterval);
}

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) fetchAdminLiveStats();
});
resetAdminStatsTimer();
</script>

<?php require '../layouts/footer.php'; ?>
