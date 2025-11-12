<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if ($user['is_2fa_enabled']) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_2fa_secret'] = $user['two_factor_secret'];
            header('Location: verify_2fa.php?reset=1');
            exit();
        } else {
            // Generate temporary password
            $temp_password = bin2hex(random_bytes(4));
            if (resetPassword($email, $temp_password)) {
                $message = "Password reset successful. Your temporary password is: <strong>$temp_password</strong>";
                $message_type = "success";
            }
        }
    } else {
        $error = "No account found with that email address";
    }
}
?>

<?php include 'includes/header.php'; ?>
    <div class="auth-container">
        <div class="auth-form">
            <h2>Reset Credentials</h2>
            
            <?php if (isset($message)): ?>
                <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
            
            <div class="auth-links">
                <p><a href="login.php">Back to Login</a></p>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>

<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if ($user['is_2fa_enabled']) {
            $_SESSION['reset_email'] = $email;
            // Send 2FA code for reset
            if (send2FACodeToUser($user['id'], $email)) {
                header('Location: verify_2fa.php?reset=1');
                exit();
            } else {
                $error = "Failed to send verification code. Please try again.";
            }
        } else {
            // Generate temporary password
            $temp_password = bin2hex(random_bytes(4));
            if (resetPassword($email, $temp_password)) {
                $message = "Password reset successful. Your temporary password is: <strong>$temp_password</strong>";
                $message_type = "success";
            }
        }
    } else {
        $error = "No account found with that email address";
    }
}
?>

<!-- Rest of your reset form remains the same -->