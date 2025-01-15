<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

require_once 'config/db.php';

// Fetch GP details
try {
    $stmt = $db->prepare("
        SELECT gp.*, c.name as clinic_name 
        FROM gp_doctors gp 
        LEFT JOIN clinics c ON gp.clinic_id = c.id 
        WHERE gp.id = ?
    ");
    $stmt->execute([$_SESSION['gp_id']]);
    $gp = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("GP Affiliated Program error: " . $e->getMessage());
}

// Initialize programs array
$programs = [];

// Fetch CME Programs
try {
    // Debug: Check if database connection is successful
    if (!$db) {
        error_log("Database connection failed");
    }

    $stmt = $db->prepare("
        SELECT * FROM gp_programs 
        WHERE is_published = 1 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Check if any programs were found
    if (empty($programs)) {
        error_log("No programs found in the database");
    }
} catch(PDOException $e) {
    error_log("CME Programs error: " . $e->getMessage());
    $_SESSION['error'] = "Unable to load CME programs at this time.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJMC GP Affiliated Program</title>
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
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .program-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .benefit-card {
            transition: transform 0.3s ease;
        }

        .benefit-card:hover {
            transform: translateY(-5px);
        }

        .icon-large {
            font-size: 2.5rem;
            color: var(--primary-color);
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
        <div class="program-header">
            <h2><i class="bi bi-building-check me-2"></i>SJMC GP Affiliated Program</h2>
            <p class="mb-0">Welcome to our exclusive GP partnership program</p>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">About the Program</h4>
                        <p class="card-text">
                            The SJMC GP Affiliated Program is designed to create a strong partnership between SJMC and general practitioners. 
                            This program offers exclusive benefits and opportunities for collaboration to enhance patient care and professional growth.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Program Benefits Section -->
            <div class="col-md-4 mb-4">
                <div class="card benefit-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-star-fill icon-large mb-3"></i>
                        <h5 class="card-title">Honor Points System</h5>
                        <p class="card-text">Earn points for referrals and redeem them for various benefits and rewards.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card benefit-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill icon-large mb-3"></i>
                        <h5 class="card-title">Professional Network</h5>
                        <p class="card-text">Connect with SJMC specialists and fellow GPs in our growing medical community.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card benefit-card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-journal-medical icon-large mb-3"></i>
                        <h5 class="card-title">CME Programs</h5>
                        <p class="card-text">Access to exclusive Continuing Medical Education programs and workshops.</p>
                    </div>
                </div>
            </div>

            <!-- CME Programs Section -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-journal-medical me-2"></i>CME Programs</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($programs as $program): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <?php if ($program['program_image']): ?>
                                    <img src="<?php echo htmlspecialchars($program['program_image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($program['program_title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($program['program_title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($program['program_description']); ?></p>
                                        <?php if ($program['program_link']): ?>
                                        <a href="<?php echo htmlspecialchars($program['program_link']); ?>" 
                                           class="btn btn-primary" 
                                           target="_blank">
                                            <i class="bi bi-play-circle me-2"></i>Watch Program
                                        </a>
                                        <?php endif; ?>
                                        <div class="mt-2 text-muted">
                                            <small>Published: <?php echo date('F j, Y', strtotime($program['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status Section -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Your Program Status</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Doctor Information</h5>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($gp['gp_name']); ?></p>
                                <p><strong>Clinic:</strong> <?php echo htmlspecialchars($gp['clinic_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Program Benefits</h5>
                                <ul>
                                    <li>Access to SJMC Specialist Network</li>
                                    <li>Priority Referral Processing</li>
                                    <li>Regular CME Updates</li>
                                    <li>Exclusive Event Invitations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add bottom nav bar -->
    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" class="bottom-nav-logo">
            <span class="bottom-nav-text">Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>