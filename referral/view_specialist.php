<?php
// Start session for user authentication
session_start();

// Database connection
require_once 'config/db.php';
$conn = require 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['gp_id'])) {
    header("Location: login.php");
    exit();
}

// Check if specialist ID is provided
if (!isset($_GET['id'])) {
    header("Location: specialists.php");
    exit();
}

// Fetch specialist details with department name
try {
    $stmt = $conn->prepare("SELECT s.*, dept.name as department_name 
                           FROM specialists s 
                           LEFT JOIN departments dept ON s.department_id = dept.id 
                           WHERE s.id = ?");
    $stmt->execute([$_GET['id']]);
    $specialist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$specialist) {
        header("Location: specialists.php");
        exit();
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the specialist details.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialist Profile - Hospital Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .profile-card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.5rem;
        }
        .profile-header {
            background: linear-gradient(to right, #4e73df, #224abe);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .info-label {
            font-weight: 600;
            color: #4e73df;
        }
        @media (max-width: 768px) {
            .profile-img {
                width: 120px;
                height: 120px;
            }
            .profile-header {
                padding: 1.5rem;
            }
            .card-body {
                padding: 1rem !important;
            }
        }
        @media (max-width: 576px) {
            .profile-img {
                width: 100px;
                height: 100px;
            }
            .profile-header h2 {
                font-size: 1.5rem;
            }
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            .btn:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body class="bg-light">
    <?php 
    $currentPage = 'specialists';
    include 'includes/navbar.php';
    ?>

    <div class="container mt-4">
        <div class="mb-3">
            <a href="specialists.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Specialists List
            </a>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="text-center">
                    <div class="profile-img mx-auto mb-3">
                        <?php if (!empty($specialist['picture']) && file_exists($specialist['picture'])): ?>
                            <img src="<?php echo htmlspecialchars($specialist['picture']); ?>" 
                                 alt="<?php echo htmlspecialchars($specialist['name']); ?>" 
                                 class="img-fluid rounded-circle" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user-md fa-4x text-secondary"></i>
                        <?php endif; ?>
                    </div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($specialist['name']); ?></h2>
                    <p class="mb-0"><?php echo htmlspecialchars($specialist['specialization']); ?></p>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h4 class="mb-3">Personal Information</h4>
                        <div class="row">
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="info-label">Department</div>
                                <div><?php echo htmlspecialchars($specialist['department_name']); ?></div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="info-label">Email</div>
                                <div><?php echo htmlspecialchars($specialist['email']); ?></div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="info-label">Phone</div>
                                <div><?php echo htmlspecialchars($specialist['phone']); ?></div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="info-label">Status</div>
                                <div>
                                    <span class="badge bg-<?php echo $specialist['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($specialist['status'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($specialist['profile'])): ?>
                <div class="mt-3">
                    <h5 class="mb-3">About</h5>
                    <div class="mb-3">
                        <?php echo $specialist['profile']; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-4 text-center">
                    <a href="specialist_availability.php?id=<?php echo $specialist['id']; ?>" class="btn btn-secondary me-2">
                        <i class="fas fa-calendar-alt me-2"></i>View Availability
                    </a>
                    <a href="edit_specialist.php?id=<?php echo $specialist['id']; ?>" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                    <button class="btn btn-danger" onclick="deleteSpecialist(<?php echo $specialist['id']; ?>)">
                        <i class="fas fa-trash me-2"></i>Delete Specialist
                    </button>
                </div>
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