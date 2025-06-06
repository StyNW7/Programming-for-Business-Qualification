<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Delete remember me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: ../pages/login.php');
exit();
?>