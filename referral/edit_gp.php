<?php
session_start();

// Get database connection
$db = require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Set current page for navbar
$currentPage = 'gp_doctors';

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No GP ID provided.";
    header("Location: gp_doctors.php");
    exit();
}

$gp_id = $_GET['id'];

// Fetch GP details
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'gp'");
    $stmt->execute([$gp_id]);
    $gp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gp) {
        $_SESSION['error_message'] = "GP not found.";
        header("Location: gp_doctors.php");
        exit();
    }
} catch(PDOException $e) {
    error_log("Error fetching GP: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching GP details.";
    header("Location: gp_doctors.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['name', 'email', 'practice_name', 'phone'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            throw new Exception("Please fill in all required fields: " . implode(", ", $missing_fields));
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email exists for other users
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND role = 'gp'");
        $stmt->execute([$_POST['email'], $gp_id]);
        if ($stmt->fetch()) {
            throw new Exception("Email already exists");
        }

        // Update GP
        $stmt = $db->prepare("UPDATE users SET 
                             name = ?,
                             email = ?,
                             practice_name = ?,
                             phone = ?,
                             is_active = ?
                             WHERE id = ? AND role = 'gp'");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['practice_name'],
            $_POST['phone'],
            isset($_POST['is_active']) ? 1 : 0,
            $gp_id
        ]);

        $_SESSION['success_message'] = "GP doctor updated successfully.";
        header("Location: gp_doctors.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit GP Doctor - Hospital Admin</title>
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
                        <h3 class="card-title">Edit GP Doctor</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($gp['name']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($gp['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="practice_name" class="form-label">Practice Name *</label>
                                <input type="text" class="form-control" id="practice_name" name="practice_name" 
                                       value="<?php echo htmlspecialchars($gp['practice_name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($gp['phone']); ?>" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                       <?php echo $gp['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="gp_doctors.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update GP Doctor</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>