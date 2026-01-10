<?php session_start(); 
include 'db.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Create uploads directory if it doesn't exist
if (!file_exists('uploads/brands')) {
    mkdir('uploads/brands', 0777, true);
}

// Create brands table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle AJAX requests
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if($_POST['action'] == 'add') {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Handle logo upload
        $logo = '';
        if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '_' . time() . '.' . $ext;
                if(move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/brands/' . $newname)) {
                    $logo = $newname;
                }
            }
        }
        
        $sql = "INSERT INTO brands (name, description, logo) VALUES ('$name', '$description', '$logo')";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Brand added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Get existing logo
        $existing = $conn->query("SELECT logo FROM brands WHERE id=$id")->fetch_assoc();
        $logo = $existing['logo'];
        
        // Handle new logo upload
        if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $filename = $_FILES['logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '_' . time() . '.' . $ext;
                if(move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/brands/' . $newname)) {
                    // Delete old logo
                    if($logo && file_exists('uploads/brands/' . $logo)) {
                        unlink('uploads/brands/' . $logo);
                    }
                    $logo = $newname;
                }
            }
        }
        
        $sql = "UPDATE brands SET name='$name', description='$description', logo='$logo' WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Brand updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'delete') {
        $id = intval($_POST['id']);
        
        // Get logo to delete
        $result = $conn->query("SELECT logo FROM brands WHERE id=$id");
        if($row = $result->fetch_assoc()) {
            if($row['logo'] && file_exists('uploads/brands/' . $row['logo'])) {
                unlink('uploads/brands/' . $row['logo']);
            }
        }
        
        $sql = "DELETE FROM brands WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Brand deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
}

// Get brand data for editing
if(isset($_GET['get']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM brands WHERE id=$id");
    echo json_encode($result->fetch_assoc());
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Management - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--text-primary); }

        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: var(--sidebar-bg); color: white; overflow-y: auto; z-index: 1000; }
        .sidebar-header { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 20px; font-weight: 700; }
        .sidebar-header p { font-size: 12px; color: #9ca3af; margin-top: 4px; }
        .sidebar-menu { padding: 20px 0; }
        .menu-item { display: flex; align-items: center; padding: 12px 20px; color: #d1d5db; text-decoration: none; transition: all 0.3s; font-size: 14px; font-weight: 500; }
        .menu-item:hover, .menu-item.active { background: var(--sidebar-hover); color: white; border-left: 3px solid var(--primary); }
        .menu-item i { width: 20px; margin-right: 12px; font-size: 16px; }

        .main-content { margin-left: 260px; min-height: 100vh; }
        .topbar { background: white; padding: 16px 32px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .topbar h1 { font-size: 24px; font-weight: 700; }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .content-area { padding: 32px; }

        .card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 24px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .card-title { font-size: 20px; font-weight: 700; }

        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-danger { background: var(--danger); color: white; }
        .logout-btn { background: var(--danger); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 14px; }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 20px; font-weight: 700; }
        .close-modal { background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary); }
        .modal-body { padding: 24px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Inter', sans-serif; }
        .form-control:focus { outline: none; border-color: var(--primary); }
        textarea.form-control { resize: vertical; min-height: 100px; }

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
            max-height: 150px;
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
            object-fit: contain;
            border-radius: 8px;
            background: white;
            padding: 8px;
        }

        table.dataTable { border-collapse: collapse !important; width: 100% !important; }
        table.dataTable thead th { background: var(--bg-light); padding: 16px 12px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border); }
        table.dataTable tbody td { padding: 16px 12px; border-bottom: 1px solid var(--border); font-size: 14px; }
        table.dataTable tbody tr:hover { background: var(--bg-light); }

        .action-btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; margin-right: 4px; transition: all 0.3s; }
        .action-btn i { margin-right: 4px; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: none; }
        .alert-success { background: #d1fae5; color: var(--success); border: 1px solid var(--success); }
        .alert-danger { background: #fee2e2; color: var(--danger); border: 1px solid var(--danger); }

        .brand-logo { 
            width: 60px; 
            height: 60px; 
            object-fit: contain; 
            border-radius: 8px; 
            border: 1px solid var(--border);
            background: white;
            padding: 8px;
        }

        .no-logo {
            width: 60px;
            height: 60px;
            background: var(--bg-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Luxury Cars</h2>
            <p>Admin Dashboard</p>
        </div>
        <nav class="sidebar-menu">
            <a href="admin_dashboard.php" class="menu-item">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="admin_vehicles.php" class="menu-item">
                <i class="fas fa-car"></i> Vehicle Management
            </a>
            <a href="admin_brands.php" class="menu-item active">
                <i class="fas fa-tags"></i> Brand Management
            </a>
            <a href="admin_inquiries.php" class="menu-item">
                <i class="fas fa-envelope"></i> Inquiry Management
            </a>
            <a href="admin_users.php" class="menu-item">
                <i class="fas fa-users"></i> User Management
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1>Brand Management</h1>
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
                    <h3 class="card-title">All Brands</h3>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Brand
                    </button>
                </div>

                <table id="brandsTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Brand Name</th>
                            <th>Description</th>
                            <th>Vehicles Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT b.*, COUNT(c.id) as vehicle_count FROM brands b 
                                               LEFT JOIN cars c ON b.name = c.brand 
                                               GROUP BY b.id");
                        while($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php if($row['logo']): ?>
                                    <img src="uploads/brands/<?php echo htmlspecialchars($row['logo']); ?>" alt="Logo" class="brand-logo">
                                <?php else: ?>
                                    <div class="no-logo">
                                        <i class="fas fa-image" style="color: var(--text-secondary);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td style="max-width: 300px;"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?><?php echo strlen($row['description']) > 100 ? '...' : ''; ?></td>
                            <td><span style="color: var(--primary); font-weight: 600;"><?php echo $row['vehicle_count']; ?></span> vehicles</td>
                            <td>
                                <button class="action-btn btn-primary" onclick="editBrand(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="action-btn btn-danger" onclick="deleteBrand(<?php echo $row['id']; ?>)">
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

    <div class="modal" id="brandModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Brand</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="brandForm" enctype="multipart/form-data">
                    <input type="hidden" id="brandId" name="id">
                    <input type="hidden" id="formAction" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Brand Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Brand Logo</label>
                        <div id="currentLogoDiv" style="display: none;"></div>
                        <div class="image-upload-wrapper" onclick="document.getElementById('logoInput').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p style="margin: 8px 0; color: var(--text-secondary);">Click to upload logo</p>
                            <small style="color: var(--text-secondary);">JPG, PNG, GIF, SVG, WEBP (Max 5MB)</small>
                        </div>
                        <input type="file" id="logoInput" name="logo" accept="image/*" style="display: none;" onchange="previewLogo(this)">
                        <img id="logoPreview" class="image-preview" style="display: none;">
                    </div>
                    
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" class="btn" onclick="closeModal()" style="background: #e5e7eb; color: #374151;">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#brandsTable').DataTable({ pageLength: 10, order: [[0, 'desc']] });
        });

        function previewLogo(input) {
            const preview = document.getElementById('logoPreview');
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
            document.getElementById('modalTitle').textContent = 'Add Brand';
            document.getElementById('formAction').value = 'add';
            document.getElementById('brandForm').reset();
            document.getElementById('brandId').value = '';
            document.getElementById('logoPreview').style.display = 'none';
            document.getElementById('currentLogoDiv').style.display = 'none';
            document.getElementById('brandModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('brandModal').classList.remove('active');
        }

        function editBrand(id) {
            fetch(`admin_brands.php?get=1&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = 'Edit Brand';
                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('brandId').value = data.id;
                    document.getElementById('name').value = data.name;
                    document.getElementById('description').value = data.description || '';
                    
                    // Show current logo
                    const currentLogoDiv = document.getElementById('currentLogoDiv');
                    if(data.logo) {
                        currentLogoDiv.innerHTML = `
                            <div class="current-image">
                                <img src="uploads/brands/${data.logo}" alt="Current">
                                <div>
                                    <strong>Current Logo</strong><br>
                                    <small style="color: var(--text-secondary);">Upload new logo to replace</small>
                                </div>
                            </div>
                        `;
                        currentLogoDiv.style.display = 'block';
                    } else {
                        currentLogoDiv.style.display = 'none';
                    }
                    
                    document.getElementById('logoPreview').style.display = 'none';
                    document.getElementById('brandModal').classList.add('active');
                });
        }

        function deleteBrand(id) {
            if(confirm('Are you sure you want to delete this brand? This may affect related vehicles.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('admin_brands.php', { method: 'POST', body: formData })
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

        document.getElementById('brandForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('admin_brands.php', { method: 'POST', body: new FormData(this) })
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
            setTimeout(() => alert.style.display = 'none', 5000);
        }
    </script>
</body>
</html>