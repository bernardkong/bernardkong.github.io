<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

require_once 'config/db.php';

// Define the days of the week
$daysOfWeek = [
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
];

// Add this new section at the top of the file, after the require_once
if (isset($_GET['check_availability']) && isset($_GET['date'])) {
    header('Content-Type: application/json');
    
    try {
        // Convert the date to day of week (1-7)
        $date = new DateTime($_GET['date']);
        $day_of_week = $date->format('N');

        // Query the schedule for this day
        $stmt = $db->prepare("
            SELECT start_time, end_time, is_available 
            FROM specialist_schedule 
            WHERE specialist_id = :specialist_id 
            AND day_of_week = :day_of_week
            AND is_available = 1
        ");
        
        $stmt->execute([
            ':specialist_id' => $_GET['id'],
            ':day_of_week' => $day_of_week
        ]);
        
        $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'slots' => $slots,
            'day_name' => $daysOfWeek[$day_of_week]
        ]);
        exit;
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }
}

// Get the specialist ID from the query parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $specialist_id = $_GET['id'];

    // Fetch the specialist's schedule
    try {
        $stmt = $db->prepare("SELECT * FROM specialist_schedule WHERE specialist_id = :specialist_id ORDER BY day_of_week, start_time");
        $stmt->bindParam(':specialist_id', $specialist_id, PDO::PARAM_INT);
        $stmt->execute();
        $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error_message = "An error occurred while fetching the specialist's schedule.";
    }
} else {
    $error_message = "Invalid specialist ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialist Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <!-- Add logo and title here -->
        <div class="text-center mb-4">
            <img src="uploads/sjmclogo.png" alt="SJMC Logo" class="img-fluid" style="max-width: 200px;">
            <h1>SJMC GP Referral</h1>
        </div>
        <h2>Specialist Schedule</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Day of Week</th>
                        <th>9:00 - 13:00</th>
                        <th>14:00 - 18:00</th>
                        <th>18:30 - 21:30</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule as $slot): ?>
                        <tr>
                            <td>
                                <?php 
                                // Check if the day_of_week is valid before accessing the array
                                if (isset($daysOfWeek[$slot['day_of_week']])) {
                                    echo htmlspecialchars($daysOfWeek[$slot['day_of_week']]);
                                } else {
                                    echo 'Invalid Day'; // Handle invalid day gracefully
                                }
                                ?>
                            </td>
                            <td><?php echo ($slot['start_time'] <= '13:00' && $slot['end_time'] >= '09:00') ? ($slot['is_available'] ? '<span class="text-danger">SJMC</span>' : 'No') : 'N/A'; ?></td>
                            <td><?php echo ($slot['start_time'] <= '18:00' && $slot['end_time'] >= '14:00') ? ($slot['is_available'] ? '<span class="text-danger">SJMC</span>' : 'No') : 'N/A'; ?></td>
                            <td><?php echo ($slot['start_time'] <= '21:30' && $slot['end_time'] >= '18:30') ? ($slot['is_available'] ? '<span class="text-danger">SJMC</span>' : 'No') : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>