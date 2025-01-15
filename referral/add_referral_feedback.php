<?php
// Ensure no output before this point
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors directly
header('Content-Type: application/json'); // Set JSON content type

session_start();
require_once 'config/db.php';

// Check if specialist is logged in
if (!isset($_SESSION['specialist_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$specialist_id = $_SESSION['specialist_id'];
$referral_id = filter_input(INPUT_POST, 'referral_id', FILTER_SANITIZE_NUMBER_INT);
$diagnosis = filter_input(INPUT_POST, 'diagnosis', FILTER_SANITIZE_STRING);
$physical_findings = filter_input(INPUT_POST, 'physical_findings', FILTER_SANITIZE_STRING);
$investigation = filter_input(INPUT_POST, 'investigation', FILTER_SANITIZE_STRING);
$further_plan = filter_input(INPUT_POST, 'further_plan', FILTER_SANITIZE_STRING);

if (!$referral_id || !$diagnosis || !$physical_findings || !$investigation || !$further_plan) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

try {
    // Verify the referral belongs to this specialist
    $stmt = $db->prepare("
        SELECT id FROM referrals 
        WHERE id = :referral_id 
        AND specialist_id = :specialist_id
        AND status = 'completed'
    ");
    $stmt->execute([
        'referral_id' => $referral_id,
        'specialist_id' => $specialist_id
    ]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized access']);
        exit();
    }

    // Check if feedback already exists
    $stmt = $db->prepare("SELECT id FROM referral_clinical_feedback WHERE referral_id = :referral_id");
    $stmt->execute(['referral_id' => $referral_id]);
    $existing_feedback = $stmt->fetch();

    if ($existing_feedback) {
        // Update existing feedback
        $stmt = $db->prepare("
            UPDATE referral_clinical_feedback 
            SET diagnosis = :diagnosis,
                physical_findings = :physical_findings,
                investigation = :investigation,
                further_plan = :further_plan
            WHERE referral_id = :referral_id
        ");
    } else {
        // Insert new feedback
        $stmt = $db->prepare("
            INSERT INTO referral_clinical_feedback 
            (referral_id, diagnosis, physical_findings, investigation, further_plan)
            VALUES 
            (:referral_id, :diagnosis, :physical_findings, :investigation, :further_plan)
        ");
    }
    
    $stmt->execute([
        'diagnosis' => $diagnosis,
        'physical_findings' => $physical_findings,
        'investigation' => $investigation,
        'further_plan' => $further_plan,
        'referral_id' => $referral_id
    ]);

    echo json_encode(['message' => 'Clinical feedback saved successfully']);

} catch(PDOException $e) {
    error_log("Add Clinical Feedback Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while saving clinical feedback',
        'details' => $e->getMessage()
    ]);
}
?>