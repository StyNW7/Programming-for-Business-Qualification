<?php
require_once 'includes/functions.php';

// Redirect to appropriate page based on login status
if (isLoggedIn()) {
    redirect('pages/home.php');
} else {
    redirect('pages/login.php');
}
?>