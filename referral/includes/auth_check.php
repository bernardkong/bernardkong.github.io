<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: login.php');
    exit();
}

// Optionally, you can also check for specific roles
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    // User doesn't have the required role
    header('Location: login.php');
    exit();
}
?>