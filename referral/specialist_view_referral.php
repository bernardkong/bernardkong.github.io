<?php
session_start();
require_once 'config/db.php';

// Check specialist login
if (!isset($_SESSION['specialist_id'])) {
    header("Location: specialist_login.php");
    exit();
}

try {
    // Modified query to get all referrals for the logged-in specialist
    $stmt = $db->prepare("SELECT r.*,
                         p.name as patient_name,
                         p.ic_number,
                         d.name as department_name,
                         g.gp_name,
                         c.name as clinic_name
                         FROM referrals r 
                         LEFT JOIN patients p ON r.patient_id = p.id 
                         LEFT JOIN departments d ON r.department_id = d.id 
                         LEFT JOIN gp_doctors g ON r.referring_gp_id = g.id 
                         LEFT JOIN clinics c ON g.clinic_id = c.id 
                         WHERE r.specialist_id = ?
                         ORDER BY r.created_at DESC");
    
    $stmt->execute([$_SESSION['specialist_id']]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $_SESSION['error'] = "Error loading referrals: " . $e->getMessage();
    header("Location: specialist_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Referrals - SJMC GP Portal</title>
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

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .table thead tr:first-child th:first-child {
            border-top-left-radius: 15px;
        }

        .table thead tr:first-child th:last-child {
            border-top-right-radius: 15px;
        }

        .btn {
            border-radius: 10px;
            padding: 8px 15px;
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
                    <h2><i class="bi bi-file-text me-2"></i>All Referrals</h2>
                    <p class="mb-0">View and manage all your referrals</p>
                </div>
                <a href="specialist_dashboard.php" class="btn btn-light">
                    <i class="bi bi-house me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>GP Doctor</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrals as $referral): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($referral['id']); ?></td>
                                <td><?php echo htmlspecialchars($referral['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($referral['gp_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getPriorityColor($referral['priority_level']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($referral['priority_level'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusColor($referral['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($referral['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($referral['created_at'])); ?></td>
                                <td>
                                    <a href="specialist_view_referral_details.php?id=<?php echo $referral['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($referral['status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success" onclick="acceptReferral(<?php echo $referral['id']; ?>)">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="rejectReferral(<?php echo $referral['id']; ?>)">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            <span>Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function acceptReferral(referralId) {
        if (confirm('Are you sure you want to accept this referral?')) {
            window.location.href = `specialist_process_referral.php?id=${referralId}&action=accept`;
        }
    }

    function rejectReferral(referralId) {
        if (confirm('Are you sure you want to reject this referral?')) {
            window.location.href = `specialist_process_referral.php?id=${referralId}&action=reject`;
        }
    }
    </script>
</body>
</html>

<?php
// ... [Keep the helper functions for status and priority colors] ...
?>