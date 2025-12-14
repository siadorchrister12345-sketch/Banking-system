<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use src\Services\PersistenceManager;

$persistence = new PersistenceManager(
    dirname(__DIR__) . '/data/accounts.txt',
    dirname(__DIR__) . '/data/users.txt'
);

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($persistence->verifyUser($username, $password)) {
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $loginError = 'Invalid username or password';
    }
}

// Handle registration
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'] ?? '';
    $password = $_POST['reg_password'] ?? '';
    $password_confirm = $_POST['reg_password_confirm'] ?? '';
    
    if ($username && $password && $password === $password_confirm) {
        if ($persistence->userExists($username)) {
            $regError = 'Username already exists';
        } else {
            $persistence->saveUser($username, $password);
            $regSuccess = 'Registration successful! Please login.';
        }
    } else {
        $regError = 'Passwords do not match or fields are empty';
    }
}

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking System - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <!-- Login Form -->
            <div class="auth-form login-form active" id="loginForm">
                <div class="form-header">
                    <p>21-ITE-05, Group-2</p>
                    <h1>🏦 Banking System</h1>
                    <p>Login to your account</p>
                </div>
                
                <?php if (isset($loginError)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($loginError); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </form>
                
                <p class="form-footer">
                    Don't have an account? <a href="#" onclick="toggleForms(); return false;">Register here</a>
                </p>
            </div>

            <!-- Registration Form -->
            <div class="auth-form register-form" id="registerForm">
                <div class="form-header">
                    
                    <h1>🏦 Banking System</h1>
                    
                    <p>📝 Create a new account</p>
                </div>
                
                <?php if (isset($regError)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($regError); ?></div>
                <?php endif; ?>
                
                <?php if (isset($regSuccess)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($regSuccess); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="reg_username">Username</label>
                        <input type="text" id="reg_username" name="reg_username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="reg_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_password_confirm">Confirm Password</label>
                        <input type="password" id="reg_password_confirm" name="reg_password_confirm" required>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </form>
                
                <p class="form-footer">
                    Already have an account? <a href="#" onclick="toggleForms(); return false;">Login here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleForms() {
            document.getElementById('loginForm').classList.toggle('active');
            document.getElementById('registerForm').classList.toggle('active');
        }
    </script>
</body>
</html>
