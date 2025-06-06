<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include '../includes/header.php';
?>

<div class="container">
    <div class="task-form-container">
        <h2>Add New Task</h2>
        <form id="addTaskForm" action="../actions/task_actions.php" method="POST">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label for="title">Task Title *</label>
                <input type="text" id="title" name="title" required>
                <span class="error-message" id="titleError"></span>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
                <span class="error-message" id="descriptionError"></span>
            </div>
            
            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" id="due_date" name="due_date">
                <span class="error-message" id="dueDateError"></span>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Task</button>
                <a href="home.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
document.getElementById('addTaskForm').addEventListener('submit', function(e) {
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