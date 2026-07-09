<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

if (!isset($_SESSION['wedding_id'])) {
    header("Location: login.php");
    exit();
}

$wedding_id = $_SESSION['wedding_id'];
$user_name  = $_SESSION['user_name'];

// Stats
// 1.Sum of Seats_Reserved
$stmtTotal = $pdo->prepare("SELECT SUM(seats_reserved) as total FROM guests WHERE wedding_id = ?");
$stmtTotal->execute([$wedding_id]);
$total_guests = $stmtTotal->fetch()['total'] ?? 0; // null 0 

// 2. Sum of seats_reserved where is_opened = 1
$stmtOpened = $pdo->prepare("SELECT SUM(seats_reserved) as opened FROM guests WHERE wedding_id = ? AND is_opened = 1");
$stmtOpened->execute([$wedding_id]);
$opened_invitations = $stmtOpened->fetch()['opened'] ?? 0;

// 3. Sum of seats_reserved where rsvp_status = 'accepted'
$stmtAccepted = $pdo->prepare("SELECT SUM(seats_reserved) as accepted FROM guests WHERE wedding_id = ? AND rsvp_status = 'accepted'");
$stmtAccepted->execute([$wedding_id]);
$accepted_rsvp = $stmtAccepted->fetch()['accepted'] ?? 0;


$stmtRejected = $pdo->prepare("SELECT SUM(seats_reserved) as rejected FROM guests WHERE wedding_id = ? AND rsvp_status = 'rejected'");
$stmtRejected->execute([$wedding_id]);
$rejected_rsvp = $stmtRejected->fetch()['rejected'] ?? 0;

// Recent guests
$stmtRecent   = $pdo->prepare("SELECT * FROM guests WHERE wedding_id = ? ORDER BY id DESC LIMIT 5");
$stmtRecent->execute([$wedding_id]);
$recent_guests = $stmtRecent->fetchAll();

// Wedding info
$stmtWed = $pdo->prepare("SELECT * FROM weddings WHERE id = ?");
$stmtWed->execute([$wedding_id]);
$wedding = $stmtWed->fetch();

// Tasks progress
$stmtTasksTotal = $pdo->prepare("SELECT COUNT(*) as t FROM tasks WHERE wedding_id = ?");
$stmtTasksTotal->execute([$wedding_id]);
$tasks_total = $stmtTasksTotal->fetch()['t'];

$stmtTasksDone = $pdo->prepare("SELECT COUNT(*) as c FROM tasks WHERE wedding_id = ? AND is_completed = 1");
$stmtTasksDone->execute([$wedding_id]);
$tasks_done = $stmtTasksDone->fetch()['c'];
$task_pct = $tasks_total > 0 ? round(($tasks_done / $tasks_total) * 100) : 0;

// Invitation link

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF']));
$ring = "\u{1F48D}";
$flower = "\u{1F338}"; // 🌸
$heart = "\u{2764}\u{FE0F}"; // ❤️
$invite_slug = !empty($wedding['slug']) ? $wedding['slug'] : 'invite.php?w_id=' . $wedding_id;
$invitation_link = rtrim($domain, '/') . '/' . $invite_slug;
$invite_share_message = $ring . " You're Invited! " . $ring . "\n\n"
    ."With so much love and happiness in our hearts, we're excited to invite you to celebrate the invitation of our journey together - " . $user_name . "\n\n"
    . "It would truly mean the world to us on this special day\n\n"
    . "Invitation: " . $invitation_link. "\n\n"
    . "We can't wait to celebrate, laugh, and create beautiful memories with you! " . $heart;


require 'layouts/header.php';
?>

<style>
    .stat-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s;
    }
    .stat-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 80px; height: 80px;
        border-radius: 50%;
        opacity: 0.06;
        transform: translate(20px, -20px);
    }
    .stat-card.gold::after   { background: #c9a96e; }
    .stat-card.blue::after   { background: #3b82f6; }
    .stat-card.green::after  { background: #22c55e; }
    .stat-card.red::after    { background: #ef4444; }

    .stat-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 16px;
    }
    .stat-icon.gold   { background: rgba(201,169,110,0.12); color: #c9a96e; }
    .stat-icon.blue   { background: rgba(59,130,246,0.12);  color: #3b82f6; }
    .stat-icon.green  { background: rgba(34,197,94,0.12);   color: #22c55e; }
    .stat-icon.red    { background: rgba(239,68,68,0.12);   color: #ef4444; }

    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-label {
        font-size: 0.8rem;
        color: #9ea3b0;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Link share card */
    .link-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #242440 100%);
        border: 1px solid rgba(201,169,110,0.15);
        border-radius: 16px;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .link-card-left { flex: 1; min-width: 0; }
    .link-card-title {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: rgba(201,169,110,0.6);
        margin-bottom: 6px;
    }
    .link-display {
        font-family: 'Inter', sans-serif;
        font-size: 0.88rem;
        color: #e8e4dc;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        opacity: 0.7;
    }
    .link-card-actions { display: flex; gap: 10px; flex-shrink: 0; }
    .btn-copy-link {
        background: linear-gradient(135deg, #c9a96e, #a07840);
        color: #0f0f1a;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-family: 'Inter', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-copy-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(201,169,110,0.35);
    }

    /* Recent guests table */
    .dashboard-table th {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ea3b0;
        border-bottom: 1px solid #e8ecf0;
        padding: 12px 16px;
        background: #f8fafc;
    }
    .dashboard-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        color: #4a5568;
        vertical-align: middle;
    }
    .dashboard-table tr:last-child td { border-bottom: none; }
    .dashboard-table tr:hover td { background: #fafbfc; }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
    }
    .badge-attending { background: rgba(34,197,94,0.1); color: #16a34a; }
    .badge-not-attending { background: rgba(239,68,68,0.1); color: #dc2626; }
    .badge-pending { background: rgba(245,158,11,0.1); color: #d97706; }
    .badge-opened { background: rgba(59,130,246,0.1); color: #2563eb; }
    .badge-not-opened { background: rgba(107,114,128,0.1); color: #6b7280; }

    /* Countdown in dashboard */
    .wedding-countdown {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .cd-unit { text-align: center; }
    .cd-num {
        display: block;
        font-size: 1.6rem;
        font-weight: 700;
        color: #c9a96e;
        line-height: 1;
    }
    .cd-lbl {
        font-size: 0.62rem;
        color: #9ea3b0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .cd-sep { font-size: 1.4rem; color: rgba(201,169,110,0.3); margin-top: -6px; }
</style>

<!-- PAGE HEADER -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:1.5rem; font-weight:700; color:#1a1a2e; margin:0;">Welcome back 👋</h1>
        <p style="color:#9ea3b0; font-size:0.88rem; margin:4px 0 0;"><?php echo htmlspecialchars($user_name); ?> — your wedding dashboard</p>
    </div>
    <?php if ($wedding && $wedding['wedding_date']): ?>
    <div style="background:white; border:1px solid #e8ecf0; border-radius:14px; padding:14px 20px;">
        <div style="font-size:0.65rem; color:#9ea3b0; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Counting down to your day</div>
        <div class="wedding-countdown" id="dash-countdown">
            <div class="cd-unit"><span class="cd-num" id="dc-d">--</span><span class="cd-lbl">Days</span></div>
            <div class="cd-sep">:</div>
            <div class="cd-unit"><span class="cd-num" id="dc-h">--</span><span class="cd-lbl">Hrs</span></div>
            <div class="cd-sep">:</div>
            <div class="cd-unit"><span class="cd-num" id="dc-m">--</span><span class="cd-lbl">Min</span></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- INVITATION LINK SHARE CARD -->
<div class="link-card mb-4">
    <div class="link-card-left">
        <div class="link-card-title"><i class="fas fa-link" style="margin-right:6px;"></i> Your Invitation Link — Share this with all guests</div>
        <div class="link-display" id="inv-link-display"><?php echo htmlspecialchars($invitation_link); ?></div>
    </div>
    <div class="link-card-actions">
        <a href="<?php echo $invitation_link; ?>" target="_blank"
           style="background:rgba(255,255,255,0.07); color:#c9a96e; border:1px solid rgba(201,169,110,0.2); border-radius:10px; padding:10px 16px; font-size:0.82rem; font-weight:500; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s;" title="Preview the invitation">
            <i class="fas fa-eye"></i> Preview
        </a>
        <button class="btn-copy-link" onclick="doCopyLink()">
            <i class="fas fa-copy"></i> Copy Link
        </button>
    </div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card gold">
            <div class="stat-icon gold"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo $total_guests; ?></div>
            <div class="stat-label">Total Guests</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-envelope-open"></i></div>
            <div class="stat-number"><?php echo $opened_invitations; ?></div>
            <div class="stat-label">Opened Invitation</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number"><?php echo $accepted_rsvp; ?></div>
            <div class="stat-label">Attending (RSVP)</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
            <div class="stat-number"><?php echo $rejected_rsvp; ?></div>
            <div class="stat-label">Not Attending</div>
        </div>
    </div>
</div>

<!-- PROGRESS + RECENT GUESTS -->
<div class="row g-3">

    <!-- Checklist Progress mini card -->
    <div class="col-md-4">
        <div class="card-custom p-4" style="height:100%;">
            <h6 style="font-size:0.8rem; font-weight:600; color:#9ea3b0; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px;">Planning Progress</h6>
            <div style="display:flex; align-items:flex-end; gap:8px; margin-bottom:12px;">
                <span style="font-size:2rem; font-weight:700; color:#1a1a2e; line-height:1;"><?php echo $task_pct; ?>%</span>
                <span style="font-size:0.8rem; color:#9ea3b0; margin-bottom:4px;"><?php echo $tasks_done; ?>/<?php echo $tasks_total; ?> tasks done</span>
            </div>
            <div style="background:#f1f5f9; border-radius:50px; height:8px; overflow:hidden;">
                <div style="background:linear-gradient(135deg, #c9a96e, #a07840); height:100%; width:<?php echo $task_pct; ?>%; border-radius:50px; transition:width 1s;"></div>
            </div>
            <a href="checklist.php" style="display:block; margin-top:16px; font-size:0.8rem; color:#c9a96e; text-decoration:none; font-weight:500;">
                View Checklist <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i>
            </a>
        </div>
    </div>

    <!-- Recent guests table -->
    <div class="col-md-8">
        <div class="card-custom" style="overflow:hidden;">
            <div style="padding:20px 20px 0; display:flex; align-items:center; justify-content:space-between;">
                <h6 style="font-size:0.85rem; font-weight:700; color:#1a1a2e; margin:0;">Recent Guests</h6>
                <a href="guests.php" style="font-size:0.78rem; color:#c9a96e; text-decoration:none; font-weight:500;">View All →</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="table dashboard-table mb-0" style="margin-top:12px; min-width:500px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>WhatsApp</th>
                            <th>Opened</th>
                            <th>RSVP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_guests) > 0): ?>
                            <?php foreach ($recent_guests as $guest): ?>
                            <tr>
                                <td style="font-weight:600; color:#1a1a2e;"><?php echo htmlspecialchars($guest['name']); ?></td>
                                <td><?php echo htmlspecialchars($guest['whatsapp_number']); ?></td>
                                <td>
                                    <?php if ($guest['is_opened']): ?>
                                        <span class="badge-status badge-opened"><i class="fas fa-check"></i> Opened</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-not-opened">Not yet</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($guest['rsvp_status'] == 'accepted')
                                        echo '<span class="badge-status badge-attending"><i class="fas fa-check"></i> Attending</span>';
                                    elseif ($guest['rsvp_status'] == 'rejected')
                                        echo '<span class="badge-status badge-not-attending"><i class="fas fa-times"></i> Declined</span>';
                                    else
                                        echo '<span class="badge-status badge-pending">Pending</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:30px; color:#9ea3b0; font-style:italic;">
                                    No guests added yet. <a href="guests.php" style="color:#c9a96e;">Add your first guest →</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard countdown
<?php if ($wedding && $wedding['wedding_date']): ?>
(function() {
    const target = new Date("<?php echo $wedding['wedding_date']; ?> 00:00:00").getTime();
    function update() {
        const dist = target - Date.now();
        if (dist < 0) { document.getElementById('dash-countdown').innerHTML = '<span style="color:#c9a96e;">Just Married! 🎉</span>'; return; }
        const d = Math.floor(dist / 86400000);
        const h = Math.floor((dist % 86400000) / 3600000);
        const m = Math.floor((dist % 3600000) / 60000);
        document.getElementById('dc-d').textContent = String(d).padStart(2,'0');
        document.getElementById('dc-h').textContent = String(h).padStart(2,'0');
        document.getElementById('dc-m').textContent = String(m).padStart(2,'0');
    }
    update();
    setInterval(update, 60000);
})();
<?php endif; ?>

function doCopyLink() {
    const shareText = <?php echo json_encode($invite_share_message); ?>;
    const link = "<?php echo addslashes($invitation_link); ?>";
    document.getElementById('inv-link-display').textContent = link;

    copyTextToClipboard(shareText).then(() => {
        showToast('✓ Copied invitation message');
    }).catch(() => {
        prompt('Copy this invitation message:', shareText);
    });
}

function copyTextToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
    }

    return new Promise((resolve, reject) => {
        const tempInput = document.createElement('textarea');
        tempInput.value = text;
        tempInput.setAttribute('readonly', '');
        tempInput.style.position = 'fixed';
        tempInput.style.left = '-9999px';
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            const copied = document.execCommand('copy');
            document.body.removeChild(tempInput);
            if (copied) {
                resolve();
            } else {
                reject(new Error('Copy failed'));
            }
        } catch (err) {
            document.body.removeChild(tempInput);
            reject(err);
        }
    });
}
</script>

<?php require 'layouts/footer.php'; ?>