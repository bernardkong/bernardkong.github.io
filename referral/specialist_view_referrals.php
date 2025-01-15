<?php
session_start();
require_once 'config/db.php';

// Check if specialist is logged in
if (!isset($_SESSION['specialist_id'])) {
    header('Location: specialist_login.php');
    exit();
}

$specialist_id = $_SESSION['specialist_id'];

// Get all referrals with related information
$referrals_query = $db->prepare("
    SELECT r.*, 
           p.name as patient_name,
           p.ic_number,
           g.gp_name,
           d.name as department_name,
           c.name as clinic_name,
           DATE_FORMAT(r.created_at, '%d %M %Y') as formatted_date
    FROM referrals r
    JOIN patients p ON r.patient_id = p.id 
    JOIN gp_doctors g ON r.referring_gp_id = g.id
    JOIN departments d ON r.department_id = d.id
    JOIN clinics c ON g.clinic_id = c.id
    WHERE r.specialist_id = ? 
    ORDER BY r.created_at DESC
");

$referrals_query->execute([$specialist_id]);
$referrals = $referrals_query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Referrals - SJMC GP Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        
        body {
            background-color: #f8f9fa;
            padding-bottom: 70px;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .referral-card {
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
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
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px;">
                SJMC GP Portal
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
        <div class="page-header">
            <h2><i class="bi bi-list-ul me-2"></i>All Referrals</h2>
            <p class="mb-0">View and manage all your referrals</p>
        </div>

        <?php foreach ($referrals as $referral): ?>
            <div class="card referral-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title">
                                <i class="bi bi-person me-2"></i>
                                Patient: <?php echo htmlspecialchars($referral['patient_name']); ?> 
                                (IC: <?php echo htmlspecialchars($referral['ic_number']); ?>)
                            </h5>
                            <p class="card-text">
                                <i class="bi bi-hospital me-2"></i>
                                Referring Clinic: <?php echo htmlspecialchars($referral['clinic_name']); ?>
                            </p>
                            <p class="card-text">
                                <i class="bi bi-person-badge me-2"></i>
                                Referring GP: <?php echo htmlspecialchars($referral['gp_name']); ?>
                            </p>
                            <p class="card-text">
                                <i class="bi bi-calendar me-2"></i>
                                Referred on: <?php echo htmlspecialchars($referral['formatted_date']); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <?php
                            $status_class = match($referral['status']) {
                                'pending' => 'bg-warning',
                                'accepted' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'completed' => 'bg-info',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?php echo $status_class; ?> status-badge mb-3">
                                <?php echo ucfirst(htmlspecialchars($referral['status'])); ?>
                            </span>
                            <br>
                            <a href="specialist_view_referral.php?id=<?php echo $referral['id']; ?>" 
                               class="btn btn-primary">
                                <i class="bi bi-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($referrals)): ?>
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle me-2"></i>No referrals found.
            </div>
        <?php endif; ?>
    </div>

    <nav class="bottom-nav">
        <div class="d-flex justify-content-center align-items-center">
            <img src="uploads/qmed_logo.png" alt="Logo" class="bottom-nav-logo">
            <span>Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>