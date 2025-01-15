<?php
// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $db->prepare("UPDATE hospital_settings SET 
            hospital_name = :hospital_name,
            hospital_address = :hospital_address,
            hospital_phone = :hospital_phone,
            hospital_email = :hospital_email
            WHERE id = 1");
        
        $stmt->execute([
            'hospital_name' => $_POST['hospital_name'],
            'hospital_address' => $_POST['hospital_address'],
            'hospital_phone' => $_POST['hospital_phone'],
            'hospital_email' => $_POST['hospital_email']
        ]);

        $success_message = "Hospital settings updated successfully!";
    } catch(PDOException $e) {
        error_log("Settings update error: " . $e->getMessage());
        $error_message = "An error occurred while updating settings.";
    }
}

// Fetch current settings
try {
    $stmt = $db->query("SELECT * FROM hospital_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    $error_message = "An error occurred while fetching settings.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php 
    $currentPage = 'settings';
    require_once 'includes/navbar.php'; 
    ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Hospital Settings</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="hospital_logo" class="form-label">Hospital Logo</label>
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <?php if (!empty($settings['hospital_logo']) && file_exists($settings['hospital_logo'])): ?>
                                            <img src="<?php echo htmlspecialchars($settings['hospital_logo']); ?>" 
                                                 alt="Current Hospital Logo" 
                                                 class="img-thumbnail" 
                                                 style="max-height: 100px;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col">
                                        <input type="file" class="form-control" id="hospital_logo" name="hospital_logo" 
                                               accept="image/jpeg,image/png,image/gif">
                                        <div class="form-text">
                                            Recommended size: 200x200px. Maximum file size: 5MB.<br>
                                            Allowed formats: JPG, PNG, GIF
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="hospital_name" class="form-label">Hospital Name</label>
                                <input type="text" class="form-control" id="hospital_name" name="hospital_name" 
                                       value="<?php echo htmlspecialchars($settings['hospital_name'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="hospital_address" class="form-label">Hospital Address</label>
                                <textarea class="form-control" id="hospital_address" name="hospital_address" 
                                          rows="3" required><?php echo htmlspecialchars($settings['hospital_address'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="hospital_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="hospital_phone" name="hospital_phone" 
                                       value="<?php echo htmlspecialchars($settings['hospital_phone'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="hospital_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="hospital_email" name="hospital_email" 
                                       value="<?php echo htmlspecialchars($settings['hospital_email'] ?? ''); ?>" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>