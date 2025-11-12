<?php
require_once 'includes/auth.php';
requireLogin();

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
    <h2>User Management</h2>
    
    <div class="card">
        <h3>All Users</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>2FA Enabled</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $user['is_2fa_enabled'] ? 'status-confirmed' : 'status-pending'; ?>">
                                <?php echo $user['is_2fa_enabled'] ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>