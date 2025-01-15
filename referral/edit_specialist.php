<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = require 'config/db.php';

// Fetch departments for dropdown
try {
    $stmt = $conn->query("SELECT id, name FROM departments ORDER BY name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching departments.";
}

// Fetch specialist details
if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM specialists WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $specialist = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$specialist) {
            header("Location: specialists.php");
            exit();
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error_message = "An error occurred while fetching specialist details.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Handle file upload
        $picture_path = $specialist['picture']; // Keep existing picture by default
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/specialists/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '.' . $file_extension;
            $picture_path = $upload_dir . $unique_filename;
            
            // Delete old picture if it exists
            if ($specialist['picture'] && file_exists($specialist['picture'])) {
                unlink($specialist['picture']);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['picture']['tmp_name'], $picture_path)) {
                throw new Exception("Failed to upload file.");
            }
        }

        $stmt = $conn->prepare("
            UPDATE specialists 
            SET nsr_number = :nsr_number,
                name = :name,
                department_id = :department_id,
                specialization = :specialization,
                email = :email,
                phone = :phone,
                status = :status,
                profile = :profile,
                picture = :picture
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':nsr_number' => $_POST['nsr_number'],
            ':name' => $_POST['name'],
            ':department_id' => $_POST['department_id'],
            ':specialization' => $_POST['specialization'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':status' => $_POST['status'],
            ':profile' => $_POST['profile'],
            ':picture' => $picture_path,
            ':id' => $_GET['id']
        ]);

        header("Location: specialists.php");
        exit();
    } catch(Exception $e) {
        error_log("Error: " . $e->getMessage());
        $error_message = "An error occurred while updating the specialist.";
        // Clean up newly uploaded file if database update failed
        if (isset($picture_path) && $picture_path !== $specialist['picture'] && file_exists($picture_path)) {
            unlink($picture_path);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Specialist - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Specialist</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo htmlspecialchars($specialist['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="department_id" class="form-label">Department</label>
                <select class="form-control" id="department_id" name="department_id" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo $department['id']; ?>"
                                <?php echo ($specialist['department_id'] == $department['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="specialization" class="form-label">Specialization</label>
                <input type="text" class="form-control" id="specialization" name="specialization"
                       value="<?php echo htmlspecialchars($specialist['specialization']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?php echo htmlspecialchars($specialist['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone"
                       value="<?php echo htmlspecialchars($specialist['phone']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="active" <?php echo ($specialist['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($specialist['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="nsr_number" class="form-label">NSR Number</label>
                <input type="text" class="form-control" id="nsr_number" name="nsr_number" 
                       value="<?php echo htmlspecialchars($specialist['nsr_number']); ?>"
                       pattern="NSR/\d+" title="Format: NSR/followed by numbers" required>
                <small class="form-text text-muted">Format: NSR/12345</small>
            </div>

            <div class="mb-3">
                <label for="profile" class="form-label">Profile/Description</label>
                <textarea class="form-control" id="profile" name="profile" rows="6"><?php echo htmlspecialchars($specialist['profile']); ?></textarea>
                <small class="form-text text-muted">Add detailed information about the specialist's background, expertise, and achievements.</small>
            </div>

            <div class="mb-3">
                <label for="picture" class="form-label">Profile Picture</label>
                <?php if ($specialist['picture'] && file_exists($specialist['picture'])): ?>
                    <div class="mb-2">
                        <img src="<?php echo htmlspecialchars($specialist['picture']); ?>" 
                             alt="Current profile picture" 
                             class="img-thumbnail" 
                             style="max-width: 200px;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                <small class="form-text text-muted">Upload a new photo to replace the current one (JPEG, PNG formats recommended)</small>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Update Specialist</button>
                <a href="specialists.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        ClassicEditor
            .create(document.querySelector('#profile'))
            .catch(error => {
                console.error(error);
            });
    </script>
</body>
</html>