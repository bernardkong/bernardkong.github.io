<?php
session_start();

// Debug output
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add logging
file_put_contents('debug.log', "Script started: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

try {
    // Test TCPDF inclusion
    require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
    file_put_contents('debug.log', "TCPDF loaded successfully\n", FILE_APPEND);
    
    // Test database connection
    require_once 'config/db.php';
    file_put_contents('debug.log', "Database connected\n", FILE_APPEND);
    
    // Log the received ID
    file_put_contents('debug.log', "Referral ID: " . $_GET['id'] . "\n", FILE_APPEND);
    
    // Check if GP is logged in
    if (!isset($_SESSION['gp_id'])) {
        header("Location: gp_login.php");
        exit();
    }

    // Check if referral ID is provided
    if (!isset($_GET['id'])) {
        header("Location: gp_view_referrals.php");
        exit();
    }

    // Fetch GP doctor, patient, and specialist details
    $stmt = $db->prepare("
        SELECT 
            g.gp_name,
            g.phone,
            g.certification,
            g.mmc_no,
            c.name as clinic_name,
            p.name as patient_name,
            p.date_of_birth,
            p.ic_number,
            p.contact_number as patient_phone,
            p.email as patient_email,
            p.address as patient_address,
            s.name as specialist_name,
            s.nsr_number,
            s.specialization,
            d.name as department_name,
            r.created_at
        FROM gp_doctors g
        LEFT JOIN clinics c ON g.clinic_id = c.id
        LEFT JOIN referrals r ON r.referring_gp_id = g.id
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN specialists s ON r.specialist_id = s.id
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE g.id = ? AND r.id = ?
    ");
    
    $stmt->execute([$_SESSION['gp_id'], $_GET['id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception("Doctor or patient details not found");
    }

    // Format the date of birth
    $dob = date('d/m/Y', strtotime($data['date_of_birth']));
    $date = date('d/m/Y', strtotime($data['created_at'] ?? 'now'));

    // Clear any output before PDF generation
    ob_clean();

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('SJMC');
    $pdf->SetAuthor('SJMC GP Portal');
    $pdf->SetTitle('Medical Referral Letter');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(20, 20, 20);
    
    // Important: Set auto page break to false to prevent empty second page
    $pdf->SetAutoPageBreak(false);

    // Add a new page for the first page content
    $pdf->AddPage();

    // Add letterhead
    $letterheadPath = 'templates/letterhead.png';
    $pdf->Image($letterheadPath, 0, 0, 210);

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Move starting position down to account for letterhead
    $pdf->SetY(60);

    // Create the HTML content
    $html = <<<EOD
    <p style="text-align:right;">Date: {$date}</p>
    
    <p>From:<br>
    Dr. {$data['gp_name']}<br>
    {$data['certification']}<br>
    MMC Registration No: {$data['mmc_no']}<br>
    {$data['clinic_name']}<br>
    Contact: {$data['phone']}</p>

    <p>To:<br>
    {$data['specialist_name']}<br>
    NSR Number: {$data['nsr_number']}<br>
    {$data['specialization']}<br>
    Department: {$data['department_name']}</p>

    <p>Patient Details:<br>
    Name: {$data['patient_name']}<br>
    Date of Birth: {$dob}<br>
    IC/Passport: {$data['ic_number']}<br>
    Contact: {$data['patient_phone']}<br>
    Email: {$data['patient_email']}<br>
    Address: {$data['patient_address']}</p>
    EOD;

    // Write the HTML content
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // Add a new page for the template
    $pdf->AddPage();
    
    // Add the template PNG file
    $templatePath = 'templates/sjmc_page2.png';
    $pdf->Image($templatePath, 21, 29.7, 210); // Moved right 21mm, down 29.7mm

    // Update the filename generation
    $filename = 'Referral_Letter_' . $data['patient_name'] . '_' . date('Y-m-d') . '.pdf';

    // Clean filename of special characters and ensure .pdf extension
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

    // Make sure the extension is correct
    if (!str_ends_with(strtolower($filename), '.pdf')) {
        $filename .= '.pdf';
    }

    // Close and output PDF document
    $pdf->Output($filename, 'D'); // 'D' means download
    exit();

    // Log success (though this might not execute due to the PDF download)
    file_put_contents('debug.log', "PDF generated successfully\n", FILE_APPEND);

} catch (Exception $e) {
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    $_SESSION['pdf_error'] = "Error generating PDF: " . $e->getMessage();
    header("Location: gp_view_referrals.php");
    exit();
}