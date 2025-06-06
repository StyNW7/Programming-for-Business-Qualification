<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function redirect($page) {
    header("Location: $page");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

function showAlert($type, $message) {
    return "<div class='alert alert-$type'>$message</div>";
}

function getTaskStats($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN due_date < CURDATE() AND status = 'pending' THEN 1 ELSE 0 END) as overdue
        FROM tasks WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function getPriorityClass($priority) {
    switch($priority) {
        case 'high': return 'priority-high';
        case 'medium': return 'priority-medium';
        case 'low': return 'priority-low';
        default: return 'priority-medium';
    }
}

function getStatusClass($status) {
    return $status === 'completed' ? 'status-completed' : 'status-pending';
}
?>