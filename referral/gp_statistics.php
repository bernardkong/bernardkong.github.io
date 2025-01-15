<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

require_once 'config/db.php';

// Fetch statistics for the logged-in GP
try {
    // Total referrals
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM referrals WHERE referring_gp_id = ?");
    $stmt->execute([$_SESSION['gp_id']]);
    $totalReferrals = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Referrals by status
    $stmt = $db->prepare("
        SELECT status, COUNT(*) as count 
        FROM referrals 
        WHERE referring_gp_id = ? 
        GROUP BY status
    ");
    $stmt->execute([$_SESSION['gp_id']]);
    $statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Referrals by department
    $stmt = $db->prepare("
        SELECT d.name as department, COUNT(*) as count 
        FROM referrals r
        JOIN departments d ON r.department_id = d.id 
        WHERE r.referring_gp_id = ? 
        GROUP BY d.id, d.name
    ");
    $stmt->execute([$_SESSION['gp_id']]);
    $departmentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly referrals for the current year - Updated query
    $stmt = $db->prepare("
        SELECT 
            MONTHNAME(created_at) as month,
            COUNT(*) as count 
        FROM referrals 
        WHERE referring_gp_id = ? 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        GROUP BY MONTH(created_at), MONTHNAME(created_at)
        ORDER BY MONTH(created_at)
    ");
    $stmt->execute([$_SESSION['gp_id']]);
    $monthlyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no monthly data, initialize with zeros
    if (empty($monthlyStats)) {
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $monthlyStats = array_map(function($month) {
            return ['month' => $month, 'count' => 0];
        }, $months);
    }

    // Payment modes - Updated query with COALESCE
    $stmt = $db->prepare("
        SELECT 
            COALESCE(NULLIF(payment_mode, ''), 'Not Specified') as payment_mode,
            COUNT(*) as count 
        FROM referrals 
        WHERE referring_gp_id = ? 
        GROUP BY payment_mode
    ");
    $stmt->execute([$_SESSION['gp_id']]);
    $paymentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no payment data, initialize with default
    if (empty($paymentStats)) {
        $paymentStats = [
            ['payment_mode' => 'Not Specified', 'count' => 0]
        ];
    }

} catch(PDOException $e) {
    error_log("Statistics error: " . $e->getMessage());
    // Initialize empty arrays if queries fail
    $monthlyStats = [];
    $paymentStats = [];
}

// Remove the var_dump statements after testing
// var_dump($monthlyStats);
// var_dump($departmentStats);
// var_dump($statusStats);
// var_dump($paymentStats);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GP Statistics - SJMC GP Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
            margin-bottom: 20px;
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

        .stats-card {
            height: 400px;
        }
        
        .small-stats-card {
            height: 150px;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="gp_dashboard.php">
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px;">
                SJMC GP Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="gp_dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
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
        <div class="welcome-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="bi bi-graph-up me-2"></i>Your Referral Statistics</h2>
                    <p class="mb-0">Overview of your referral activities and patterns</p>
                </div>
                <a href="gp_dashboard.php" class="btn btn-light">
                    <i class="bi bi-house-door me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card small-stats-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Referrals</h5>
                        <div class="stats-number"><?php echo $totalReferrals; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9 mb-4">
                <div class="card stats-card">
                    <div class="card-header">
                        <h4><i class="bi bi-graph-up me-2"></i>Monthly Referrals (<?php echo date('Y'); ?>)</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card stats-card">
                    <div class="card-header">
                        <h4><i class="bi bi-pie-chart me-2"></i>Referrals by Department</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card stats-card">
                    <div class="card-header">
                        <h4><i class="bi bi-pie-chart me-2"></i>Referrals by Status</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card stats-card">
                    <div class="card-header">
                        <h4><i class="bi bi-currency-dollar me-2"></i>Payment Modes</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" class="bottom-nav-logo">
            <span class="bottom-nav-text">Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Debug data
        console.log('Monthly Stats:', <?php echo json_encode($monthlyStats); ?>);
        console.log('Department Stats:', <?php echo json_encode($departmentStats); ?>);
        console.log('Status Stats:', <?php echo json_encode($statusStats); ?>);
        console.log('Payment Stats:', <?php echo json_encode($paymentStats); ?>);

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Chart
            try {
                const monthlyData = <?php echo json_encode(array_column($monthlyStats, 'count')); ?> || [];
                const monthlyLabels = <?php echo json_encode(array_column($monthlyStats, 'month')); ?> || [];
                
                new Chart(document.getElementById('monthlyChart'), {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Number of Referrals',
                            data: monthlyData,
                            borderColor: '#3498db',
                            tension: 0.1,
                            fill: false,
                            pointBackgroundColor: '#3498db'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
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
            } catch (error) {
                console.error('Error creating monthly chart:', error);
            }

            // Department Chart
            try {
                const departmentData = <?php echo json_encode(array_column($departmentStats, 'count')); ?> || [];
                const departmentLabels = <?php echo json_encode(array_column($departmentStats, 'department')); ?> || [];
                
                new Chart(document.getElementById('departmentChart'), {
                    type: 'pie',
                    data: {
                        labels: departmentLabels,
                        datasets: [{
                            data: departmentData,
                            backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f1c40f', '#9b59b6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right'
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating department chart:', error);
            }

            // Status Chart
            try {
                const statusData = <?php echo json_encode(array_column($statusStats, 'count')); ?> || [];
                const statusLabels = <?php echo json_encode(array_column($statusStats, 'status')); ?> || [];
                
                new Chart(document.getElementById('statusChart'), {
                    type: 'pie',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: ['#2ecc71', '#e74c3c', '#f1c40f', '#3498db']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right'
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating status chart:', error);
            }

            // Payment Chart
            try {
                const paymentData = <?php echo json_encode(array_column($paymentStats, 'count')); ?> || [];
                const paymentLabels = <?php echo json_encode(array_column($paymentStats, 'payment_mode')); ?> || [];
                
                new Chart(document.getElementById('paymentChart'), {
                    type: 'pie',
                    data: {
                        labels: paymentLabels,
                        datasets: [{
                            data: paymentData,
                            backgroundColor: ['#3498db', '#2ecc71', '#e74c3c']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right'
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error creating payment chart:', error);
            }
        });
    </script>
</body>
</html>
