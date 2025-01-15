<?php
session_start();
require_once 'config/db.php';

// Check if specialist is logged in
if (!isset($_SESSION['specialist_id'])) {
    header('Location: specialist_login.php');
    exit();
}

$specialist_id = $_SESSION['specialist_id'];

try {
    // Fetch completed referrals
    $stmt = $db->prepare("
        SELECT r.*, p.name as patient_name, p.ic_number as patient_ic, gp.gp_name as gp_name 
        FROM referrals r 
        JOIN patients p ON r.patient_id = p.id 
        JOIN gp_doctors gp ON r.referring_gp_id = gp.id 
        WHERE r.specialist_id = :specialist_id 
        AND r.status = 'completed'
        ORDER BY r.updated_at DESC
    ");
    $stmt->execute(['specialist_id' => $specialist_id]);
    $completed_referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Completed Referrals error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Referrals - SJMC Specialist Portal</title>
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

        .referral-card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }

        .referral-card:hover {
            transform: translateY(-5px);
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

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            background-color: #28a745;
            color: white;
            display: inline-block;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="specialist_dashboard.php">
                <img src="uploads/sjmclogo.png" alt="SJMC Logo" style="height: 120px; margin-right: 10px;">
                SJMC Specialist Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="specialist_dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="specialist_logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <h2 class="mb-4">
            <i class="bi bi-check-circle me-2"></i>Completed Referrals
        </h2>

        <?php if (empty($completed_referrals)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No completed referrals found.
            </div>
        <?php else: ?>
            <?php foreach ($completed_referrals as $referral): ?>
                <div class="card referral-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <span class="status-badge">
                                    <i class="bi bi-check-circle me-2"></i>Completed
                                </span>
                                <h5 class="card-title">
                                    Patient: <?php echo htmlspecialchars($referral['patient_name']); ?> 
                                    (IC: <?php echo htmlspecialchars($referral['patient_ic']); ?>)
                                </h5>
                                <p class="card-text">
                                    <strong>Referring GP:</strong> <?php echo htmlspecialchars($referral['gp_name']); ?><br>
                                    <strong>Date Referred:</strong> <?php echo date('d M Y', strtotime($referral['created_at'])); ?><br>
                                    <strong>Date Completed:</strong> <?php echo date('d M Y', strtotime($referral['updated_at'])); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="view_referral.php?id=<?php echo $referral['id']; ?>" class="btn btn-primary">
                                    <i class="bi bi-eye me-2"></i>View Details
                                </a>
                                <button class="btn btn-secondary mt-2" 
                                        onclick="showFeedbackModal(<?php echo $referral['id']; ?>)">
                                    <i class="bi bi-chat-dots me-2"></i>Add Feedback
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <nav class="bottom-nav">
        <div class="d-flex align-items-center justify-content-center">
            <img src="uploads/qmed_logo.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            <span>Qmed.asia © 2024</span>
        </div>
    </nav>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Clinical Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="feedbackForm">
                        <input type="hidden" id="referral_id" name="referral_id">
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="physical_findings" class="form-label">Physical Findings</label>
                            <textarea class="form-control" id="physical_findings" name="physical_findings" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="investigation" class="form-label">Investigation</label>
                            <textarea class="form-control" id="investigation" name="investigation" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="further_plan" class="form-label">Further Plan</label>
                            <textarea class="form-control" id="further_plan" name="further_plan" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-body" id="successMessage" style="display: none;">
                    <div class="alert alert-success">
                        Clinical feedback submitted successfully!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitFeedback()">Submit Feedback</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showFeedbackModal(referralId) {
        document.getElementById('referral_id').value = referralId;
        document.getElementById('diagnosis').value = '';
        document.getElementById('physical_findings').value = '';
        document.getElementById('investigation').value = '';
        document.getElementById('further_plan').value = '';
        document.getElementById('successMessage').style.display = 'none';
        document.getElementById('feedbackForm').style.display = 'block';
        new bootstrap.Modal(document.getElementById('feedbackModal')).show();
    }

    function submitFeedback() {
        const formData = new FormData(document.getElementById('feedbackForm'));
        
        fetch('add_referral_feedback.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Server response:', text);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            if (data.error) {
                alert('Error: ' + (data.details || data.error));
                console.error('Server error:', data);
                return;
            }
            document.getElementById('feedbackForm').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
            setTimeout(() => {
                location.reload();
            }, 2000);
        })
        .catch(error => {
            alert('An error occurred: ' + error.message);
            console.error('Error:', error);
        });
    }
    </script>
</body>
</html>