<?php
// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';
if (!$db) {
    die("Database connection failed");
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize departments array
$departments = [];
$recent_referrals = [];
$stats = [
    'total_referrals' => 0,
    'pending_referrals' => 0,
    'completed_referrals' => 0
];

// Fetch departments
try {
    if (!$db) {
        throw new Exception("Database connection not available");
    }

    $stmt = $db->query("SELECT d.*, 
                        (SELECT COUNT(*) FROM specialists WHERE department_id = d.id) as specialist_count
                        FROM departments d 
                        ORDER BY d.name");
    if (!$stmt) {
        throw new Exception("Failed to execute department query");
    }
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching departments.";
    $departments = [];
}

// Fetch statistics and referrals
try {
    // Get referral statistics
    $stats_query = $db->query("SELECT 
        COUNT(*) as total_referrals,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_referrals,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_referrals,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_referrals,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_referrals,
        SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show_referrals
        FROM referrals");
    $stats = $stats_query->fetch(PDO::FETCH_ASSOC);

    // Fetch recent referrals with sorting
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date'; // Default sort by date
    $orderBy = 'r.created_at DESC'; // Default order

    switch ($sort) {
        case 'priority':
            $orderBy = 'r.priority_level ASC'; // Sort by priority
            break;
        case 'status':
            $orderBy = 'r.status ASC'; // Sort by status
            break;
        case 'date':
        default:
            $orderBy = 'r.created_at DESC'; // Sort by date
            break;
    }

    // Fetch recent referrals with filtering based on status and search
    $status_filter = isset($_GET['status']) ? $_GET['status'] : null;
    $search_term = isset($_GET['search']) ? $_GET['search'] : null;
    $where_conditions = [];
    $params = [];

    if ($status_filter) {
        $where_conditions[] = "r.status = :status";
        $params[':status'] = $status_filter;
    }

    if ($search_term) {
        $where_conditions[] = "p.name LIKE :search";
        $params[':search'] = "%{$search_term}%";
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : '';

    // Get recent referrals with all necessary joins
    $referrals_query = $db->prepare("SELECT 
        r.id, 
        r.*, 
        d.name as department_name,
        p.name as patient_name,
        p.ic_number,
        g.gp_name as referring_doctor,
        s.name as specialist_name
        FROM referrals r
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN gp_doctors g ON r.referring_gp_id = g.id
        LEFT JOIN specialists s ON r.specialist_id = s.id
        $where_clause
        ORDER BY $orderBy
        ");

    $referrals_query->execute($params);
    $recent_referrals = $referrals_query->fetchAll(PDO::FETCH_ASSOC);

} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Admin Dashboard</title>
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
            border: none;
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

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            height: 100%;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: white;
        }

        .table-card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .container {
            max-width: 1800px;
            margin: 30px auto;
            padding: 20px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Responsive table */
        @media (max-width: 992px) {
            .table-responsive {
                margin: 0 -15px;
                padding: 0 15px;
            }
            
            .table th, .table td {
                min-width: 120px;
            }
        }

        /* Responsive cards */
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 15px;
            }
            
            .dashboard-header {
                padding: 1.5rem;
                margin: 1rem auto;
            }
            
            .card-body {
                padding: 1rem;
            }
        }

        /* Small screens */
        @media (max-width: 576px) {
            .dashboard-header {
                padding: 1rem;
                border-radius: 10px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
            }
        }

        /* Extra small screens */
        @media (max-width: 400px) {
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .card-title {
                font-size: 1.1rem;
            }
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

        /* Navbar specific styles */
        .navbar .nav-link {
            font-size: 1.1rem;
            padding: 0.8rem 1.2rem !important;
            margin: 0 0.3rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .navbar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 500;
        }

        .navbar .nav-link i {
            font-size: 1.2rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .navbar .nav-link {
                padding: 1rem !important;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Replace existing navbar with new styled navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px; display: block;">
                SJMC Admin Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="departments.php">
                            <i class="bi bi-building me-1"></i>Departments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="specialists.php">
                            <i class="bi bi-people me-1"></i>Specialists
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear me-1"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Replace dashboard header with welcome section -->
        <div class="welcome-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="bi bi-person-circle me-2"></i>Welcome, Admin</h2>
                    <p class="mb-0">Manage referrals and view hospital statistics below</p>
                </div>
                <div>
                    <a href="statistics.php" class="btn btn-light">
                        <i class="bi bi-graph-up me-2"></i>View Statistics
                    </a>
                </div>
            </div>
        </div>

        <!-- Keep existing statistics cards and table but update their styling -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary">
                                <i class="bi bi-file-text"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="card-subtitle mb-1">Total Referrals</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['total_referrals'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="card-subtitle mb-1">Pending Referrals</h6>
                                <h3 class="card-title mb-0">
                                    <a href="?status=pending" class="text-decoration-none"><?php echo $stats['pending_referrals'] ?? 0; ?></a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="card-subtitle mb-1">Approved Referrals</h6>
                                <h3 class="card-title mb-0">
                                    <a href="?status=approved" class="text-decoration-none"><?php echo $stats['approved_referrals'] ?? 0; ?></a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="card-subtitle mb-1">Rejected Referrals</h6>
                                <h3 class="card-title mb-0">
                                    <a href="?status=rejected" class="text-decoration-none"><?php echo $stats['rejected_referrals'] ?? 0; ?></a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info">
                                <i class="bi bi-check-all"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="card-subtitle mb-1">Completed Referrals</h6>
                                <h3 class="card-title mb-0">
                                    <a href="?status=completed" class="text-decoration-none"><?php echo $stats['completed_referrals'] ?? 0; ?></a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Field -->
        <div class="mb-4">
            <form action="" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by Patient Name" aria-label="Search">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- Recent Referrals Table -->
        <div class="card table-card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Referrals</h5>
                    <div class="btn-group">
                        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?sort=date">Sort by Date</a></li>
                            <li><a class="dropdown-item" href="?sort=priority">Sort by Priority</a></li>
                            <li><a class="dropdown-item" href="?sort=status">Sort by Status</a></li>
                        </ul>
                        <button class="btn btn-light btn-sm">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th><a href="?sort=patient_name" class="text-decoration-none">Patient Name</a></th>
                                <th><a href="?sort=department" class="text-decoration-none">Department</a></th>
                                <th><a href="?sort=priority" class="text-decoration-none">Priority</a></th>
                                <th><a href="?sort=status" class="text-decoration-none">Status</a></th>
                                <th><a href="?sort=date" class="text-decoration-none">Date</a></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_referrals as $referral): ?>
                            <tr>
                                <td>
                                    <?php 
                                    // Highlight search term in patient name
                                    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
                                    $patientName = htmlspecialchars($referral['patient_name']);
                                    if ($searchTerm) {
                                        $highlightedName = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<span class="highlight">$1</span>', $patientName);
                                        echo $highlightedName;
                                    } else {
                                        echo $patientName;
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($referral['department_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $referral['priority_level'] === 'emergency' ? 'danger' : 
                                            ($referral['priority_level'] === 'urgent' ? 'warning' : 'success'); 
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($referral['priority_level'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php     
                                        echo $referral['status'] === 'completed' ? 'success' : 
                                            ($referral['status'] === 'pending' ? 'warning' : 
                                            ($referral['status'] === 'approved' ? 'info' : 
                                            ($referral['status'] === 'rejected' ? 'danger' : 
                                            ($referral['status'] === 'no_show' ? 'secondary' : 'secondary')))); 
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($referral['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($referral['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    // Add direct URL for debugging
                                    $view_url = "view_referral.php?id=" . htmlspecialchars($referral['id']);
                                    ?>
                                    <a href="<?php echo $view_url; ?>" 
                                       class="btn btn-sm btn-light"
                                       onclick="console.log('Clicking view for referral: <?php echo $view_url; ?>');">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <form action="update_referral_status.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                                        <?php
                                        // Show different buttons based on current status
                                        switch($referral['status']) {
                                            case 'pending':
                                                ?>
                                                <div class="btn-group">
                                                    <button type="submit" name="status" value="approved" class="btn btn-sm btn-success" style="width: 130px;">
                                                        <i class="bi bi-check"></i> Approve
                                                    </button>
                                                    <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger" style="width: 130px;">
                                                        <i class="bi bi-x"></i> Reject
                                                    </button>
                                                </div>
                                                <?php
                                                break;
                                            case 'approved':
                                                ?>
                                                <div class="btn-group">
                                                    <button type="submit" name="status" value="completed" class="btn btn-sm btn-primary" style="width: 130px;">
                                                        <i class="bi bi-check-all"></i> Complete
                                                    </button>
                                                    <button type="submit" name="status" value="no_show" class="btn btn-sm btn-secondary" style="width: 130px;">
                                                        <i class="bi bi-person-x"></i> No Show
                                                    </button>
                                                </div>
                                                <?php
                                                break;
                                            case 'completed':
                                                ?>
                                                <span class="badge bg-success" style="width: 130px;">
                                                    <i class="bi bi-check-circle"></i> Completed
                                                </span>
                                                <?php
                                                break;
                                            case 'rejected':
                                                ?>
                                                <span class="badge bg-danger" style="width: 130px;">
                                                    <i class="bi bi-x-circle"></i> Rejected
                                                </span>
                                                <?php
                                                break;
                                            case 'no_show':
                                                ?>
                                                <span class="badge bg-secondary" style="width: 130px;">
                                                    <i class="bi bi-person-x"></i> No Show
                                                </span>
                                                <?php
                                                break;
                                        }
                                        ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
</body>
</html>
