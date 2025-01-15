<?php
// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';
if (!$db) {
    die("Database connection failed");
}

// Fetch Specialist performance data
$specialist_performance = [];
try {
    $specialist_stmt = $db->query("SELECT s.name as specialist_name, COUNT(r.id) as total_referrals 
                                    FROM specialists s 
                                    LEFT JOIN referrals r ON s.id = r.specialist_id 
                                    GROUP BY s.id");
    $specialist_performance = $specialist_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Prepare data for the chart
$specialist_names = array_column($specialist_performance, 'specialist_name');
$total_referrals = array_column($specialist_performance, 'total_referrals');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialists Performance Graph</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Specialists Performance</h1>
        <label for="dateSelector">Select Date Range:</label>
        <select id="dateSelector" class="form-select mb-3">
            <option value="monthly" selected>This Month</option>
            <option value="weekly">This Week</option>
            <option value="daily">Today</option>
        </select>
        <canvas id="specialistChart" width="400" height="200"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('specialistChart').getContext('2d');
        const specialistChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($specialist_names); ?>,
                datasets: [{
                    label: 'Total Referrals',
                    data: <?php echo json_encode($total_referrals); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
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

        // Handle date selection change
        document.getElementById('dateSelector').addEventListener('change', function() {
            const selectedValue = this.value;
            // Logic to update the chart based on selected date range
            // This part needs to be implemented based on your data fetching logic
        });
    </script>
</body>
</html>