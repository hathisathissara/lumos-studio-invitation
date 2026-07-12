<?php
session_start();
require '../config/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: login.php");
    exit();
}
$msg = "";

$wedding_id = $_SESSION['wedding_id'];

// ============================================
// 1. AJAX ACTION: WhatsApp Click කල විට Sent ලෙස සටහන් කිරීම
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'mark_sent' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $g_id = intval($_GET['id']);
    
    $stmt = $pdo->prepare("UPDATE guests SET is_sent = 1, sent_at = NOW() WHERE id = ? AND wedding_id = ?");
    if ($stmt->execute([$g_id, $wedding_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// ============================================
// 2. AJAX ACTION: සජීවී (Live) Guest Status ලබාගැනීම — Opened/Sent/RSVP & Guest Note (නම සහ Note එක එකතු කර ඇත!)
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'live_status') {
    header('Content-Type: application/json');
    $stmtLive = $pdo->prepare("SELECT id, name, is_opened, opened_at, is_sent, sent_at, rsvp_status, guest_note FROM guests WHERE wedding_id = ?");
    $stmtLive->execute([$wedding_id]);
    echo json_encode(['guests' => $stmtLive->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

function normalize_whatsapp_number($value) {
    $value = trim((string) $value);
    $digits = preg_replace('/\D+/', '', $value);
    if ($digits === '') {
        return '';
    }

    if (strlen($digits) > 10 && substr($digits, 0, 2) === '94') {
        $digits = '0' . substr($digits, 2);
    } elseif (strlen($digits) === 9) {
        $digits = '0' . $digits;
    }

    return $digits;
}

function to_whatsapp_intl($local_number) {
    $digits = preg_replace('/\D+/', '', (string) $local_number);
    if ($digits === '') return '';

    if (substr($digits, 0, 1) === '0') {
        $digits = '94' . substr($digits, 1);
    } elseif (substr($digits, 0, 2) !== '94') {
        $digits = '94' . $digits;
    }
    return $digits;
}

// Token එකක් නිපදවන ප්‍රධාන Function එක
function generate_invite_token($pdo) {
    do {
        $token = bin2hex(random_bytes(6)); // 12 hex chars
        $check = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE invite_token = ?");
        $check->execute([$token]);
    } while ($check->fetchColumn() > 0);
    return $token;
}

// 1. පරිශීලකයාගේ පැකේජය සහ අමුත්තන්ගේ සීමාවන් පරීක්ෂා කිරීම
$stmtUserPlan = $pdo->prepare("SELECT package FROM users WHERE id = ?");
$stmtUserPlan->execute([$_SESSION['user_id']]);
$userPlan = $stmtUserPlan->fetch();
$user_package = $userPlan['package'] ?? 'basic';

$guest_limit = 150;
if ($user_package === 'standard') {
    $guest_limit = 300;
} elseif ($user_package === 'premium') {
    $guest_limit = 999999;
}

// Add guest
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_guest'])) {
    $name     = trim($_POST['name']);
    $whatsapp = trim($_POST['whatsapp_number']);
    $whatsapp_normalized = normalize_whatsapp_number($whatsapp);
    $category = $_POST['category'];
    $side     = $_POST['side'];
    $seats    = isset($_POST['seats_reserved']) ? intval($_POST['seats_reserved']) : 1;

    $stmtSum = $pdo->prepare("SELECT SUM(seats_reserved) as total FROM guests WHERE wedding_id = ?");
    $stmtSum->execute([$wedding_id]);
    $current_seats = $stmtSum->fetch()['total'] ?? 0;

    if (($current_seats + $seats) > $guest_limit) {
        $msg = "<div class='flash flash-warn'><i class='fas fa-exclamation-triangle'></i> <strong>Limit Reached!</strong> Your current " . ucfirst($user_package) . " plan only allows up to <strong>{$guest_limit} guests (seats)</strong>. (Current: {$current_seats} seats). Please upgrade your package to add more guests.</div>";
    } else {
        $stmtCheck = $pdo->prepare("SELECT id, whatsapp_number FROM guests WHERE wedding_id = ?");
        $stmtCheck->execute([$wedding_id]);
        $already_exists = false;

        while ($row = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            if ($whatsapp_normalized !== '' && normalize_whatsapp_number($row['whatsapp_number']) === $whatsapp_normalized) {
                $already_exists = true;
                break;
            }
        }

        if ($already_exists) {
            $msg = "<div class='flash flash-warn'><i class='fas fa-exclamation-triangle'></i> This WhatsApp number is already in the guest list.</div>";
        } else {
            // අලුතින් Guest කෙනෙක් ඇඩ් කරද්දීම Token එකත් එකපාරම Generate කර සේව් කරයි!
            $token = generate_invite_token($pdo);
            $stmtInsert = $pdo->prepare("INSERT INTO guests (wedding_id, name, whatsapp_number, category, side, seats_reserved, invite_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmtInsert->execute([$wedding_id, $name, $whatsapp_normalized, $category, $side, $seats, $token])) {
                $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Guest added successfully!</div>";
                echo "<script>setTimeout(() => { location.href='guests.php'; }, 1000);</script>";
            }
        }
    }
}

// Delete guest
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmtDel = $pdo->prepare("DELETE FROM guests WHERE id = ? AND wedding_id = ?");
    $stmtDel->execute([$delete_id, $wedding_id]);
    header("Location: guests.php?deleted=1");
    exit();
}
if (isset($_GET['deleted'])) {
    $msg = "<div class='flash flash-info'><i class='fas fa-trash'></i> Guest removed from list.</div>";
}

// Get all guests
$stmtGuests = $pdo->prepare("SELECT * FROM guests WHERE wedding_id = ? ORDER BY id DESC");
$stmtGuests->execute([$wedding_id]);
$guestsList = $stmtGuests->fetchAll();

// Backfill: කලින් Token නැතිව ඇඩ් කරපු අමුත්තන් සිටී නම් පමණක් Token සාදයි (Safety)
foreach ($guestsList as &$g) {
    if (empty($g['invite_token'])) {
        $g['invite_token'] = generate_invite_token($pdo);
        $pdo->prepare("UPDATE guests SET invite_token = ? WHERE id = ?")
            ->execute([$g['invite_token'], $g['id']]);
    }
}
unset($g);

// PHP මඟින් මුළු ආසන ගණන එකතු කිරීම
$total_seats = 0;
foreach ($guestsList as $g) {
    $total_seats += intval($g['seats_reserved'] ?? 1);
}

require 'layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }
    .flash-warn    { background: rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.25); color: #d97706; }
    .flash-info    { background: rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.25); color: #2563eb; }

    .form-field { margin-bottom: 16px; }
    .form-field label { display: block; font-size: 0.73rem; font-weight: 600; color: #9ea3b0; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 7px; }
    .form-field input, .form-field select { width: 100%; border: 1px solid #e8ecf0; border-radius: 10px; padding: 10px 14px; font-family: 'Inter', sans-serif; font-size: 0.88rem; color: #1a1a2e; background: #fafbfc; outline: none; transition: border-color 0.2s; }
    .form-field input:focus, .form-field select:focus { border-color: #c9a96e; background: #fffdf9; }
    .form-field .hint { font-size: 0.73rem; color: #9ea3b0; margin-top: 4px; }
    .btn-add-guest { width: 100%; background: linear-gradient(135deg, #1a1a2e, #2d2d50); color: #c9a96e; border: none; border-radius: 10px; padding: 12px; font-family: 'Inter', sans-serif; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.2s; }
    .btn-add-guest:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,26,46,0.3); color: #c9a96e; }

    /* Page header / toolbar */
    .page-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .page-toolbar h1 { font-size: 1.35rem; font-weight: 700; color: #1a1a2e; margin: 0; }
    .page-toolbar p { font-size: 0.85rem; color: #9ea3b0; margin: 0; }
    .btn-open-add-guest {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, #c9a96e, #a07840);
        color: #0f0f1a; border: none; border-radius: 10px;
        padding: 11px 20px; font-family: 'Inter', sans-serif;
        font-size: 0.85rem; font-weight: 700; cursor: pointer;
        transition: all 0.2s; white-space: nowrap; box-shadow: 0 4px 14px rgba(201,169,110,0.25);
    }
    .btn-open-add-guest:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(201,169,110,0.35); }

    /* Modal styling to match theme */
    #addGuestModal .modal-content { border-radius: 18px; border: none; overflow: hidden; }
    #addGuestModal .modal-header { background: linear-gradient(135deg, #1a1a2e, #2d2d50); border: none; padding: 22px 26px; }
    #addGuestModal .modal-header .modal-title { color: #f8f5ef; font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
    #addGuestModal .modal-header .modal-title i { color: #c9a96e; }
    #addGuestModal .btn-close { filter: invert(1) grayscale(1) brightness(2); opacity: 0.7; }
    #addGuestModal .modal-body { padding: 26px; }
    #addGuestModal .modal-footer { border: none; padding: 0 26px 26px; }

    .guest-list-header { background: white; border: 1px solid #e8ecf0; border-radius: 16px 16px 0 0; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .guest-count { font-size: 0.85rem; font-weight: 700; color: #1a1a2e; }
    .guest-count span { background: rgba(201,169,110,0.12); color: #c9a96e; border-radius: 20px; padding: 2px 10px; font-size: 0.78rem; margin-left: 8px; }

    .search-filter-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .search-wrap { position: relative; }
    .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ea3b0; font-size: 0.8rem; }
    .search-input { border: 1px solid #e8ecf0; border-radius: 10px; padding: 8px 12px 8px 34px; font-family: 'Inter', sans-serif; font-size: 0.82rem; color: #1a1a2e; outline: none; transition: border-color 0.2s; width: 200px; }
    .search-input:focus { border-color: #c9a96e; }
    .filter-select { border: 1px solid #e8ecf0; border-radius: 10px; padding: 8px 28px 8px 12px; font-family: 'Inter', sans-serif; font-size: 0.82rem; color: #4a5568; outline: none; background: white; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ea3b0' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }

    .guest-table-wrap { background: white; border: 1px solid #e8ecf0; border-top: none; border-radius: 0 0 16px 16px; overflow: hidden; }
    .guest-table { width: 100%; border-collapse: collapse; }
    .guest-table th { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #9ea3b0; padding: 12px 16px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e8ecf0; white-space: nowrap; }
    .guest-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.87rem; color: #4a5568; vertical-align: middle; }
    .guest-table tr:last-child td { border-bottom: none; }
    .guest-table tr:hover td { background: #fafbfc; }

    .guest-name-cell { font-weight: 700; color: #1a1a2e; }
    .guest-phone { font-family: monospace; font-size: 0.85rem; color: #6b7280; }

    .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
    .badge-cat { background: rgba(107,114,128,0.1); color: #6b7280; }
    .badge-family { background: rgba(168,85,247,0.1); color: #7c3aed; }
    .badge-friends { background: rgba(59,130,246,0.1); color: #2563eb; }
    .badge-office { background: rgba(245,158,11,0.1); color: #d97706; }
    .badge-vip { background: rgba(201,169,110,0.15); color: #a07840; }
    .badge-bride-side { background: rgba(236,72,153,0.1); color: #be185d; }
    .badge-groom-side { background: rgba(59,130,246,0.1); color: #1d4ed8; }
    .badge-both-side { background: rgba(107,114,128,0.1); color: #6b7280; }
    .badge-attending { display: inline-flex; align-items: center; gap: 4px; background: rgba(34,197,94,0.1); color: #16a34a; }
    .badge-declined { display: inline-flex; align-items: center; gap: 4px; background: rgba(239,68,68,0.1); color: #dc2626; }
    .badge-pending-rsvp { display: inline-flex; align-items: center; gap: 4px; background: rgba(245,158,11,0.1); color: #d97706; }
    
    /* Opened / Sent Badges */
    .badge-opened { display: inline-flex; align-items: center; gap: 4px; background: rgba(34, 197, 94, 0.1); color: #16a34a; }
    .badge-sent { display: inline-flex; align-items: center; gap: 4px; background: rgba(14, 165, 233, 0.1); color: #0284c7; }
    .badge-not-sent { display: inline-flex; align-items: center; gap: 4px; background: rgba(107,114,128,0.08); color: #9ea3b0; }

    .btn-del { background: none; border: 1px solid #fee2e2; border-radius: 8px; color: #dc2626; padding: 6px 10px; cursor: pointer; font-size: 0.75rem; transition: all 0.2s; }
    .btn-del:hover { background: #fee2e2; }

    .btn-wa-send { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 8px; background: rgba(37,211,102,0.1); color: #25d366; text-decoration: none; font-size: 0.9rem; margin-right: 6px; transition: all 0.2s; }
    .btn-wa-send:hover { background: #25d366; color: white; }
    .btn-wa-send.disabled { background: #f1f5f9; color: #d1d5db; cursor: not-allowed; pointer-events: none; }

    .btn-note-view { background: none; border: 1px solid #e8ecf0; border-radius: 8px; color: #4a5568; padding: 6px 10px; font-size: 0.75rem; cursor: pointer; transition: all 0.2s; }
    .btn-wa-note:hover { background: #f1f5f9; }

    .guest-note-box { background: #fffdf5; border-left: 3px solid #c9a96e; padding: 8px 12px; font-size: 0.78rem; color: #5a4a35; border-radius: 4px; margin-top: 6px; display: flex; align-items: center; gap: 6px; }

    .empty-state { text-align: center; padding: 60px 20px; color: #9ea3b0; }
    .empty-state i { font-size: 2.5rem; margin-bottom: 16px; opacity: 0.3; }
    .empty-state p { font-size: 0.9rem; }
</style>

<?php if ($msg) echo $msg; ?>

<!-- Page toolbar -->
<div class="page-toolbar">
    <div>
        <h1>Guest List</h1>
        <p>Manage invitations, track RSVPs, and keep your seat count on point.</p>
    </div>
    <button type="button" class="btn-open-add-guest" data-bs-toggle="modal" data-bs-target="#addGuestModal">
        <i class="fas fa-user-plus"></i> Add New Guest
    </button>
</div>

<!-- Add Guest Modal -->
<div class="modal fade" id="addGuestModal" tabindex="-1" aria-labelledby="addGuestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGuestModalLabel"><i class="fas fa-user-plus"></i> Add New Guest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="guests.php">
                <div class="modal-body">
                    <div class="form-field">
                        <label>Guest Name <span style="color:#c9a96e;">*</span></label>
                        <input type="text" name="name" placeholder="e.g. Kamal Perera" required>
                    </div>
                    <div class="form-field">
                        <label>WhatsApp Number <span style="color:#c9a96e;">*</span></label>
                        <input type="text" name="whatsapp_number" placeholder="e.g. 0771234567" required>
                        <div class="hint">Guests enter this to open their invitation</div>
                    </div>
                    <div class="form-field">
                        <label>Category</label>
                        <select name="category">
                            <option value="Family">👨‍👩‍👧 Family</option>
                            <option value="Friends">👥 Friends</option>
                            <option value="Office">💼 Office</option>
                            <option value="VIP">⭐ VIP</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Side</label>
                        <select name="side">
                            <option value="Bride">Bride's Side</option>
                            <option value="Groom">Groom's Side</option>
                            <option value="Both">Both Sides</option>
                        </select>
                    </div>
                    <div class="form-field" style="margin-bottom:0;">
                        <label>Seats Reserved (ආසන ගණන)</label>
                        <input type="number" name="seats_reserved" value="1" min="1" required>
                        <div class="hint">වෙන් කර ඇති උපරිම ආසන ගණන</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_guest" class="btn-add-guest">
                        <i class="fas fa-plus" style="margin-right:6px;"></i> Add to Guest List
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Guest Table -->
<div class="row g-3">
    <div class="col-12">
        <div class="guest-list-header">
            <div class="guest-count">
                Total Guests (Seats)
                <span id="visible-count"><?php echo $total_seats; ?></span>
                <small style="color:#9ea3b0; font-size:0.75rem; margin-left:5px; font-weight: 500;">
                    (from <?php echo count($guestsList); ?> invitations)
                </small>
            </div>
            <div class="search-filter-bar">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" id="guest-search" placeholder="Search guests...">
                </div>
                <select class="filter-select" id="filter-cat">
                    <option value="">All Categories</option>
                    <option value="Family">Family</option>
                    <option value="Friends">Friends</option>
                    <option value="Office">Office</option>
                    <option value="VIP">VIP</option>
                </select>
                <select class="filter-select" id="filter-rsvp">
                    <option value="">All RSVP</option>
                    <option value="accepted">Attending</option>
                    <option value="rejected">Declined</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>

        <div class="guest-table-wrap">
            <?php if (count($guestsList) > 0): ?>
            <div style="overflow-x:auto;">
                <table class="guest-table" id="guest-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>WhatsApp & Seats</th>
                            <th>Category</th>
                            <th>Status (Opened / Sent)</th>
                            <th>RSVP</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guestsList as $g): ?>
                        <tr
                            data-id="<?php echo $g['id']; ?>"
                            data-name="<?php echo strtolower(htmlspecialchars($g['name'])); ?>"
                            data-cat="<?php echo htmlspecialchars($g['category']); ?>"
                            data-rsvp="<?php echo htmlspecialchars($g['rsvp_status']); ?>"
                            data-seats="<?php echo intval($g['seats_reserved'] ?? 1); ?>"
                        >
                            <td class="guest-name-cell">
                                <?php echo htmlspecialchars($g['name']); ?>
                                
                                <?php if (!empty($g['guest_note'])): ?>
                                    <div class="guest-note-box">
                                        <i class="fas fa-comment-dots" style="color: #c9a96e;"></i>
                                        <strong>Note:</strong> "<?php echo htmlspecialchars($g['guest_note']); ?>"
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="guest-phone"><?php echo htmlspecialchars($g['whatsapp_number']); ?></span>
                                <br>
                                <small style="color:#4a5568; font-size:0.78rem; margin-top:4px; display:inline-block; font-weight: 600;">
                                    <i class="fas fa-chair" style="color:#c9a96e; margin-right:4px;"></i> 
                                    Seats: <?php echo intval($g['seats_reserved'] ?? 1); ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $cat = $g['category'];
                                $catClass = strtolower($cat);
                                echo "<span class='badge badge-{$catClass}'>{$cat}</span>";
                                ?>
                                <br>
                                <?php
                                $side = $g['side'];
                                $sideClass = strtolower(str_replace("'s", '', $side)) . '-side';
                                echo "<span class='badge badge-{$sideClass}' style='margin-top:4px;'>{$side}</span>";
                                ?>
                            </td>
                            
                            <!-- 💡 3-Stage Delivery Funnel Display Column (Mark Sent logic integrated) -->
                            <td class="opened-status-cell">
                                <?php if ($g['is_opened']): ?>
                                    <span class="badge badge-opened"><i class="fas fa-check-double"></i> Opened</span>
                                    <?php if ($g['opened_at']): ?>
                                    <br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">
                                        <?php echo date("d M h:i A", strtotime($g['opened_at'])); ?>
                                    </small>
                                    <?php endif; ?>
                                <?php elseif ($g['is_sent']): ?>
                                    <span class="badge badge-sent"><i class="fas fa-paper-plane"></i> Sent</span>
                                    <?php if ($g['sent_at']): ?>
                                    <br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">
                                        <?php echo date("d M h:i A", strtotime($g['sent_at'])); ?>
                                    </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-not-sent">Not sent</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="rsvp-status-cell">
                                <?php
                                if ($g['rsvp_status'] == 'accepted')
                                    echo "<span class='badge badge-attending'><i class='fas fa-check'></i> Attending</span>";
                                elseif ($g['rsvp_status'] == 'rejected')
                                    echo "<span class='badge badge-declined'><i class='fas fa-times'></i> Declined</span>";
                                else
                                    echo "<span class='badge badge-pending-rsvp'>Pending</span>";
                                ?>
                            </td>
                            <td>
                                <?php
                                    $guest_wa_intl = to_whatsapp_intl($g['whatsapp_number']);
                                ?>
                                <?php if (!empty($guest_wa_intl) && !empty($invite_url_for_header)): ?>
                                    <a href="#"
                                       target="_blank"
                                       class="btn-wa-send"
                                       data-id="<?php echo $g['id']; ?>"
                                       data-phone="<?php echo $guest_wa_intl; ?>"
                                       data-token="<?php echo htmlspecialchars($g['invite_token']); ?>"
                                       data-guest-name="<?php echo htmlspecialchars($g['name']); ?>"
                                       title="Send personalized invitation to <?php echo htmlspecialchars($g['name']); ?> via WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="btn-wa-send disabled" title="No valid WhatsApp number">
                                        <i class="fab fa-whatsapp"></i>
                                    </span>
                                <?php endif; ?>

                                <a href="guests.php?delete=<?php echo $g['id']; ?>"
                                   class="btn-del"
                                   onclick="return confirm('Remove <?php echo addslashes($g['name']); ?> from the guest list?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div><i class="fas fa-users"></i></div>
                <p>No guests yet. Click "Add New Guest" above to get started.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function filterGuests() {
    const search = document.getElementById('guest-search').value.toLowerCase();
    const cat    = document.getElementById('filter-cat').value;
    const rsvp   = document.getElementById('filter-rsvp').value;
    const rows   = document.querySelectorAll('#guest-table tbody tr');
    let visibleSeats  = 0;

    rows.forEach(row => {
        const name    = row.dataset.name || '';
        const rowCat  = row.dataset.cat  || '';
        const rowRsvp = row.dataset.rsvp || '';
        const rowSeats = parseInt(row.dataset.seats) || 1;

        const matchSearch = name.includes(search);
        const matchCat    = !cat  || rowCat  === cat;
        const matchRsvp   = !rsvp || rowRsvp === rsvp;

        const show = matchSearch && matchCat && matchRsvp;
        row.style.display = show ? '' : 'none';
        
        if (show) {
            visibleSeats += rowSeats;
        }
    });

    document.getElementById('visible-count').textContent = visibleSeats;
}

document.getElementById('guest-search').addEventListener('input', filterGuests);
document.getElementById('filter-cat').addEventListener('change', filterGuests);
document.getElementById('filter-rsvp').addEventListener('change', filterGuests);

document.querySelectorAll('.btn-wa-send').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const guestId = this.getAttribute('data-id');
        const phone = this.getAttribute('data-phone');
        const token = this.getAttribute('data-token');
        const guestName = this.getAttribute('data-guest-name');
        const coupleName = "<?php echo htmlspecialchars($user_name); ?>";
        
        const inviteBaseUrl = "<?php echo $invite_url_for_header; ?>";
        const separator = inviteBaseUrl.includes('?') ? '&' : '?';
        const personalLink = inviteBaseUrl + separator + 't=' + encodeURIComponent(token);
        
        const flower = "\u{1F338}"; 
        const heart = "\u{2764}\u{FE0F}";
        
        const personalMessage = `Hi ${guestName} ${flower}\n\n`
            + `With so much love and happiness in our hearts, we're excited to invite you to celebrate the invitation of our journey together - ${coupleName}\n\n`
            + `It would truly mean the world to us to have you with us on this special day\n\n`
            + `Invitation: ${personalLink}\n\n`
            + `We can't wait to celebrate, laugh, and create beautiful memories with you! ${heart}`;
            
        const encodedMessage = encodeURIComponent(personalMessage);
        
        // 1. AJAX එකෙන් පසුබිමෙන් (Background) සර්වර් එකට යවා "Sent" ලෙස DB එකේ සටහන් කිරීම
        fetch(`guests.php?action=mark_sent&id=${guestId}`);

        // 2. සජීවීව (Instantly) Table Row එකේ පෙනුම "Sent" ලෙස වෙනස් කිරීම (Wow factor!)
        const statusCell = this.closest('tr').querySelector('.opened-status-cell');
        if (statusCell && !statusCell.querySelector('.badge-opened')) {
            statusCell.innerHTML = `<span class="badge badge-sent"><i class="fas fa-paper-plane"></i> Sent</span><br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">Just now</small>`;
        }
        
        let waUrl = "";
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (isMobile) {
            waUrl = `whatsapp://send?phone=${phone}&text=${encodedMessage}`;
            window.open(waUrl, '_blank');
        } else {
            let hasApp = false;       
            const checkBlur = () => { hasApp = true; };
            window.addEventListener('blur', checkBlur);           
            window.location.href = `whatsapp://send?phone=${phone}&text=${encodedMessage}`;           
            setTimeout(() => {
                window.removeEventListener('blur', checkBlur);
                if (!hasApp) {
                    const webUrl = `https://web.whatsapp.com/send?phone=${phone}&text=${encodedMessage}`;
                    window.open(webUrl, '_blank');
                }
            }, 1000);
        }
    });
});

// =====================================================================
// 🔥 සජීවීව Guest Status Update කිරීම (Opened / Sent / RSVP) — 5s Polling
// Note: WA/Delete buttons ම untouched ව තියෙනවා, status cells විතරයි update වෙන්නේ.
// =====================================================================
function formatLiveDateTime(mysqlDatetime) {
    if (!mysqlDatetime) return '';
    const d = new Date(mysqlDatetime.replace(' ', 'T'));
    if (isNaN(d.getTime())) return '';
    return d.toLocaleString('en-US', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit', hour12: true });
}

// Security HTML Escaper Helper to prevent XSS during live DOM updates
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}

function renderOpenedStatusCell(g) {
    if (g.is_opened == 1) {
        let html = `<span class="badge badge-opened"><i class="fas fa-check-double"></i> Opened</span>`;
        if (g.opened_at) html += `<br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">${formatLiveDateTime(g.opened_at)}</small>`;
        return html;
    } else if (g.is_sent == 1) {
        let html = `<span class="badge badge-sent"><i class="fas fa-paper-plane"></i> Sent</span>`;
        if (g.sent_at) html += `<br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">${formatLiveDateTime(g.sent_at)}</small>`;
        return html;
    }
    return `<span class="badge badge-not-sent">Not sent</span>`;
}

function renderRsvpStatusCell(rsvp) {
    if (rsvp === 'accepted') return `<span class='badge badge-attending'><i class='fas fa-check'></i> Attending</span>`;
    if (rsvp === 'rejected') return `<span class='badge badge-declined'><i class='fas fa-times'></i> Declined</span>`;
    return `<span class='badge badge-pending-rsvp'>Pending</span>`;
}

function fetchGuestsLiveStatus() {
    fetch('guests.php?action=live_status')
        .then(r => r.json())
        .then(data => {
            if (!data.guests) return;
            data.guests.forEach(g => {
                const row = document.querySelector(`#guest-table tr[data-id="${g.id}"]`);
                if (!row) return;

                // 💡 Real-time Guest Note Render Algorithm (සජීවීව Note එකද සහිතව update කරයි!)
                const nameCell = row.querySelector('.guest-name-cell');
                if (nameCell) {
                    let cellHtml = escapeHtml(g.name);
                    if (g.guest_note && g.guest_note.trim() !== '') {
                        cellHtml += `
                            <div class="guest-note-box">
                                <i class="fas fa-comment-dots" style="color: #c9a96e;"></i>
                                <strong>Note:</strong> "${escapeHtml(g.guest_note)}"
                            </div>
                        `;
                    }
                    nameCell.innerHTML = cellHtml;
                }

                const openedCell = row.querySelector('.opened-status-cell');
                if (openedCell) openedCell.innerHTML = renderOpenedStatusCell(g);

                const rsvpCell = row.querySelector('.rsvp-status-cell');
                if (rsvpCell) rsvpCell.innerHTML = renderRsvpStatusCell(g.rsvp_status);

                // Keep the RSVP filter dropdown accurate against live data
                row.dataset.rsvp = g.rsvp_status;
            });
        })
        .catch(err => console.error('Error syncing guest live status:', err));
}
setInterval(fetchGuestsLiveStatus, 5000);
</script>

<?php require 'layouts/footer.php'; ?>