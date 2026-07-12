<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: ../admin/index.php");
    exit();
}

if (!isset($_SESSION['wedding_id'])) {
    header("Location: ../login.php");
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
$domain = $protocol . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF'])));
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


require '../layouts/header.php';
?>

<style>
    /* Bootstrap utility overrides and specific aesthetic styles */
    .page-hero {
        background: linear-gradient(135deg, #ffffff 0%, #fcfbf7 100%);
        border-radius: 16px; /* Assuming card-custom covers this, but ensuring it */
    }
    .page-hero-badge {
        background: rgba(201,169,110,0.12);
        color: #a07840;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.8px;
    }
    .page-hero h1 {
        color: #1a1a2e;
        font-size: 1.45rem;
        font-weight: 700;
    }
    .page-hero p {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    .status-pill {
        background: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }
    
    .countdown-card {
        min-width: 260px;
        background: linear-gradient(135deg, #1a1a2e 0%, #242440 100%);
        color: #f8f5ef;
        box-shadow: 0 10px 24px rgba(15, 15, 26, 0.12);
    }
    .countdown-card .eyebrow {
        font-size: 0.7rem;
        letter-spacing: 1.2px;
        color: rgba(201,169,110,0.7);
    }
    .countdown-card .countdown-value {
        font-size: 1.55rem;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .countdown-card .countdown-caption {
        font-size: 0.8rem;
        color: rgba(248,245,239,0.72);
    }

    .stat-card {
        background: white;
        border: 1px solid #e8ecf0;
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
        font-size: 1.1rem;
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
    }
    .link-card-title {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 2px;
        color: rgba(201,169,110,0.6);
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

    @media (max-width: 768px) {
        .page-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
        }
        .countdown-card {
            width: 100%;
            min-width: 0;
        }
        .link-card-actions {
            width: 100%;
        }
        .btn-copy-link,
        .link-card-actions a {
            flex: 1;
            justify-content: center;
        }
    }

    /* Quick actions */
    .quick-action-btn {
        background: white;
        border: 1px solid #e8ecf0;
        text-decoration: none;
        transition: all 0.2s;
    }
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15,15,26,0.08);
        border-color: rgba(201,169,110,0.35);
    }
    .quick-action-icon {
        width: 40px; height: 40px;
        font-size: 1rem;
    }
    .quick-action-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1a1a2e;
    }
    .quick-action-sub {
        font-size: 0.72rem;
        color: #9ea3b0;
    }

    /* Guest avatar in recent table */
    .guest-avatar-sm {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #c9a96e, #a07840);
        color: #0f0f1a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        font-weight: 700;
        margin-right: 10px;
        flex-shrink: 0;
    }
    .guest-name-flex { display: flex; align-items: center; }
</style>

<!-- PAGE HEADER -->
<div class="page-hero card-custom mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-4">
    <div>
        <div class="page-hero-badge d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2 text-uppercase mb-3"><i class="fas fa-sparkles"></i> Wedding dashboard</div>
        <h1 class="mb-2">Welcome back, <?php echo htmlspecialchars($user_name); ?> 👋</h1>
        <p class="m-0">Your planning hub is ready. Review guest activity, share the invitation, and keep momentum going toward your celebration.</p>
        <div class="page-hero-meta d-flex flex-wrap gap-2 mt-3">
            <!-- සජීවීව අමුත්තන් ගණන update වීමට id එකතු කර ඇත -->
            <span class="status-pill d-inline-flex align-items-center gap-2 rounded-pill px-2 py-1"><i class="fas fa-users"></i> <span id="meta-total-guests"><?php echo $total_guests; ?></span> guests</span>
            <span class="status-pill d-inline-flex align-items-center gap-2 rounded-pill px-2 py-1"><i class="fas fa-check-circle"></i> <?php echo $tasks_done; ?>/<?php echo $tasks_total; ?> tasks done</span>
            <span class="status-pill d-inline-flex align-items-center gap-2 rounded-pill px-2 py-1"><i class="fas fa-heart"></i> RSVP progress live</span>
        </div>
    </div>
    <?php if ($wedding && $wedding['wedding_date']): ?>
    <div class="countdown-card p-3 rounded-4">
        <div class="eyebrow text-uppercase mb-2">Counting down to your day</div>
        <div class="countdown-value" id="dash-countdown">--</div>
        <div class="countdown-caption">Days to go</div>
    </div>
    <?php endif; ?>
</div>

<!-- INVITATION LINK SHARE CARD -->
<div class="link-card mb-4 p-3 p-md-4 d-flex align-items-md-center justify-content-between flex-column flex-md-row gap-3">
    <div class="flex-grow-1" style="min-width: 0;">
        <div class="link-card-title text-uppercase mb-2"><i class="fas fa-link me-2"></i> Your Invitation Link — Share this with all guests</div>
        <div class="link-display" id="inv-link-display"><?php echo htmlspecialchars($invitation_link); ?></div>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 mt-3 mt-md-0">
        <a href="<?php echo $invitation_link; ?>" target="_blank"
           class="d-inline-flex align-items-center justify-content-center gap-2 text-decoration-none rounded-3 flex-grow-1"
           style="background:rgba(255,255,255,0.07); color:#c9a96e; border:1px solid rgba(201,169,110,0.2); padding:10px 16px; font-size:0.82rem; font-weight:500; transition:all 0.2s;" title="Preview the invitation">
            <i class="fas fa-eye"></i> Preview
        </a>
        <button class="btn-copy-link flex-grow-1 justify-content-center" onclick="doCopyLink()">
            <i class="fas fa-copy"></i> Copy Link
        </button>
    </div>
</div>

<!-- QUICK ACTIONS -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="guests.php" class="quick-action-btn d-flex align-items-center gap-3 p-3 rounded-4 h-100">
            <div class="quick-action-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(201,169,110,0.12); color:#c9a96e;"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="quick-action-label">Add Guest</div>
                <div class="quick-action-sub">Grow your guest list</div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="events.php" class="quick-action-btn d-flex align-items-center gap-3 p-3 rounded-4 h-100">
            <div class="quick-action-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(59,130,246,0.12); color:#3b82f6;"><i class="fas fa-calendar-plus"></i></div>
            <div>
                <div class="quick-action-label">Add Event</div>
                <div class="quick-action-sub">Poruwa, Reception & more</div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="gallery.php" class="quick-action-btn d-flex align-items-center gap-3 p-3 rounded-4 h-100">
            <div class="quick-action-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(34,197,94,0.12); color:#22c55e;"><i class="fas fa-images"></i></div>
            <div>
                <div class="quick-action-label">Upload Photos</div>
                <div class="quick-action-sub">Share your love story</div>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="checklist.php" class="quick-action-btn d-flex align-items-center gap-3 p-3 rounded-4 h-100">
            <div class="quick-action-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(245,158,11,0.12); color:#d97706;"><i class="fas fa-tasks"></i></div>
            <div>
                <div class="quick-action-label">Checklist</div>
                <!-- සජීවීව Checklist ප්‍රතිශතය update වීමට id එකතු කර ඇත -->
                <div class="quick-action-sub"><span id="meta-task-pct"><?php echo $task_pct; ?></span>% planning complete</div>
            </div>
        </a>
    </div>
</div>

<!-- STAT CARDS (සජීවීව update වීම සඳහා IDs එකතු කර ඇත) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card gold p-4 rounded-4">
            <div class="stat-icon gold d-flex align-items-center justify-content-center rounded-3 mb-3"><i class="fas fa-users"></i></div>
            <div class="stat-number" id="live-total-guests"><?php echo $total_guests; ?></div>
            <div class="stat-label">Total Guests</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card blue p-4 rounded-4">
            <div class="stat-icon blue d-flex align-items-center justify-content-center rounded-3 mb-3"><i class="fas fa-envelope-open"></i></div>
            <div class="stat-number" id="live-opened-invitations"><?php echo $opened_invitations; ?></div>
            <div class="stat-label">Opened Invitation</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card green p-4 rounded-4">
            <div class="stat-icon green d-flex align-items-center justify-content-center rounded-3 mb-3"><i class="fas fa-check-circle"></i></div>
            <div class="stat-number" id="live-accepted-rsvp"><?php echo $accepted_rsvp; ?></div>
            <div class="stat-label">Attending (RSVP)</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card red p-4 rounded-4">
            <div class="stat-icon red d-flex align-items-center justify-content-center rounded-3 mb-3"><i class="fas fa-times-circle"></i></div>
            <div class="stat-number" id="live-rejected-rsvp"><?php echo $rejected_rsvp; ?></div>
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
                <!-- සජීවීව update වීමට IDs එකතු කර ඇත -->
                <span style="font-size:2rem; font-weight:700; color:#1a1a2e; line-height:1;" id="live-task-pct"><?php echo $task_pct; ?>%</span>
                <span style="font-size:0.8rem; color:#9ea3b0; margin-bottom:4px;"><span id="live-tasks-done"><?php echo $tasks_done; ?></span>/<?php echo $tasks_total; ?> tasks done</span>
            </div>
            <div style="background:#f1f5f9; border-radius:50px; height:8px; overflow:hidden;">
                <!-- සජීවීව width එක update වීමට ID එකතු කර ඇත -->
                <div id="live-task-progress-bar" style="background:linear-gradient(135deg, #c9a96e, #a07840); height:100%; width:<?php echo $task_pct; ?>%; border-radius:50px; transition:width 1s;"></div>
            </div>
            <a href="checklist.php" style="display:block; margin-top:16px; font-size:0.8rem; color:#c9a96e; text-decoration:none; font-weight:500;">
                View Checklist <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i>
            </a>
        </div>
    </div>

    <!-- Recent guests table (සජීවීව update වීම සඳහා ID එකතු කර ඇත) -->
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
                    <tbody id="live-recent-guests-tbody">
                        <?php if (count($recent_guests) > 0): ?>
                            <?php foreach ($recent_guests as $guest): ?>
                            <?php
                                $g_parts = explode(' ', trim($guest['name']));
                                $g_initials = '';
                                foreach (array_slice($g_parts, 0, 2) as $gp) {
                                    $g_initials .= strtoupper(mb_substr($gp, 0, 1));
                                }
                            ?>
                            <tr>
                                <td style="font-weight:600; color:#1a1a2e;">
                                    <div class="guest-name-flex">
                                        <span class="guest-avatar-sm"><?php echo htmlspecialchars($g_initials); ?></span>
                                        <?php echo htmlspecialchars($guest['name']); ?>
                                    </div>
                                </td>
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
        const countdown = document.getElementById('dash-countdown');
        if (dist < 0) { countdown.textContent = 'Just Married! 🎉'; return; }
        const d = Math.floor(dist / 86400000);
        const h = Math.floor((dist % 86400000) / 3600000);
        const m = Math.floor((dist % 3600000) / 60000);
        countdown.textContent = `${String(d).padStart(2,'0')}d ${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m`;
    }
    update();
    setInterval(update, 60000);
})();
<?php endif; ?>

// =====================================================================
// 🔥 සජීවීව දත්ත ලබාගන්නා REAL-TIME COMMUNICATOR JAVASCRIPT
// =====================================================================
let pollingInterval = 5000;
let consecutiveErrors = 0;
let statsTimer = null;

function fetchDashboardLiveStats() {
    // If the tab is not visible, don't poll
    if (document.hidden) {
        return;
    }

    fetch('api_get_stats.php')
        .then(response => response.json())
        .then(data => {
            consecutiveErrors = 0; // Reset errors on success
            
            if (data.error) return;

            // 1. Stats වෙනස් වී ඇත්නම් සුමටව Fade-flash කර වෙනස් කරයි
            updateLiveText('live-total-guests', data.total_guests);
            updateLiveText('meta-total-guests', data.total_guests);
            updateLiveText('live-opened-invitations', data.opened_invitations);
            updateLiveText('live-accepted-rsvp', data.accepted_rsvp);
            updateLiveText('live-rejected-rsvp', data.rejected_rsvp);

            // 2. Checklist Progress එක සජීවීව update කරයි
            if (data.tasks_total !== undefined) {
                updateLiveText('live-tasks-done', data.tasks_done);
                updateLiveText('live-task-pct', data.task_pct + '%');
                updateLiveText('meta-task-pct', data.task_pct);
                
                const progressBar = document.getElementById('live-task-progress-bar');
                if (progressBar) {
                    progressBar.style.width = data.task_pct + '%';
                }
            }

            // 3. Recent Guests Table එක සජීවීව update කිරීම (Avatar එකද සහිතව!)
            const tbody = document.getElementById('live-recent-guests-tbody');
            if (tbody && data.recent_guests) {
                let html = '';
                if (data.recent_guests.length > 0) {
                    data.recent_guests.forEach(guest => {
                        const initials = getInitials(guest.name);
                        
                        let openedBadge = guest.is_opened == 1 
                            ? `<span class="badge-status badge-opened"><i class="fas fa-check"></i> Opened</span>` 
                            : `<span class="badge-status badge-not-opened">Not yet</span>`;
                            
                        let rsvpBadge = '';
                        if (guest.rsvp_status === 'accepted') {
                            rsvpBadge = `<span class="badge-status badge-attending"><i class="fas fa-check"></i> Attending</span>`;
                        } else if (guest.rsvp_status === 'rejected') {
                            rsvpBadge = `<span class="badge-status badge-not-attending"><i class="fas fa-times"></i> Declined</span>`;
                        } else {
                            rsvpBadge = `<span class="badge-status badge-pending">Pending</span>`;
                        }

                        html += `<tr>
                            <td style="font-weight:600; color:#1a1a2e;">
                                <div class="guest-name-flex">
                                    <span class="guest-avatar-sm">${initials}</span>
                                    ${guest.name}
                                </div>
                            </td>
                            <td>${guest.whatsapp_number}</td>
                            <td>${openedBadge}</td>
                            <td>${rsvpBadge}</td>
                        </tr>`;
                    });
                } else {
                    html = `<tr>
                        <td colspan="4" style="text-align:center; padding:30px; color:#9ea3b0; font-style:italic;">
                            No guests added yet. <a href="guests.php" style="color:#c9a96e;">Add your first guest →</a>
                        </td>
                    </tr>`;
                }
                tbody.innerHTML = html;
            }
            
            // Adjust polling rate back to normal if it was slowed down
            if (pollingInterval > 5000) {
                pollingInterval = 5000;
                resetStatsTimer();
            }
        })
        .catch(err => {
            console.error("Error syncing dashboard live stats:", err);
            consecutiveErrors++;
            // Exponential backoff up to 1 minute
            if (consecutiveErrors > 2) {
                pollingInterval = Math.min(60000, pollingInterval * 2);
                resetStatsTimer();
            }
        });
}

function resetStatsTimer() {
    if (statsTimer) clearInterval(statsTimer);
    statsTimer = setInterval(fetchDashboardLiveStats, pollingInterval);
}

// Listen for tab visibility changes to immediately fetch if coming back
document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
        fetchDashboardLiveStats();
    }
});

// Avatar Initials සාදාගන්නා Function එක
function getInitials(name) {
    const parts = name.trim().split(' ');
    let initials = '';
    parts.slice(0, 2).forEach(p => {
        if (p.length > 0) {
            initials += p.charAt(0).toUpperCase();
        }
    });
    return initials;
}

// Fade animation helper
function updateLiveText(id, newValue) {
    const el = document.getElementById(id);
    if (el && el.textContent != newValue) {
        el.style.transition = 'opacity 0.2s';
        el.style.opacity = '0.2';
        setTimeout(() => {
            el.textContent = newValue;
            el.style.opacity = '1';
        }, 200);
    }
}

// Start polling
resetStatsTimer();


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

<?php require '../layouts/footer.php'; ?>
