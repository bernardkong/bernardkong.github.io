<?php
session_start();
$db = require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Add this after the database connection
try {
    // Fetch all clinics
    $stmt = $db->prepare("SELECT id, name FROM clinics WHERE is_active = 1");
    $stmt->execute();
    $clinics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error loading clinics: " . $e->getMessage();
    $clinics = [];
}

$currentPage = 'clinics';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['name', 'email', 'phone'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst($field) . " is required.";
            }
        }

        // Validate email format
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if (empty($errors)) {
            $stmt = $db->prepare("
                INSERT INTO clinics (name, address, phone, email, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $stmt->execute([
                $_POST['name'],
                $_POST['address'],
                $_POST['phone'],
                $_POST['email'],
                $is_active
            ]);

            $_SESSION['success_message'] = "Clinic added successfully.";
            header("Location: clinics.php");
            exit();
        } else {
            $_SESSION['error_message'] = implode("<br>", $errors);
        }
    } catch(PDOException $e) {
        error_log("Add clinic error: " . $e->getMessage());
        $_SESSION['error_message'] = "Error adding clinic.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Clinic - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Clinic</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Clinic Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $_POST['name'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <textarea class="form-control" id="address" name="address" 
                                          rows="3" required><?php echo $_POST['address'] ?? ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo $_POST['phone'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $_POST['email'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" 
                                       name="is_active" <?php echo (isset($_POST['is_active'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="clinics.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Clinic</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>