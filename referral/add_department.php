<?php
// Start session for user authentication
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'config/db.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    // Basic validation
    if (empty($name)) {
        $_SESSION['error'] = "Department name is required";
        header("Location: departments.php");
        exit();
    }

    try {
        // Prepare the SQL statement
        $stmt = $db->prepare("INSERT INTO departments (name, description, status) VALUES (:name, :description, :status)");
        
        // Execute the statement with the form data
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':status' => $status
        ]);

        // Set success message
        $_SESSION['success'] = "Department added successfully";
        
    } catch(PDOException $e) {
        // Log the error and set error message
        error_log("Error adding department: " . $e->getMessage());
        $_SESSION['error'] = "Failed to add department";
    }

    // Redirect back to departments page
    header("Location: departments.php");
    exit();
} else {
    // If someone tries to access this file directly without POST data
    header("Location: departments.php");
    exit();
}