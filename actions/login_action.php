<?php
session_start();
require_once '../config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit();
}

// Get form data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// Validate input
$errors = [];

if (empty($username)) {
    $errors[] = 'Username is required';
}

if (empty($password)) {
    $errors[] = 'Password is required';
}

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['old_username'] = $username;
    header('Location: ../pages/login.php');
    exit();
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Set remember me cookie if checked
        if ($remember_me) {
            $cookie_value = base64_encode($user['id'] . ':' . $user['username']);
            setcookie('remember_me', $cookie_value, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
        }
        
        // Clear any existing errors
        unset($_SESSION['login_errors']);
        unset($_SESSION['old_username']);
        
        // Redirect to home page
        header('Location: ../pages/home.php');
        exit();
    } else {
        // Login failed
        $_SESSION['login_errors'] = ['Invalid username or password'];
        $_SESSION['old_username'] = $username;
        header('Location: ../pages/login.php');
        exit();
    }
    
} catch (Exception $e) {
    $_SESSION['login_errors'] = ['An error occurred. Please try again.'];
    $_SESSION['old_username'] = $username;
    header('Location: ../pages/login.php');
    exit();
}
?>