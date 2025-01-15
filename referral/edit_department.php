<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get department details
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $db->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$department) {
            $_SESSION['error'] = "Department not found.";
            header("Location: departments.php");
            exit();
        }
    } catch(PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while fetching department details.";
        header("Location: departments.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    if (empty($name)) {
        $_SESSION['error'] = "Department name is required.";
    } else {
        try {
            $stmt = $db->prepare("UPDATE departments SET name = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $status, $id]);
            
            $_SESSION['success'] = "Department updated successfully.";
            header("Location: departments.php");
            exit();
        } catch(PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while updating the department.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php 
    $currentPage = 'departments';
    include 'includes/navbar.php';
    ?>

    <div class="container mt-4">
        <h2>Edit Department</h2>
        
        <form action="edit_department.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $department['id']; ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Department Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo htmlspecialchars($department['name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" 
                          rows="3"><?php echo htmlspecialchars($department['description']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?php echo $department['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $department['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Department</button>
            <a href="departments.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>