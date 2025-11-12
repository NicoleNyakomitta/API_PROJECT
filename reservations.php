<?php
require_once 'includes/auth.php';
requireLogin();

// Helper function to format currency
function formatCurrency($amount) {
    return '$' . number_format((float)$amount, 2);
}

// Include payment configuration
require_once 'config/payments.php';

// Debug: Check if user_id is valid
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in. Please <a href='login.php'>login</a> first.");
}

// Verify user exists in database
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_exists) {
    // User doesn't exist - destroy session and redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

// Helper function to generate a unique transaction ID
function generateTransactionId($payment_method) {
    return strtoupper($payment_method) . '-' . uniqid();
}

// Handle reservation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_reservation'])) {
        $user_id = $_SESSION['user_id'];
        $item_name = sanitizeInput($_POST['item_name']);
        $reservation_date = sanitizeInput($_POST['reservation_date']);
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
        $payment_required = isset($_POST['payment_required']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, item_name, reservation_date, quantity, price, payment_required) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $item_name, $reservation_date, $quantity, $price, $payment_required]);
            
            $reservation_id = $pdo->lastInsertId();
            $message = "Reservation created successfully!";
            
            // If payment is required, redirect to payment page
            if ($payment_required && $price > 0) {
                $_SESSION['pending_payment'] = [
                    'reservation_id' => $reservation_id,
                    'amount' => $price * $quantity,
                    'item_name' => $item_name
                ];
                header('Location: payment.php?reservation_id=' . $reservation_id);
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } 
    elseif (isset($_POST['update_reservation_status'])) {
        $reservation_id = (int)$_POST['reservation_id'];
        $status = sanitizeInput($_POST['status']);
        
        try {
            $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $reservation_id]);
            $message = "Reservation status updated!";
        } catch (PDOException $e) {
            $error = "Error updating reservation: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['process_payment'])) {
        $reservation_id = (int)$_POST['reservation_id'];
        $payment_method = sanitizeInput($_POST['payment_method']);
        
        // Get reservation details
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reservation) {
            $amount = $reservation['price'] * $reservation['quantity'];
            $transaction_id = generateTransactionId($payment_method);
            
            try {
                // Create payment record
                $stmt = $pdo->prepare("INSERT INTO payments (reservation_id, user_id, amount, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$reservation_id, $_SESSION['user_id'], $amount, $payment_method, $transaction_id]);
                
                $payment_id = $pdo->lastInsertId();
                
                // Redirect to payment processing
                header('Location: process_payment.php?payment_id=' . $payment_id);
                exit();
            } catch (PDOException $e) {
                $error = "Payment error: " . $e->getMessage();
            }
        } else {
            $error = "Reservation not found or you don't have permission to access it.";
        }
    }
}

// Get reservations for the current user only
$stmt = $pdo->prepare("
    SELECT r.*, u.username, p.status as payment_status, p.payment_method
    FROM reservations r 
    JOIN users u ON r.user_id = u.id 
    LEFT JOIN payments p ON r.id = p.reservation_id AND p.status = 'completed'
    WHERE r.user_id = ?
    ORDER BY r.reservation_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
    <h2>Reservation Management</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <h3>Create New Reservation</h3>
        <form method="POST" class="form-grid">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <input type="date" name="reservation_date" required>
            <input type="number" name="quantity" placeholder="Quantity" min="1" required>
            <input type="number" step="0.01" name="price" placeholder="Price per item" min="0" required>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="payment_required" value="1">
                    <span>Payment required for this reservation</span>
                </label>
            </div>
            <button type="submit" name="create_reservation" class="btn btn-primary">Create Reservation</button>
        </form>
    </div>
    
    <div class="card">
        <h3>Your Reservations</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Qty</th>
                        <th>Total Price</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem;">
                                No reservations found. Create your first reservation above.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo $reservation['id']; ?></td>
                            <td><?php echo htmlspecialchars($reservation['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($reservation['reservation_date'])); ?></td>
                            <td><?php echo $reservation['quantity']; ?></td>
                            <td><?php echo formatCurrency($reservation['price'] * $reservation['quantity']); ?></td>
                            <td>
                                <?php if ($reservation['price'] > 0): ?>
                                    <?php if ($reservation['payment_status'] == 'completed'): ?>
                                        <span class="status-badge status-confirmed">
                                            <i class="fas fa-check"></i> Paid (<?php echo strtoupper($reservation['payment_method']); ?>)
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" name="process_payment" class="btn btn-sm btn-success">
                                                <i class="fas fa-credit-card"></i> Pay Now
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status-badge status-pending">Free</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                    <?php echo ucfirst($reservation['status']); ?>
                                </span>
                            </td>
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>