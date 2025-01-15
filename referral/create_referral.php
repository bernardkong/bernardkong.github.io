<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get database connection
$db = require_once 'config/db.php';
if (!$db) {
    die("Database connection failed");
}

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

// Initialize variables
$departments = [];
$specialists = [];
$message = '';
$error = '';

// Fetch GP details
try {
    $stmt = $db->prepare("SELECT gp.*, c.name as clinic_name FROM gp_doctors gp LEFT JOIN clinics c ON gp.clinic_id = c.id WHERE gp.id = ?");
    $stmt->execute([$_SESSION['gp_id']]);
    $gp = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching GP details: " . $e->getMessage());
    $error = "Error loading GP details";
}

// Fetch departments
try {
    $stmt = $db->query("SELECT * FROM departments ORDER BY name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading departments";
}

// Fetch specialists
try {
    $stmt = $db->query("SELECT s.*, d.name as department_name FROM specialists s LEFT JOIN departments d ON s.department_id = d.id WHERE s.status = 'active' ORDER BY s.name");
    $specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading specialists";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Insert patient data
        $stmt = $db->prepare("INSERT INTO patients (name, date_of_birth, ic_number, contact_number, email, address) VALUES (:name, :dob, :ic, :contact, :email, :address)");
        $stmt->execute([
            'name' => $_POST['patient_name'],
            'dob' => $_POST['birthDate'],
            'ic' => $_POST['patientIC'],
            'contact' => $_POST['contact_number'],
            'email' => $_POST['email'],
            'address' => $_POST['address']
        ]);

        $patient_id = $db->lastInsertId();

        // Create the referral
        $stmt = $db->prepare("INSERT INTO referrals (patient_id, referring_gp_id, department_id, specialist_id, priority_level, status, notes, created_at) VALUES (:patient_id, :gp_id, :dept_id, :specialist_id, :priority, 'pending', :notes, NOW())");
        $stmt->execute([
            'patient_id' => $patient_id,
            'gp_id' => $_SESSION['gp_id'],
            'dept_id' => $_POST['department'],
            'specialist_id' => $_POST['specialist'] ?? null,
            'priority' => $_POST['priority_level'],
            'notes' => $_POST['notes']
        ]);

        $db->commit();
        $_SESSION['success_message'] = "Referral created successfully!";
        header("Location: index.php"); // Redirect to index.php after successful submission
        exit();

    } catch(PDOException $e) {
        $db->rollBack();
        error_log("Error creating referral: " . $e->getMessage());
        $error = "Error creating referral. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Referral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .age-inputs {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?> <!-- Include the navbar -->

    <div class="container">
        <h1>Create New Referral</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="patient_name" class="form-label">Patient Name</label>
                <input type="text" class="form-control" id="patient_name" name="patient_name" required>
            </div>
            <div class="mb-3">
                <label for="idType" class="form-label">ID Type</label>
                <select id="idType" name="idType" class="form-select" required>
                    <option value="IC" selected>IC</option>
                    <option value="Passport">Passport</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="patientIC" class="form-label">IC/Passport Number</label>
                <input type="text" class="form-control" id="patientIC" name="patientIC" placeholder="Format: YYMMDD-SS-NNNN" required>
            </div>
            <div class="mb-3">
                <label for="birthDate" class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="birthDate" name="birthDate" required>
            </div>
            <div class="mb-3">
                <label>Age:</label>
                <div class="age-inputs">
                    <input type="number" id="patientAge" name="patientAge" readonly class="form-control" placeholder="Years">
                    <input type="number" id="ageMonths" name="ageMonths" readonly class="form-control" placeholder="Months">
                    <input type="number" id="ageDays" name="ageDays" readonly class="form-control" placeholder="Days">
                </div>
            </div>
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" id="department" name="department" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="specialist" class="form-label">Preferred Specialist (Optional)</label>
                <select class="form-select" id="specialist" name="specialist">
                    <option value="">Select Specialist</option>
                    <?php foreach ($specialists as $spec): ?>
                        <option value="<?php echo $spec['id']; ?>" data-department="<?php echo $spec['department_id']; ?>"><?php echo htmlspecialchars($spec['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="priority_level" class="form-label">Priority Level</label>
                <select class="form-select" id="priority_level" name="priority_level" required>
                    <option value="routine">Routine</option>
                    <option value="urgent">Urgent</option>
                    <option value="emergency">Emergency</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Clinical Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Referral</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add this function to validate IC format
        document.getElementById('idType').addEventListener('change', function() {
            const icInput = document.getElementById('patientIC');
            if (this.value === 'IC') {
                icInput.pattern = '\\d{6}-\\d{2}-\\d{4}';
                icInput.placeholder = 'Format: YYMMDD-SS-NNNN';
                icInput.title = 'Please enter IC in format: YYMMDD-SS-NNNN (e.g., 870624-22-5044)';
            } else {
                icInput.removeAttribute('pattern');
                icInput.placeholder = 'Enter Passport Number';
                icInput.title = 'Enter your passport number';
            }
        });

        // Add input formatting for IC
        document.getElementById('patientIC').addEventListener('input', function(e) {
            if (document.getElementById('idType').value === 'IC') {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                if (value.length > 12) {
                    value = value.substr(0, 12);
                }
                if (value.length >= 6) {
                    value = value.substr(0, 6) + '-' + value.substr(6);
                }
                if (value.length >= 9) {
                    value = value.substr(0, 9) + '-' + value.substr(9);
                }
                e.target.value = value;

                // Extract birth date if 6 digits are entered
                if (value.length >= 6) {
                    const yearPrefix = parseInt(value.substr(0, 2)) > 23 ? '19' : '20'; // Assume 19xx for years > 23
                    const year = yearPrefix + value.substr(0, 2);
                    const month = value.substr(2, 2);
                    const day = value.substr(4, 2);
                    
                    const birthDate = `${year}-${month}-${day}`;
                    document.getElementById('birthDate').value = birthDate;
                    
                    // Trigger the age calculation
                    const event = new Event('change');
                    document.getElementById('birthDate').dispatchEvent(event);
                }

                // Extract gender if 12 digits are entered (last digit is odd for male, even for female)
                if (value.length === 12) {
                    const lastDigit = parseInt(value.substr(11, 1));
                    document.getElementById('patientGender').value = lastDigit % 2 === 1 ? 'Male' : 'Female';
                }
            }
        });

        // Update the age calculation function
        document.getElementById('birthDate').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            
            let ageYears = today.getFullYear() - birthDate.getFullYear();
            let ageMonths = today.getMonth() - birthDate.getMonth();
            let ageDays = today.getDate() - birthDate.getDate();

            if (ageDays < 0) {
                ageMonths--;
                // Get days in last month
                const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                ageDays += lastMonth.getDate();
            }
            
            if (ageMonths < 0) {
                ageYears--;
                ageMonths += 12;
            }

            // Always show all fields
            document.getElementById('patientAge').value = ageYears;
            document.getElementById('ageMonths').value = ageMonths;
            document.getElementById('ageDays').value = ageDays;
        });

        // Trigger the IC format setup when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const icInput = document.getElementById('patientIC');
            icInput.pattern = '\\d{6}-\\d{2}-\\d{4}';
            icInput.title = 'Please enter IC in format: YYMMDD-SS-NNNN (e.g., 870624-22-5044)';
        });

        // Show specialists based on selected department
        document.getElementById('department').addEventListener('change', function() {
            const selectedDepartmentId = this.value;
            const specialistSelect = document.getElementById('specialist');
            const options = specialistSelect.querySelectorAll('option');

            // Reset specialist dropdown
            specialistSelect.innerHTML = '<option value="">Select Specialist</option>';

            options.forEach(option => {
                if (option.dataset.department === selectedDepartmentId) {
                    specialistSelect.appendChild(option.cloneNode(true));
                }
            });
        });
    </script>
</body>
</html>