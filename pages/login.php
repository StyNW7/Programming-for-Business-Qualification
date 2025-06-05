<?php
$page_title = 'Login';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('home.php');
}

$error = '';
$username = isset($_COOKIE['remember_username']) ? $_COOKIE['remember_username'] : '';

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Task Manager</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your account</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo sanitize($success); ?></div>
                <?php endif; ?>
                
                <form id="loginForm" method="POST" action="../actions/login_action.php" onsubmit="return validateLoginForm()">
                    <div class="form-group">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" id="username" name="username" class="form-input" 
                               value="<?php echo sanitize($username); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="remember" class="form-checkbox" <?php echo $username ? 'checked' : ''; ?>>
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                        Sign In
                    </button>
                    
                    <div style="text-align: center;">
                        <p>Don't have an account? <a href="register.php" style="color: var(--primary-color); text-decoration: none;">Sign up here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../js/validation.js"></script>
</body>
</html>