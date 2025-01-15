<?php
session_start();
require_once 'config/db.php';
$conn = require 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get specialist ID from URL
$specialist_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch specialist details
try {
    $stmt = $conn->prepare("SELECT name FROM specialists WHERE id = ?");
    $stmt->execute([$specialist_id]);
    $specialist = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch existing schedule
    $stmt = $conn->prepare("
        SELECT * FROM specialist_schedule 
        WHERE specialist_id = ? 
        ORDER BY day_of_week, start_time
    ");
    $stmt->execute([$specialist_id]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the schedule.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Delete existing schedules for this specialist
        $stmt = $conn->prepare("DELETE FROM specialist_schedule WHERE specialist_id = ?");
        $stmt->execute([$specialist_id]);
        
        // Insert new schedules
        $stmt = $conn->prepare("
            INSERT INTO specialist_schedule 
            (specialist_id, day_of_week, start_time, end_time, is_available) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['schedule'] as $day => $times) {
            foreach ($times as $slot) {
                if (!empty($slot['start']) && !empty($slot['end'])) {
                    $stmt->execute([
                        $specialist_id,
                        $day,
                        $slot['start'],
                        $slot['end'],
                        isset($slot['available']) ? 1 : 0
                    ]);
                }
            }
        }
        
        $conn->commit();
        $success_message = "Schedule updated successfully!";
    } catch(PDOException $e) {
        $conn->rollBack();
        error_log("Database error: " . $e->getMessage());
        $error_message = "An error occurred while updating the schedule.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Specialist Availability</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .schedule-row { margin-bottom: 1rem; }
        .time-slot { border: 1px solid #dee2e6; padding: 1rem; margin-bottom: 1rem; border-radius: 0.25rem; }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Manage Schedule: <?php echo htmlspecialchars($specialist['name']); ?></h3>
                <a href="view_specialist.php?id=<?php echo $specialist_id; ?>" class="btn btn-secondary">Back to Profile</a>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    foreach ($days as $index => $day):
                    ?>
                    <div class="schedule-row">
                        <h5><?php echo $day; ?></h5>
                        <div class="time-slots" data-day="<?php echo $index + 1; ?>">
                            <?php
                            // Filter schedules for current day
                            $daySchedules = array_filter($schedules, function($schedule) use ($index) {
                                return $schedule['day_of_week'] == ($index + 1);
                            });
                            
                            if (empty($daySchedules)): 
                            ?>
                            <!-- Default empty slot -->
                            <div class="time-slot">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Start Time</label>
                                        <input type="time" name="schedule[<?php echo $index + 1; ?>][0][start]" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label>End Time</label>
                                        <input type="time" name="schedule[<?php echo $index + 1; ?>][0][end]" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Available</label>
                                        <div class="form-check">
                                            <input type="checkbox" name="schedule[<?php echo $index + 1; ?>][0][available]" class="form-check-input" checked>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-primary btn-sm add-slot" data-day="<?php echo $index + 1; ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            else:
                                foreach ($daySchedules as $slotIndex => $slot):
                            ?>
                            <div class="time-slot">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Start Time</label>
                                        <input type="time" 
                                               name="schedule[<?php echo $index + 1; ?>][<?php echo $slotIndex; ?>][start]" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($slot['start_time']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>End Time</label>
                                        <input type="time" 
                                               name="schedule[<?php echo $index + 1; ?>][<?php echo $slotIndex; ?>][end]" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($slot['end_time']); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Available</label>
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   name="schedule[<?php echo $index + 1; ?>][<?php echo $slotIndex; ?>][available]" 
                                                   class="form-check-input" 
                                                   <?php echo $slot['is_available'] ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <?php if ($slotIndex === array_key_first($daySchedules)): ?>
                                            <button type="button" class="btn btn-primary btn-sm add-slot" data-day="<?php echo $index + 1; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-danger btn-sm remove-slot">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-slot').forEach(button => {
                button.addEventListener('click', function() {
                    const day = this.dataset.day;
                    const timeSlots = this.closest('.time-slots');
                    const slotCount = timeSlots.querySelectorAll('.time-slot').length;
                    
                    const newSlot = document.createElement('div');
                    newSlot.className = 'time-slot mt-2';
                    newSlot.innerHTML = `
                        <div class="row">
                            <div class="col-md-4">
                                <input type="time" name="schedule[${day}][${slotCount}][start]" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <input type="time" name="schedule[${day}][${slotCount}][end]" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="schedule[${day}][${slotCount}][available]" class="form-check-input" checked>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-slot">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    
                    timeSlots.appendChild(newSlot);
                });
            });

            // Event delegation for remove buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-slot')) {
                    e.target.closest('.time-slot').remove();
                }
            });
        });
    </script>
</body>
</html>
