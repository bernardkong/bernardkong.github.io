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

// Set current page for navbar
$currentPage = 'gp_doctors';

// Handle delete request
if (isset($_POST['delete_gp'])) {
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'gp'");
        $stmt->execute([$_POST['delete_gp']]);
        $_SESSION['success_message'] = "GP doctor deleted successfully.";
        header("Location: gp_doctors.php");
        exit();
    } catch(PDOException $e) {
        error_log("Delete GP error: " . $e->getMessage());
        $_SESSION['error_message'] = "Error deleting GP doctor.";
    }
}

// Handle search request
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
}

// Fetch GP doctors with search functionality
$gp_doctors = [];
try {
    $stmt = $db->prepare("
        SELECT u.*, 
        (SELECT COUNT(*) FROM referrals WHERE referring_gp_id = u.id) as referral_count,
        c.name as clinic_name
        FROM gp_doctors u 
        LEFT JOIN clinics c ON u.clinic_id = c.id
        WHERE u.gp_name LIKE ? OR u.login LIKE ?
        ORDER BY u.gp_name
    ");
    $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
    $gp_doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("GP doctors query error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching GP doctors.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GP Doctors - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Custom styles */
        .page-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }
        
        .btn {
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
        
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        /* Hover effect for table rows */
        .table tbody tr:hover {
            background-color: rgba(0,0,0,.03);
            transition: background-color 0.2s ease-in-out;
        }
        
        /* Action buttons styling */
        .action-buttons .btn {
            margin: 0 0.25rem;
            transition: transform 0.2s;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h2 class="mb-0 me-4">GP Doctors</h2>
                    <a href="clinics.php" class="btn btn-info btn-lg">
                        <i class="bi bi-building me-2"></i> Clinics
                    </a>
                </div>
                <div>
                    <a href="add_clinic.php" class="btn btn-success btn-lg me-2">
                        <i class="bi bi-plus-circle me-2"></i> Add Clinic
                    </a>
                    <a href="add_gp.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i> Add New GP
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <form method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by GP Name or Login" value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="px-4">ID</th>
                                <th>GP Name</th>
                                <th>Login</th>
                                <th>Phone</th>
                                <th>Certification</th>
                                <th>MMC No</th>
                                <th>Status</th>
                                <th>Clinic</th>
                                <th class="text-end px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gp_doctors as $gp): ?>
                                <tr>
                                    <td class="px-4"><?php echo htmlspecialchars($gp['id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($gp['gp_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($gp['login']); ?></td>
                                    <td><?php echo htmlspecialchars($gp['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($gp['certification']); ?></td>
                                    <td><?php echo htmlspecialchars($gp['mmc_no']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $gp['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $gp['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($gp['clinic_name'] ?? 'Not Assigned'); ?></td>
                                    <td class="text-end px-4">
                                        <div class="action-buttons">
                                            <a href="edit_gp.php?id=<?php echo $gp['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this GP?');">
                                                <input type="hidden" name="delete_gp" value="<?php echo $gp['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($gp_doctors)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No GP doctors found.
                                    </td>
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