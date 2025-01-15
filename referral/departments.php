<?php
// Start session for user authentication
session_start();

// Database connection
require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if a search term is provided
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Database connection
try {
    $stmt = $db->prepare("SELECT d.*, 
                        (SELECT COUNT(*) FROM specialists WHERE department_id = d.id) as specialist_count
                        FROM departments d 
                        WHERE d.name LIKE :searchTerm
                        ORDER BY d.name");
    $stmt->execute(['searchTerm' => '%' . $searchTerm . '%']);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Database query error: " . $e->getMessage());
    $departments = []; // Initialize as empty array to avoid undefined variable error
    echo "An error occurred while fetching departments.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .department-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .department-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .btn-action {
            border-radius: 8px;
            padding: 0.375rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-edit {
            background-color: #4834d4;
            border: none;
        }

        .btn-edit:hover {
            background-color: #686de0;
        }

        .btn-delete {
            background-color: #eb4d4b;
            border: none;
        }

        .btn-delete:hover {
            background-color: #ff7675;
        }
    </style>
</head>
<body>
    <?php 
    $currentPage = 'departments';
    include 'includes/navbar.php';
    ?>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="container mt-5">
            <div class="section-header d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Departments</h2>
                <button type="button" class="btn add-department-btn text-white" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                    <i class="bi bi-plus-lg me-2"></i>Add New Department
                </button>
            </div>

            <!-- Search Bar -->
            <div class="mb-3 d-flex">
                <form action="" method="GET" class="d-flex w-100">
                    <input type="text" id="searchInput" name="search" class="form-control me-2" placeholder="Search Departments..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <div class="row">
                <?php foreach ($departments as $department): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card department-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-building me-2"></i>
                                    <?php echo htmlspecialchars($department['name']); ?>
                                </h5>
                                <p class="card-text"><?php echo htmlspecialchars($department['description']); ?></p>
                                <small class="text-muted">
                                    <i class="bi bi-people-fill me-2"></i>
                                    <?php echo $department['specialist_count']; ?> Specialists
                                </small>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <span class="status-badge bg-<?php echo $department['status'] == 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($department['status'])); ?>
                                </span>
                                <div>
                                    <button class="btn btn-action btn-edit text-white" onclick="editDepartment(<?php echo $department['id']; ?>)">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </button>
                                    <button class="btn btn-action btn-delete text-white" onclick="deleteDepartment(<?php echo $department['id']; ?>)">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_department.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="departmentName" class="form-label">Department Name</label>
                            <input type="text" class="form-control" id="departmentName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="departmentDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="departmentDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="departmentStatus" class="form-label">Status</label>
                            <select class="form-select" id="departmentStatus" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editDepartment(departmentId) {
            window.location.href = 'edit_department.php?id=' + departmentId;
        }

        function deleteDepartment(departmentId) {
            if (confirm('Are you sure you want to delete this department?')) {
                window.location.href = 'delete_department.php?id=' + departmentId;
            }
        }
    </script>
</body>
</html>