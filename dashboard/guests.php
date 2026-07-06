<?php
session_start();
// config.php එක config folder එකේ ඇති පරිදි සම්බන්ධ කිරීම
require '../config/config.php';

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
        // 0771234567 -> 94771234567
        $digits = '94' . substr($digits, 1);
    } elseif (substr($digits, 0, 2) !== '94') {
        // just in case a bare 771234567 slipped through
        $digits = '94' . $digits;
    }
    return $digits;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: login.php");
    exit();
}

$wedding_id = $_SESSION['wedding_id'];
$msg = "";

// Add guest
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_guest'])) {
    $name     = trim($_POST['name']);
    $whatsapp = trim($_POST['whatsapp_number']);
    $whatsapp_normalized = normalize_whatsapp_number($whatsapp);
    $category = $_POST['category'];
    $side     = $_POST['side'];
    // ආසන ගණන ලබාගැනීම (Default 1)
    $seats    = isset($_POST['seats_reserved']) ? intval($_POST['seats_reserved']) : 1;

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
        // SQL query එකට seats_reserved එකතු කිරීම
        $stmtInsert = $pdo->prepare("INSERT INTO guests (wedding_id, name, whatsapp_number, category, side, seats_reserved) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmtInsert->execute([$wedding_id, $name, $whatsapp_normalized, $category, $side, $seats])) {
            $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Guest added successfully!</div>";
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

// PHP මඟින් මුළු ආසන (Seats) ගණන එකතු කිරීම
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

    .add-guest-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        padding: 28px;
        position: sticky;
        top: 80px;
    }
    .add-guest-card h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .form-field { margin-bottom: 16px; }
    .form-field label {
        display: block;
        font-size: 0.73rem;
        font-weight: 600;
        color: #9ea3b0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 7px;
    }
    .form-field input, .form-field select {
        width: 100%;
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 10px 14px;
        font-family: 'Inter', sans-serif;
        font-size: 0.88rem;
        color: #1a1a2e;
        background: #fafbfc;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-field input:focus, .form-field select:focus {
        border-color: #c9a96e;
        background: #fffdf9;
    }
    .form-field .hint {
        font-size: 0.73rem;
        color: #9ea3b0;
        margin-top: 4px;
    }
    .btn-add-guest {
        width: 100%;
        background: linear-gradient(135deg, #1a1a2e, #2d2d50);
        color: #c9a96e;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-add-guest:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(26,26,46,0.3);
    }

    /* Guest list */
    .guest-list-header {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px 16px 0 0;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .guest-count {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1a1a2e;
    }
    .guest-count span {
        background: rgba(201,169,110,0.12);
        color: #c9a96e;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 0.78rem;
        margin-left: 8px;
    }

    .search-filter-bar {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .search-wrap {
        position: relative;
    }
    .search-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ea3b0;
        font-size: 0.8rem;
    }
    .search-input {
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 8px 12px 8px 34px;
        font-family: 'Inter', sans-serif;
        font-size: 0.82rem;
        color: #1a1a2e;
        outline: none;
        transition: border-color 0.2s;
        width: 200px;
    }
    .search-input:focus { border-color: #c9a96e; }
    .filter-select {
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 8px 28px 8px 12px;
        font-family: 'Inter', sans-serif;
        font-size: 0.82rem;
        color: #4a5568;
        outline: none;
        background: white;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ea3b0' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }

    /* Table */
    .guest-table-wrap {
        background: white;
        border: 1px solid #e8ecf0;
        border-top: none;
        border-radius: 0 0 16px 16px;
        overflow: hidden;
    }
    .guest-table { width: 100%; border-collapse: collapse; }
    .guest-table th {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ea3b0;
        padding: 12px 16px;
        text-align: left;
        background: #f8fafc;
        border-bottom: 1px solid #e8ecf0;
        white-space: nowrap;
    }
    .guest-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.87rem;
        color: #4a5568;
        vertical-align: middle;
    }
    .guest-table tr:last-child td { border-bottom: none; }
    .guest-table tr:hover td { background: #fafbfc; }

    .guest-name-cell { font-weight: 700; color: #1a1a2e; }
    .guest-phone { font-family: monospace; font-size: 0.85rem; color: #6b7280; }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .badge-cat { background: rgba(107,114,128,0.1); color: #6b7280; }
    .badge-family { background: rgba(168,85,247,0.1); color: #7c3aed; }
    .badge-friends { background: rgba(59,130,246,0.1); color: #2563eb; }
    .badge-office { background: rgba(245,158,11,0.1); color: #d97706; }
    .badge-vip { background: rgba(201,169,110,0.15); color: #a07840; }
    .badge-bride-side { background: rgba(236,72,153,0.1); color: #be185d; }
    .badge-groom-side { background: rgba(59,130,246,0.1); color: #1d4ed8; }
    .badge-both-side { background: rgba(107,114,128,0.1); color: #6b7280; }
    .badge-attending { background: rgba(34,197,94,0.1); color: #16a34a; }
    .badge-declined { background: rgba(239,68,68,0.1); color: #dc2626; }
    .badge-pending-rsvp { background: rgba(245,158,11,0.1); color: #d97706; }
    .badge-opened { background: rgba(59,130,246,0.1); color: #2563eb; }
    .badge-not-opened { background: rgba(107,114,128,0.08); color: #9ea3b0; }

    .btn-del {
        background: none;
        border: 1px solid #fee2e2;
        border-radius: 8px;
        color: #dc2626;
        padding: 6px 10px;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
    .btn-del:hover { background: #fee2e2; }

    .btn-wa-send {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(37,211,102,0.1);
        color: #25d366;
        text-decoration: none;
        font-size: 0.9rem;
        margin-right: 6px;
        transition: all 0.2s;
    }
    .btn-wa-send:hover { background: #25d366; color: white; }
    .btn-wa-send.disabled {
        background: #f1f5f9;
        color: #d1d5db;
        cursor: not-allowed;
        pointer-events: none;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ea3b0;
    }
    .empty-state i { font-size: 2.5rem; margin-bottom: 16px; opacity: 0.3; }
    .empty-state p { font-size: 0.9rem; }
</style>

<?php if ($msg) echo $msg; ?>

<div class="row g-3">
    <!-- Left: Add Guest Form -->
    <div class="col-lg-4">
        <div class="add-guest-card">
            <h5><i class="fas fa-user-plus" style="color:#c9a96e; margin-right:8px;"></i> Add New Guest</h5>
            <form method="POST" action="guests.php">
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
                <!-- අලුත් ආසන ගණන ඇතුලත් කිරීමේ කොටස -->
                <div class="form-field">
                    <label>Seats Reserved (ආසන ගණන)</label>
                    <input type="number" name="seats_reserved" value="1" min="1" required>
                    <div class="hint">වෙන් කර ඇති උපරිම ආසන ගණන</div>
                </div>
                <button type="submit" name="add_guest" class="btn-add-guest">
                    <i class="fas fa-plus" style="margin-right:6px;"></i> Add to Guest List
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Guest Table -->
    <div class="col-lg-8">
        <div class="guest-list-header">
            <div class="guest-count">
                Total Guests (Seats)
                <span id="visible-count"><?php echo $total_seats; ?></span>
                <!-- Invitations/Rows ගණන වෙනම පෙන්වීම -->
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
                            <th>Opened</th>
                            <th>RSVP</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guestsList as $g): ?>
                        <!-- JS Live Filter එක සඳහා data-seats එකතු කිරීම -->
                        <tr
                            data-name="<?php echo strtolower(htmlspecialchars($g['name'])); ?>"
                            data-cat="<?php echo htmlspecialchars($g['category']); ?>"
                            data-rsvp="<?php echo htmlspecialchars($g['rsvp_status']); ?>"
                            data-seats="<?php echo intval($g['seats_reserved'] ?? 1); ?>"
                        >
                            <td class="guest-name-cell"><?php echo htmlspecialchars($g['name']); ?></td>
                            <td>
                                <span class="guest-phone"><?php echo htmlspecialchars($g['whatsapp_number']); ?></span>
                                <br>
                                <!-- Table එක ඇතුලේ වෙන් කර ඇති ආසන ගණන පෙන්වීම -->
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
                            <td>
                                <?php if ($g['is_opened']): ?>
                                    <span class="badge badge-opened"><i class="fas fa-check"></i> Opened</span>
                                    <?php if ($g['opened_at']): ?>
                                    <br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">
                                        <?php echo date("d M, h:i A", strtotime($g['opened_at'])); ?>
                                    </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-not-opened">Not opened</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if ($g['rsvp_status'] == 'accepted')
                                    echo "<span class='badge badge-attending'><i class='fas fa-check'></i> Attending</span>";
                                elseif ($g['rsvp_status'] == 'rejected')
                                    echo "<span class='badge badge-declined'><i class='fas fa-times'></i> Declined</span>";
                                else
                                    echo "<span class='badge badge-pending-rsvp'>Pending</span>";
                                ?>
                                <?php if (!empty($g['guest_note'])): ?>
                                <br><small style="color:#9ea3b0; font-size:0.7rem;" title="<?php echo htmlspecialchars($g['guest_note']); ?>">
                                    <i class="fas fa-sticky-note"></i> Has note
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $guest_wa_intl = to_whatsapp_intl($g['whatsapp_number']);
                                ?>
                                <?php if (!empty($guest_wa_intl) && !empty($invite_url_for_header)): ?>
                                    <?php
                                        // Personalized link: guest's own number embedded so the
                                        // seal-open flow can auto-verify without them typing it.
                                        $personal_link = $invite_url_for_header
                                            . (strpos($invite_url_for_header, '?') !== false ? '&' : '?')
                                            . 'wa=' . rawurlencode($g['whatsapp_number']);

                                        // Reuse the exact wording from header.php, just swap the
                                        // link at the end for this guest's personal one.
                                        $personal_message = str_replace($invite_url_for_header, $personal_link, $invite_share_message_header);

                                        $guest_wa_link = "https://wa.me/{$guest_wa_intl}?text=" . rawurlencode($personal_message);
                                    ?>
                                    <a href="<?php echo htmlspecialchars($guest_wa_link); ?>"
                                       target="_blank"
                                       class="btn-wa-send"
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
                <p>No guests yet. Add your first guest using the form.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Live search + filter (ආසන ගණන එකතු වන ලෙස සකසා ඇත)
function filterGuests() {
    const search = document.getElementById('guest-search').value.toLowerCase();
    const cat    = document.getElementById('filter-cat').value;
    const rsvp   = document.getElementById('filter-rsvp').value;
    const rows   = document.querySelectorAll('#guest-table tbody tr');
    let visibleSeats  = 0; // පෙනෙන්නට ඇති මුළු ආසන ගණන

    rows.forEach(row => {
        const name    = row.dataset.name || '';
        const rowCat  = row.dataset.cat  || '';
        const rowRsvp = row.dataset.rsvp || '';
        const rowSeats = parseInt(row.dataset.seats) || 1; // row එකේ seats ගණන ගැනීම

        const matchSearch = name.includes(search);
        const matchCat    = !cat  || rowCat  === cat;
        const matchRsvp   = !rsvp || rowRsvp === rsvp;

        const show = matchSearch && matchCat && matchRsvp;
        row.style.display = show ? '' : 'none';
        
        if (show) {
            visibleSeats += rowSeats; // Filter වන row වල seats එකතු කිරීම
        }
    });

    document.getElementById('visible-count').textContent = visibleSeats;
}

document.getElementById('guest-search').addEventListener('input', filterGuests);
document.getElementById('filter-cat').addEventListener('change', filterGuests);
document.getElementById('filter-rsvp').addEventListener('change', filterGuests);
</script>

<?php require 'layouts/footer.php'; ?>