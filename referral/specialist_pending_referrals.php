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
    // Fetch pending referrals
    $stmt = $db->prepare("
        SELECT r.*, p.name as patient_name, p.ic_number as patient_ic, gp.gp_name as gp_name 
        FROM referrals r 
        JOIN patients p ON r.patient_id = p.id 
        JOIN gp_doctors gp ON r.referring_gp_id = gp.id 
        WHERE r.specialist_id = :specialist_id 
        AND r.status = 'pending'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute(['specialist_id' => $specialist_id]);
    $pending_referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Pending Referrals error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Referrals - SJMC Specialist Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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

        .referral-card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }

        .referral-card:hover {
            transform: translateY(-5px);
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

    <div class="container mt-4 mb-5">
        <h2 class="mb-4">
            <i class="bi bi-clock-history me-2"></i>Pending Referrals
        </h2>

        <?php if (empty($pending_referrals)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No pending referrals at the moment.
            </div>
        <?php else: ?>
            <?php foreach ($pending_referrals as $referral): ?>
                <div class="card referral-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="card-title">
                                    Patient: <?php echo htmlspecialchars($referral['patient_name']); ?> 
                                    (IC: <?php echo htmlspecialchars($referral['patient_ic']); ?>)
                                </h5>
                                <p class="card-text">
                                    <strong>Referring GP:</strong> <?php echo htmlspecialchars($referral['gp_name']); ?><br>
                                    <strong>Date Referred:</strong> <?php echo date('d M Y', strtotime($referral['created_at'])); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="view_referral.php?id=<?php echo $referral['id']; ?>" class="btn btn-primary mb-2">
                                    <i class="bi bi-eye me-2"></i>View Details
                                </a>
                                <a href="update_referral.php?id=<?php echo $referral['id']; ?>" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Update Status
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            <span>Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>