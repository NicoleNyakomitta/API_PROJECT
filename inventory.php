<?php
require_once 'includes/auth.php';
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
        $category = sanitizeInput($_POST['category']);
        
        $stmt = $pdo->prepare("INSERT INTO inventory (name, description, quantity, price, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $quantity, $price, $category]);
        $message = "Item added successfully!";
    } elseif (isset($_POST['update_item'])) {
        $id = (int)$_POST['id'];
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
        $category = sanitizeInput($_POST['category']);
        
        $stmt = $pdo->prepare("UPDATE inventory SET name=?, description=?, quantity=?, price=?, category=? WHERE id=?");
        $stmt->execute([$name, $description, $quantity, $price, $category, $id]);
        $message = "Item updated successfully!";
    } elseif (isset($_POST['delete_item'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id=?");
        $stmt->execute([$id]);
        $message = "Item deleted successfully!";
    }
}

// Get all inventory items
$stmt = $pdo->query("SELECT * FROM inventory ORDER BY created_at DESC");
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
    <h2>Inventory Management</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <h3>Add New Item</h3>
        <form method="POST" class="form-grid">
            <input type="text" name="name" placeholder="Item Name" required>
            <input type="text" name="description" placeholder="Description" required>
            <input type="number" name="quantity" placeholder="Quantity" required>
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <input type="text" name="category" placeholder="Category" required>
            <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
        </form>
    </div>
    
    <div class="card">
        <h3>Inventory Items</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['description']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['category']; ?></td>
                        <td class="actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="name" value="<?php echo $item['name']; ?>">
                                <input type="hidden" name="description" value="<?php echo $item['description']; ?>">
                                <input type="hidden" name="quantity" value="<?php echo $item['quantity']; ?>">
                                <input type="hidden" name="price" value="<?php echo $item['price']; ?>">
                                <input type="hidden" name="category" value="<?php echo $item['category']; ?>">
                                <button type="submit" name="update_item" class="btn btn-sm btn-warning">Edit</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_item" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>