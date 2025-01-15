<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // First check if there are any specialists in this department
        $stmt = $db->prepare("SELECT COUNT(*) FROM specialists WHERE department_id = ?");
        $stmt->execute([$id]);
        $specialistCount = $stmt->fetchColumn();
        
        if ($specialistCount > 0) {
            $_SESSION['error'] = "Cannot delete department: There are specialists assigned to this department.";
            header("Location: departments.php");
            exit();
        }
        
        // If no specialists, proceed with deletion
        $stmt = $db->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Department deleted successfully.";
    } catch(PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while deleting the department.";
    }
} else {
    $_SESSION['error'] = "Invalid department ID.";
}

header("Location: departments.php");
exit();