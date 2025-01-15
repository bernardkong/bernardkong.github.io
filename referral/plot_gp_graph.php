<?php
// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';
if (!$db) {
    die("Database connection failed");
}

// Fetch GP performance data
$gp_performance = [];
try {
    $gp_stmt = $db->query("SELECT g.gp_name, COUNT(r.id) as total_referrals 
                            FROM gp_doctors g 
                            LEFT JOIN referrals r ON g.id = r.referring_gp_id 
                            GROUP BY g.id");
    $gp_performance = $gp_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Prepare data for the chart
$gp_names = array_column($gp_performance, 'gp_name');
$total_referrals = array_column($gp_performance, 'total_referrals');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GP Doctors Performance Graph</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>GP Doctors Performance</h1>
        <input type="month" id="dateSelector" value="<?php echo date('Y-m'); ?>" />
        <canvas id="gpChart" width="400" height="200"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('gpChart').getContext('2d');
        const gpChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($gp_names); ?>,
                datasets: [{
                    label: 'Total Referrals',
                    data: <?php echo json_encode($total_referrals); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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
            // You may need to fetch new data based on the selected month
        });
    </script>
</body>
</html>