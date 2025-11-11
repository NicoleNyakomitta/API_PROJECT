<?php
require_once 'includes/auth.php';
requireLogin();

// Check 2FA if enabled
if ($_SESSION['is_2fa_enabled'] && !isset($_SESSION['2fa_verified'])) {
    header('Location: verify_2fa.php');
    exit();
}

// Get stats
$total_inventory = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$active_reservations = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="dashboard-hero">
    <div class="hero-content">
        <h1>Welcome back, <?php echo $_SESSION['username']; ?>! 👋</h1>
        <p>Manage your inventory, orders, and reservations with ease</p>
    </div>
</section>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card floating">
        <div class="stat-icon">
            <i class="fas fa-warehouse"></i>
        </div>
        <div class="stat-number"><?php echo $total_inventory; ?></div>
        <div class="stat-label">Total Items</div>
    </div>
    
    <div class="stat-card floating" style="animation-delay: 0.2s;">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-number"><?php echo $pending_orders; ?></div>
        <div class="stat-label">Pending Orders</div>
    </div>
    
    <div class="stat-card floating" style="animation-delay: 0.4s;">
        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-number"><?php echo $active_reservations; ?></div>
        <div class="stat-label">Active Reservations</div>
    </div>
    
    <div class="stat-card floating" style="animation-delay: 0.6s;">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number"><?php echo $total_users; ?></div>
        <div class="stat-label">Total Users</div>
    </div>
</div>

<!-- Quick Actions -->
<section class="quick-actions">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Quick Actions</h2>
        </div>
        <div class="action-grid">
            <a href="inventory.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <h3>Add Inventory</h3>
                <p>Add new items to your inventory</p>
            </a>
            
            <a href="orders.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3>Create Order</h3>
                <p>Process new customer orders</p>
            </a>
            
            <a href="reservations.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Manage Reservations</h3>
                <p>Handle item reservations</p>
            </a>
            
            <a href="users.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>User Management</h3>
                <p>Manage system users</p>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>