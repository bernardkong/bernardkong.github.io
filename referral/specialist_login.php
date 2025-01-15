<?php
session_start();
require_once 'config/db.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        $stmt = $db->prepare("SELECT * FROM specialists WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $specialist = $stmt->fetch();
        
        if ($specialist && $specialist['password'] === $password) {
            $_SESSION['specialist_id'] = $specialist['id'];
            $_SESSION['specialist_name'] = $specialist['name'];
            header('Location: specialist_dashboard.php');
            exit();
        } else {
            $error_message = 'Invalid email or password';
        }
    } catch(PDOException $e) {
        $error_message = 'An error occurred during login';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialist Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            color: #495057;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 5px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 0.75rem;
            width: 100%;
            font-weight: 500;
            margin-top: 1rem;
            border-radius: 5px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Specialist Login</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>