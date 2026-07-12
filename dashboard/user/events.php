<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: login.php");
    exit();
}

$wedding_id = $_SESSION['wedding_id'];
$msg = "";

// Add event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_name     = trim($_POST['event_name']);
    $event_date_time = $_POST['event_date_time'];
    $location_name  = trim($_POST['location_name']);
    $google_map_link = trim($_POST['google_map_link']);

    $stmtInsert = $pdo->prepare("INSERT INTO events (wedding_id, event_name, event_date_time, location_name, google_map_link) VALUES (?, ?, ?, ?, ?)");
    if ($stmtInsert->execute([$wedding_id, $event_name, $event_date_time, $location_name, $google_map_link])) {
        $msg = "<div class='flash flash-success'><i class='fas fa-check-circle'></i> Event added successfully!</div>";
    } else {
        $msg = "<div class='flash flash-error'><i class='fas fa-times-circle'></i> Failed to add event.</div>";
    }
}

// Delete event
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmtDel = $pdo->prepare("DELETE FROM events WHERE id = ? AND wedding_id = ?");
    $stmtDel->execute([$delete_id, $wedding_id]);
    header("Location: events.php?deleted=1");
    exit();
}
if (isset($_GET['deleted'])) {
    $msg = "<div class='flash flash-info'><i class='fas fa-trash'></i> Event removed.</div>";
}

// Fetch events
$stmtEvents = $pdo->prepare("SELECT * FROM events WHERE wedding_id = ? ORDER BY event_date_time ASC");
$stmtEvents->execute([$wedding_id]);
$eventsList = $stmtEvents->fetchAll();

require 'layouts/header.php';
?>

<style>
    .flash { padding: 13px 18px; border-radius: 12px; font-size: 0.87rem; margin-bottom: 20px; display:flex; align-items:center; gap:10px; }
    .flash-success { background: rgba(34,197,94,0.1);  border:1px solid rgba(34,197,94,0.25);  color: #16a34a; }
    .flash-error   { background: rgba(239,68,68,0.1);  border:1px solid rgba(239,68,68,0.25);  color: #dc2626; }
    .flash-info    { background: rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.25); color: #2563eb; }

    /* Form card */
    .form-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        padding: 28px;
        position: sticky;
        top: 80px;
    }
    .form-card h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-card h5 i { color: #c9a96e; }
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
    .form-field input, .form-field select, .form-field textarea {
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
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus {
        border-color: #c9a96e;
        background: #fffdf9;
    }
    .form-field .hint { font-size: 0.73rem; color: #9ea3b0; margin-top: 4px; }
    .btn-add {
        width: 100%;
        background: linear-gradient(135deg, #1a1a2e, #2d2d50);
        color: #c9a96e;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-add:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(26,26,46,0.3);
    }

    /* Event cards */
    .event-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.25s;
        height: 100%;
    }
    .event-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px; height: 100%;
        background: linear-gradient(to bottom, #c9a96e, rgba(201,169,110,0.2));
    }
    .event-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .event-card-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 14px;
        padding-right: 30px;
    }
    .event-meta-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.83rem;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .event-meta-row i { color: #c9a96e; width: 14px; text-align: center; font-size: 0.8rem; }
    .event-card-actions {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .btn-map-sm {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(201,169,110,0.1);
        color: #a07840;
        border: 1px solid rgba(201,169,110,0.2);
        border-radius: 8px;
        padding: 7px 13px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-map-sm:hover { background: rgba(201,169,110,0.2); color: #a07840; }
    .btn-del-sm {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: none;
        border: 1px solid #fee2e2;
        color: #dc2626;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        margin-left: auto;
    }
    .btn-del-sm:hover { background: #fee2e2; color: #dc2626; }

    /* Empty */
    .empty-events {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        text-align: center;
        padding: 60px 20px;
        color: #9ea3b0;
    }
    .empty-events i { font-size: 2.5rem; opacity: 0.3; margin-bottom: 14px; display: block; }
    .empty-events p { font-size: 0.9rem; }

    /* Page toolbar */
    .page-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .page-toolbar h1 { font-size: 1.35rem; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; }
    .page-toolbar p { font-size: 0.85rem; color: #9ea3b0; margin: 0; }
    .btn-open-add-event {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, #c9a96e, #a07840);
        color: #0f0f1a; border: none; border-radius: 10px;
        padding: 11px 20px; font-family: 'Inter', sans-serif;
        font-size: 0.85rem; font-weight: 700; cursor: pointer;
        transition: all 0.2s; white-space: nowrap; box-shadow: 0 4px 14px rgba(201,169,110,0.25);
    }
    .btn-open-add-event:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(201,169,110,0.35); }

    /* Modal styling to match theme */
    #addEventModal .modal-content { border-radius: 18px; border: none; overflow: hidden; }
    #addEventModal .modal-header { background: linear-gradient(135deg, #1a1a2e, #2d2d50); border: none; padding: 22px 26px; }
    #addEventModal .modal-header .modal-title { color: #f8f5ef; font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
    #addEventModal .modal-header .modal-title i { color: #c9a96e; }
    #addEventModal .btn-close { filter: invert(1) grayscale(1) brightness(2); opacity: 0.7; }
    #addEventModal .modal-body { padding: 26px; }
    #addEventModal .modal-footer { border: none; padding: 0 26px 26px; }
</style>

<?php if ($msg) echo $msg; ?>

<!-- Page toolbar -->
<div class="page-toolbar">
    <div>
        <h1>Wedding Events</h1>
        <p>Add every ceremony guests need to know about — Poruwa, Reception, Church, Homecoming.</p>
    </div>
    <button type="button" class="btn-open-add-event" data-bs-toggle="modal" data-bs-target="#addEventModal">
        <i class="fas fa-calendar-plus"></i> Add Event
    </button>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel"><i class="fas fa-calendar-plus"></i> Add Wedding Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="events.php">
                <div class="modal-body">
                    <div class="form-field">
                        <label>Event Name <span style="color:#c9a96e;">*</span></label>
                        <input type="text" name="event_name" placeholder="e.g. Poruwa Ceremony, Reception" required>
                    </div>
                    <div class="form-field">
                        <label>Date & Time <span style="color:#c9a96e;">*</span></label>
                        <input type="datetime-local" name="event_date_time" required>
                    </div>
                    <div class="form-field">
                        <label>Venue / Location <span style="color:#c9a96e;">*</span></label>
                        <input type="text" name="location_name" placeholder="Hotel or hall name" required>
                    </div>
                    <div class="form-field" style="margin-bottom:0;">
                        <label>Google Maps Link</label>
                        <input type="url" name="google_map_link" placeholder="https://maps.google.com/...">
                        <div class="hint">Paste the share link from Google Maps</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_event" class="btn-add">
                        <i class="fas fa-plus"></i> Add Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Events Grid -->
<div class="row g-3">
    <div class="col-12">
        <?php if (count($eventsList) > 0): ?>
        <div class="row g-3">
            <?php foreach ($eventsList as $event): ?>
            <div class="col-sm-6 col-xl-4">
                <div class="event-card">
                    <div class="event-card-name"><?php echo htmlspecialchars($event['event_name']); ?></div>
                    <div class="event-meta-row">
                        <i class="far fa-calendar"></i>
                        <?php echo date("l, d F Y", strtotime($event['event_date_time'])); ?>
                    </div>
                    <div class="event-meta-row">
                        <i class="far fa-clock"></i>
                        <?php echo date("h:i A", strtotime($event['event_date_time'])); ?>
                    </div>
                    <div class="event-meta-row">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($event['location_name']); ?>
                    </div>
                    <div class="event-card-actions">
                        <?php if (!empty($event['google_map_link'])): ?>
                        <a href="<?php echo htmlspecialchars($event['google_map_link']); ?>" target="_blank" class="btn-map-sm" rel="noopener">
                            <i class="fas fa-map-marked-alt"></i> View Map
                        </a>
                        <?php else: ?>
                        <span style="font-size:0.75rem; color:#d1d5db; font-style:italic;">No map link</span>
                        <?php endif; ?>
                        <a href="events.php?delete=<?php echo $event['id']; ?>"
                           class="btn-del-sm"
                           onclick="return confirm('Remove this event?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-events">
            <i class="fas fa-calendar-alt"></i>
            <p>No events added yet.<br>Click "Add Event" above to add your first — Poruwa, Reception, Church, Homecoming.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'layouts/footer.php'; ?>