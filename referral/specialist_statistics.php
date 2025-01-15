<?php
session_start();
require_once 'config/db.php';

// Check if specialist is logged in
if (!isset($_SESSION['specialist_id'])) {
    header('Location: specialist_login.php');
    exit();
}

$specialist_id = $_SESSION['specialist_id'];

try {
    // Get total referrals count
    $stmt = $db->prepare("SELECT COUNT(*) FROM referrals WHERE specialist_id = ?");
    $stmt->execute([$specialist_id]);
    $total_referrals = $stmt->fetchColumn();

    // Get pending referrals count
    $stmt = $db->prepare("SELECT COUNT(*) FROM referrals WHERE specialist_id = ? AND status = 'pending'");
    $stmt->execute([$specialist_id]);
    $pending_referrals = $stmt->fetchColumn();

    // Get completed referrals count
    $stmt = $db->prepare("SELECT COUNT(*) FROM referrals WHERE specialist_id = ? AND status = 'completed'");
    $stmt->execute([$specialist_id]);
    $completed_referrals = $stmt->fetchColumn();

    // Get referrals by month (last 6 months)
    $stmt = $db->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
        FROM referrals 
        WHERE specialist_id = ? 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
    ");
    $stmt->execute([$specialist_id]);
    $monthly_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Statistics error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - SJMC Specialist Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .stats-card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="specialist_dashboard.php">
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px;">
                SJMC Specialist Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="specialist_dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
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
        <h2 class="mb-4"><i class="bi bi-graph-up me-2"></i>Statistics Overview</h2>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="card-title">Total Referrals</h3>
                        <h2 class="text-primary"><?php echo $total_referrals; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="card-title">Pending</h3>
                        <h2 class="text-warning"><?php echo $pending_referrals; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="card-title">Completed</h3>
                        <h2 class="text-success"><?php echo $completed_referrals; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stats-card mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4">Monthly Referrals</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Monthly statistics chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_stats, 'month')); ?>,
                datasets: [{
                    label: 'Number of Referrals',
                    data: <?php echo json_encode(array_column($monthly_stats, 'count')); ?>,
                    borderColor: '#3498db',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>