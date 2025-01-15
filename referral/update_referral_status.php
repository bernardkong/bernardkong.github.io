<?php
// update_referral_status.php

// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';
if (!$db) {
    die("Database connection failed");
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get the referral ID and new status from the POST request
$referral_id = $_POST['referral_id'];
$new_status = $_POST['status'];

// Update the referral status in the database
try {
    $stmt = $db->prepare("UPDATE referrals SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $referral_id);
    $stmt->execute();

    // Redirect back to the dashboard with a success message
    header("Location: index.php?status_update=success");
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Redirect back with an error message
    header("Location: index.php?status_update=error");
}
?>