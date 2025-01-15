<?php
session_start();
require_once 'config/db.php';

// Check if specialist is logged in
if (!isset($_SESSION['specialist_id'])) {
    header('Location: specialist_login.php');
    exit();
}

$specialist_id = $_SESSION['specialist_id'];

// Update the query to match actual columns
$specialist_query = "SELECT s.*, d.name as department_name 
                    FROM specialists s 
                    LEFT JOIN departments d ON s.department_id = d.id 
                    WHERE s.id = ?";
$stmt = $db->prepare($specialist_query);
$stmt->execute([$_SESSION['specialist_id']]);
$specialist = $stmt->fetch(PDO::FETCH_ASSOC);

// Get pending referrals with GP and patient information
$pending_referrals_query = $db->prepare("
    SELECT r.*, 
           p.name as patient_name,
           p.ic_number,
           g.gp_name,
           d.name as department_name,
           c.name as clinic_name
    FROM referrals r
    JOIN patients p ON r.patient_id = p.id 
    JOIN gp_doctors g ON r.referring_gp_id = g.id
    JOIN departments d ON r.department_id = d.id
    JOIN clinics c ON g.clinic_id = c.id
    WHERE r.specialist_id = ? 
    AND r.status = 'pending'
    ORDER BY r.created_at DESC
");

$pending_referrals_query->execute([$specialist_id]);
$pending_referrals = $pending_referrals_query->fetchAll();

$pending_count = count($pending_referrals);

// Get total referrals for this specialist
$total_referrals_query = $db->prepare("
    SELECT COUNT(*) as total 
    FROM referrals 
    WHERE specialist_id = ?
");
$total_referrals_query->execute([$specialist_id]);
$total_referrals = $total_referrals_query->fetch(PDO::FETCH_ASSOC)['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialist Dashboard - SJMC GP Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .welcome-section h2 {
            color: white;
            margin: 0;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem;
        }

        .card-header h4 {
            margin: 0;
            color: white;
        }

        .btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-info {
            background-color: var(--secondary-color);
            border: none;
            color: white;
        }

        .btn-info:hover {
            background-color: #2980b9;
            color: white;
            transform: translateY(-2px);
        }

        .list-group-item {
            border: none;
            padding: 1rem;
            margin-bottom: 5px;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .detail-icon {
            color: var(--secondary-color);
            margin-right: 10px;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="specialist_dashboard.php">
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px; display: block;">
                SJMC GP Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="specialist_logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="welcome-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="bi bi-person-circle me-2"></i>Welcome, <?php echo htmlspecialchars($specialist['name']); ?></h2>
                    <p class="mb-0">Manage your referrals and view your information below</p>
                </div>
                <a href="specialist_statistics.php" class="btn btn-light">
                    <i class="bi bi-graph-up me-2"></i>View Statistics
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h4><i class="bi bi-person-badge me-2"></i>Your Details</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <i class="bi bi-person detail-icon"></i>Name: <?php echo htmlspecialchars($specialist['name']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-card-text detail-icon"></i>NSR Number: <?php echo htmlspecialchars($specialist['nsr_number']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-building detail-icon"></i>Department: <?php echo htmlspecialchars($specialist['department_name']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-award detail-icon"></i>Specialization: <?php echo htmlspecialchars($specialist['specialization']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-telephone detail-icon"></i>Contact: <?php echo htmlspecialchars($specialist['phone']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-envelope detail-icon"></i>Email: <?php echo htmlspecialchars($specialist['email']); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h4><i class="bi bi-gear-fill me-2"></i>Quick Actions</h4>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <a href="specialist_pending_referrals.php" class="btn btn-primary w-75 mb-3">
                            <i class="bi bi-clock me-2"></i>Pending Referrals (<?php echo $pending_count; ?>)
                        </a>
                        <a href="specialist_view_referrals.php" class="btn btn-info w-75 mb-3">
                            <i class="bi bi-list-ul me-2"></i>View All Referrals
                        </a>
                        <a href="specialist_completed_referrals.php" class="btn btn-info w-75">
                            <i class="bi bi-check-circle me-2"></i>Completed Referrals
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="bottom-nav">
        <div class="d-flex align-items-center">
            <img src="uploads/qmed_logo.png" alt="Logo" class="bottom-nav-logo">
            <span class="bottom-nav-text">Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
