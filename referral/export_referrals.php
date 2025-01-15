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

// Fetch recent referrals
try {
    $referrals_query = $db->query("SELECT 
        r.*, 
        d.name as department_name,
        p.name as patient_name,
        p.ic_number,
        g.gp_name as referring_doctor,
        s.name as specialist_name
        FROM referrals r
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN gp_doctors g ON r.referring_gp_id = g.id
        LEFT JOIN specialists s ON r.specialist_id = s.id
        ORDER BY r.created_at DESC");

    $recent_referrals = $referrals_query->fetchAll(PDO::FETCH_ASSOC);

} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while fetching data.");
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="referrals.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, ['ID', 'Patient Name', 'Department', 'Priority', 'Status', 'Date']);

// Write data rows
foreach ($recent_referrals as $referral) {
    fputcsv($output, [
        $referral['id'],
        $referral['patient_name'],
        $referral['department_name'],
        $referral['priority_level'],
        $referral['status'],
        date('M d, Y H:i', strtotime($referral['created_at']))
    ]);
}

// Close output stream
fclose($output);
exit();
?>