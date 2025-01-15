<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

// Check if referral ID is provided
if (!isset($_GET['id'])) {
    header("Location: gp_view_referrals.php");
    exit();
}

require_once 'config/db.php';

// Add debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, let's check if the referral exists at all, without GP restriction
$stmt = $db->prepare("SELECT * FROM referrals WHERE id = ?");
$stmt->execute([$_GET['id']]);
$referral_check = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$referral_check) {
    $_SESSION['error'] = "Referral not found.";
    header("Location: gp_view_referrals.php");
    exit();
}

// Now fetch the full referral details
$stmt = $db->prepare("SELECT r.id,
                     r.patient_id,
                     r.referring_gp_id,
                     r.department_id,
                     r.priority_level,
                     r.status,
                     r.clinical_history,
                     r.diagnosis,
                     r.investigation_results,
                     r.remarks,
                     r.created_at,
                     r.updated_at,
                     r.specialist_id,
                     r.referral_letter_path,
                     r.payment_mode,
                     p.name as patient_name,
                     p.ic_number,
                     d.name as department_name,
                     g.gp_name,
                     s.name as specialist_name,
                     rcf.diagnosis as specialist_diagnosis,
                     rcf.physical_findings,
                     rcf.investigation as specialist_investigation,
                     rcf.further_plan,
                     rcf.created_at as feedback_created_at,
                     rcf.updated_at as feedback_updated_at
                     FROM referrals r 
                     JOIN patients p ON r.patient_id = p.id 
                     JOIN departments d ON r.department_id = d.id 
                     JOIN gp_doctors g ON r.referring_gp_id = g.id 
                     LEFT JOIN specialists s ON r.specialist_id = s.id 
                     LEFT JOIN referral_clinical_feedback rcf ON r.id = rcf.referral_id 
                     WHERE r.id = ?");

$stmt->execute([$_GET['id']]);
$referral = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the logged-in GP has permission to view this referral
if ($referral['referring_gp_id'] != $_SESSION['gp_id']) {
    $_SESSION['error'] = "You don't have permission to view this referral.";
    header("Location: gp_view_referrals.php");
    exit();
}

// Add this check right before the HTML output
if (!isset($referral) || !is_array($referral)) {
    $_SESSION['error'] = "Unable to retrieve referral details.";
    header("Location: gp_view_referrals.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Details - SJMC GP Portal</title>
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
            padding-bottom: 80px; /* For bottom nav */
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .card-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem;
        }

        .btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border: none;
        }

        .btn-secondary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .badge {
            padding: 8px 12px;
            border-radius: 8px;
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

        /* Additional styles for referral details */
        .detail-section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .detail-section h5 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .feedback-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
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
                        <a class="nav-link" href="gp_logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-file-text me-2"></i>Referral Details</h4>
                <a href="gp_view_referrals.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to List
                </a>
            </div>
            <div class="card-body">
                <div class="detail-section">
                    <h5><i class="bi bi-person me-2"></i>Patient Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($referral['patient_name']); ?></p>
                            <p><strong>IC Number:</strong> <?php echo htmlspecialchars($referral['ic_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo ($referral['status'] == 'pending' ? 'warning' : 
                                         ($referral['status'] == 'accepted' ? 'success' : 
                                         ($referral['status'] == 'rejected' ? 'danger' : 'secondary')));
                                ?>">
                                    <?php echo ucfirst(htmlspecialchars($referral['status'])); ?>
                                </span>
                            </p>
                            <p><strong>Priority Level:</strong> <?php echo htmlspecialchars($referral['priority_level']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h5><i class="bi bi-hospital me-2"></i>Department Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Department:</strong> <?php echo htmlspecialchars($referral['department_name']); ?></p>
                            <p><strong>Specialist:</strong> <?php echo htmlspecialchars($referral['specialist_name'] ?? 'Not assigned'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created Date:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($referral['created_at']))); ?></p>
                            <p><strong>Payment Mode:</strong> <?php echo htmlspecialchars($referral['payment_mode']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h5><i class="bi bi-clipboard2-pulse me-2"></i>Clinical Information</h5>
                    <p><strong>Clinical History:</strong><br> 
                        <?php echo nl2br(htmlspecialchars($referral['clinical_history'] ?? 'Not provided')); ?>
                    </p>
                    <p><strong>Diagnosis:</strong><br>
                        <?php echo nl2br(htmlspecialchars($referral['diagnosis'] ?? 'Not provided')); ?>
                    </p>
                    <p><strong>Investigation Results:</strong><br>
                        <?php echo nl2br(htmlspecialchars($referral['investigation_results'] ?? 'Not provided')); ?>
                    </p>
                    <p><strong>Remarks:</strong><br>
                        <?php echo nl2br(htmlspecialchars($referral['remarks'] ?? 'Not provided')); ?>
                    </p>
                    <?php if ($referral['referral_letter_path']): ?>
                        <p><strong>Referral Letter:</strong><br>
                            <a href="<?php echo htmlspecialchars($referral['referral_letter_path']); ?>" class="btn btn-secondary" target="_blank">
                                <i class="bi bi-file-earmark-text me-2"></i>View Referral Letter
                            </a>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (isset($referral['specialist_diagnosis']) || isset($referral['physical_findings']) || 
                          isset($referral['specialist_investigation']) || isset($referral['further_plan'])): ?>
                    <div class="detail-section">
                        <h5><i class="bi bi-reply-fill me-2"></i>Specialist Feedback</h5>
                        <div class="feedback-card">
                            <p><strong>Diagnosis:</strong><br>
                                <?php echo nl2br(htmlspecialchars($referral['specialist_diagnosis'] ?? 'Not provided')); ?>
                            </p>
                            <p><strong>Physical Findings:</strong><br>
                                <?php echo nl2br(htmlspecialchars($referral['physical_findings'] ?? 'Not provided')); ?>
                            </p>
                            <p><strong>Investigation:</strong><br>
                                <?php echo nl2br(htmlspecialchars($referral['specialist_investigation'] ?? 'Not provided')); ?>
                            </p>
                            <p><strong>Further Plan:</strong><br>
                                <?php echo nl2br(htmlspecialchars($referral['further_plan'] ?? 'Not provided')); ?>
                            </p>
                            <p class="text-muted mt-2">
                                <small>
                                    <i class="bi bi-clock me-1"></i>Feedback provided on: <?php echo date('d/m/Y H:i', strtotime($referral['feedback_created_at'])); ?>
                                    <?php if ($referral['feedback_updated_at'] != $referral['feedback_created_at']): ?>
                                        <br><i class="bi bi-pencil me-1"></i>Last updated: <?php echo date('d/m/Y H:i', strtotime($referral['feedback_updated_at'])); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
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
</body>
</html>