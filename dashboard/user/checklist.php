<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['wedding_id'])) {
    header("Location: ../login.php");
    exit();
}

$wedding_id = $_SESSION['wedding_id'];

// Add task
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $task_name = trim($_POST['task_name']);
    if (!empty($task_name)) {
        $pdo->prepare("INSERT INTO tasks (wedding_id, task_name) VALUES (?, ?)")
            ->execute([$wedding_id, $task_name]);
    }
    header("Location: checklist.php");
    exit();
}

// Toggle complete
if (isset($_GET['toggle'])) {
    $task_id = intval($_GET['toggle']);
    $stmtStatus = $pdo->prepare("SELECT is_completed FROM tasks WHERE id = ? AND wedding_id = ?");
    $stmtStatus->execute([$task_id, $wedding_id]);
    $task = $stmtStatus->fetch();
    if ($task) {
        $new_status = $task['is_completed'] == 1 ? 0 : 1;
        $pdo->prepare("UPDATE tasks SET is_completed = ? WHERE id = ? AND wedding_id = ?")
            ->execute([$new_status, $task_id, $wedding_id]);
    }
    header("Location: checklist.php");
    exit();
}

// Delete task
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM tasks WHERE id = ? AND wedding_id = ?")
        ->execute([$delete_id, $wedding_id]);
    header("Location: checklist.php");
    exit();
}

// Stats
$stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE wedding_id = ?");
$stmtTotal->execute([$wedding_id]);
$total_tasks = $stmtTotal->fetch()['total'];

$stmtComp = $pdo->prepare("SELECT COUNT(*) as completed FROM tasks WHERE wedding_id = ? AND is_completed = 1");
$stmtComp->execute([$wedding_id]);
$completed_tasks = $stmtComp->fetch()['completed'];

$progress_pct = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;

$stmtTasks = $pdo->prepare("SELECT * FROM tasks WHERE wedding_id = ? ORDER BY is_completed ASC, id DESC");
$stmtTasks->execute([$wedding_id]);
$tasksList = $stmtTasks->fetchAll();

require '../layouts/header.php';
?>

<style>
    /* Left sidebar */
    .progress-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        padding: 28px;
        text-align: center;
        margin-bottom: 16px;
    }
    .progress-pct {
        font-size: 3.5rem;
        font-weight: 800;
        color: #1a1a2e;
        line-height: 1;
    }
    .progress-pct span { font-size: 1.8rem; color: #9ea3b0; }
    .progress-label {
        font-size: 0.78rem;
        color: #9ea3b0;
        margin: 8px 0 16px;
    }
    .progress-track {
        background: #f1f5f9;
        border-radius: 50px;
        height: 10px;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .progress-fill {
        background: linear-gradient(135deg, #c9a96e, #a07840);
        height: 100%;
        border-radius: 50px;
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .progress-sub {
        font-size: 0.78rem;
        color: #9ea3b0;
    }

    .add-task-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        padding: 20px;
    }
    .add-task-card h5 {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 14px;
    }
    .add-task-form { display: flex; gap: 8px; }
    .add-task-input {
        flex: 1;
        border: 1px solid #e8ecf0;
        border-radius: 10px;
        padding: 10px 14px;
        font-family: 'Inter', sans-serif;
        font-size: 0.86rem;
        color: #1a1a2e;
        outline: none;
        transition: border-color 0.2s;
    }
    .add-task-input:focus { border-color: #c9a96e; }
    .btn-add-task {
        background: linear-gradient(135deg, #c9a96e, #a07840);
        color: #0f0f1a;
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-add-task:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(201,169,110,0.3); }

    /* Quick add suggestions */
    .suggestions {
        margin-top: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .suggestion-chip {
        font-size: 0.72rem;
        color: #9ea3b0;
        border: 1px solid #e8ecf0;
        border-radius: 20px;
        padding: 4px 10px;
        cursor: pointer;
        transition: all 0.2s;
        background: none;
        font-family: 'Inter', sans-serif;
    }
    .suggestion-chip:hover { border-color: #c9a96e; color: #c9a96e; background: rgba(201,169,110,0.05); }

    /* Task list */
    .tasks-card {
        background: white;
        border: 1px solid #e8ecf0;
        border-radius: 16px;
        overflow: hidden;
    }
    .tasks-card-header {
        padding: 20px 24px 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .tasks-card-header h5 {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }
    .filter-tabs {
        display: flex;
        gap: 4px;
    }
    .filter-tab {
        font-size: 0.73rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        border: none;
        background: transparent;
        color: #9ea3b0;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .filter-tab.active { background: #f1f5f9; color: #1a1a2e; }

    .task-list { list-style: none; padding: 0; margin: 0; }
    .task-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 24px;
        border-bottom: 1px solid #f8fafc;
        transition: background 0.15s;
        cursor: default;
    }
    .task-item:last-child { border-bottom: none; }
    .task-item:hover { background: #fafbfc; }

    .task-toggle {
        flex-shrink: 0;
        width: 22px; height: 22px;
        border-radius: 50%;
        border: 2px solid #d1d5db;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.25s;
        font-size: 0.7rem;
    }
    .task-toggle:hover { border-color: #c9a96e; background: rgba(201,169,110,0.08); }
    .task-toggle.done {
        background: linear-gradient(135deg, #c9a96e, #a07840);
        border-color: #c9a96e;
        color: white;
    }

    .task-name {
        flex: 1;
        font-size: 0.9rem;
        color: #1a1a2e;
        transition: all 0.2s;
    }
    .task-name.done {
        text-decoration: line-through;
        color: #9ea3b0;
    }

    .task-del {
        opacity: 0;
        background: none;
        border: none;
        color: #d1d5db;
        cursor: pointer;
        font-size: 0.78rem;
        transition: all 0.2s;
        padding: 4px 6px;
        border-radius: 6px;
        text-decoration: none;
    }
    .task-item:hover .task-del { opacity: 1; }
    .task-del:hover { color: #ef4444; background: #fee2e2; }

    .empty-tasks {
        text-align: center;
        padding: 50px 20px;
        color: #9ea3b0;
    }
    .empty-tasks i { font-size: 2rem; opacity: 0.25; display: block; margin-bottom: 12px; }
</style>

<div class="row g-3">
    <!-- Left: Progress + Add -->
    <div class="col-lg-4">
        <div class="progress-card">
            <div class="progress-pct"><?php echo $progress_pct; ?><span>%</span></div>
            <p class="progress-label">Wedding planning complete</p>
            <div class="progress-track">
                <div class="progress-fill" style="width: <?php echo $progress_pct; ?>%;"></div>
            </div>
            <p class="progress-sub"><?php echo $completed_tasks; ?> of <?php echo $total_tasks; ?> tasks done</p>
        </div>

        <div class="add-task-card">
            <h5>Add a Task</h5>
            <form method="POST" action="checklist.php" class="add-task-form">
                <input type="text" name="task_name" class="add-task-input"
                    id="task-input" placeholder="What needs to be done?" required>
                <button type="submit" name="add_task" class="btn-add-task">
                    <i class="fas fa-plus"></i>
                </button>
            </form>
            <div class="suggestions">
                <?php
                $suggestions = ['Book photographer', 'Book caterer', 'Saree fitting', 'Send invitations', 'Book florist', 'Thank-you cards', 'Confirm venue', 'Book DJ / Band', 'Order cake', 'Hair & makeup trial'];
                foreach ($suggestions as $s):
                ?>
                <button class="suggestion-chip" onclick="document.getElementById('task-input').value='<?php echo $s; ?>'">
                    <?php echo $s; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right: Task List -->
    <div class="col-lg-8">
        <div class="tasks-card">
            <div class="tasks-card-header">
                <h5>Your Tasks (<?php echo $total_tasks; ?>)</h5>
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterTasks('all', this)">All</button>
                    <button class="filter-tab" onclick="filterTasks('pending', this)">Pending</button>
                    <button class="filter-tab" onclick="filterTasks('done', this)">Done</button>
                </div>
            </div>

            <?php if (count($tasksList) > 0): ?>
            <ul class="task-list" id="task-list">
                <?php foreach ($tasksList as $task): ?>
                <li class="task-item" data-status="<?php echo $task['is_completed'] ? 'done' : 'pending'; ?>">
                    <a href="checklist.php?toggle=<?php echo $task['id']; ?>"
                       class="task-toggle <?php echo $task['is_completed'] ? 'done' : ''; ?>">
                        <?php if ($task['is_completed']): ?><i class="fas fa-check"></i><?php endif; ?>
                    </a>
                    <span class="task-name <?php echo $task['is_completed'] ? 'done' : ''; ?>">
                        <?php echo htmlspecialchars($task['task_name']); ?>
                    </span>
                    <a href="checklist.php?delete=<?php echo $task['id']; ?>"
                       class="task-del"
                       onclick="return confirm('Remove this task?');">
                        <i class="fas fa-times"></i>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="empty-tasks">
                <i class="fas fa-tasks"></i>
                <p>No tasks yet. Add your first task to start tracking your wedding planning!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function filterTasks(filter, btn) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#task-list .task-item').forEach(item => {
        const s = item.dataset.status;
        item.style.display = (filter === 'all' || s === filter) ? '' : 'none';
    });
}
</script>

<?php require '../layouts/footer.php'; ?>
