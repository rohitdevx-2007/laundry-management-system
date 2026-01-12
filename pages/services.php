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
        $stmt = $conn->prepare("DELETE FROM services WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $message = "Service deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting service: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_name = sanitize($_POST['service_name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if (!empty($service_name) && $price > 0) {
        try {
            if ($id) {
                // Update
                $stmt = $conn->prepare("UPDATE services SET service_name = :name, description = :desc, price = :price WHERE id = :id");
                $stmt->bindParam(':id', $id);
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO services (service_name, description, price) VALUES (:name, :desc, :price)");
            }
            $stmt->bindParam(':name', $service_name);
            $stmt->bindParam(':desc', $description);
            $stmt->bindParam(':price', $price);
            $stmt->execute();
            $message = "Service saved successfully!";
        } catch (PDOException $e) {
            $error = "Error saving service: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Fetch Services
$stmt = $conn->query("SELECT * FROM services ORDER BY id DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Service Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i> Add New Service
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
                            <th>Service Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($services as $service): ?>
                            <tr>
                                <td><?php echo $service['id']; ?></td>
                                <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($service['description']); ?></td>
                                <td><?php echo formatCurrency($service['price']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white" 
                                            onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $service['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this service?')">
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
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="id" id="service_id">
                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text" class="form-control" name="service_name" id="service_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="price" id="price" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editService(service) {
    document.getElementById('modalTitle').innerText = 'Edit Service';
    document.getElementById('service_id').value = service.id;
    document.getElementById('service_name').value = service.service_name;
    document.getElementById('description').value = service.description;
    document.getElementById('price').value = service.price;
    
    var modal = new bootstrap.Modal(document.getElementById('serviceModal'));
    modal.show();
}

function resetForm() {
    document.getElementById('modalTitle').innerText = 'Add New Service';
    document.getElementById('service_id').value = '';
    document.getElementById('service_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('price').value = '';
}
</script>

<?php require_once '../includes/footer.php'; ?>
