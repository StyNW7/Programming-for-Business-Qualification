<?php
session_start();
require_once '../config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/register.php');
    exit();
}

// Get form data
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$full_name = trim($_POST['full_name'] ?? '');

// Validate input
$errors = [];

// Username validation
if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores';
}

// Email validation
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

// Password validation
if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

// Confirm password validation
if (empty($confirm_password)) {
    $errors[] = 'Please confirm your password';
} elseif ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// Full name validation (optional)
if (!empty($full_name) && strlen($full_name) > 100) {
    $errors[] = 'Full name must not exceed 100 characters';
}

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['old_data'] = [
        'username' => $username,
        'email' => $email,
        'full_name' => $full_name
    ];
    header('Location: ../pages/register.php');
    exit();
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['register_errors'] = ['Username already exists'];
        $_SESSION['old_data'] = [
            'username' => $username,
            'email' => $email,
            'full_name' => $full_name
        ];
        header('Location: ../pages/register.php');
        exit();
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['register_errors'] = ['Email already exists'];
        $_SESSION['old_data'] = [
            'username' => $username,
            'email' => $email,
            'full_name' => $full_name
        ];
        header('Location: ../pages/register.php');
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $email, $hashed_password, $full_name]);
    
    // Get the new user ID
    $user_id = $pdo->lastInsertId();
    
    // Auto login after registration
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    
    // Clear any existing errors
    unset($_SESSION['register_errors']);
    unset($_SESSION['old_data']);
    
    // Set success message
    $_SESSION['success_message'] = 'Registration successful! Welcome to Task Manager.';
    
    // Redirect to home page
    header('Location: ../pages/home.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['register_errors'] = ['An error occurred during registration. Please try again.'];
    $_SESSION['old_data'] = [
        'username' => $username,
        'email' => $email,
        'full_name' => $full_name
    ];
    header('Location: ../pages/register.php');
    exit();
}
?>