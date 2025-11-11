<?php
require_once 'includes/auth.php';
requireLogin();

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_order'])) {
        $user_id = $_SESSION['user_id'];
        $inventory_id = (int)$_POST['inventory_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Get item price
        $stmt = $pdo->prepare("SELECT price FROM inventory WHERE id = ?");
        $stmt->execute([$inventory_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            $total_amount = $item['price'] * $quantity;
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
            $stmt->execute([$user_id, $total_amount]);
            $order_id = $pdo->lastInsertId();
            
            // Add order item
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, inventory_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $inventory_id, $quantity, $item['price']]);
            
            // Update inventory
            $stmt = $pdo->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $inventory_id]);
            
            $message = "Order created successfully!";
        }
    } elseif (isset($_POST['update_status'])) {
        $order_id = (int)$_POST['order_id'];
        $status = sanitizeInput($_POST['status']);
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $message = "Order status updated!";
    }
}

// Get orders
$stmt = $pdo->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get inventory for dropdown
$stmt = $pdo->query("SELECT * FROM inventory WHERE quantity > 0");
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
    <h2>Order Management</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <h3>Create New Order</h3>
        <form method="POST" class="form-grid">
            <select name="inventory_id" required>
                <option value="">Select Item</option>
                <?php foreach ($inventory as $item): ?>
                    <option value="<?php echo $item['id']; ?>">
                        <?php echo $item['name']; ?> - $<?php echo number_format($item['price'], 2); ?> (Stock: <?php echo $item['quantity']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantity" placeholder="Quantity" min="1" required>
            <button type="submit" name="create_order" class="btn btn-primary">Create Order</button>
        </form>
    </div>
    
    <div class="card">
        <h3>All Orders</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo $order['username']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                        <td class="actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>
