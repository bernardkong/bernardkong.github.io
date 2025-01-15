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

// Initialize statistics arrays
$gp_performance = [];
$specialist_performance = [];
$department_performance = [];

// Fetch GP performance
try {
    $gp_stmt = $db->query("SELECT g.gp_name, COUNT(r.id) as total_referrals 
                            FROM gp_doctors g 
                            LEFT JOIN referrals r ON g.id = r.referring_gp_id 
                            GROUP BY g.id");
    $gp_performance = $gp_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Fetch Specialist performance
try {
    $specialist_stmt = $db->query("SELECT s.name as specialist_name, COUNT(r.id) as total_referrals 
                                    FROM specialists s 
                                    LEFT JOIN referrals r ON s.id = r.specialist_id 
                                    GROUP BY s.id");
    $specialist_performance = $specialist_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Fetch Department performance
try {
    $department_stmt = $db->query("SELECT d.name as department_name, COUNT(r.id) as total_referrals 
                                    FROM departments d 
                                    LEFT JOIN referrals r ON d.id = r.department_id 
                                    GROUP BY d.id");
    $department_performance = $department_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Hospital Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="mb-4">Performance Statistics</h1>

        <!-- Buttons to Plot Graphs -->
        <div class="mb-4">
            <a href="plot_gp_graph.php" class="btn btn-primary me-2">Plot GP Doctors Graph</a>
            <a href="plot_specialist_graph.php" class="btn btn-primary me-2">Plot Specialists Graph</a>
            <a href="plot_department_graph.php" class="btn btn-primary">Plot Departments Graph</a>
        </div>

        <!-- GP Doctors Performance -->
        <h2>GP Doctors Performance</h2>
        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>GP Name</th>
                    <th>Total Referrals</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gp_performance as $gp): ?>
                <tr>
                    <td><?php echo htmlspecialchars($gp['gp_name']); ?></td>
                    <td><?php echo $gp['total_referrals']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Specialists Performance -->
        <h2>Specialists Performance</h2>
        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>Specialist Name</th>
                    <th>Total Referrals</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($specialist_performance as $specialist): ?>
                <tr>
                    <td><?php echo htmlspecialchars($specialist['specialist_name']); ?></td>
                    <td><?php echo $specialist['total_referrals']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Departments Performance -->
        <h2>Departments Performance</h2>
        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th>Total Referrals</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($department_performance as $department): ?>
                <tr>
                    <td><?php echo htmlspecialchars($department['department_name']); ?></td>
                    <td><?php echo $department['total_referrals']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>