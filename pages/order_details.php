<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
checkLogin();

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
    $message = "Order status updated successfully!";
}

// Fetch Order Details
$stmt = $conn->prepare("SELECT o.*, c.customer_name, c.mobile, c.address 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.id 
                        WHERE o.id = :id");
$stmt->bindParam(':id', $order_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found.";
    exit();
}

// Fetch Order Items
$stmt = $conn->prepare("SELECT oi.*, s.service_name 
                        FROM order_items oi 
                        JOIN services s ON oi.service_id = s.id 
                        WHERE oi.order_id = :id");
$stmt->bindParam(':id', $order_id);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>Order Details #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h2>
        <div>
            <a href="orders.php" class="btn btn-secondary me-2">Back</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i> Print Bill</button>
        </div>
    </div>

    <?php if(isset($message)): ?>
        <div class="alert alert-success no-print"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="row">
                        <div class="col-6">
                            <h4 class="mb-0 text-primary">INVOICE</h4>
                            <small>#INV-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></small>
                        </div>
                        <div class="col-6 text-end">
                            <h5>Laundry Management System</h5>
                            <small>123 Laundry Street, City</small><br>
                            <small>Phone: +91 1234567890</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-6">
                            <h6 class="text-muted">Bill To:</h6>
                            <h5><?php echo htmlspecialchars($order['customer_name']); ?></h5>
                            <p class="mb-1"><?php echo htmlspecialchars($order['address']); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($order['mobile']); ?></p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1"><strong>Delivery Date:</strong> <?php echo $order['delivery_date'] ? date('d M Y', strtotime($order['delivery_date'])) : '-'; ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-secondary"><?php echo $order['status']; ?></span></p>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['service_name']); ?></td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end"><?php echo formatCurrency($item['price']); ?></td>
                                    <td class="text-end"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
                                <td class="text-end"><strong><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="mt-5 text-center text-muted">
                        <small>Thank you for your business!</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 no-print">
            <div class="card">
                <div class="card-header">Manage Order</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Update Status</label>
                            <select class="form-select" name="status">
                                <?php 
                                $statuses = ['Pending', 'Processing', 'Ready', 'Delivered', 'Cancelled'];
                                foreach($statuses as $s): 
                                ?>
                                    <option value="<?php echo $s; ?>" <?php echo $order['status'] == $s ? 'selected' : ''; ?>>
                                        <?php echo $s; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
