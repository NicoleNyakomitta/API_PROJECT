<?php
require_once 'includes/auth.php';

// Check if this is part of a password reset flow
if (!isset($_SESSION['reset_2fa_required']) || !$_SESSION['reset_2fa_required']) {
    header('Location: reset_password.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitizeInput($_POST['2fa_code']);
    
    if (verify2FACode($_SESSION['reset_user_id'], $code)) {
        // 2FA verified, generate temporary password
        $temp_password = bin2hex(random_bytes(6));
        
        if (resetPassword($_SESSION['reset_user_id'], $temp_password)) {
            logSecurityEvent($_SESSION['reset_user_id'], 'password_reset_2fa_success', $_SERVER['REMOTE_ADDR']);
            
            // Clear reset session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_2fa_required']);
            
            $_SESSION['message'] = "Password reset successful. Check your email for the temporary password.";
            $_SESSION['message_type'] = "success";
            header('Location: login.php');
            exit();
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="auth-container">
    <div class="auth-form">
        <h2>Two-Factor Verification</h2>
        <p>Please enter the verification code from your authenticator app to reset your password.</p>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="2fa_code">Verification Code:</label>
                <input type="text" id="2fa_code" name="2fa_code" required maxlength="6" pattern="[0-9]{6}">
            </div>
            
            <button type="submit" class="btn btn-primary">Verify & Reset Password</button>
        </form>
        
        <div class="auth-links">
            <p><a href="reset_password.php">Back to Reset</a></p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>