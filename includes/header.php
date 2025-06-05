<?php
require_once 'functions.php';
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Task Manager</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="home.php" class="logo">📋 TaskManager</a>
                
                <ul class="nav-links">
                    <li><a href="home.php">Dashboard</a></li>
                    <li><a href="add_task.php">Add Task</a></li>
                    <li><a href="profile.php">Profile</a></li>
                </ul>
                
                <div class="user-menu">
                    <button id="theme-toggle" class="theme-toggle" title="Toggle Theme">🌙</button>
                    <span>Welcome, <?php echo sanitize($_SESSION['username']); ?></span>
                    <a href="../actions/logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </nav>
        </div>
    </header>
    <?php endif; ?>
    
    <main class="main">
        <div class="container">