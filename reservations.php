<?php
require_once 'includes/auth.php';
if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
    }
}
}

// Define sanitizeInput function if not already defined
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

requireLogin();

// Handle reservation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_reservation'])) {
        $user_id = $_SESSION['user_id'];
        $item_name = sanitizeInput($_POST['item_name']);
        $reservation_date = sanitizeInput($_POST['reservation_date']);
        $quantity = (int)$_POST['quantity'];
        
        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, item_name, reservation_date, quantity) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $item_name, $reservation_date, $quantity]);
        $message = "Reservation created successfully!";
    } elseif (isset($_POST['update_reservation_status'])) {
        $reservation_id = (int)$_POST['reservation_id'];
        $status = sanitizeInput($_POST['status']);
        
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $reservation_id]);
        $message = "Reservation status updated!";
    }
}

// Get reservations
$stmt = $pdo->query("
    SELECT r.*, u.username 
    FROM reservations r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.reservation_date DESC
");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
    <h2>Reservation Management</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <h3>Create New Reservation</h3>
        <form method="POST" class="form-grid">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <input type="date" name="reservation_date" required>
            <input type="number" name="quantity" placeholder="Quantity" min="1" required>
            <button type="submit" name="create_reservation" class="btn btn-primary">Create Reservation</button>
        </form>
    </div>
    
    <div class="card">
        <h3>All Reservations</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Customer</th>
                        <th>Reservation Date</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?php echo $reservation['id']; ?></td>
                        <td><?php echo $reservation['item_name']; ?></td>
                        <td><?php echo $reservation['username']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($reservation['reservation_date'])); ?></td>
                        <td><?php echo $reservation['quantity']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                <?php echo ucfirst($reservation['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($reservation['created_at'])); ?></td>
                        <td class="actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $reservation['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $reservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $reservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_reservation_status" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>