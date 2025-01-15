<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = require 'config/db.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM specialists WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        header("Location: specialists.php");
        exit();
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("An error occurred while deleting the specialist.");
    }
} else {
    header("Location: specialists.php");
    exit();
}
?>