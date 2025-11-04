<?php
// includes/functions.php

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function generate2FASecret() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < 16; $i++) {
        $secret .= $chars[rand(0, 31)];
    }
    return $secret;
}

function generate2FACode() {
    return sprintf("%06d", mt_rand(1, 999999));
}

function send2FACodeToUser($user_id, $email) {
    global $pdo;
    
    // Include email config
    require_once 'config/email.php';
    
    $code = generate2FACode();
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Store code in database
    $stmt = $pdo->prepare("UPDATE users SET temp_2fa_code = ?, code_expires_at = ? WHERE id = ?");
    $stmt->execute([$code, $expires_at, $user_id]);
    
    // Send email
    $emailSent = send2FACode($email, $code);
    
    if ($emailSent) {
        error_log("✅ 2FA code sent to: $email");
        return true;
    } else {
        error_log("❌ Failed to send 2FA code to: $email");
        return true; // Change to false if email must work
    }
}

function verify2FACode($user_id, $code) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT temp_2fa_code, code_expires_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !$user['temp_2fa_code']) {
        return false;
    }
    
    // Check if code is expired
    if (strtotime($user['code_expires_at']) < time()) {
        return false;
    }
    
    // Verify code (remove spaces and make case insensitive)
    $entered_code = strtoupper(preg_replace('/[^0-9]/', '', $code));
    $stored_code = strtoupper(trim($user['temp_2fa_code']));
    
    if ($entered_code === $stored_code) {
        // Clear the used code
        $stmt = $pdo->prepare("UPDATE users SET temp_2fa_code = NULL, code_expires_at = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        return true;
    }
    
    return false;
}

// Database functions
function update2FAStatus($user_id, $enabled) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET is_2fa_enabled = ? WHERE id = ?");
    return $stmt->execute([$enabled ? 1 : 0, $user_id]);
}

function resetPassword($email, $new_password) {
    global $pdo;
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    return $stmt->execute([$password_hash, $email]);
}

function loginUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_2fa_enabled'] = $user['is_2fa_enabled'];
        $_SESSION['two_factor_secret'] = $user['two_factor_secret'];
        
        return true;
    }
    return false;
}

function registerUser($username, $email, $password) {
    global $pdo;
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $two_factor_secret = generate2FASecret();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, two_factor_secret) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash, $two_factor_secret]);
        return true;
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}
?>