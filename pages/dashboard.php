<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
checkLogin();

// Fetch Stats
$today = date('Y-m-d');

// Total Orders
$stmt = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmt->fetchColumn();

// Pending Orders
$stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'");
$pending_orders = $stmt->fetchColumn();

// Today's Revenue
$stmt = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = :today");
$stmt->bindParam(':today', $today);
$stmt->execute();
$todays_revenue = $stmt->fetchColumn() ?: 0;

// Total Customers
$stmt = $conn->query("SELECT COUNT(*) FROM customers");
$total_customers = $stmt->fetchColumn();

// Recent Orders
$stmt = $conn->query("SELECT o.id, c.customer_name, o.total_amount, o.status, o.created_at 
                      FROM orders o 
                      JOIN customers c ON o.customer_id = c.id 
                      ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Orders</h6>
                            <h2 class="mb-0"><?php echo $total_orders; ?></h2>
                        </div>
                        <i class="fas fa-shopping-bag fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Pending Orders</h6>
                            <h2 class="mb-0"><?php echo $pending_orders; ?></h2>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Today's Revenue</h6>
                            <h2 class="mb-0"><?php echo formatCurrency($todays_revenue); ?></h2>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Customers</h6>
                            <h2 class="mb-0"><?php echo $total_customers; ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($recent_orders) > 0): ?>
                                    <?php foreach($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($order['status']) {
                                                        'Pending' => 'warning',
                                                        'Processing' => 'info',
                                                        'Ready' => 'primary',
                                                        'Delivered' => 'success',
                                                        'Cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-light"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
