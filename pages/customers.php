<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
checkLogin();

$message = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $message = "Customer deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting customer: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = sanitize($_POST['customer_name']);
    $mobile = sanitize($_POST['mobile']);
    $address = sanitize($_POST['address']);
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if (!empty($customer_name) && !empty($mobile)) {
        try {
            if ($id) {
                // Update
                $stmt = $conn->prepare("UPDATE customers SET customer_name = :name, mobile = :mobile, address = :address WHERE id = :id");
                $stmt->bindParam(':id', $id);
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO customers (customer_name, mobile, address) VALUES (:name, :mobile, :address)");
            }
            $stmt->bindParam(':name', $customer_name);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':address', $address);
            $stmt->execute();
            $message = "Customer saved successfully!";
        } catch (PDOException $e) {
            $error = "Error saving customer: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Fetch Customers
$stmt = $conn->query("SELECT * FROM customers ORDER BY id DESC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Customer Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="resetForm()">
            <i class="fas fa-user-plus me-2"></i> Add New Customer
        </button>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($customers as $customer): ?>
                            <tr>
                                <td><?php echo $customer['id']; ?></td>
                                <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                                <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white" 
                                            onclick="editCustomer(<?php echo htmlspecialchars(json_encode($customer)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $customer['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this customer? This will also delete their orders.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="id" id="customer_id">
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" name="customer_name" id="customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile" id="mobile" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCustomer(customer) {
    document.getElementById('modalTitle').innerText = 'Edit Customer';
    document.getElementById('customer_id').value = customer.id;
    document.getElementById('customer_name').value = customer.customer_name;
    document.getElementById('mobile').value = customer.mobile;
    document.getElementById('address').value = customer.address;
    
    var modal = new bootstrap.Modal(document.getElementById('customerModal'));
    modal.show();
}

function resetForm() {
    document.getElementById('modalTitle').innerText = 'Add New Customer';
    document.getElementById('customer_id').value = '';
    document.getElementById('customer_name').value = '';
    document.getElementById('mobile').value = '';
    document.getElementById('address').value = '';
}
</script>

<?php require_once '../includes/footer.php'; ?>
