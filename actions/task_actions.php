<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/home.php');
    exit();
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'create':
            createTask($pdo, $user_id);
            break;
        case 'update':
            updateTask($pdo, $user_id);
            break;
        case 'delete':
            deleteTask($pdo, $user_id);
            break;
        case 'toggle_status':
            toggleTaskStatus($pdo, $user_id);
            break;
        default:
            header('Location: ../pages/home.php');
            exit();
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'An error occurred. Please try again.';
    header('Location: ../pages/home.php');
    exit();
}

function createTask($pdo, $user_id) {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    
    // Validate input
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Task title is required';
    } elseif (strlen($title) < 3) {
        $errors[] = 'Task title must be at least 3 characters';
    } elseif (strlen($title) > 100) {
        $errors[] = 'Task title must not exceed 100 characters';
    }
    
    if (!empty($description) && strlen($description) > 500) {
        $errors[] = 'Description must not exceed 500 characters';
    }
    
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        $priority = 'medium';
    }
    
    if (!empty($due_date)) {
        $today = date('Y-m-d');
        if ($due_date < $today) {
            $errors[] = 'Due date cannot be in the past';
        }
    } else {
        $due_date = null;
    }
    
    if (!empty($errors)) {
        $_SESSION['task_errors'] = $errors;
        header('Location: ../pages/add_task.php');
        exit();
    }
    
    // Insert task
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, priority, due_date, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$user_id, $title, $description, $priority, $due_date]);
    
    $_SESSION['success_message'] = 'Task created successfully!';
    header('Location: ../pages/home.php');
    exit();
}

function updateTask($pdo, $user_id) {
    // Get form data
    $task_id = (int)($_POST['task_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    $status = $_POST['status'] ?? 'pending';
    
    // Validate input
    $errors = [];
    
    if ($task_id <= 0) {
        $_SESSION['error_message'] = 'Invalid task ID';
        header('Location: ../pages/home.php');
        exit();
    }
    
    if (empty($title)) {
        $errors[] = 'Task title is required';
    } elseif (strlen($title) < 3) {
        $errors[] = 'Task title must be at least 3 characters';
    } elseif (strlen($title) > 100) {
        $errors[] = 'Task title must not exceed 100 characters';
    }
    
    if (!empty($description) && strlen($description) > 500) {
        $errors[] = 'Description must not exceed 500 characters';
    }
    
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        $priority = 'medium';
    }
    
    if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
        $status = 'pending';
    }
    
    if (!empty($due_date)) {
        $today = date('Y-m-d');
        if ($due_date < $today && $status !== 'completed') {
            $errors[] = 'Due date cannot be in the past';
        }
    } else {
        $due_date = null;
    }
    
    if (!empty($errors)) {
        $_SESSION['task_errors'] = $errors;
        header('Location: ../pages/edit_task.php?id=' . $task_id);
        exit();
    }
    
    // Check if task belongs to user
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    if (!$stmt->fetch()) {
        $_SESSION['error_message'] = 'Task not found';
        header('Location: ../pages/home.php');
        exit();
    }
    
    // Update task
    $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, due_date = ?, status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $description, $priority, $due_date, $status, $task_id, $user_id]);
    
    $_SESSION['success_message'] = 'Task updated successfully!';
    header('Location: ../pages/home.php');
    exit();
}

function deleteTask($pdo, $user_id) {
    $task_id = (int)($_POST['task_id'] ?? 0);
    
    if ($task_id <= 0) {
        $_SESSION['error_message'] = 'Invalid task ID';
        header('Location: ../pages/home.php');
        exit();
    }
    
    // Check if task belongs to user and delete
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Task deleted successfully!';
    } else {
        $_SESSION['error_message'] = 'Task not found';
    }
    
    header('Location: ../pages/home.php');
    exit();
}

function toggleTaskStatus($pdo, $user_id) {

    $task_id = (int)($_POST['task_id'] ?? 0);
    
    if ($task_id <= 0) {
        $_SESSION['error_message'] = 'Invalid task ID';
        header('Location: ../pages/home.php');
        exit();
    }
    
    // Get current status
    $stmt = $pdo->prepare("SELECT status FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        $_SESSION['error_message'] = 'Task not found';
        header('Location: ../pages/home.php');
        exit();
    }
    
    // Toggle status

    $new_status = "";

    if ($task['status'] === 'completed'){
        $new_status = "Completed";
    }

    else if ($task['status'] === 'pending'){
        $new_status = "Pending";
    }

    else if ($task['status'] === 'in_progress'){
        $new_status = "In Progress";
    }
    
    $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$new_status, $task_id, $user_id]);
    
    $_SESSION['success_message'] = 'Task status updated successfully!';
    header('Location: ../pages/home.php');
    exit();

}
?>