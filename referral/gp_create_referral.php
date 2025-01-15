<?php
session_start();

// Check if GP is logged in
if (!isset($_SESSION['gp_id'])) {
    header("Location: gp_login.php");
    exit();
}

// Get database connection
require_once 'config/db.php';

// Include points system
require_once 'includes/points_system.php';

// Initialize variables
$departments = [];
$specialists = [];
$message = '';
$error = '';

// Fetch GP details
try {
    $stmt = $db->prepare("
        SELECT gp.*, c.name as clinic_name 
        FROM gp_doctors gp
        LEFT JOIN clinics c ON gp.clinic_id = c.id 
        WHERE gp.id = ?
    ");
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
    $stmt = $db->query("
        SELECT s.*, d.name as department_name, s.specialization 
        FROM specialists s
        LEFT JOIN departments d ON s.department_id = d.id 
        WHERE s.status = 'active' 
        ORDER BY s.name
    ");
    $specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Error loading specialists";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Handle file upload if present
        $referral_letter_path = null;
        if (!empty($_FILES['referral_letter']['name'])) {
            $upload_dir = 'uploads/referral_letters/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['referral_letter']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('ref_') . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['referral_letter']['tmp_name'], $target_path)) {
                $referral_letter_path = $target_path;
            }
        }

        // Insert patient data
        $stmt = $db->prepare("INSERT INTO patients (name, date_of_birth, ic_number, contact_number, email, address) 
                             VALUES (:name, :dob, :ic, :contact, :email, :address)");
        
        // Combine country code and phone number
        $country_code = $_POST['country_code'];
        $phone_number = $_POST['contact_number'];
        $full_phone_number = $country_code . $phone_number;
        
        $stmt->execute([
            'name' => $_POST['patient_name'],
            'dob' => $_POST['birthDate'],
            'ic' => $_POST['patientIC'],
            'contact' => $full_phone_number,
            'email' => $_POST['email'],
            'address' => $_POST['address']
        ]);

        $patient_id = $db->lastInsertId();

        // Create the referral
        $stmt = $db->prepare("
            INSERT INTO referrals (
                patient_id, 
                referring_gp_id, 
                department_id, 
                specialist_id, 
                priority_level,
                payment_mode, 
                status, 
                clinical_history,
                diagnosis,
                investigation_results,
                remarks,
                referral_letter_path, 
                created_at
            ) 
            VALUES (
                :patient_id, 
                :gp_id, 
                :dept_id, 
                :specialist_id, 
                :priority,
                :payment_mode, 
                'pending', 
                :clinical_history,
                :diagnosis,
                :investigation_results,
                :remarks,
                :referral_letter_path, 
                NOW()
            )
        ");

        $specialist_id = !empty($_POST['specialist']) ? $_POST['specialist'] : null;
        
        $stmt->execute([
            'patient_id' => $patient_id,
            'gp_id' => $_SESSION['gp_id'],
            'dept_id' => $_POST['department'],
            'specialist_id' => $specialist_id,
            'priority' => $_POST['priority_level'],
            'payment_mode' => $_POST['payment_mode'],
            'clinical_history' => $_POST['clinical_history'],
            'diagnosis' => $_POST['diagnosis'],
            'investigation_results' => $_POST['investigation_results'],
            'remarks' => $_POST['remarks'],
            'referral_letter_path' => $referral_letter_path
        ]);

        $referral_id = $db->lastInsertId();

        // Award points for creating a referral
        if (!awardReferralPoints($db, $_SESSION['gp_id'], $referral_id)) {
            error_log("Failed to award points for referral creation to GP ID: " . $_SESSION['gp_id']);
        }

        $db->commit();
        
        // Set success message
        $_SESSION['success_message'] = "Referral created successfully! You earned 1 point for creating this referral.";
        
        // Redirect to GP dashboard
        header("Location: gp_dashboard.php?success=1");
        exit();

    } catch(PDOException $e) {
        $db->rollBack();
        error_log("Error creating referral: " . $e->getMessage());
        $error = "Error creating referral. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Referral</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1600px;
            margin: 30px auto;
            padding: 20px;
        }

        .form-sections {
            display: grid;
            grid-template-columns: 1fr 1fr 2fr;
            gap: 30px;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .section:hover {
            transform: translateY(-5px);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            color: var(--primary-color);
            font-weight: 500;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .priority-select {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .priority-option {
            flex: 1;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .priority-option.routine {
            background: #e9ecef;
        }

        .priority-option.urgent {
            background: #fff3cd;
        }

        .priority-option.emergency {
            background: #f8d7da;
        }

        .priority-option.selected {
            transform: scale(0.95);
            border: 2px solid var(--secondary-color);
        }

        .btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #fff;
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        .form-actions {
            margin-top: 30px;
            text-align: center;
            gap: 15px;
            display: flex;
            justify-content: center;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 10px;
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: #f8f9fa;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .age-inputs {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .age-inputs input {
            width: 80px;
            text-align: center;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 8px;
        }

        /* Add a nice header section similar to dashboard */
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .header-section h1 {
            color: white;
            margin: 0;
        }

        .header-section p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        /* Add these new styles */
        .input-group .btn-info {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            margin-left: 10px;
        }

        .input-group .btn-info:hover {
            background-color: #2980b9;
        }

        .input-group .btn-info:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-hospital-user me-2"></i>Create New Referral</h1>
                    <p>Fill in the patient information and referral details below</p>
                </div>
                <a href="templates/sjmc-gp-referral-form.pdf" class="btn btn-light" download>
                    <i class="fas fa-download me-2"></i>Download Referral Template
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="needs-validation" enctype="multipart/form-data" novalidate>
            <div class="form-sections">
                <!-- Patient Information Section -->
                <div class="section">
                    <h2 class="mb-4">Patient Information</h2>
                    <div class="form-group">
                        <label class="form-label" for="patient_name">Patient Name</label>
                        <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                    </div>

                    <div class="form-group">
                        <label for="idType">ID Type</label>
                        <select id="idType" name="idType" required>
                            <option value="IC" selected>IC</option>
                            <option value="Passport">Passport</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="patientIC">IC/Passport Number:</label>
                        <input type="text" id="patientIC" name="patientIC" required>
                    </div>

                    <div class="form-group">
                        <label for="birthDate">Date of Birth:</label>
                        <input type="date" id="birthDate" name="birthDate" required>
                    </div>

                    <div class="form-group">
                        <label>Age:</label>
                        <div class="age-inputs">
                            <input type="number" id="patientAge" name="patientAge" readonly> Years
                            <input type="number" id="ageMonths" name="ageMonths" readonly> Months
                            <input type="number" id="ageDays" name="ageDays" readonly> Days
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number:</label>
                        <div class="input-group">
                            <select class="form-select" id="country_code" name="country_code" style="max-width: 200px;">
                                <option value="+60" selected>Malaysia (+60)</option>
                                <option value="+65">Singapore (+65)</option>
                                <option value="+62">Indonesia (+62)</option>
                                <option value="+66">Thailand (+66)</option>
                                <option value="+63">Philippines (+63)</option>
                                <option value="+673">Brunei (+673)</option>
                                <option value="+95">Myanmar (+95)</option>
                                <option value="+856">Laos (+856)</option>
                                <option value="+855">Cambodia (+855)</option>
                                <option value="+84">Vietnam (+84)</option>
                            </select>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                   placeholder="Example: 123456789" 
                                   pattern="[0-9]{9,10}" 
                                   title="Please enter a valid phone number" 
                                   required>
                        </div>
                        <small class="form-text text-muted">Enter number without leading zero. Example: 123456789</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email">
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                    </div>
                </div>

                <!-- Referral Details Section -->
                <div class="section">
                    <h2 class="mb-4">Referral Details</h2>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select class="form-select" id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="specialist">Preferred Specialist (Optional):</label>
                        <div class="input-group">
                            <select id="specialist" name="specialist" class="form-select" disabled>
                                <option value="">Select Specialist</option>
                            </select>
                            <button type="button" class="btn btn-info" id="viewProfileBtn" disabled>
                                <i class="fas fa-user-md"></i> View Profile
                            </button>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="preferred_date">Preferred Date:</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="preferred_date" name="preferred_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                            <button type="button" class="btn btn-info" id="checkPreferredDate">
                                Check Availability
                            </button>
                        </div>
                        <div id="preferred_date_slots" class="mt-2"></div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="alternative_date">Alternative Date:</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="alternative_date" name="alternative_date"
                                   min="<?php echo date('Y-m-d'); ?>" required>
                            <button type="button" class="btn btn-info" id="checkAlternativeDate">
                                Check Availability
                            </button>
                        </div>
                        <div id="alternative_date_slots" class="mt-2"></div>
                    </div>

                    <div class="form-group">
                        <label>Priority Level</label>
                        <div class="priority-select">
                            <div class="priority-option routine" data-value="routine">
                                <i class="fas fa-clock"></i>
                                <span>Routine</span>
                            </div>
                            <div class="priority-option urgent" data-value="urgent">
                                <i class="fas fa-exclamation"></i>
                                <span>Urgent</span>
                            </div>
                            <div class="priority-option emergency" data-value="emergency">
                                <i class="fas fa-ambulance"></i>
                                <span>Emergency</span>
                            </div>
                        </div>
                        <input type="hidden" name="priority_level" id="priority_level" required>
                    </div>

                    <div class="form-group">
                        <label>Mode of Payment</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_mode" id="selfPay" value="self_pay" required>
                            <label class="form-check-label" for="selfPay">
                                Self Pay
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_mode" id="insurance" value="insurance">
                            <label class="form-check-label" for="insurance">
                                Insurance or TPA
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_mode" id="clinicBill" value="clinic_bill">
                            <label class="form-check-label" for="clinicBill">
                                Bill my clinic
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="referral_letter">Upload Referral Letter (Optional):</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="referral_letter" name="referral_letter" accept=".pdf,.doc,.docx">
                            <button type="button" class="btn btn-outline-secondary" id="clearFileBtn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX. Max size: 5MB</small>
                    </div>
                </div>

                <!-- New Clinical Info Section -->
                <div class="section">
                    <h2 class="mb-4">Clinical Info</h2>
                    
                    <div class="form-group">
                        <label for="clinical_history">Clinical History & Physical Findings:</label>
                        <textarea class="form-control" id="clinical_history" name="clinical_history" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="diagnosis">Diagnosis:</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="investigation_results">Investigation Results/Requested:</label>
                        <textarea class="form-control" id="investigation_results" name="investigation_results" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks:</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Referral</button>
                <a href="gp_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Add Bootstrap JS and its dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Keep your existing JavaScript code -->
    <script>
        // Constants for validation patterns and messages
        const VALIDATION = {
            IC: {
                pattern: '\\d{6}-\\d{2}-\\d{4}',
                placeholder: 'Format: YYMMDD-SS-NNNN',
                title: 'Please enter IC in format: YYMMDD-SS-NNNN (e.g., 870624-22-5044)'
            },
            PASSPORT: {
                placeholder: 'Enter Passport Number',
                title: 'Enter your passport number'
            }
        };

        // Utility functions
        const formatIC = (value) => {
            value = value.replace(/\D/g, '').substr(0, 12);
            if (value.length >= 6) value = value.substr(0, 6) + '-' + value.substr(6);
            if (value.length >= 9) value = value.substr(0, 9) + '-' + value.substr(9);
            return value;
        };

        const calculateAge = (birthDate) => {
            const today = new Date();
            const birth = new Date(birthDate);
            
            let years = today.getFullYear() - birth.getFullYear();
            let months = today.getMonth() - birth.getMonth();
            let days = today.getDate() - birth.getDate();

            // Adjust for negative days
            if (days < 0) {
                months--;
                const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                days += lastMonth.getDate();
            }

            // Adjust for negative months
            if (months < 0) {
                years--;
                months += 12;
            }

            return { years, months, days };
        };

        const extractBirthDateFromIC = (icValue) => {
            if (icValue.length >= 6) {
                const yearPrefix = parseInt(icValue.substr(0, 2)) > 23 ? '19' : '20';
                const year = yearPrefix + icValue.substr(0, 2);
                const month = icValue.substr(2, 2);
                const day = icValue.substr(4, 2);
                return `${year}-${month}-${day}`;
            }
            return null;
        };

        // Event Handlers
        const handleIDTypeChange = (e) => {
            const icInput = document.getElementById('patientIC');
            const isIC = e.target.value === 'IC';
            
            if (isIC) {
                icInput.pattern = VALIDATION.IC.pattern;
                icInput.placeholder = VALIDATION.IC.placeholder;
                icInput.title = VALIDATION.IC.title;
            } else {
                icInput.removeAttribute('pattern');
                icInput.placeholder = VALIDATION.PASSPORT.placeholder;
                icInput.title = VALIDATION.PASSPORT.title;
            }
        };

        const handleICInput = (e) => {
            if (document.getElementById('idType').value === 'IC') {
                const formattedValue = formatIC(e.target.value);
                e.target.value = formattedValue;

                // Extract and set birth date
                const birthDate = extractBirthDateFromIC(formattedValue);
                if (birthDate) {
                    const birthDateInput = document.getElementById('birthDate');
                    birthDateInput.value = birthDate;
                    birthDateInput.dispatchEvent(new Event('change'));
                }
            }
        };

        const handleBirthDateChange = (e) => {
            try {
                const { years, months, days } = calculateAge(e.target.value);
                
                document.getElementById('patientAge').value = years;
                document.getElementById('ageMonths').value = months;
                document.getElementById('ageDays').value = days;
            } catch (error) {
                console.error('Error calculating age:', error);
            }
        };

        // Initialize event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Set up event listeners
            document.getElementById('idType')?.addEventListener('change', handleIDTypeChange);
            document.getElementById('patientIC')?.addEventListener('input', handleICInput);
            document.getElementById('birthDate')?.addEventListener('change', handleBirthDateChange);

            // Initial setup for IC input
            const icInput = document.getElementById('patientIC');
            if (icInput) {
                icInput.pattern = VALIDATION.IC.pattern;
                icInput.title = VALIDATION.IC.title;
            }
        });

        // Priority selection handling
        document.querySelectorAll('.priority-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.priority-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update hidden input
                document.getElementById('priority_level').value = this.dataset.value;
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const contactInput = document.getElementById('contact_number');
            
            contactInput.addEventListener('input', function(e) {
                // Remove any non-numeric characters
                this.value = this.value.replace(/\D/g, '');
                
                // Limit length to 10 digits
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });
            
            // Validate phone number format
            contactInput.addEventListener('blur', function() {
                const phoneNumber = this.value;
                if (phoneNumber.length < 9 || phoneNumber.length > 10) {
                    this.setCustomValidity('Phone number must be 9-10 digits');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department');
            const specialistSelect = document.getElementById('specialist');
            const viewProfileBtn = document.getElementById('viewProfileBtn');
            
            // Store all specialists data
            const specialists = <?php echo json_encode($specialists); ?>;
            
            departmentSelect.addEventListener('change', function() {
                const selectedDepartmentId = parseInt(this.value);
                
                // Clear and disable specialist dropdown if no department is selected
                if (!selectedDepartmentId) {
                    specialistSelect.innerHTML = '<option value="">Select Specialist</option>';
                    specialistSelect.disabled = true;
                    viewProfileBtn.disabled = true;
                    return;
                }
                
                // Filter specialists by department
                const filteredSpecialists = specialists.filter(
                    spec => spec.department_id === selectedDepartmentId
                );
                
                // Enable specialist dropdown and update options
                specialistSelect.disabled = false;
                specialistSelect.innerHTML = '<option value="">Select Specialist</option>';
                
                if (filteredSpecialists.length === 0) {
                    // No specialists found for this department
                    specialistSelect.innerHTML = '<option value="">No specialists in this department yet</option>';
                    specialistSelect.disabled = true;
                    viewProfileBtn.disabled = true;
                } else {
                    // Add specialists to dropdown
                    filteredSpecialists.forEach(spec => {
                        const option = document.createElement('option');
                        option.value = spec.id;
                        option.textContent = `${spec.name} (${spec.specialization})`;
                        specialistSelect.appendChild(option);
                    });
                }
            });

            // Add event listener for specialist selection
            specialistSelect.addEventListener('change', function() {
                viewProfileBtn.disabled = !this.value;
            });

            // Add event listener for view profile button
            viewProfileBtn.addEventListener('click', function() {
                const specialistId = specialistSelect.value;
                if (specialistId) {
                    window.open(`gp_view_specialist.php?id=${specialistId}`, '_blank');
                }
            });
        });

        // File upload handling
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('referral_letter');
            const clearFileBtn = document.getElementById('clearFileBtn');
            
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Check file size (5MB limit)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        this.value = '';
                        return;
                    }
                    
                    // Check file type
                    const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    if (!validTypes.includes(file.type)) {
                        alert('Only PDF, DOC, and DOCX files are allowed');
                        this.value = '';
                        return;
                    }
                }
            });
            
            clearFileBtn.addEventListener('click', function() {
                fileInput.value = '';
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const preferredDate = document.getElementById('preferred_date');
            const alternativeDate = document.getElementById('alternative_date');
            const specialistSelect = document.getElementById('specialist');
            
            function checkAvailability(date, targetDiv, button) {
                const specialistId = specialistSelect.value;
                if (!specialistId) {
                    alert('Please select a specialist first');
                    return;
                }
                
                if (!date) {
                    alert('Please select a date first');
                    return;
                }

                button.disabled = true;
                button.innerHTML = 'Checking...';
                
                fetch(`view_slots.php?check_availability=1&id=${specialistId}&date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let html = `<div class="alert alert-info">
                                <strong>${data.day_name} Availability:</strong><br>`;
                            
                            if (data.slots.length === 0) {
                                html += 'No available slots on this day';
                            } else {
                                data.slots.forEach(slot => {
                                    html += `${slot.start_time} - ${slot.end_time}<br>`;
                                });
                            }
                            
                            html += '</div>';
                            document.getElementById(targetDiv).innerHTML = html;
                        } else {
                            document.getElementById(targetDiv).innerHTML = 
                                '<div class="alert alert-danger">Error checking availability</div>';
                        }
                    })
                    .catch(error => {
                        document.getElementById(targetDiv).innerHTML = 
                            '<div class="alert alert-danger">Error checking availability</div>';
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.innerHTML = 'Check Availability';
                    });
            }

            // Set up event listeners for the check availability buttons
            document.getElementById('checkPreferredDate').addEventListener('click', function() {
                checkAvailability(
                    preferredDate.value,
                    'preferred_date_slots',
                    this
                );
            });

            document.getElementById('checkAlternativeDate').addEventListener('click', function() {
                checkAvailability(
                    alternativeDate.value,
                    'alternative_date_slots',
                    this
                );
            });

            // Existing date validation code
            const today = new Date().toISOString().split('T')[0];
            preferredDate.min = today;
            alternativeDate.min = today;
            
            preferredDate.addEventListener('change', function() {
                alternativeDate.min = this.value;
                if (alternativeDate.value && alternativeDate.value < this.value) {
                    alternativeDate.value = this.value;
                }
                // Clear previous availability check
                document.getElementById('preferred_date_slots').innerHTML = '';
            });
            
            alternativeDate.addEventListener('change', function() {
                if (this.value < preferredDate.value) {
                    alert('Alternative date cannot be earlier than preferred date');
                    this.value = preferredDate.value;
                }
                // Clear previous availability check
                document.getElementById('alternative_date_slots').innerHTML = '';
            });

            // Clear availability results when specialist changes
            specialistSelect.addEventListener('change', function() {
                document.getElementById('preferred_date_slots').innerHTML = '';
                document.getElementById('alternative_date_slots').innerHTML = '';
            });
        });
    </script>
</body>
</html>