<?php
// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';
if (!$db) {
    die("Database connection failed");
}

// Fetch Department performance data
$department_performance = [];
try {
    $department_stmt = $db->query("SELECT d.name as department_name, COUNT(r.id) as total_referrals 
                                    FROM departments d 
                                    LEFT JOIN referrals r ON d.id = r.department_id 
                                    GROUP BY d.id");
    $department_performance = $department_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Prepare data for the chart
$department_names = array_column($department_performance, 'department_name');
$total_referrals = array_column($department_performance, 'total_referrals');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments Performance Graph</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Departments Performance</h1>
        <input type="month" id="dateSelector" value="<?php echo date('Y-m'); ?>" />
        <canvas id="departmentChart" width="400" height="200"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('departmentChart').getContext('2d');
        const departmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($department_names); ?>,
                datasets: [{
                    label: 'Total Referrals',
                    data: <?php echo json_encode($total_referrals); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Add event listener for date selection
        document.getElementById('dateSelector').addEventListener('change', function() {
            const selectedDate = this.value;
            // Logic to update the chart based on the selected date
            // This part needs to be implemented based on your data fetching logic
        });
    </script>
</body>
</html>