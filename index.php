<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('pages/home.php');
} else {
    redirect('pages/login.php');
}
?>