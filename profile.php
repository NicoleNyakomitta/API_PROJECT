<?php
require_once 'includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_2fa'])) {
        $new_status = !$_SESSION['is_2fa_enabled'];
        
        if (update2FAStatus($_SESSION['user_id'], $new_status)) {
            $_SESSION['is_2fa_enabled'] = $new_status;
            $message = "2FA " . ($new_status ? "enabled" : "disabled") . " successfully!";
            $message_type = "success";
        } else {
            $error = "Failed to update 2FA settings. Please try again.";
        }
    }
    
    // Handle logout from profile page
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_details = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
    <div class="profile-container">
        <div class="profile-header">
            <h1>User Profile</h1>
            <p>Manage your account settings and preferences</p>
        </div>
        
        <div class="profile-content">
            <!-- Profile Information Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user-circle"></i> Profile Information
                    </h2>
                </div>
                
                <?php if (isset($message)): ?>
                    <div class="alert <?php echo $message_type; ?>">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-info-grid">
                    <div class="info-item">
                        <label>User ID:</label>
                        <span class="info-value">#<?php echo $user_details['id']; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Username:</label>
                        <span class="info-value"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Email Address:</label>
                        <span class="info-value"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Account Created:</label>
                        <span class="info-value"><?php echo date('F j, Y g:i A', strtotime($user_details['created_at'])); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Two-Factor Authentication:</label>
                        <span class="info-value">
                            <span class="status-badge <?php echo $_SESSION['is_2fa_enabled'] ? 'status-confirmed' : 'status-pending'; ?>">
                                <?php echo $_SESSION['is_2fa_enabled'] ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Security Settings Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-shield-alt"></i> Security Settings
                    </h2>
                </div>
                
                <div class="security-actions">
                    <form method="POST" class="security-form">
                        <div class="form-group">
                            <label>Two-Factor Authentication (2FA):</label>
                            <p class="form-help">Add an extra layer of security to your account by enabling 2FA. You'll receive a verification code via email when logging in.</p>
                            
                            <button type="submit" name="toggle_2fa" class="btn <?php echo $_SESSION['is_2fa_enabled'] ? 'btn-warning' : 'btn-primary'; ?>">
                                <i class="fas <?php echo $_SESSION['is_2fa_enabled'] ? 'fa-lock-open' : 'fa-lock'; ?>"></i>
                                <?php echo $_SESSION['is_2fa_enabled'] ? 'Disable 2FA' : 'Enable 2FA'; ?>
                            </button>
                        </div>
                    </form>
                    
                    <div class="security-info">
                        <h4><i class="fas fa-info-circle"></i> Security Tips:</h4>
                        <ul>
                            <li>Keep your password secure and don't share it with anyone</li>
                            <li>Enable 2FA for enhanced security</li>
                            <li>Log out when using public computers</li>
                            <li>Regularly update your password</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Account Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-cog"></i> Account Actions
                    </h2>
                </div>
                
                <div class="account-actions-grid">
                    <form method="POST" class="action-form">
                        <button type="submit" name="logout" class="btn btn-danger btn-lg action-btn" onclick="return confirm('Are you sure you want to log out?')">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Log Out</span>
                            <small>Sign out of your account</small>
                        </button>
                    </form>
                    
                    <a href="reset_credentials.php" class="btn btn-warning btn-lg action-btn">
                        <i class="fas fa-key"></i>
                        <span>Reset Password</span>
                        <small>Change your account password</small>
                    </a>
                    
                    <a href="dashboard.php" class="btn btn-secondary btn-lg action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                        <small>Return to main dashboard</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .profile-header h1 {
            font-size: 2.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .profile-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .profile-content {
            display: grid;
            gap: 2rem;
        }
        
        .profile-info-grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .info-value {
            color: var(--gray);
            font-weight: 500;
        }
        
        .security-actions {
            display: grid;
            gap: 2rem;
        }
        
        .security-form {
            padding: 1.5rem;
            background: rgba(67, 97, 238, 0.03);
            border-radius: 10px;
            border: 1px solid rgba(67, 97, 238, 0.1);
        }
        
        .form-help {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .security-info {
            padding: 1.5rem;
            background: rgba(76, 201, 240, 0.05);
            border-radius: 10px;
            border-left: 4px solid #4cc9f0;
        }
        
        .security-info h4 {
            color: #4cc9f0;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .security-info ul {
            color: var(--gray);
            padding-left: 1.5rem;
            margin: 0;
        }
        
        .security-info li {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }
        
        .account-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .action-form, .action-btn {
            height: 100%;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem 1rem;
            text-decoration: none;
            gap: 0.5rem;
        }
        
        .action-btn i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .action-btn span {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .action-btn small {
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .profile-info-grid .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .account-actions-grid {
                grid-template-columns: 1fr;
            }
            
            .action-btn {
                padding: 1.5rem 1rem;
            }
        }
    </style>
<?php include 'includes/footer.php'; ?>