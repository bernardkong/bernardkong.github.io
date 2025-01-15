<?php
// Start session for user authentication
session_start();

// Database connection
require_once 'config/db.php';
$conn = require 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if a search query is provided
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Database connection and fetch doctors with search functionality
try {
    // Use the database connection from config/db.php
    $stmt = $conn->prepare("SELECT s.*, dept.name as department_name 
                             FROM specialists s 
                             LEFT JOIN departments dept ON s.department_id = dept.id 
                             WHERE s.name LIKE :search 
                             ORDER BY s.name");
    $stmt->execute(['search' => '%' . $searchQuery . '%']);
    $specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Log the error and show user-friendly message
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the specialists list.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialists List - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.1em;
            padding: 1rem;
        }
        
        .table td {
            vertical-align: middle;
            padding: 1rem;
        }
        
        .badge {
            padding: 0.5em 1em;
            font-size: 0.75rem;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .page-header {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .action-buttons .btn {
            margin: 0 0.2rem;
        }
        
        .specialist-count {
            color: #858796;
            font-size: 0.875rem;
        }
        
        /* Add this style for scrolling */
        .table-container {
            max-height: 400px; /* Set your desired max height */
            overflow-y: auto; /* Enable vertical scrolling */
        }
    </style>
</head>
<body class="bg-light">
    <?php 
    $currentPage = 'specialists';
    include 'includes/navbar.php';
    ?>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">Specialists List</h2>
                <p class="specialist-count mb-0">
                    <i class="fas fa-user-md me-2"></i>
                    Total Specialists: <?php echo count($specialists); ?>
                </p>
            </div>
            <form class="d-flex" method="GET" action="">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by name" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <a href="add_specialist.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Specialist
            </a>
        </div>

        <!-- Specialists Table -->
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Specialization</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($specialists as $specialist): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($specialist['id']); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($specialist['name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($specialist['department_name']); ?></td>
                                <td><?php echo htmlspecialchars($specialist['specialization']); ?></td>
                                <td>
                                    <div><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($specialist['email']); ?></div>
                                    <div><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($specialist['phone']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $specialist['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <i class="fas fa-<?php echo $specialist['status'] == 'active' ? 'check' : 'times'; ?> me-1"></i>
                                        <?php echo ucfirst(htmlspecialchars($specialist['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="specialist_availability.php?id=<?php echo $specialist['id']; ?>" class="btn btn-sm btn-success me-1" title="Manage Availability">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_specialist.php?id=<?php echo $specialist['id']; ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_specialist.php?id=<?php echo $specialist['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteSpecialist(<?php echo $specialist['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteSpecialist(specialistId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_specialist.php?id=' + specialistId;
                }
            })
        }
    </script>
</body>
</html>