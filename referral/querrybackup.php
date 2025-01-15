// Update the query to match actual columns
$specialist_query = "SELECT s.*, d.name as department_name 
                    FROM specialists s 
                    LEFT JOIN departments d ON s.department_id = d.id 
                    WHERE s.id = ?";
$stmt = $db->prepare($specialist_query);
$stmt->execute([$_SESSION['specialist_id']]);
$specialist = $stmt->fetch(PDO::FETCH_ASSOC);

// Get pending referrals with GP and patient information
$pending_referrals_query = $db->prepare("
    SELECT r.*, 
           p.name as patient_name,
           p.ic_number,
           g.gp_name,
           d.name as department_name,
           c.name as clinic_name
    FROM referrals r
    JOIN patients p ON r.patient_id = p.id 
    JOIN gp_doctors g ON r.referring_gp_id = g.id
    JOIN departments d ON r.department_id = d.id
    JOIN clinics c ON g.clinic_id = c.id
    WHERE r.specialist_id = ? 
    AND r.status = 'pending'
    ORDER BY r.created_at DESC
");

$pending_referrals_query->execute([$specialist_id]);
$pending_referrals = $pending_referrals_query->fetchAll();

$pending_count = count($pending_referrals);

// Get total referrals for this specialist
$total_referrals_query = $db->prepare("
    SELECT COUNT(*) as total 
    FROM referrals 
    WHERE specialist_id = ?
");