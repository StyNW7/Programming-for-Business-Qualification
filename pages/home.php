<?php
$page_title = 'Dashboard';
require_once '../includes/header.php';
requireLogin();

// Get user stats
$stats = getTaskStats($pdo, $_SESSION['user_id']);

// Get tasks with filtering
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($filter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $filter;
}

if ($search) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Handle success/error messages
$success = '';
$error = '';

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<div class="dashboard-header">
    <h1 class="welcome-text">Welcome back, <?php echo sanitize($_SESSION['username']); ?>! 👋</h1>
    <p style="color: var(--text-secondary); font-size: 1.1rem;">Here's what's happening with your tasks today.</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo sanitize($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['total']; ?></div>
        <div class="stat-label">Total Tasks</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['completed']; ?></div>
        <div class="stat-label">Completed</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['pending']; ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo $stats['overdue']; ?></div>
        <div class="stat-label">Overdue</div>
    </div>
</div>

<!-- Task Management Section -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Your Tasks</h2>
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <input type="text" id="search" placeholder="Search tasks..." class="form-input" 
                   style="max-width: 250px;" value="<?php echo sanitize($search); ?>">
            <a href="add_task.php" class="btn btn-primary">+ Add New Task</a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <div class="task-filters">
            <button class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                    onclick="filterTasks('all')">All Tasks</button>
            <button class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>" 
                    onclick="filterTasks('pending')">Pending</button>
            <button class="filter-btn <?php echo $filter === 'completed' ? 'active' : ''; ?>" 
                    onclick="filterTasks('completed')">Completed</button>
        </div>
        
        <!-- Tasks List -->
        <?php if (empty($tasks)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                <h3>No tasks found</h3>
                <p>Start by creating your first task!</p>
                <a href="add_task.php" class="btn btn-primary" style="margin-top: 1rem;">Create Task</a>
            </div>
        <?php else: ?>
            <div class="tasks-grid">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card" data-status="<?php echo $task['status']; ?>">
                        <div class="task-header">
                            <div style="flex-grow: 1;">
                                <h3 class="task-title"><?php echo sanitize($task['title']); ?></h3>
                                <div class="task-meta">
                                    <span class="task-priority <?php echo getPriorityClass($task['priority']); ?>">
                                        <?php echo ucfirst($task['priority']); ?> Priority
                                    </span>
                                    <span class="task-status <?php echo getStatusClass($task['status']); ?>">
                                        <?php echo ucfirst($task['status']); ?>
                                    </span>
                                    <?php if ($task['due_date']): ?>
                                        <span class="task-due" style="background: rgba(66, 153, 225, 0.1); color: var(--info-color);">
                                            Due: <?php echo formatDate($task['due_date']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="task-actions">
                                <button onclick="toggleTaskStatus(<?php echo $task['id']; ?>, '<?php echo $task['status']; ?>')" 
                                        class="btn btn-sm <?php echo $task['status'] === 'completed' ? 'btn-warning' : 'btn-success'; ?>" 
                                        title="<?php echo $task['status'] === 'completed' ? 'Mark as Pending' : 'Mark as Completed'; ?>">
                                    <?php echo $task['status'] === 'completed' ? '↶' : '✓'; ?>
                                </button>
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-secondary" title="Edit Task">✏️</a>
                                <form method="POST" action="../actions/task_actions.php" style="display: inline;" 
                                      onsubmit="return confirmDelete(<?php echo $task['id']; ?>, '<?php echo sanitize($task['title']); ?>')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete Task">🗑️</button>
                                </form>
                            </div>
                        </div>
                        
                        <?php if ($task['description']): ?>
                            <div class="task-description">
                                <?php echo nl2br(sanitize($task['description'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 1rem;">
                            Created: <?php echo formatDate($task['created_at']); ?>
                            <?php if ($task['updated_at'] !== $task['created_at']): ?>
                                • Updated: <?php echo formatDate($task['updated_at']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Add search functionality with real-time filtering
document.getElementById('search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const title = task.querySelector('.task-title').textContent.toLowerCase();
        const description = task.querySelector('.task-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>