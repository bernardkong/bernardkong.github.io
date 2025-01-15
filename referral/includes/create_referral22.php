<?php
// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$departments = [];
$doctors = [];
$specialists = [];
$message = '';
$error = '';

// Fetch departments
try {
    $stmt = $db->query("SELECT * FROM departments ORDER BY name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading departments";
}

// Fetch GPs (referring doctors)
try {
    $stmt = $db->query("
        SELECT gp.*, c.name as clinic_name 
        FROM gp_doctors gp
        LEFT JOIN clinics c ON gp.clinic_id = c.id 
        WHERE gp.is_active = 1 
        AND c.is_active = 1
        ORDER BY gp.gp_name
    ");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading doctors";
}

// Fetch clinics
try {
    $stmt = $db->query("SELECT * FROM clinics WHERE is_active = 1 ORDER BY name");
    $clinics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading clinics";
}

// Fetch specialists
try {
    $stmt = $db->query("
        SELECT s.*, d.name as department_name 
        FROM specialists s
        LEFT JOIN departments d ON s.department_id = d.id 
        WHERE s.status = 'active' 
        ORDER BY s.name
    ");
    
    if ($stmt === false) {
        error_log("Query failed: " . print_r($db->errorInfo(), true));
        throw new PDOException("Query failed");
    }
    
    $specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug output
    error_log("Specialists query executed. Found " . count($specialists) . " specialists");
    
} catch(PDOException $e) {
    error_log("Database error in specialists query: " . $e->getMessage());
    $error = "Error loading specialists";
    
    // Additional debug information
    error_log("Full error details: " . print_r($e, true));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Debug log
        error_log("Starting referral creation process");

        // First, insert patient data
        $stmt = $db->prepare("INSERT INTO patients (name, date_of_birth, ic_number, contact_number, email, address) 
                             VALUES (:name, :dob, :ic, :contact, :email, :address)");
        
        $patientParams = [
            'name' => $_POST['patient_name'],
            'dob' => $_POST['date_of_birth'],
            'ic' => $_POST['ic_number'],
            'contact' => $_POST['contact_number'],
            'email' => $_POST['email'],
            'address' => $_POST['address']
        ];
        
        // Debug log patient data
        error_log("Inserting patient data: " . print_r($patientParams, true));
        
        $stmt->execute($patientParams);
        $patient_id = $db->lastInsertId();
        
        error_log("Patient created with ID: " . $patient_id);

        // Debugging: Log the POST data
        error_log("POST Data: " . print_r($_POST, true));

        // Prepare referral parameters
        $referralParams = [
            'patient_id' => $patient_id,
            'gp_id' => $_POST['referring_gp_id'],
            'dept_id' => $_POST['department_id'],
            'specialist_id' => $_POST['specialist_id'] ?: null,
            'priority' => $_POST['priority_level'],
            'notes' => $_POST['notes']
        ];

        // Debugging: Log the referral parameters
        error_log("Referral Params: " . print_r($referralParams, true));

        // Insert referral data without specifying the id
        $stmt = $db->prepare("INSERT INTO referrals (patient_id, referring_gp_id, department_id, specialist_id, priority_level, status, notes) 
                             VALUES (:patient_id, :gp_id, :dept_id, :specialist_id, :priority, 'pending', :notes)");
        $stmt->execute($referralParams);
        $referral_id = $db->lastInsertId();
        
        error_log("Referral created with ID: " . $referral_id);

        // Award points for the new referral
        if ($referral_id) {
            try {
                require_once 'includes/points_system.php';
                error_log("Awarding points for GP ID: " . $_POST['referring_gp_id'] . " and referral ID: " . $referral_id);
                awardReferralPoints($db, $_POST['referring_gp_id'], $referral_id);
            } catch (Exception $e) {
                // Log points system error but don't fail the whole transaction
                error_log("Points system error: " . $e->getMessage());
            }
        }

        $db->commit();
        error_log("Transaction committed successfully");
        
        $message = "Referral created successfully!";
        
        // Redirect to the dashboard after successful creation
        header("Location: index.php?success=1");
        exit();

    } catch(PDOException $e) {
        $db->rollBack();
        error_log("Database error in referral creation: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Stack trace: " . $e->getTraceAsString());
        $error = "Error creating referral. Please try again. Error code: " . $e->getCode();
    } catch(Exception $e) {
        $db->rollBack();
        error_log("General error in referral creation: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $error = "An unexpected error occurred. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Manual Referral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            padding-bottom: 80px; /* Space for fixed footer */
        }
        
        .fixed-bottom-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 15px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .container {
            margin-bottom: 2rem; /* Add some space at the bottom of the container */
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2>Create Manual Referral</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Patient Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="patient_name" class="form-label">Patient Name</label>
                            <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_type" class="form-label">ID Type</label>
                            <select class="form-select" id="id_type" name="id_type" required>
                                <option value="ic">Malaysian IC</option>
                                <option value="passport">Passport</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ic_number" class="form-label">IC/Passport Number</label>
                            <input type="text" class="form-control" id="ic_number" name="ic_number" required>
                            <small class="text-muted" id="ic_format_help">For IC: Format YYMMDD-SS-NNNN</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="age_years" class="form-label">Age (Years)</label>
                            <input type="number" class="form-control" id="age_years" name="age_years" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="age_months" class="form-label">Months</label>
                            <input type="number" class="form-control" id="age_months" name="age_months" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="age_days" class="form-label">Days</label>
                            <input type="number" class="form-control" id="age_days" name="age_days" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <select class="form-select" id="country_code" name="country_code" style="min-width: 200px; width: auto;">
                                    <option value="+60" selected>Malaysia (+60)</option>
                                    <option value="+65">Singapore (+65)</option>
                                    <option value="+62">Indonesia (+62)</option>
                                    <option value="+66">Thailand (+66)</option>
                                    <option value="+673">Brunei (+673)</option>
                                    <option value="+63">Philippines (+63)</option>
                                    <option value="+95">Myanmar (+95)</option>
                                    <option value="+84">Vietnam (+84)</option>
                                    <option value="+856">Laos (+856)</option>
                                    <option value="+855">Cambodia (+855)</option>
                                </select>
                                <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                       placeholder="e.g., 123456789" required>
                            </div>
                            <div class="form-text" id="phone_format_help">Enter numbers only, without spaces or dashes</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Referral Details</h4>
                </div>
                <div class="card-body">
                    <!-- From Section -->
                    <div class="border-bottom mb-4 pb-3">
                        <h5 class="text-muted mb-3">From (Referring Clinic)</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="clinic" class="form-label fw-bold">Clinic</label>
                                <select class="form-select" id="clinic" name="clinic" required>
                                    <option value="">Select Referring Clinic</option>
                                    <?php foreach ($clinics as $clinic): ?>
                                        <option value="<?php echo $clinic['id']; ?>">
                                            <?php echo htmlspecialchars($clinic['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="referring_gp" class="form-label fw-bold">Referring Doctor</label>
                                <select class="form-select" id="referring_gp" name="referring_gp_id" required>
                                    <option value="">Select Referring Doctor</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>" data-clinic="<?php echo $doctor['clinic_id']; ?>">
                                            Dr. <?php echo htmlspecialchars($doctor['gp_name']); ?>
                                            <?php echo $doctor['clinic_name'] ? ' (' . htmlspecialchars($doctor['clinic_name']) . ')' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- To Section -->
                    <div class="border-bottom mb-4 pb-3">
                        <h5 class="text-muted mb-3">To (Hospital Department)</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label fw-bold">Referring To Department</label>
                                <select class="form-select" id="department" name="department_id" required>
                                    <option value="">Select Hospital Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="specialist" class="form-label fw-bold">Preferred Specialist (Optional)</label>
                                <select class="form-select" id="specialist" name="specialist_id">
                                    <option value="">Select Specialist (Optional)</option>
                                    <?php foreach ($specialists as $specialist): ?>
                                        <option value="<?php echo $specialist['id']; ?>" 
                                                data-department="<?php echo $specialist['department_id']; ?>"
                                                class="specialist-option">
                                            Dr. <?php echo htmlspecialchars($specialist['name']); ?>
                                            <?php echo $specialist['department_name'] ? ' (' . htmlspecialchars($specialist['department_name']) . ')' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Notes -->
                    <div>
                        <h5 class="text-muted mb-3">Clinical Information</h5>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label fw-bold">Clinical Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" required 
                                          placeholder="Please include:&#13;&#10;- Reason for referral&#13;&#10;- Relevant clinical findings&#13;&#10;- Current medications&#13;&#10;- Investigations and results"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed-bottom-footer">
                <div class="container">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Referral</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const icNumber = document.getElementById('ic_number');
        const dateOfBirth = document.getElementById('date_of_birth');
        const idType = document.getElementById('id_type');
        const ageYears = document.getElementById('age_years');
        const ageMonths = document.getElementById('age_months');
        const ageDays = document.getElementById('age_days');
        const icFormatHelp = document.getElementById('ic_format_help');

        function calculateAge(birthDate) {
            const today = new Date();
            const birth = new Date(birthDate);
            
            let years = today.getFullYear() - birth.getFullYear();
            let months = today.getMonth() - birth.getMonth();
            let days = today.getDate() - birth.getDate();

            if (days < 0) {
                months--;
                const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                days += lastMonth.getDate();
            }

            if (months < 0) {
                years--;
                months += 12;
            }

            return { years, months, days };
        }

        function formatIC(value) {
            value = value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 12) value = value.substr(0, 12);
            
            if (value.length >= 6) {
                value = value.substr(0, 6) + '-' + value.substr(6);
                if (value.length >= 9) {
                    value = value.substr(0, 9) + '-' + value.substr(9);
                }
            }
            return value;
        }

        function handleIcInput(e) {
            if (idType.value === 'ic') {
                let value = formatIC(e.target.value);
                e.target.value = value;

                const digits = value.replace(/\D/g, '');
                
                if (digits.length >= 6) {
                    // Extract and set birth date
                    const yearPrefix = parseInt(digits.substr(0, 2)) > 30 ? '19' : '20';
                    const year = yearPrefix + digits.substr(0, 2);
                    const month = digits.substr(2, 2);
                    const day = digits.substr(4, 2);
                    
                    const birthDate = `${year}-${month}-${day}`;
                    dateOfBirth.value = birthDate;
                    
                    // Calculate and set age
                    const age = calculateAge(birthDate);
                    ageYears.value = age.years;
                    ageMonths.value = age.months;
                    ageDays.value = age.days;
                }
            }
        }

        dateOfBirth.addEventListener('change', function() {
            const age = calculateAge(this.value);
            ageYears.value = age.years;
            ageMonths.value = age.months;
            ageDays.value = age.days;
        });

        idType.addEventListener('change', function() {
            if (this.value === 'passport') {
                dateOfBirth.readOnly = false;
                icNumber.placeholder = "Enter passport number";
                icFormatHelp.style.display = 'none';
            } else {
                dateOfBirth.readOnly = true;
                icNumber.placeholder = "YYMMDD-SS-NNNN";
                icFormatHelp.style.display = 'block';
            }
        });

        icNumber.addEventListener('input', handleIcInput);

        // Phone number formatting
        const contactNumber = document.getElementById('contact_number');
        const countryCode = document.getElementById('country_code');

        function formatPhoneNumber(value, country) {
            // Remove all non-digit characters
            let cleaned = value.replace(/\D/g, '');
            
            // Format based on country code
            switch(country) {
                case '+60': // Malaysia
                    // Remove leading '0' if present
                    if (cleaned.startsWith('0')) {
                        cleaned = cleaned.substring(1);
                    }
                    
                    // Mobile number format: +60 12-345 6789
                    if (cleaned.length > 9) {
                        cleaned = cleaned.substring(0, 9);
                    }
                    
                    if (cleaned.length >= 2) {
                        let formatted = cleaned.substring(0, 2);
                        if (cleaned.length > 2) {
                            formatted += '-' + cleaned.substring(2, 6);
                            if (cleaned.length > 6) {
                                formatted += ' ' + cleaned.substring(6);
                            }
                        }
                        return formatted;
                    }
                    break;
                    
                default:
                    // Generic formatting for other countries
                    if (cleaned.length > 15) {
                        cleaned = cleaned.substring(0, 15);
                    }
                    return cleaned;
            }
            return cleaned;
        }

        contactNumber.addEventListener('input', function(e) {
            let formatted = formatPhoneNumber(e.target.value, countryCode.value);
            e.target.value = formatted;
        });

        countryCode.addEventListener('change', function() {
            // Clear and reformat number when country changes
            contactNumber.value = formatPhoneNumber(contactNumber.value, this.value);
            
            // Update placeholder based on country
            switch(this.value) {
                case '+60':
                    contactNumber.placeholder = "e.g., 123456789";
                    break;
                case '+65':
                    contactNumber.placeholder = "e.g., 91234567";
                    break;
                default:
                    contactNumber.placeholder = "Enter phone number";
            }
        });

        // Validate phone number before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneNumber = contactNumber.value.replace(/\D/g, '');
            const selectedCountry = countryCode.value;
            
            let isValid = true;
            let message = '';

            switch(selectedCountry) {
                case '+60':
                    if (phoneNumber.length < 9 || phoneNumber.length > 10) {
                        isValid = false;
                        message = 'Malaysian phone numbers must be 9-10 digits long';
                    }
                    break;
                case '+65':
                    if (phoneNumber.length !== 8) {
                        isValid = false;
                        message = 'Singapore phone numbers must be 8 digits long';
                    }
                    break;
                default:
                    if (phoneNumber.length < 8) {
                        isValid = false;
                        message = 'Phone number is too short';
                    }
            }

            if (!isValid) {
                e.preventDefault();
                alert(message);
                contactNumber.focus();
            }
        });

        const clinicSelect = document.getElementById('clinic');
        const gpSelect = document.getElementById('referring_gp');
        const originalGpOptions = [...gpSelect.options];

        clinicSelect.addEventListener('change', function() {
            const selectedClinicId = this.value;
            
            // Reset GP select
            gpSelect.innerHTML = '<option value="">Select Referring Doctor</option>';
            
            // Filter and add relevant GPs
            if (selectedClinicId) {
                originalGpOptions.forEach(option => {
                    if (option.dataset.clinic === selectedClinicId) {
                        gpSelect.add(option.cloneNode(true));
                    }
                });
            }
        });

        // Style priority levels
        const prioritySelect = document.getElementById('priority_level');
        prioritySelect.addEventListener('change', function() {
            this.className = 'form-select';
            switch(this.value) {
                case 'emergency':
                    this.classList.add('text-danger');
                    break;
                case 'urgent':
                    this.classList.add('text-warning');
                    break;
            }
        });

        const departmentSelect = document.getElementById('department');
        const specialistSelect = document.getElementById('specialist');
        const originalSpecialistOptions = [...specialistSelect.options];

        departmentSelect.addEventListener('change', function() {
            const selectedDepartmentId = this.value;
            console.log('Selected Department ID:', selectedDepartmentId);
            
            // Reset specialist select
            specialistSelect.innerHTML = '<option value="">Select Specialist (Optional)</option>';
            
            // Filter and add relevant specialists
            if (selectedDepartmentId) {
                originalSpecialistOptions.forEach(option => {
                    console.log('Comparing:', {
                        'optionDepartment': option.dataset.department,
                        'selectedDepartment': selectedDepartmentId,
                        'match': option.dataset.department === selectedDepartmentId
                    });
                    
                    if (option.dataset.department === selectedDepartmentId) {
                        specialistSelect.add(option.cloneNode(true));
                    }
                });
            }
        });
    });
    </script>
</body>
</html>