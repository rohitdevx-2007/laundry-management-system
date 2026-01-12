<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
checkLogin();

// Fetch Customers for dropdown
$stmt = $conn->query("SELECT * FROM customers ORDER BY customer_name ASC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Services for selection
$stmt = $conn->query("SELECT * FROM services ORDER BY service_name ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'];
    $delivery_date = $_POST['delivery_date'];
    $service_ids = $_POST['service_id']; // Array
    $quantities = $_POST['quantity']; // Array
    
    if ($customer_id && !empty($service_ids)) {
        try {
            $conn->beginTransaction();

            // Create Order
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, delivery_date, status) VALUES (:cid, NOW(), :ddate, 'Pending')");
            $stmt->bindParam(':cid', $customer_id);
            $stmt->bindParam(':ddate', $delivery_date);
            $stmt->execute();
            $order_id = $conn->lastInsertId();

            $total_amount = 0;

            // Add Order Items
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, service_id, quantity, price) VALUES (:oid, :sid, :qty, :price)");
            
            for ($i = 0; $i < count($service_ids); $i++) {
                $sid = $service_ids[$i];
                $qty = $quantities[$i];
                
                if ($qty > 0) {
                    // Get current price
                    $stmt_price = $conn->prepare("SELECT price FROM services WHERE id = :id");
                    $stmt_price->bindParam(':id', $sid);
                    $stmt_price->execute();
                    $price = $stmt_price->fetchColumn();
                    
                    $line_total = $price * $qty;
                    $total_amount += $line_total;

                    $stmt_item->bindParam(':oid', $order_id);
                    $stmt_item->bindParam(':sid', $sid);
                    $stmt_item->bindParam(':qty', $qty);
                    $stmt_item->bindParam(':price', $price);
                    $stmt_item->execute();
                }
            }

            // Update Order Total
            $stmt_update = $conn->prepare("UPDATE orders SET total_amount = :total WHERE id = :id");
            $stmt_update->bindParam(':total', $total_amount);
            $stmt_update->bindParam(':id', $order_id);
            $stmt_update->execute();

            $conn->commit();
            header("Location: order_details.php?id=" . $order_id);
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Error creating order: " . $e->getMessage();
        }
    } else {
        $error = "Please select a customer and at least one service.";
    }
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create New Order</h2>
        <a href="orders.php" class="btn btn-secondary">Back to List</a>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">Customer Details</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Customer</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">-- Select Customer --</option>
                                <?php foreach($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>">
                                        <?php echo htmlspecialchars($customer['customer_name']) . ' (' . htmlspecialchars($customer['mobile']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2">
                                <a href="customers.php" class="text-decoration-none small">+ Add New Customer</a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expected Delivery Date</label>
                            <input type="date" class="form-control" name="delivery_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Order Items</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th width="150">Price</th>
                                        <th width="120">Quantity</th>
                                        <th width="150">Total</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr>
                                        <td>
                                            <select class="form-select service-select" name="service_id[]" required onchange="updatePrice(this)">
                                                <option value="" data-price="0">-- Select Service --</option>
                                                <?php foreach($services as $service): ?>
                                                    <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['price']; ?>">
                                                        <?php echo htmlspecialchars($service['service_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control price-input" readonly value="0.00">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control qty-input" name="quantity[]" value="1" min="1" onchange="calculateRow(this)">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control total-input" readonly value="0.00">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                        <td><strong id="grandTotal">0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-info text-white btn-sm" onclick="addRow()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check me-2"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function updatePrice(select) {
    var price = select.options[select.selectedIndex].getAttribute('data-price');
    var row = select.closest('tr');
    row.querySelector('.price-input').value = parseFloat(price).toFixed(2);
    calculateRow(select);
}

function calculateRow(element) {
    var row = element.closest('tr');
    var price = parseFloat(row.querySelector('.price-input').value) || 0;
    var qty = parseInt(row.querySelector('.qty-input').value) || 0;
    var total = price * qty;
    row.querySelector('.total-input').value = total.toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    var total = 0;
    document.querySelectorAll('.total-input').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('grandTotal').innerText = total.toFixed(2);
}

function addRow() {
    var row = document.querySelector('#itemsBody tr').cloneNode(true);
    row.querySelector('.price-input').value = '0.00';
    row.querySelector('.qty-input').value = '1';
    row.querySelector('.total-input').value = '0.00';
    row.querySelector('select').value = '';
    document.getElementById('itemsBody').appendChild(row);
}

function removeRow(btn) {
    if(document.querySelectorAll('#itemsBody tr').length > 1) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
