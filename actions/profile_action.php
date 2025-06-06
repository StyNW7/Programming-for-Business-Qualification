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
    header('Location: ../pages/profile.php');
    exit();
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'update_profile':
            updateProfile($pdo, $user_id);
            break;
        case 'change_password':
            changePassword($pdo, $user_id);
            break;
        default:
            header('Location: ../pages/profile.php');
            exit();
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'An error occurred. Please try again.';
    header('Location: ../pages/profile.php');
    exit();
}

// ------------------ FUNCTION: Update Profile ------------------ //
function updateProfile($pdo, $user_id) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');

    $errors = [];

    if (empty($username) || strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Invalid username.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (!empty($full_name) && strlen($full_name) > 100) {
        $errors[] = 'Full name too long.';
    }

    // Cek username/email yang sama
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = 'Username or email already in use.';
    }

    if (!empty($errors)) {
        $_SESSION['profile_errors'] = $errors;
        header('Location: ../pages/profile.php');
        exit();
    }

    // Update
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ? WHERE id = ?");
    $stmt->execute([$username, $email, $full_name, $user_id]);

    $_SESSION['username'] = $username;
    $_SESSION['success_message'] = 'Profile updated successfully.';
    header('Location: ../pages/profile.php');
    exit();
}

// ------------------ FUNCTION: Change Password ------------------ //
function changePassword($pdo, $user_id) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = 'All password fields are required.';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match.';
    }

    if (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }

    // Fetch current user password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password'])) {
        $errors[] = 'Current password is incorrect.';
    }

    if (!empty($errors)) {
        $_SESSION['password_errors'] = $errors;
        header('Location: ../pages/profile.php');
        exit();
    }

    // Update password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);

    $_SESSION['success_message'] = 'Password changed successfully.';
    header('Location: ../pages/profile.php');
    exit();
}
