<?php
session_start();
$db = require_once 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$currentPage = 'clinics';

// Handle delete request
if (isset($_POST['delete_clinic'])) {
    try {
        $stmt = $db->prepare("DELETE FROM clinics WHERE id = ?");
        $stmt->execute([$_POST['delete_clinic']]);
        $_SESSION['success_message'] = "Clinic deleted successfully.";
        header("Location: clinics.php");
        exit();
    } catch(PDOException $e) {
        error_log("Delete clinic error: " . $e->getMessage());
        $_SESSION['error_message'] = "Error deleting clinic.";
    }
}

// Fetch clinics with GP count
try {
    $stmt = $db->prepare("
        SELECT c.*, 
        (SELECT COUNT(*) FROM users WHERE clinic_id = c.id AND role = 'gp') as gp_count
        FROM clinics c
        ORDER BY c.name
    ");
    $stmt->execute();
    $clinics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Clinics query error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching clinics.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinics - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Clinics</h2>
            <a href="add_clinic.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Clinic
            </a>
        </div>

        <?php include 'includes/messages.php'; ?>

        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>GP Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clinics as $clinic): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($clinic['id']); ?></td>
                                    <td><?php echo htmlspecialchars($clinic['name']); ?></td>
                                    <td><?php echo htmlspecialchars($clinic['address']); ?></td>
                                    <td><?php echo htmlspecialchars($clinic['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($clinic['email']); ?></td>
                                    <td><?php echo $clinic['gp_count']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $clinic['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $clinic['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_clinic.php?id=<?php echo $clinic['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this clinic?');">
                                            <input type="hidden" name="delete_clinic" value="<?php echo $clinic['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($clinics)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No clinics found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>