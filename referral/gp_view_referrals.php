<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

require_once 'config/db.php';

// Get search query if exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Modify the SQL query to include search
try {
    $query = "
        SELECT 
            r.*,
            p.name as patient_name,
            p.ic_number as patient_ic,
            d.name as department_name,
            s.name as specialist_name,
            s.specialization as specialty
        FROM referrals r
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN specialists s ON r.specialist_id = s.id
        WHERE r.referring_gp_id = ?
    ";

    $params = [$_SESSION['gp_id']];

    if (!empty($search)) {
        $query .= " AND (
            p.name LIKE ? OR 
            p.ic_number LIKE ? OR
            s.name LIKE ? OR
            s.specialization LIKE ? OR
            d.name LIKE ?
        )";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
    }

    $query .= " ORDER BY r.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("View Referrals error: " . $e->getMessage());
    $error = "An error occurred while fetching referrals.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Referrals - SJMC GP Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px; /* Make room for bottom nav */
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            padding: 1rem 0;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 2px solid #f0f0f0;
            border-radius: 15px 15px 0 0 !important;
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
            background-color: #fff;
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
        }

        .btn-info:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            background-color: #f8f9fa;
            color: var(--primary-color);
        }

        .badge {
            padding: 8px 12px;
            border-radius: 8px;
        }

        .btn-sm {
            padding: 5px 10px;
        }

        /* Add new bottom nav styles */
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
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['pdf_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['pdf_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['pdf_error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>My Referrals</h4>
                        <a href="gp_create_referral.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>New Referral
                        </a>
                    </div>
                    <!-- Add this new search form -->
                    <div class="card-body border-bottom">
                        <form class="row g-3" method="GET">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search by patient name, IC, specialist, specialty, or department..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (empty($referrals)): ?>
                            <p class="text-center">No referrals found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Patient</th>
                                            <th>Specialist</th>
                                            <th>Specialty</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($referrals as $referral): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($referral['created_at']))); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($referral['patient_name']); ?>
                                                    <br>
                                                    <small class="text-muted">IC: <?php echo htmlspecialchars($referral['patient_ic']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($referral['specialist_name']); ?></td>
                                                <td><?php echo htmlspecialchars($referral['specialty']); ?></td>
                                                <td><?php echo htmlspecialchars($referral['department_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo ($referral['status'] == 'pending' ? 'warning' : 
                                                             ($referral['status'] == 'accepted' ? 'success' : 
                                                             ($referral['status'] == 'rejected' ? 'danger' : 'secondary')));
                                                    ?>">
                                                        <?php echo ucfirst(htmlspecialchars($referral['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="gp_view_referral.php?id=<?php echo htmlspecialchars($referral['id']); ?>" 
                                                           class="btn btn-sm btn-info">
                                                            <i class="bi bi-eye me-1"></i>View
                                                        </a>
                                                        <a href="print_referral_letter.php?id=<?php echo htmlspecialchars($referral['id']); ?>" 
                                                           class="btn btn-sm btn-secondary print-btn"
                                                           onclick="handlePrint(event, this)">
                                                            <i class="bi bi-printer me-1"></i><span>Print Referral Letter</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add bottom nav -->
    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" class="bottom-nav-logo">
            <span class="bottom-nav-text">Qmed.asia © 2024</span>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function handlePrint(event, button) {
        // Show loading state
        const span = button.querySelector('span');
        const originalText = span.textContent;
        span.textContent = 'Generating PDF...';
        button.disabled = true;
        
        // Set a timeout to revert the button state if no response
        setTimeout(() => {
            span.textContent = originalText;
            button.disabled = false;
        }, 10000); // 10 seconds timeout
    }
    </script>
</body>
</html>