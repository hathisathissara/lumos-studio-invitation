<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

if (isset($_GET['deleted'])) {
    $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Account and all associated data permanently deleted.</div>";
}

// Activate / Deactivate
if (isset($_GET['action']) && isset($_GET['uid'])) {
    $action_status = ($_GET['action'] === 'activate') ? 'active' : 'pending';
    $u_id = intval($_GET['uid']);
    
    if ($action_status === 'active') {
        // Fetch and delete the slip file
        $stmtSlip = $pdo->prepare("SELECT payment_slip FROM users WHERE id = ?");
        $stmtSlip->execute([$u_id]);
        $slipFile = $stmtSlip->fetchColumn();
        
        if (!empty($slipFile) && file_exists('../' . $slipFile)) {
            unlink('../' . $slipFile);
        }
        
        $stmtUpdate = $pdo->prepare("UPDATE users SET status = ?, payment_slip = NULL WHERE id = ?");
    } else {
        $stmtUpdate = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    }

    if ($stmtUpdate->execute([$action_status, $u_id])) {
        $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Account status updated to <strong>{$action_status}</strong>.</div>";
    }
}

// Fetch all couples
$stmtUsers = $pdo->prepare("
    SELECT users.id, users.name, users.email, users.status, users.payment_slip, users.created_at,
           weddings.wedding_date, weddings.bride_name, weddings.groom_name, weddings.id as wedding_id,
           weddings.slug
    FROM users 
    LEFT JOIN weddings ON users.id = weddings.user_id 
    WHERE users.role = 'couple' 
    ORDER BY users.id DESC
");
$stmtUsers->execute();
$usersList = $stmtUsers->fetchAll();

$domain = rtrim('http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])), '/');

// Stats
$total   = count($usersList);
$active  = count(array_filter($usersList, fn($u) => $u['status'] === 'active'));
$pending = $total - $active;

require 'layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.25); color: #16a34a; }

    /* Admin stat strip */
    .admin-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .admin-stat {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 14px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 160px;
    }
    .admin-stat-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .icon-gold  { background: rgba(201,169,110,0.12); color: #c9a96e; }
    .icon-green { background: rgba(34,197,94,0.12);  color: #22c55e; }
    .icon-amber { background: rgba(245,158,11,0.12); color: #d97706; }
    .admin-stat-num { font-size: 1.8rem; font-weight: 800; color: #1a1a2e; line-height: 1; }
    .admin-stat-label { font-size: 0.75rem; color: #9ea3b0; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Table card */
    .table-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        overflow: hidden;
    }
    .table-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .table-card-header h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }
    .search-wrap { position: relative; }
    .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ea3b0; font-size: 0.8rem; }
    .search-input {
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 8px 12px 8px 34px;
        font-family: 'Inter', sans-serif;
        font-size: 0.82rem;
        outline: none;
        width: 220px;
    }
    .search-input:focus { border-color: #c9a96e; }

    /* Admin table */
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th {
        padding: 12px 16px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ea3b0;
        background: #f8fafc;
        border-bottom: 1px solid #e8ecf0;
        text-align: left;
        white-space: nowrap;
    }
    .admin-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.86rem;
        color: #4a5568;
        vertical-align: middle;
    }
    .admin-table tr:last-child td { border-bottom: none; }
    .admin-table tr:hover td { background: #fafbfc; }

    .couple-name { font-weight: 700; color: #1a1a2e; }
    .couple-email { font-size: 0.78rem; color: #9ea3b0; margin-top: 2px; }

    .badge-active  { display:inline-flex; align-items:center; gap:4px; background:rgba(34,197,94,0.1); color:#16a34a; border-radius:20px; padding:4px 10px; font-size:0.72rem; font-weight:700; }
    .badge-pending { display:inline-flex; align-items:center; gap:4px; background:rgba(245,158,11,0.1); color:#d97706; border-radius:20px; padding:4px 10px; font-size:0.72rem; font-weight:700; }

    .slip-thumb {
        width: 44px; height: 44px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e8ecf0;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .slip-thumb:hover { transform: scale(1.08); }

    .btn-activate {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(34,197,94,0.1);
        color: #16a34a;
        border: 1px solid rgba(34,197,94,0.2);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-activate:hover { background: rgba(34,197,94,0.2); color: #16a34a; }

    .btn-deactivate {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(245,158,11,0.1);
        color: #d97706;
        border: 1px solid rgba(245,158,11,0.2);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-deactivate:hover { background: rgba(245,158,11,0.2); color: #d97706; }

    .btn-preview {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: transparent;
        color: #9ea3b0;
        border: 1px solid #e8ecf0;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        margin-right: 4px;
    }
    .btn-preview:hover { border-color: #c9a96e; color: #c9a96e; }

    .btn-delete-account {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px; height: 30px;
        background: rgba(239,68,68,0.08);
        color: #dc2626;
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: 8px;
        font-size: 0.75rem;
        text-decoration: none;
        transition: all 0.2s;
        margin-left: 2px;
    }
    .btn-delete-account:hover { background: rgba(239,68,68,0.18); color: #dc2626; }

    /* Lightbox */
    .lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .lightbox.open { display: flex; }
    .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 12px; object-fit: contain; }
    .lightbox-close { position: absolute; top: 20px; right: 20px; color: white; font-size: 1.8rem; cursor: pointer; }

    .empty-table { text-align: center; padding: 50px; color: #9ea3b0; }
</style>

<?php if ($msg) echo $msg; ?>

<!-- Stats -->
<div class="admin-stats">
    <div class="admin-stat">
        <div class="admin-stat-icon icon-gold"><i class="fas fa-users"></i></div>
        <div>
            <div class="admin-stat-num"><?php echo $total; ?></div>
            <div class="admin-stat-label">Total Couples</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon icon-green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="admin-stat-num"><?php echo $active; ?></div>
            <div class="admin-stat-label">Active</div>
        </div>
    </div>
    <div class="admin-stat">
        <div class="admin-stat-icon icon-amber"><i class="fas fa-clock"></i></div>
        <div>
            <div class="admin-stat-num"><?php echo $pending; ?></div>
            <div class="admin-stat-label">Pending Review</div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="table-card">
    <div class="table-card-header">
        <h5>Registered Couples</h5>
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="admin-search" placeholder="Search couples...">
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table" id="admin-table">
            <thead>
                <tr>
                    <th>Couple</th>
                    <th>Email</th>
                    <th>Wedding Date</th>
                    <th>Bank Slip</th>
                    <th>Status</th>
                    <th>Invite Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($usersList) > 0): ?>
                    <?php foreach ($usersList as $user):
                        $wedding_past = !empty($user['wedding_date']) && strtotime($user['wedding_date']) < strtotime('today');
                        $invite_slug  = !empty($user['slug']) ? $user['slug'] : ('invite.php?w_id=' . $user['wedding_id']);
                        $invite_url   = $domain . '/' . $invite_slug;
                    ?>
                    <tr data-search="<?php echo strtolower($user['name'] . ' ' . $user['email']); ?>">
                        <td>
                            <div class="couple-name"><?php echo htmlspecialchars($user['name']); ?></div>
                            <div class="couple-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($user['wedding_date']): ?>
                                <?php echo date("d M Y", strtotime($user['wedding_date'])); ?>
                                <?php if ($wedding_past): ?>
                                    <span style="display:inline-flex;align-items:center;gap:3px;background:rgba(239,68,68,0.08);color:#dc2626;border-radius:6px;padding:2px 7px;font-size:0.68rem;font-weight:700;margin-left:4px;"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($user['payment_slip'])): ?>
                                <?php
                                $slip_path = '../' . $user['payment_slip'];
                                $ext_slip = strtolower(pathinfo($user['payment_slip'], PATHINFO_EXTENSION));
                                ?>
                                <?php if ($ext_slip === 'pdf'): ?>
                                    <a href="<?php echo $slip_path; ?>" target="_blank"
                                       style="font-size:0.78rem; color:#c9a96e; text-decoration:none;">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($slip_path); ?>"
                                         class="slip-thumb"
                                         onclick="openLightbox(this.src)"
                                         alt="Payment slip">
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:#d1d5db; font-size:0.78rem; font-style:italic;">No slip yet</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge-active"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Active</span>
                            <?php else: ?>
                                <span class="badge-pending"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['wedding_id'] && !empty($user['slug'])): ?>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-size:0.78rem;color:#4a5568;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo htmlspecialchars($invite_url); ?>"><?php echo htmlspecialchars($invite_url); ?></span>
                                    <button onclick="adminCopyLink('<?php echo addslashes($invite_url); ?>', this)" style="background:none;border:1px solid #e8ecf0;border-radius:6px;padding:3px 8px;cursor:pointer;font-size:0.72rem;color:#9ea3b0;flex-shrink:0;" title="Copy link"><i class="fas fa-copy"></i></button>
                                </div>
                            <?php elseif ($user['wedding_id']): ?>
                                <span style="font-size:0.75rem;color:#d1d5db;">No slug yet</span>
                            <?php else: ?>
                                <span style="color:#d1d5db;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if ($user['wedding_id']): ?>
                            <a href="../view_invitation.php?w_id=<?php echo $user['wedding_id']; ?>&preview=1"
                               target="_blank" class="btn-preview">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            <?php endif; ?>

                            <?php if ($user['status'] === 'pending'): ?>
                            <a href="admin_dashboard.php?action=activate&uid=<?php echo $user['id']; ?>"
                               class="btn-activate"
                               onclick="return confirm('Activate account for <?php echo addslashes($user['name']); ?>?');">
                                <i class="fas fa-check"></i> Activate
                            </a>
                            <?php else: ?>
                            <a href="admin_dashboard.php?action=deactivate&uid=<?php echo $user['id']; ?>"
                               class="btn-deactivate"
                               onclick="return confirm('Deactivate account for <?php echo addslashes($user['name']); ?>?');">
                                <i class="fas fa-times"></i> Deactivate
                            </a>
                            <?php endif; ?>

                            <?php if ($wedding_past): ?>
                            <a href="admin_delete_account.php?uid=<?php echo $user['id']; ?>"
                               class="btn-delete-account"
                               onclick="return confirm('Permanently delete this account?');"
                               title="Wedding has passed — delete this account">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty-table">No couples registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Lightbox for slip images -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close"><i class="fas fa-times"></i></span>
    <img src="" id="lightbox-img" alt="Payment slip">
</div>

<script>
// Search
document.getElementById('admin-search').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#admin-table tbody tr').forEach(row => {
        row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
    });
});

// Lightbox
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// Admin copy invite link
function adminCopyLink(url, btn) {
    navigator.clipboard.writeText(url).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check" style="color:#22c55e;"></i>';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
        showToast('\u2713 Invite link copied!');
    }).catch(() => {
        prompt('Copy this invite link:', url);
    });
}
</script>

<?php require 'layouts/footer.php'; ?>