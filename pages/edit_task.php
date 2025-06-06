<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get task ID from URL
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id <= 0) {
    header('Location: home.php');
    exit();
}

// Fetch task data
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: home.php');
    exit();
}

include '../includes/header.php';
?>

<div class="container">
    <div class="task-form-container">
        <h2>Edit Task</h2>
        <form id="editTaskForm" action="../actions/task_actions.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
            
            <div class="form-group">
                <label for="title">Task Title *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
                <span class="error-message" id="titleError"></span>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
                <span class="error-message" id="descriptionError"></span>
            </div>
            
            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="low" <?= $task['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $task['priority'] == 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $task['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="<?= $task['due_date'] ?>">
                <span class="error-message" id="dueDateError"></span>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <!-- <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option> -->
                    <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Task</button>
                <a href="home.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
document.getElementById('editTaskForm').addEventListener('submit', function(e) {
    if (!validateTaskForm()) {
        e.preventDefault();
    }
});

function validateTaskForm() {
    let isValid = true;
    
    // Clear previous errors
    clearErrors();
    
    // Validate title
    const title = document.getElementById('title').value.trim();
    if (title === '') {
        showError('titleError', 'Task title is required');
        isValid = false;
    } else if (title.length < 3) {
        showError('titleError', 'Task title must be at least 3 characters');
        isValid = false;
    } else if (title.length > 100) {
        showError('titleError', 'Task title must not exceed 100 characters');
        isValid = false;
    }
    
    // Validate description
    const description = document.getElementById('description').value.trim();
    if (description.length > 500) {
        showError('descriptionError', 'Description must not exceed 500 characters');
        isValid = false;
    }
    
    // Validate due date
    const dueDate = document.getElementById('due_date').value;
    if (dueDate) {
        const today = new Date().toISOString().split('T')[0];
        if (dueDate < today) {
            showError('dueDateError', 'Due date cannot be in the past');
            isValid = false;
        }
    }
    
    return isValid;
}

function showError(elementId, message) {
    document.getElementById(elementId).textContent = message;
}

function clearErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(element => {
        element.textContent = '';
    });
}
</script>

<?php include '../includes/footer.php'; ?>