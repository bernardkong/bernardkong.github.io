<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

require_once 'config/db.php';

// Fetch all specialists
try {
    $stmt = $db->query("SELECT s.*, dept.name as department_name 
                        FROM specialists s 
                        LEFT JOIN departments dept ON s.department_id = dept.id 
                        ORDER BY s.name");
    $specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the specialists list.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialists List - SJMC GP Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px; /* Add space for bottom nav */
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .card-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem;
        }

        .btn {
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .bottom-nav {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
        }

        .bottom-nav-logo {
            height: 40px;
            margin-right: 10px;
        }

        .table {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <!-- Updated Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="gp_dashboard.php">
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px; display: block;">
                SJMC GP Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="gp_logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card gp-card">
            <div class="card-header gp-card-header">
                <h3 class="mb-0">Specialists Directory</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th> 
                                <th>Name</th>
                                <th>Department</th>
                                <th>Specialization</th>
                                <th>Contact</th>
                                <th>Picture</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($specialists as $specialist): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($specialist['id']); ?></td>
                                    <td><?php echo htmlspecialchars($specialist['name']); ?></td>
                                    <td><?php echo htmlspecialchars($specialist['department_name']); ?></td>
                                    <td><?php echo htmlspecialchars($specialist['specialization']); ?></td>
                                    <td><?php echo htmlspecialchars($specialist['phone']); ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($specialist['picture']); ?>" 
                                             alt="Doctor Picture" 
                                             class="rounded-circle"
                                             style="width: 50px; height: 50px; object-fit: cover;"/>
                                    </td>
                                    <td>
                                        <a href="view_slots.php?id=<?php echo htmlspecialchars($specialist['id']); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-calendar-alt"></i> Show Slots
                                        </a>
                                        <a href="gp_view_specialist.php?id=<?php echo htmlspecialchars($specialist['id']); ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-user-md"></i> View Profile
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Added Bottom Nav -->
    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" class="bottom-nav-logo">
            <span class="bottom-nav-text">Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>