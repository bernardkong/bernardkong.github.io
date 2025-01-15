<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

require_once 'config/db.php';

// Fetch GP points and related information
try {
    // Get GP's total points and information
    $stmt = $db->prepare("
        SELECT 
            gp.*,
            c.name as clinic_name,
            COALESCE(hp.points, 0) as total_points,
            COALESCE(hp.level, 'Bronze') as level,
            COALESCE(hp.total_referrals, 0) as total_referrals,
            COALESCE(hp.successful_referrals, 0) as successful_referrals
        FROM gp_doctors gp 
        LEFT JOIN clinics c ON gp.clinic_id = c.id
        LEFT JOIN honour_points hp ON gp.id = hp.gp_id
        WHERE gp.id = ?
    ");
    $stmt->execute([$_SESSION['gp_id']]);
    $gp_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no honor points record exists, create one
    if (!$gp_data['total_points']) {
        $stmt = $db->prepare("
            INSERT INTO honour_points (gp_id, points, level, total_referrals, successful_referrals)
            VALUES (?, 0, 'Bronze', 0, 0)
        ");
        $stmt->execute([$_SESSION['gp_id']]);
        
        // Fetch the data again
        $stmt = $db->prepare("
            SELECT 
                gp.*,
                c.name as clinic_name,
                COALESCE(hp.points, 0) as total_points,
                COALESCE(hp.level, 'Bronze') as level,
                COALESCE(hp.total_referrals, 0) as total_referrals,
                COALESCE(hp.successful_referrals, 0) as successful_referrals
            FROM gp_doctors gp 
            LEFT JOIN clinics c ON gp.clinic_id = c.id
            LEFT JOIN honour_points hp ON gp.id = hp.gp_id
            WHERE gp.id = ?
        ");
        $stmt->execute([$_SESSION['gp_id']]);
        $gp_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get points history - Updated query
    $stmt = $db->prepare("
        SELECT 
            hp.gp_id,
            hp.points,
            hp.level,
            hp.total_referrals,
            hp.successful_referrals
        FROM honour_points hp
        WHERE hp.gp_id = ?
        ORDER BY hp.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['gp_id']]);
    $points_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("GP Points View error: " . $e->getMessage());
    // For debugging only - remove in production
    die("Database error: " . $e->getMessage());
}

// Function to determine badge class based on level
function getLevelBadgeClass($level) {
    switch($level) {
        case 'Gold':
            return 'bg-warning';
        case 'Silver':
            return 'bg-secondary';
        default:
            return 'bg-bronze';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honor Points - SJMC GP Portal</title>
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

        .points-summary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .table {
            background-color: white;
            border-radius: 15px;
        }

        .points-badge {
            background-color: #27ae60;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            color: white;
            font-weight: bold;
        }

        .bg-bronze {
            background-color: #cd7f32;
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
                        <a class="nav-link" href="gp_logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="points-summary">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="bi bi-star-fill me-2"></i>Honor Points Summary</h2>
                    <p class="mb-0">Dr. <?php echo htmlspecialchars($gp_data['gp_name']); ?> | <?php echo htmlspecialchars($gp_data['clinic_name']); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge <?php echo getLevelBadgeClass($gp_data['level']); ?> fs-5">
                        Level: <?php echo htmlspecialchars($gp_data['level']); ?>
                    </span>
                    <div class="mt-2">
                        <span class="points-badge">
                            <i class="bi bi-star-fill me-2"></i>
                            Total Points: <?php echo number_format($gp_data['total_points']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h4><i class="bi bi-clock-history me-2"></i>Points History</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>GP ID</th>
                                <th>Points</th>
                                <th>Level</th>
                                <th>Total Referrals</th>
                                <th>Successful Referrals</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($points_history)): ?>
                                <?php foreach ($points_history as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['gp_id']); ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                +<?php echo $record['points']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($record['level']); ?></td>
                                        <td><?php echo htmlspecialchars($record['total_referrals']); ?></td>
                                        <td><?php echo htmlspecialchars($record['successful_referrals']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No points history available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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