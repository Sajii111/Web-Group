<?php session_start(); 
include 'db.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Create uploads directory if it doesn't exist
if (!file_exists('uploads/vehicles')) {
    mkdir('uploads/vehicles', 0777, true);
}

// Handle AJAX requests
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if($_POST['action'] == 'add') {
        $brand = $conn->real_escape_string($_POST['brand']);
        $model = $conn->real_escape_string($_POST['model']);
        $year = intval($_POST['year']);
        $millage = intval($_POST['millage']);
        $price = floatval($_POST['price']);
        $status = $conn->real_escape_string($_POST['status']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Handle image upload
        $image = '';
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '_' . time() . '.' . $ext;
                if(move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/vehicles/' . $newname)) {
                    $image = $newname;
                }
            }
        }

        $sql = "INSERT INTO cars (brand, model, year, millage, price, status, description, image) 
                VALUES ('$brand', '$model', $year, $millage, $price, '$status', '$description', '$image')";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Vehicle added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $brand = $conn->real_escape_string($_POST['brand']);
        $model = $conn->real_escape_string($_POST['model']);
        $year = intval($_POST['year']);
        $millage = intval($_POST['millage']);
        $price = floatval($_POST['price']);
        $status = $conn->real_escape_string($_POST['status']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Get existing image
        $existing = $conn->query("SELECT image FROM cars WHERE id=$id")->fetch_assoc();
        $image = $existing['image'];
        
        // Handle new image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '_' . time() . '.' . $ext;
                if(move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/vehicles/' . $newname)) {
                    // Delete old image
                    if($image && file_exists('uploads/vehicles/' . $image)) {
                        unlink('uploads/vehicles/' . $image);
                    }
                    $image = $newname;
                }
            }
        }

        $sql = "UPDATE cars SET brand='$brand', model='$model', year=$year, millage=$millage, price=$price, 
                status='$status', description='$description', image='$image' WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'delete') {
        $id = intval($_POST['id']);
        
        // Get image to delete
        $result = $conn->query("SELECT image FROM cars WHERE id=$id");
        if($row = $result->fetch_assoc()) {
            if($row['image'] && file_exists('uploads/vehicles/' . $row['image'])) {
                unlink('uploads/vehicles/' . $row['image']);
            }
        }
        
        $sql = "DELETE FROM cars WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
}

// Get vehicle data for editing
if(isset($_GET['get']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM cars WHERE id=$id");
    echo json_encode($result->fetch_assoc());
    exit();
}

// Add image column if not exists
$conn->query("ALTER TABLE cars ADD COLUMN IF NOT EXISTS image VARCHAR(255)");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --sidebar-bg: #1f2937;
            --sidebar-hover: #374151;
            --bg-light: #f9fafb;
            --border: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-primary);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 20px;
            font-weight: 700;
        }

        .sidebar-header p {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }

        .menu-item:hover, .menu-item.active {
            background: var(--sidebar-hover);
            color: white;
            border-left: 3px solid var(--primary);
        }

        .menu-item i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 16px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .content-area {
            padding: 32px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Image Upload */
        .image-upload-wrapper {
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .image-upload-wrapper:hover {
            border-color: var(--primary);
            background: var(--bg-light);
        }

        .image-upload-wrapper i {
            font-size: 48px;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 12px;
        }

        .current-image {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--bg-light);
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .current-image img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        /* DataTables Customization */
        .dataTables_wrapper {
            padding: 0;
        }

        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.dataTable thead th {
            background: var(--bg-light);
            padding: 16px 12px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border);
        }

        table.dataTable tbody td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        table.dataTable tbody tr:hover {
            background: var(--bg-light);
        }

        .vehicle-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .no-image {
            width: 60px;
            height: 60px;
            background: var(--bg-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: var(--success);
        }

        .badge-danger {
            background: #fee2e2;
            color: var(--danger);
        }

        .badge-warning {
            background: #fef3c7;
            color: var(--warning);
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 4px;
            transition: all 0.3s;
        }

        .action-btn i {
            margin-right: 4px;
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background: #d1fae5;
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-danger {
            background: #fee2e2;
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .logo-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .logo-link:hover h2 {
            color: var(--primary);
            transition: color 0.3s;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="logo-link">
            <h2>Elite Auto</h2>
        </a>
        <p>Admin Dashboard</p>
    </div>
        <nav class="sidebar-menu">
            <a href="admin_dashboard.php" class="menu-item">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="admin_vehicles.php" class="menu-item active">
                <i class="fas fa-car"></i>
                Vehicle Management
            </a>
            <a href="admin_brands.php" class="menu-item">
                <i class="fas fa-tags"></i>
                Brand Management
            </a>
            <a href="admin_inquiries.php" class="menu-item">
                <i class="fas fa-envelope"></i>
                Inquiry Management
            </a>
            <a href="admin_users.php" class="menu-item">
                <i class="fas fa-users"></i>
                User Management
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h1>Vehicle Management</h1>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="alert alert-success" id="successAlert"></div>
                <div class="alert alert-danger" id="errorAlert"></div>
                
                <div class="card-header">
                    <h3 class="card-title">All Vehicles</h3>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Vehicle
                    </button>
                </div>

                <table id="vehiclesTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Millage (KM)</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM cars");
                        while($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php if($row['image']): ?>
                                    <img src="uploads/vehicles/<?php echo htmlspecialchars($row['image']); ?>" alt="Vehicle" class="vehicle-image">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-car" style="color: var(--text-secondary);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['brand']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['model']); ?></td>
                            <td><?php echo $row['year']; ?></td>
                            <td><?php echo $row['millage']; ?></td>
                            <td>Rs. <?php echo $row['price']; ?></td>
                            <td>
                                <span class="badge <?php 
                                    echo $row['status'] == 'Available' ? 'badge-success' : 
                                        ($row['status'] == 'Sold' ? 'badge-danger' : 'badge-warning'); 
                                ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn btn-primary" onclick="editVehicle(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="action-btn btn-danger" onclick="deleteVehicle(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal" id="vehicleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Vehicle</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="vehicleForm" enctype="multipart/form-data">
                    <input type="hidden" id="vehicleId" name="id">
                    <input type="hidden" id="formAction" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Brand *</label>
                        <select class="form-control" id="brand" name="brand" required>
                            <option value="">Select Brand</option>
                            <?php
                            $brands = $conn->query("SELECT DISTINCT name FROM brands ORDER BY name");
                            while($brand = $brands->fetch_assoc()):
                            ?>
                            <option value="<?php echo htmlspecialchars($brand['name']); ?>">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Model *</label>
                        <input type="text" class="form-control" id="model" name="model" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Year *</label>
                        <input type="number" class="form-control" id="year" name="year" min="1900" max="2030" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Price *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="Available">Available</option>
                            <option value="Sold">Sold</option>
                            <option value="Reserved">Reserved</option>
                        </select>
                    </div>

                     <div class="form-group">
                        <label>Millage (KM)</label>
                        <input type="number" class="form-control" id="millage" name="millage" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Vehicle Image</label>
                        <div id="currentImageDiv" style="display: none;"></div>
                        <div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p style="margin: 8px 0; color: var(--text-secondary);">Click to upload image</p>
                            <small style="color: var(--text-secondary);">JPG, PNG, GIF, WEBP (Max 5MB)</small>
                        </div>
                        <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        <img id="imagePreview" class="image-preview" style="display: none;">
                    </div>
                    
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" class="btn" onclick="closeModal()" style="background: #e5e7eb; color: #374151;">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        let table;

        $(document).ready(function() {
            table = $('#vehiclesTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']]
            });
        });

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Vehicle';
            document.getElementById('formAction').value = 'add';
            document.getElementById('vehicleForm').reset();
            document.getElementById('vehicleId').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('currentImageDiv').style.display = 'none';
            document.getElementById('vehicleModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('vehicleModal').classList.remove('active');
        }

        function editVehicle(id) {
            fetch(`admin_vehicles.php?get=1&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = 'Edit Vehicle';
                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('vehicleId').value = data.id;
                    document.getElementById('brand').value = data.brand;
                    document.getElementById('model').value = data.model;
                    document.getElementById('year').value = data.year;
                    document.getElementById('millage').value = data.millage;
                    document.getElementById('price').value = data.price;
                    document.getElementById('status').value = data.status;
                    document.getElementById('description').value = data.description || '';
                    
                    // Show current image
                    const currentImageDiv = document.getElementById('currentImageDiv');
                    if(data.image) {
                        currentImageDiv.innerHTML = `
                            <div class="current-image">
                                <img src="uploads/vehicles/${data.image}" alt="Current">
                                <div>
                                    <strong>Current Image</strong><br>
                                    <small style="color: var(--text-secondary);">Upload new image to replace</small>
                                </div>
                            </div>
                        `;
                        currentImageDiv.style.display = 'block';
                    } else {
                        currentImageDiv.style.display = 'none';
                    }
                    
                    document.getElementById('imagePreview').style.display = 'none';
                    document.getElementById('vehicleModal').classList.add('active');
                });
        }

        function deleteVehicle(id) {
            if(confirm('Are you sure you want to delete this vehicle?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('admin_vehicles.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        showAlert('successAlert', data.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('errorAlert', data.message);
                    }
                });
            }
        }

        document.getElementById('vehicleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);

            fetch('admin_vehicles.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    closeModal();
                    showAlert('successAlert', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('errorAlert', data.message);
                }
            });
        });

        function showAlert(elementId, message) {
            const alert = document.getElementById(elementId);
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>