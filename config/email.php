<?php
// config/email.php - Gmail configuration for 2FA

// Gmail Settings - UPDATE THESE WITH YOUR ACTUAL GMAIL
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'Kingkaleepso@gmail.com'); // Your full Gmail address
define('SMTP_PASSWORD', 'virowmvdzdalyvkf');    // 16-character App Password
define('SMTP_FROM_EMAIL', '189608'); // Same as username
define('SMTP_FROM_NAME', 'StockFlow Pro');
define('SMTP_SECURE', 'tls');

function send2FACode($email, $code) {
    // Create directory for local logs
    if (!is_dir('2fa_codes')) {
        mkdir('2fa_codes', 0777, true);
    }
    
    // Log the attempt
    $log_entry = "[" . date('Y-m-d H:i:s') . "] Attempting to send to: $email | Code: $code\n";
    file_put_contents('2fa_codes/email_attempts.log', $log_entry, FILE_APPEND);
    
    try {
        // Check if PHPMailer exists
        if (!file_exists('vendor/autoload.php')) {
            throw new Exception('PHPMailer not found. Run: composer require phpmailer/phpmailer');
        }
        
        require_once 'vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Debug output (will be captured in log)
        $mail->SMTPDebug = 2; // Enable verbose debug output
        $debug_output = '';
        $mail->Debugoutput = function($str, $level) use (&$debug_output) {
            $debug_output .= "$level: $str\n";
        };
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your StockFlow Pro Verification Code';
        $mail->Body    = createEmailTemplate($code);
        $mail->AltBody = "Your verification code is: $code\nThis code expires in 10 minutes.";
        
        $mail->send();
        
        // Log success
        $success_log = "[" . date('Y-m-d H:i:s') . "] ✅ Email sent successfully to: $email\n";
        file_put_contents('2fa_codes/email_success.log', $success_log, FILE_APPEND);
        
        return true;
        
    } catch (Exception $e) {
        // Log the full error
        $error_log = "[" . date('Y-m-d H:i:s') . "] ❌ Email failed to: $email\n";
        $error_log .= "Error: " . $e->getMessage() . "\n";
        if (isset($debug_output)) {
            $error_log .= "Debug: " . $debug_output . "\n";
        }
        file_put_contents('2fa_codes/email_errors.log', $error_log, FILE_APPEND);
        
        // Fallback: always save code locally
        saveCodeForLocalTesting($email, $code);
        
        return false;
    }
}

function createEmailTemplate($code) {
    // ... keep your existing email template code ...
    // (the same beautiful HTML template you had before)
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f6f9fc; margin: 0; padding: 0; color: #333; }
            .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 15px; padding: 40px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: 1px solid #e1e5e9; }
            .header { background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%); color: white; padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 30px; }
            .code { font-size: 42px; font-weight: bold; color: #4361ee; letter-spacing: 8px; font-family: 'Courier New', monospace; text-align: center; margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 2px dashed #4361ee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>StockFlow Pro</h1>
                <p>Two-Factor Authentication Code</p>
            </div>
            <h2>Hello!</h2>
            <p>Your verification code is:</p>
            <div class='code'>$code</div>
            <p><strong>This code expires in 10 minutes.</strong></p>
            <p>If you didn't request this code, please ignore this email.</p>
        </div>
    </body>
    </html>
    ";
}

function saveCodeForLocalTesting($email, $code) {
    $log_entry = "[" . date('Y-m-d H:i:s') . "] LOCAL FALLBACK - Email: $email | Code: $code\n";
    file_put_contents('2fa_codes/local_codes.log', $log_entry, FILE_APPEND);
    
    // Also save as individual file for easy access
    $filename = "2fa_codes/code_" . date('Y-m-d_H-i-s') . ".txt";
    file_put_contents($filename, "Email: $email\nCode: $code\nTime: " . date('Y-m-d H:i:s'));
    
    return true;
}
?>