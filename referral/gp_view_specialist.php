<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

// Database connection
require_once 'config/db.php';
$conn = require 'config/db.php';

// Check if specialist ID is provided
if (!isset($_GET['id'])) {
    echo "<script>window.close();</script>";
    exit();
}

// Fetch specialist details with department name
try {
    $stmt = $conn->prepare("SELECT s.*, dept.name as department_name 
                           FROM specialists s 
                           LEFT JOIN departments dept ON s.department_id = dept.id 
                           WHERE s.id = ? AND s.status = 'active'");
    $stmt->execute([$_GET['id']]);
    $specialist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$specialist) {
        echo "<script>window.close();</script>";
        exit();
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "<script>window.close();</script>";
    exit();
}
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialist Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .profile-card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.5rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-header {
            background: linear-gradient(to right, #4e73df, #224abe);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem 0.5rem 0 0;
            position: relative;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .info-label {
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 0.3rem;
        }
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        .close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .profile-content {
            padding: 2rem;
        }
        .section {
            margin-bottom: 2rem;
        }
        .section:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="profile-header">
            <button class="close-btn" onclick="closeWindow()">
                <i class="fas fa-times"></i>
            </button>
            <div class="text-center">
                <div class="profile-img">
                    <?php if (!empty($specialist['picture']) && file_exists($specialist['picture'])): ?>
                        <img src="<?php echo htmlspecialchars($specialist['picture']); ?>" 
                             alt="<?php echo htmlspecialchars($specialist['name']); ?>" 
                             class="img-fluid rounded-circle" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-md fa-3x text-secondary"></i>
                    <?php endif; ?>
                </div>
                <h3 class="mb-1"><?php echo htmlspecialchars($specialist['name']); ?></h3>
                <p class="mb-0"><?php echo htmlspecialchars($specialist['specialization']); ?></p>
            </div>
        </div>

        <div class="profile-content">
            <div class="row">
                <!-- Personal Information Section -->
                <div class="col-12 mb-4">
                    <h4 class="mb-3">Personal Information</h4>
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="info-label">Department</div>
                            <div><?php echo htmlspecialchars($specialist['department_name']); ?></div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="info-label">Email</div>
                            <div><?php echo htmlspecialchars($specialist['email']); ?></div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="info-label">Phone</div>
                            <div><?php echo htmlspecialchars($specialist['phone']); ?></div>
                        </div>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="info-label">Specialization</div>
                            <div><?php echo htmlspecialchars($specialist['specialization']); ?></div>
                        </div>
                        <?php if (!empty($specialist['qualifications'])): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="info-label">Qualifications</div>
                            <div><?php echo htmlspecialchars($specialist['qualifications']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- About Section -->
                <?php if (!empty($specialist['profile'])): ?>
                <div class="col-12">
                    <div class="section">
                        <h5 class="mb-3">About</h5>
                        <div class="mb-3">
                            <?php echo $specialist['profile']; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-secondary" onclick="closeWindow()">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to close the window
        function closeWindow() {
            // If this is a popup window, try to close it
            if (window.opener) {
                window.opener.focus();
                window.close();
            }
            
            // If window.close() doesn't work or if this isn't a popup,
            // redirect to the specialists list
            if (!window.closed) {
                window.location.href = 'view_specialists_list.php';
            }
        }

        // Handle back button
        window.addEventListener('popstate', closeWindow);

        // Add click handlers to close buttons
        document.querySelectorAll('[onclick="window.close()"]').forEach(button => {
            button.onclick = closeWindow;
        });
    </script>
</body>
</html>