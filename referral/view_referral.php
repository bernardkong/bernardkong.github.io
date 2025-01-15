<?php
session_start();
require_once 'config/db.php';

// Debug output
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check login and ID
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

try {
    // Prepare and execute query
    $stmt = $db->prepare("SELECT r.*,
                         p.name as patient_name,
                         p.ic_number,
                         d.name as department_name,
                         g.gp_name,
                         s.name as specialist_name
                         FROM referrals r 
                         LEFT JOIN patients p ON r.patient_id = p.id 
                         LEFT JOIN departments d ON r.department_id = d.id 
                         LEFT JOIN gp_doctors g ON r.referring_gp_id = g.id 
                         LEFT JOIN specialists s ON r.specialist_id = s.id 
                         WHERE r.id = ?");
    
    $stmt->execute([$_GET['id']]);
    $referral = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$referral) {
        throw new Exception("Referral not found");
    }
    
} catch(Exception $e) {
    $_SESSION['error'] = "Error loading referral: " . $e->getMessage();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Referral - SJMC Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            border: none;
            margin-bottom: 20px;
        }

        .section-header {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .info-group {
            background: #fff;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .info-label {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.1rem;
            margin-bottom: 1rem;
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

        .btn-action {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            margin: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar (same as index.php) -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px;">
                SJMC Admin Portal
            </a>
            <!-- ... rest of navbar code ... -->
        </div>
    </nav>

    <div class="container mt-4">
        <div class="section-header">
            <h2><i class="bi bi-file-text me-2"></i>Referral Details</h2>
            <p class="mb-0">Viewing detailed information for referral #<?php echo htmlspecialchars($referral['id']); ?></p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="info-group">
                    <h4 class="mb-3"><i class="bi bi-person me-2"></i>Patient Information</h4>
                    <div class="mb-3">
                        <div class="info-label">Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($referral['patient_name']); ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">IC Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($referral['ic_number']); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-group">
                    <h4 class="mb-3"><i class="bi bi-clipboard2-pulse me-2"></i>Referral Status</h4>
                    <div class="mb-3">
                        <div class="info-label">Current Status</div>
                        <div class="info-value">
                            <span class="badge bg-<?php echo getStatusColor($referral['status']); ?> p-2">
                                <i class="bi bi-circle-fill me-1"></i>
                                <?php echo ucfirst(htmlspecialchars($referral['status'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Priority Level</div>
                        <div class="info-value">
                            <span class="badge bg-<?php echo getPriorityColor($referral['priority_level']); ?> p-2">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php echo ucfirst(htmlspecialchars($referral['priority_level'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="info-group">
                    <h4 class="mb-3"><i class="bi bi-building me-2"></i>Department Details</h4>
                    <div class="mb-3">
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($referral['department_name']); ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Assigned Specialist</div>
                        <div class="info-value"><?php echo htmlspecialchars($referral['specialist_name'] ?? 'Not Assigned'); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-group">
                    <h4 class="mb-3"><i class="bi bi-person-vcard me-2"></i>GP Information</h4>
                    <div class="mb-3">
                        <div class="info-label">Referring Doctor</div>
                        <div class="info-value"><?php echo htmlspecialchars($referral['gp_name']); ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Referral Date</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($referral['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-group">
            <h4 class="mb-3"><i class="bi bi-journal-text me-2"></i>Clinical Information</h4>
            
            <div class="mb-3">
                <div class="info-label">Clinical History</div>
                <div class="p-3 bg-light rounded">
                    <?php echo $referral['clinical_history'] ? nl2br(htmlspecialchars($referral['clinical_history'])) : '<em>No clinical history provided</em>'; ?>
                </div>
            </div>

            <div class="mb-3">
                <div class="info-label">Diagnosis</div>
                <div class="p-3 bg-light rounded">
                    <?php echo $referral['diagnosis'] ? nl2br(htmlspecialchars($referral['diagnosis'])) : '<em>No diagnosis provided</em>'; ?>
                </div>
            </div>

            <div class="mb-3">
                <div class="info-label">Investigation Results</div>
                <div class="p-3 bg-light rounded">
                    <?php echo $referral['investigation_results'] ? nl2br(htmlspecialchars($referral['investigation_results'])) : '<em>No investigation results provided</em>'; ?>
                </div>
            </div>

            <div class="mb-3">
                <div class="info-label">Additional Remarks</div>
                <div class="p-3 bg-light rounded">
                    <?php echo $referral['remarks'] ? nl2br(htmlspecialchars($referral['remarks'])) : '<em>No additional remarks</em>'; ?>
                </div>
            </div>
        </div>

        <div class="info-group">
            <h4 class="mb-3"><i class="bi bi-info-circle me-2"></i>Additional Information</h4>
            
            <?php if ($referral['referral_letter_path']): ?>
            <div class="mb-3">
                <div class="info-label">Referral Letter</div>
                <div class="info-value">
                    <a href="<?php echo htmlspecialchars($referral['referral_letter_path']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-file-earmark-text me-2"></i>View Document
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($referral['payment_mode']): ?>
            <div class="mb-3">
                <div class="info-label">Payment Mode</div>
                <div class="info-value"><?php echo htmlspecialchars($referral['payment_mode']); ?></div>
            </div>
            <?php endif; ?>

            <?php if ($referral['no_show_date']): ?>
            <div class="mb-3">
                <div class="info-label">No Show Date</div>
                <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($referral['no_show_date'])); ?></div>
            </div>
            <?php endif; ?>

            <div class="mb-3">
                <div class="info-label">Last Updated</div>
                <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($referral['updated_at'])); ?></div>
            </div>
        </div>

        <div class="d-flex mb-5">
            <a href="index.php" class="btn btn-secondary btn-action">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Bottom nav -->
    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            <span>Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'accepted':
            return 'success';
        case 'rejected':
            return 'danger';
        case 'completed':
            return 'info';
        case 'cancelled':
            return 'secondary';
        default:
            return 'primary';
    }
}

function getPriorityColor($priority) {
    switch ($priority) {
        case 'routine':
            return 'success';
        case 'urgent':
            return 'warning';
        case 'emergency':
            return 'danger';
        default:
            return 'primary';
    }
}
?>