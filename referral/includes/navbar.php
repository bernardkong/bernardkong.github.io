<?php
$hospitalSettings = ['hospital_name' => 'Subang Jaya Medical Centre', 'hospital_logo' => 'uploads/sjmclogo.png']; // Default values

try {
    $db = require_once __DIR__ . '/../config/db.php';
    
    if (!$db || !($db instanceof PDO)) {
        error_log("Database connection failed in navbar.php");
        // Continue with default values
    } else {
        $stmt = $db->query("SELECT hospital_name, hospital_logo FROM hospital_settings WHERE id = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $hospitalSettings = $result;
        }
    }
} catch(Exception $e) {
    error_log("Error in navbar.php: " . $e->getMessage());
    // Continue with default values
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <?php if (!empty($hospitalSettings['hospital_logo']) && file_exists($hospitalSettings['hospital_logo'])): ?>
                <img src="<?php echo htmlspecialchars($hospitalSettings['hospital_logo']); ?>" 
                     alt="Hospital Logo" 
                     style="height: 120px; margin-right: 10px; display: block;">
            <?php endif; ?>
            <span class="hospital-name"><?php echo htmlspecialchars($hospitalSettings['hospital_name']); ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'departments') ? 'active' : ''; ?>" href="departments.php">
                        <i class="bi bi-building me-1"></i>Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'specialists') ? 'active' : ''; ?>" href="specialists.php">
                        <i class="bi bi-person-badge me-1"></i>Specialists
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'gp_doctors') ? 'active' : ''; ?>" href="gp_doctors.php">
                        <i class="bi bi-people me-1"></i>GP Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'settings') ? 'active' : ''; ?>" href="settings.php">
                        <i class="bi bi-gear me-1"></i>Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<nav class="bottom-nav">
    <div class="d-flex align-items-center">
        <img src="uploads/qmed_logo.png" alt="Logo" class="bottom-nav-logo">
        <span class="bottom-nav-text">Qmed.asia © 2024</span>
    </div>
</nav>

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
        margin: 0;
        border-radius: 0;
        width: 100%;
        position: relative;
        transform: none;
        left: 0;
    }

    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }

    /* Bottom nav styling */
    .bottom-nav {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1rem;
        position: fixed;
        bottom: 0;
        width: 100%;
        text-align: center;
        border-radius: 0;
        left: 0;
        transform: none;
    }

    .bottom-nav-logo {
        height: 40px;
        margin-right: 10px;
    }

    @media (max-width: 768px) {
        .navbar-brand img {
            height: 80px;
        }
    }

    .navbar-toggler {
        background-color: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.5);
        padding: 0.5rem;
        margin-right: 1rem;
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .navbar-toggler:focus {
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.2);
        outline: none;
    }

    /* Updated navbar styles to match index.php */
    .navbar .nav-link {
        font-size: 1.1rem;
        padding: 0.8rem 1.2rem !important;
        margin: 0 0.3rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .navbar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    .navbar .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        font-weight: 500;
    }

    .navbar .nav-link i {
        font-size: 1.2rem;
    }

    /* Updated responsive styles */
    @media (max-width: 992px) {
        .navbar .nav-link {
            padding: 1rem !important;
            margin: 0.5rem 0;
        }
    }
</style>