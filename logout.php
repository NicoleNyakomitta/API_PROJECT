<?php
// Start session and include files at the very top
require_once 'includes/auth.php';

// Initialize error variable
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (loginUser($username, $password)) {
        if ($_SESSION['is_2fa_enabled']) {
            // Send 2FA code via email
            if (send2FACodeToUser($_SESSION['user_id'], $_SESSION['email'])) {
                $_SESSION['2fa_pending'] = true;
                header('Location: verify_2fa.php');
                exit();
            } else {
                $error = "Failed to send 2FA code. Please try again.";
            }
        } else {
            header('Location: dashboard.php');
            exit();
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StockFlow Pro</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Welcome Back</h2>
                <p class="auth-subtitle">Sign in to your StockFlow Pro account</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="auth-links" style="text-align: center; margin-top: 2rem;">
                <p style="color: var(--gray);">Don't have an account? 
                    <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Create one here
                    </a>
                </p>
                <p style="margin-top: 0.5rem;">
                    <a href="reset_credentials.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Forgot your password?
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html><?php
// Start session and include files at the very top
require_once 'includes/auth.php';

// Initialize error variable
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (loginUser($username, $password)) {
        if ($_SESSION['is_2fa_enabled']) {
            // Send 2FA code via email
            if (send2FACodeToUser($_SESSION['user_id'], $_SESSION['email'])) {
                $_SESSION['2fa_pending'] = true;
                header('Location: verify_2fa.php');
                exit();
            } else {
                $error = "Failed to send 2FA code. Please try again.";
            }
        } else {
            header('Location: dashboard.php');
            exit();
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StockFlow Pro</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Welcome Back</h2>
                <p class="auth-subtitle">Sign in to your StockFlow Pro account</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="auth-links" style="text-align: center; margin-top: 2rem;">
                <p style="color: var(--gray);">Don't have an account? 
                    <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Create one here
                    </a>
                </p>
                <p style="margin-top: 0.5rem;">
                    <a href="reset_credentials.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                        Forgot your password?
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>