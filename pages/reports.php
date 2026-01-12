<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
checkLogin();

$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch Report Data
$stmt = $conn->prepare("SELECT o.id, c.customer_name, o.total_amount, o.status, o.created_at 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.id 
                        WHERE DATE_FORMAT(o.created_at, '%Y-%m') = :month 
                        ORDER BY o.created_at DESC");
$stmt->bindParam(':month', $month);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Totals
$total_revenue = 0;
$total_orders = count($orders);
foreach ($orders as $order) {
    $total_revenue += $order['total_amount'];
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>Monthly Report</h2>
        <div class="d-flex">
            <form method="GET" class="d-flex me-2">
                <input type="month" name="month" class="form-control me-2" value="<?php echo $month; ?>">
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
            <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white text-center">
            <h4>Sales Report - <?php echo date('F Y', strtotime($month)); ?></h4>
        </div>
        <div class="card-body">
            <div class="row mb-4 text-center">
                <div class="col-md-6">
                    <h5>Total Orders</h5>
                    <h3><?php echo $total_orders; ?></h3>
                </div>
                <div class="col-md-6">
                    <h5>Total Revenue</h5>
                    <h3><?php echo formatCurrency($total_revenue); ?></h3>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($orders) > 0): ?>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                    <td>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo $order['status']; ?></td>
                                    <td class="text-end"><?php echo formatCurrency($order['total_amount']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="text-end"><strong>TOTAL</strong></td>
                                <td class="text-end"><strong><?php echo formatCurrency($total_revenue); ?></strong></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No records found for this month.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
