<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/payments.php';

$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;

// Get reservation details
$stmt = $pdo->prepare("SELECT r.*, u.username FROM reservations r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    header('Location: reservations.php');
    exit();
}

$total_amount = $reservation['price'] * $reservation['quantity'];
?>

<?php include 'includes/header.php'; ?>
<div class="payment-container">
    <div class="payment-header">
        <h1><i class="fas fa-credit-card"></i> Complete Your Payment</h1>
        <p>Secure payment for your reservation</p>
    </div>

    <div class="payment-content">
        <!-- Order Summary -->
        <div class="card payment-summary">
            <h3>Order Summary</h3>
            <div class="order-details">
                <div class="order-item">
                    <span>Item:</span>
                    <span><?php echo htmlspecialchars($reservation['item_name']); ?></span>
                </div>
                <div class="order-item">
                    <span>Reservation Date:</span>
                    <span><?php echo date('F j, Y', strtotime($reservation['reservation_date'])); ?></span>
                </div>
                <div class="order-item">
                    <span>Quantity:</span>
                    <span><?php echo $reservation['quantity']; ?></span>
                </div>
                <div class="order-item">
                    <span>Price per item:</span>
                    <span><?php echo formatCurrency($reservation['price']); ?></span>
                </div>
                <div class="order-total">
                    <span>Total Amount:</span>
                    <span class="total-amount"><?php echo formatCurrency($total_amount); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="card payment-methods">
            <h3>Select Payment Method</h3>
            
            <form method="POST" action="process_payment.php" class="payment-form">
                <input type="hidden" name="reservation_id" value="<?php echo $reservation_id; ?>">
                <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
                
                <div class="payment-options">
                    <!-- M-Pesa Option -->
                    <div class="payment-option">
                        <input type="radio" name="payment_method" value="mpesa" id="mpesa" required>
                        <label for="mpesa" class="payment-label">
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="payment-info">
                                <h4>M-Pesa</h4>
                                <p>Pay via M-Pesa mobile money</p>
                                <?php if ($PAYMENT_METHODS['mpesa']['test_mode']): ?>
                                    <small class="test-mode">Test Mode</small>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>

                    <!-- Card Option -->
                    <div class="payment-option">
                        <input type="radio" name="payment_method" value="card" id="card">
                        <label for="card" class="payment-label">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="payment-info">
                                <h4>Credit/Debit Card</h4>
                                <p>Pay with Visa, MasterCard, or American Express</p>
                                <?php if ($PAYMENT_METHODS['card']['test_mode']): ?>
                                    <small class="test-mode">Test Mode</small>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>

                    <!-- PayPal Option -->
                    <div class="payment-option">
                        <input type="radio" name="payment_method" value="paypal" id="paypal">
                        <label for="paypal" class="payment-label">
                            <div class="payment-icon">
                                <i class="fab fa-paypal"></i>
                            </div>
                            <div class="payment-info">
                                <h4>PayPal</h4>
                                <p>Pay with your PayPal account</p>
                                <?php if ($PAYMENT_METHODS['paypal']['test_mode']): ?>
                                    <small class="test-mode">Test Mode</small>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- M-Pesa Phone Input (shown when M-Pesa is selected) -->
                <div class="payment-details" id="mpesa-details" style="display: none;">
                    <div class="form-group">
                        <label for="phone">M-Pesa Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="2547XXXXXXXX" 
                               pattern="254[0-9]{9}" class="form-control">
                        <small>Enter your M-Pesa registered phone number (format: 2547XXXXXXXX)</small>
                    </div>
                </div>

                <!-- Card Details (shown when Card is selected) -->
                <div class="payment-details" id="card-details" style="display: none;">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" 
                               placeholder="1234 5678 9012 3456" class="form-control">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" id="expiry" name="expiry" 
                                   placeholder="MM/YY" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="cvc">CVC</label>
                            <input type="text" id="cvc" name="cvc" 
                                   placeholder="123" class="form-control">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg payment-submit">
                    <i class="fas fa-lock"></i> Pay <?php echo formatCurrency($total_amount); ?>
                </button>
                
                <div class="payment-security">
                    <p><i class="fas fa-shield-alt"></i> Your payment is secure and encrypted</p>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.payment-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.payment-header {
    text-align: center;
    margin-bottom: 2rem;
}

.payment-header h1 {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 2.5rem;
}

.payment-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.payment-summary, .payment-methods {
    height: fit-content;
}

.order-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item, .order-total {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.order-total {
    border-top: 2px solid var(--primary);
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 1rem;
    padding-top: 1rem;
}

.total-amount {
    color: var(--primary);
    font-size: 1.3rem;
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.payment-option input[type="radio"] {
    display: none;
}

.payment-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-label:hover {
    border-color: var(--primary-light);
    background: rgba(67, 97, 238, 0.05);
}

.payment-option input[type="radio"]:checked + .payment-label {
    border-color: var(--primary);
    background: rgba(67, 97, 238, 0.1);
}

.payment-icon {
    width: 50px;
    height: 50px;
    background: var(--gradient-primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.payment-info h4 {
    margin: 0 0 0.25rem 0;
    color: var(--dark);
}

.payment-info p {
    margin: 0;
    color: var(--gray);
    font-size: 0.9rem;
}

.test-mode {
    color: var(--warning);
    font-weight: bold;
}

.payment-details {
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: rgba(67, 97, 238, 0.05);
    border-radius: 10px;
    border-left: 4px solid var(--primary);
}

.payment-submit {
    width: 100%;
    margin-top: 1rem;
}

.payment-security {
    text-align: center;
    margin-top: 1rem;
    color: var(--gray);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .payment-content {
        grid-template-columns: 1fr;
    }
    
    .payment-label {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
// Show/hide payment details based on selected method
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all payment details
        document.querySelectorAll('.payment-details').forEach(detail => {
            detail.style.display = 'none';
        });
        
        // Show selected payment details
        const selectedDetails = document.getElementById(this.value + '-details');
        if (selectedDetails) {
            selectedDetails.style.display = 'block';
        }
    });
});

// Format card number
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let matches = value.match(/\d{4,16}/g);
    let match = matches ? matches[0] : '';
    let parts = [];
    
    for (let i = 0; i < match.length; i += 4) {
        parts.push(match.substring(i, i + 4));
    }
    
    if (parts.length) {
        e.target.value = parts.join(' ');
    } else {
        e.target.value = value;
    }
});

// Format expiry date
document.getElementById('expiry')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
});
</script>

<?php include 'includes/footer.php'; ?>