<?php
session_start();
require_once 'config/db.php';

// Check if specialist is logged in
if (!isset($_SESSION['specialist_id'])) {
    header('Location: specialist_login.php');
    exit();
}

$specialist_id = $_SESSION['specialist_id'];
$referral_id = isset($_GET['id']) ? $_GET['id'] : null;
$error_message = '';
$success_message = '';
$referral = null;

try {
    // Fetch referral details
    $stmt = $db->prepare("
        SELECT r.*, p.name as patient_name, p.ic_number as patient_ic, 
               gp.gp_name as gp_name, d.name as department_name
        FROM referrals r 
        JOIN patients p ON r.patient_id = p.id 
        JOIN gp_doctors gp ON r.referring_gp_id = gp.id
        JOIN departments d ON r.department_id = d.id
        WHERE r.id = :referral_id AND r.specialist_id = :specialist_id
    ");
    $stmt->execute([
        'referral_id' => $referral_id,
        'specialist_id' => $specialist_id
    ]);
    $referral = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_status = $_POST['status'];
        $remarks = $_POST['remarks'];
        
        $update_stmt = $db->prepare("
            UPDATE referrals 
            SET status = :status, 
                remarks = :remarks,
                updated_at = NOW()
            WHERE id = :referral_id AND specialist_id = :specialist_id
        ");
        
        if ($update_stmt->execute([
            'status' => $new_status,
            'remarks' => $remarks,
            'referral_id' => $referral_id,
            'specialist_id' => $specialist_id
        ])) {
            $success_message = "Referral status updated successfully!";
            // Refresh referral data
            $stmt->execute([
                'referral_id' => $referral_id,
                'specialist_id' => $specialist_id
            ]);
            $referral = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = "Failed to update referral status.";
        }
    }
} catch(PDOException $e) {
    error_log("Update Referral Error: " . $e->getMessage());
    $error_message = "An error occurred while processing your request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Referral - SJMC Specialist Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* ... (keep existing styles from specialist_pending_referrals.php) ... */
        .status-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- ... (keep existing navbar) ... -->

    <div class="container mt-4 mb-5">
        <h2 class="mb-4">
            <i class="bi bi-pencil-square me-2"></i>Update Referral Status
        </h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($referral): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Referral Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Patient:</strong> <?php echo htmlspecialchars($referral['patient_name']); ?></p>
                            <p><strong>IC Number:</strong> <?php echo htmlspecialchars($referral['patient_ic']); ?></p>
                            <p><strong>Referring GP:</strong> <?php echo htmlspecialchars($referral['gp_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Department:</strong> <?php echo htmlspecialchars($referral['department_name']); ?></p>
                            <p><strong>Current Status:</strong> 
                                <span class="badge bg-<?php echo $referral['status'] === 'pending' ? 'warning' : 
                                    ($referral['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                    <?php echo ucfirst(htmlspecialchars($referral['status'])); ?>
                                </span>
                            </p>
                            <p><strong>Date Referred:</strong> <?php echo date('d M Y', strtotime($referral['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="status-form">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="status" class="form-label">Update Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="approved" <?php echo $referral['status'] === 'approved' ? 'selected' : ''; ?>>Approve</option>
                            <option value="rejected" <?php echo $referral['status'] === 'rejected' ? 'selected' : ''; ?>>Reject</option>
                            <option value="completed" <?php echo $referral['status'] === 'completed' ? 'selected' : ''; ?>>Complete</option>
                            <option value="no_show" <?php echo $referral['status'] === 'no_show' ? 'selected' : ''; ?>>No Show</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($referral['remarks'] ?? ''); ?></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="specialist_pending_referrals.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>Referral not found or you don't have permission to update it.
            </div>
        <?php endif; ?>
    </div>

    <!-- ... (keep existing bottom nav) ... -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>