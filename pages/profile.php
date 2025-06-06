<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user statistics
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks
    FROM tasks WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

include '../includes/header.php';
?>

<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert success"><?= $_SESSION['success_message'] ?></div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert error"><?= $_SESSION['error_message'] ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['profile_errors'])): ?>
    <div class="alert error">
        <ul>
            <?php foreach ($_SESSION['profile_errors'] as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['profile_errors']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['password_errors'])): ?>
    <div class="alert error">
        <ul>
            <?php foreach ($_SESSION['password_errors'] as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['password_errors']); ?>
<?php endif; ?>

<div class="container">
    <div class="profile-container">
        <div class="profile-header">
            <h2>User Profile</h2>
        </div>
        
        <div class="profile-content">

            <div class="profile-stats">
                <h3>Task Statistics</h3>
                <br>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4><?= $stats['total_tasks'] ?></h4>
                        <span>Total Tasks</span>
                    </div>
                    <div class="stat-card completed">
                        <h4><?= $stats['completed_tasks'] ?></h4>
                        <span>Completed</span>
                    </div>
                    <div class="stat-card pending">
                        <h4><?= $stats['pending_tasks'] ?></h4>
                        <span>Pending</span>
                    </div>
                    <div class="stat-card progress">
                        <h4><?= $stats['in_progress_tasks'] ?></h4>
                        <span>In Progress</span>
                    </div>
                </div>
            </div>

            <div class="profile-info">
                <h3>Profile Information</h3>
                <form id="profileForm" action="../actions/profile_action.php" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        <span class="error-message" id="usernameError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        <span class="error-message" id="emailError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        <span class="error-message" id="fullNameError"></span>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <br>
            
            <div class="change-password">
                <h3>Change Password</h3>
                <form id="passwordForm" action="../actions/profile_action.php" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required>
                        <span class="error-message" id="currentPasswordError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <span class="error-message" id="newPasswordError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

// Profile form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    if (!validateProfileForm()) {
        e.preventDefault();
    }
});

// Password form validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    if (!validatePasswordForm()) {
        e.preventDefault();
    }
});

function validateProfileForm() {

    let isValid = true;
    
    // Clear previous errors
    clearProfileErrors();
    
    // Validate username

    const username = document.getElementById('username').value.trim();
    if (username === '') {
        showError('usernameError', 'Username is required');
        isValid = false;
    } else if (username.length < 3) {
        showError('usernameError', 'Username must be at least 3 characters');
        isValid = false;
    } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        showError('usernameError', 'Username can only contain letters, numbers, and underscores');
        isValid = false;
    }
    
    // Validate email
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email === '') {
        showError('emailError', 'Email is required');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('emailError', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate full name (optional)
    const fullName = document.getElementById('full_name').value.trim();
    if (fullName.length > 100) {
        showError('fullNameError', 'Full name must not exceed 100 characters');
        isValid = false;
    }
    
    return isValid;

}

function validatePasswordForm() {
    
    let isValid = true;
    
    // Clear previous errors
    clearPasswordErrors();
    
    // Validate current password
    const currentPassword = document.getElementById('current_password').value;
    if (currentPassword === '') {
        showError('currentPasswordError', 'Current password is required');
        isValid = false;
    }
    
    // Validate new password
    const newPassword = document.getElementById('new_password').value;
    if (newPassword === '') {
        showError('newPasswordError', 'New password is required');
        isValid = false;
    } else if (newPassword.length < 6) {
        showError('newPasswordError', 'Password must be at least 6 characters');
        isValid = false;
    }
    
    // Validate confirm password
    const confirmPassword = document.getElementById('confirm_password').value;
    if (confirmPassword === '') {
        showError('confirmPasswordError', 'Please confirm your password');
        isValid = false;
    } else if (newPassword !== confirmPassword) {
        showError('confirmPasswordError', 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
}

function showError(elementId, message) {
    document.getElementById(elementId).textContent = message;
}

function clearProfileErrors() {
    const errorElements = ['usernameError', 'emailError', 'fullNameError'];
    errorElements.forEach(id => {
        document.getElementById(id).textContent = '';
    });
}

function clearPasswordErrors() {
    const errorElements = ['currentPasswordError', 'newPasswordError', 'confirmPasswordError'];
    errorElements.forEach(id => {
        document.getElementById(id).textContent = '';
    });
}
</script>

<?php include '../includes/footer.php'; ?>