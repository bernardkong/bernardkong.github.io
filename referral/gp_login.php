<?php
session_start();
require_once 'config/db.php';

// Check if already logged in
if (isset($_SESSION['gp_id'])) {
    header("Location: gp_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $db->prepare("SELECT * FROM gp_doctors WHERE login = ? AND password = ? AND is_active = 1");
        $stmt->execute([$login, $password]);
        $gp = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($gp) {
            $_SESSION['gp_id'] = $gp['id'];
            $_SESSION['gp_name'] = $gp['gp_name'];
            $_SESSION['clinic_id'] = $gp['clinic_id'];
            header("Location: gp_dashboard.php");
            exit();
        } else {
            $error = "Invalid login credentials or account is inactive";
        }
    } catch(PDOException $e) {
        $error = "Login error occurred";
        error_log("GP Login error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GP Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #20B2AA, #40E0D0);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            text-align: center;
            padding: 20px;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .btn-login {
            background: linear-gradient(135deg, #20B2AA, #40E0D0);
            border: none;
            border-radius: 5px;
            padding: 12px;
            width: 100%;
            color: white;
            font-weight: 600;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1a9089, #37c2b3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">GP Login</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="login" class="form-label">Login ID</label>
                            <input type="text" class="form-control" id="login" name="login" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-login">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>