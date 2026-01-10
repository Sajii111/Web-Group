<?php session_start(); 
include 'db.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle AJAX requests
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if($_POST['action'] == 'delete') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM inquiries WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Inquiry deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'update_status') {
        $id = intval($_POST['id']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Add status column if it doesn't exist
        $conn->query("ALTER TABLE inquiries ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pending'");
        
        $sql = "UPDATE inquiries SET status='$status' WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
}

// Get inquiry data for viewing
if(isset($_GET['get']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT inquiries.*, users.name, users.email, cars.model, cars.brand 
                            FROM inquiries 
                            JOIN users ON inquiries.user_id = users.id 
                            JOIN cars ON inquiries.car_id = cars.id 
                            WHERE inquiries.id=$id");
    echo json_encode($result->fetch_assoc());
    exit();
}

// Ensure status column exists
$conn->query("ALTER TABLE inquiries ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pending'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Management - Admin Dashboard</title>
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
            --warning: #f59e0b;
            --info: #3b82f6;
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
        .btn-info { background: var(--info); color: white; }
        .logout-btn { background: var(--danger); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 14px; }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 12px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 20px; font-weight: 700; }
        .close-modal { background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary); }
        .modal-body { padding: 24px; }

        .info-row { display: flex; margin-bottom: 20px; }
        .info-label { font-weight: 600; width: 150px; color: var(--text-secondary); font-size: 14px; }
        .info-value { flex: 1; font-size: 14px; }

        .message-box { background: var(--bg-light); padding: 16px; border-radius: 8px; border-left: 4px solid var(--primary); }
        .message-box p { font-style: italic; color: var(--text-secondary); line-height: 1.6; }

        table.dataTable { border-collapse: collapse !important; width: 100% !important; }
        table.dataTable thead th { background: var(--bg-light); padding: 16px 12px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border); }
        table.dataTable tbody td { padding: 16px 12px; border-bottom: 1px solid var(--border); font-size: 14px; }
        table.dataTable tbody tr:hover { background: var(--bg-light); }

        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: var(--warning); }
        .badge-reviewed { background: #dbeafe; color: var(--info); }
        .badge-responded { background: #d1fae5; color: var(--success); }
        .badge-closed { background: #f3f4f6; color: var(--text-secondary); }

        .action-btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; margin-right: 4px; transition: all 0.3s; }
        .action-btn i { margin-right: 4px; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: none; }
        .alert-success { background: #d1fae5; color: var(--success); border: 1px solid var(--success); }
        .alert-danger { background: #fee2e2; color: var(--danger); border: 1px solid var(--danger); }

        .status-select { padding: 6px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 12px; cursor: pointer; }
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
            <a href="admin_brands.php" class="menu-item">
                <i class="fas fa-tags"></i> Brand Management
            </a>
            <a href="admin_inquiries.php" class="menu-item active">
                <i class="fas fa-envelope"></i> Inquiry Management
            </a>
            <a href="admin_users.php" class="menu-item">
                <i class="fas fa-users"></i> User Management
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1>Inquiry Management</h1>
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
                    <h3 class="card-title">All Inquiries</h3>
                </div>

                <table id="inquiriesTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Vehicle Interest</th>
                            <th>Message Preview</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT inquiries.*, users.name, users.email, cars.model, cars.brand 
                                FROM inquiries 
                                JOIN users ON inquiries.user_id = users.id 
                                JOIN cars ON inquiries.car_id = cars.id
                                ORDER BY inquiries.created_at DESC";
                        $result = $conn->query($sql);
                        while($row = $result->fetch_assoc()):
                            $status = isset($row['status']) ? $row['status'] : 'Pending';
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($row['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['brand']." ".$row['model']); ?></td>
                            <td style="max-width: 250px; color: var(--text-secondary); font-style: italic; font-size: 13px;">
                                "<?php echo htmlspecialchars(substr($row['message'], 0, 60)); ?><?php echo strlen($row['message']) > 60 ? '...' : ''; ?>"
                            </td>
                            <td style="font-size: 13px;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <select class="status-select" onchange="updateStatus(<?php echo $row['id']; ?>, this.value)">
                                    <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Reviewed" <?php echo $status == 'Reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                    <option value="Responded" <?php echo $status == 'Responded' ? 'selected' : ''; ?>>Responded</option>
                                    <option value="Closed" <?php echo $status == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </td>
                            <td>
                                <button class="action-btn btn-info" onclick="viewInquiry(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="action-btn btn-danger" onclick="deleteInquiry(<?php echo $row['id']; ?>)">
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

    <div class="modal" id="inquiryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Inquiry Details</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="inquiryDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#inquiriesTable').DataTable({ 
                pageLength: 10, 
                order: [[0, 'desc']] 
            });
        });

        function closeModal() {
            document.getElementById('inquiryModal').classList.remove('active');
        }

        function viewInquiry(id) {
            fetch(`admin_inquiries.php?get=1&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    const details = `
                        <div class="info-row">
                            <div class="info-label">Inquiry ID:</div>
                            <div class="info-value">#${data.id}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Customer Name:</div>
                            <div class="info-value"><strong>${data.name}</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value">${data.email}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Vehicle Interest:</div>
                            <div class="info-value"><strong>${data.brand} ${data.model}</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date Received:</div>
                            <div class="info-value">${new Date(data.created_at).toLocaleString()}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status:</div>
                            <div class="info-value">
                                <span class="badge badge-${data.status ? data.status.toLowerCase() : 'pending'}">
                                    ${data.status || 'Pending'}
                                </span>
                            </div>
                        </div>
                        <div style="margin-top: 24px;">
                            <div class="info-label" style="margin-bottom: 12px;">Customer Message:</div>
                            <div class="message-box">
                                <p>${data.message}</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('inquiryDetails').innerHTML = details;
                    document.getElementById('inquiryModal').classList.add('active');
                });
        }

        function updateStatus(id, status) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);

            fetch('admin_inquiries.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showAlert('successAlert', data.message);
                } else {
                    showAlert('errorAlert', data.message);
                    location.reload();
                }
            });
        }

        function deleteInquiry(id) {
            if(confirm('Are you sure you want to delete this inquiry?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('admin_inquiries.php', { method: 'POST', body: formData })
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

        function showAlert(elementId, message) {
            const alert = document.getElementById(elementId);
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => alert.style.display = 'none', 5000);
        }
    </script>
</body>
</html>