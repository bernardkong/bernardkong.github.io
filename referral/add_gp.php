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

// Fetch clinics for dropdown
try {
    $stmt = $db->prepare("SELECT id, name FROM clinics ORDER BY name");
    $stmt->execute();
    $clinics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Clinics query error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching clinics.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['name', 'phone', 'clinic_id', 'certification', 'mmc_no', 'login', 'password'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            throw new Exception("Please fill in all required fields: " . implode(", ", $missing_fields));
        }

        // Generate random password
        $password = bin2hex(random_bytes(8));

        // Insert new GP
        $stmt = $db->prepare("INSERT INTO gp_doctors (gp_name, login, password, phone, certification, mmc_no, is_active, clinic_id) 
                             VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['login'],
            $_POST['password'],
            $_POST['phone'],
            $_POST['certification'],
            $_POST['mmc_no'],
            $_POST['clinic_id']
        ]);

        // Store temporary password to show in success message
        $_SESSION['temp_password'] = $password;
        $_SESSION['success_message'] = "GP doctor added successfully.";
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
    <title>Add GP Doctor - Hospital Admin</title>
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
                        <h3 class="card-title">Add New GP Doctor</h3>
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
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="certification" class="form-label">Certification *</label>
                                <input type="text" class="form-control" id="certification" name="certification" required>
                            </div>

                            <div class="mb-3">
                                <label for="mmc_no" class="form-label">MMC Number *</label>
                                <input type="text" class="form-control" id="mmc_no" name="mmc_no" required>
                            </div>

                            <div class="mb-3">
                                <label for="login" class="form-label">Login Username *</label>
                                <input type="text" class="form-control" id="login" name="login" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>

                            <div class="mb-3">
                                <label for="clinic_id" class="form-label">Clinic *</label>
                                <select class="form-select" id="clinic_id" name="clinic_id" required>
                                    <option value="">Select Clinic</option>
                                    <?php foreach ($clinics as $clinic): ?>
                                        <option value="<?php echo $clinic['id']; ?>">
                                            <?php echo htmlspecialchars($clinic['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="gp_doctors.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add GP Doctor</button>
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