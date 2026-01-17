<?php session_start(); 
include 'db.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle AJAX requests
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if($_POST['action'] == 'add') {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $conn->real_escape_string($_POST['role']);
        
        // Check if email already exists
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if($check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit();
        }
        
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $role = $conn->real_escape_string($_POST['role']);
        
        // Check if email already exists for other users
        $check = $conn->query("SELECT id FROM users WHERE email='$email' AND id != $id");
        if($check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit();
        }
        
        // Update password only if provided
        if(!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name='$name', email='$email', password='$password', role='$role' WHERE id=$id";
        } else {
            $sql = "UPDATE users SET name='$name', email='$email', role='$role' WHERE id=$id";
        }
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'delete') {
        $id = intval($_POST['id']);
        
        // Prevent deleting yourself
        if($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
            exit();
        }
        
        $sql = "DELETE FROM users WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
    
    if($_POST['action'] == 'toggle_status') {
        $id = intval($_POST['id']);
        $status = $conn->real_escape_string($_POST['status']);
        
        // Add status column if it doesn't exist
        $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active'");
        
        $sql = "UPDATE users SET status='$status' WHERE id=$id";
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit();
    }
}

// Get user data for editing
if(isset($_GET['get']) && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT id, name, email, role FROM users WHERE id=$id");
    echo json_encode($result->fetch_assoc());
    exit();
}

// Ensure status column exists
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
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

        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 0;
        }

        .filter-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .filter-tab:hover {
            color: var(--primary);
        }

        .filter-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-success { background: var(--success); color: white; }
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

        table.dataTable { border-collapse: collapse !important; width: 100% !important; }
        table.dataTable thead th { background: var(--bg-light); padding: 16px 12px; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border); }
        table.dataTable tbody td { padding: 16px 12px; border-bottom: 1px solid var(--border); font-size: 14px; }
        table.dataTable tbody tr:hover { background: var(--bg-light); }

        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: #dbeafe; color: var(--info); }
        .badge-user { background: #d1fae5; color: var(--success); }
        .badge-active { background: #d1fae5; color: var(--success); }
        .badge-inactive { background: #fee2e2; color: var(--danger); }

        .action-btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; margin-right: 4px; transition: all 0.3s; }
        .action-btn i { margin-right: 4px; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: none; }
        .alert-success { background: #d1fae5; color: var(--success); border: 1px solid var(--success); }
        .alert-danger { background: #fee2e2; color: var(--danger); border: 1px solid var(--danger); }

        .user-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 14px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-box {
            background: var(--bg-light);
            padding: 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .stat-box h4 {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-box .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .password-hint {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
            font-style: italic;
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
    <div class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="logo-link">
            <h2>Elite Auto</h2>
        </a>
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
            <a href="admin_inquiries.php" class="menu-item">
                <i class="fas fa-envelope"></i> Inquiry Management
            </a>
            <a href="admin_users.php" class="menu-item active">
                <i class="fas fa-users"></i> User Management
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1>User Management</h1>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="content-area">
            <div class="stats-row">
                <?php
                $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                $total_admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='admin'")->fetch_assoc()['count'];
                $total_regular = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'")->fetch_assoc()['count'];
                ?>
                <div class="stat-box">
                    <h4>Total Users</h4>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                </div>
                <div class="stat-box">
                    <h4>Administrators</h4>
                    <div class="stat-value" style="color: var(--info);"><?php echo $total_admins; ?></div>
                </div>
                <div class="stat-box">
                    <h4>Regular Users</h4>
                    <div class="stat-value" style="color: var(--success);"><?php echo $total_regular; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="alert alert-success" id="successAlert"></div>
                <div class="alert alert-danger" id="errorAlert"></div>
                
                <div class="card-header">
                    <h3 class="card-title">User Accounts</h3>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>

                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterUsers('all')">
                        <i class="fas fa-users"></i> All Users
                    </button>
                    <button class="filter-tab" onclick="filterUsers('admin')">
                        <i class="fas fa-user-shield"></i> Administrators
                    </button>
                    <button class="filter-tab" onclick="filterUsers('user')">
                        <i class="fas fa-user"></i> Regular Users
                    </button>
                </div>

                <table id="usersTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
                        while($row = $result->fetch_assoc()):
                            $status = isset($row['status']) ? $row['status'] : 'active';
                            $initial = strtoupper(substr($row['name'], 0, 1));
                            $colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4'];
                            $color = $colors[$row['id'] % count($colors)];
                        ?>
                        <tr data-role="<?php echo $row['role']; ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="user-initial" style="background: <?php echo $color; ?>">
                                        <?php echo $initial; ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $row['role'] == 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo $row['role'] == 'admin' ? '<i class="fas fa-shield-alt"></i> Admin' : '<i class="fas fa-user"></i> User'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $status == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td style="font-size: 13px; color: var(--text-secondary);">
                                <?php echo isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A'; ?>
                            </td>
                            <td>
                                <button class="action-btn btn-primary" onclick="editUser(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <?php if($row['id'] != $_SESSION['user_id']): ?>
                                <button class="action-btn btn-danger" onclick="deleteUser(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add User</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId" name="id">
                    <input type="hidden" id="formAction" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password <span id="passwordLabel">*</span></label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="password-hint" id="passwordHint">Minimum 6 characters</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Role *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" class="btn" onclick="closeModal()" style="background: #e5e7eb; color: #374151;">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save User</button>
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
            table = $('#usersTable').DataTable({ 
                pageLength: 10, 
                order: [[0, 'desc']] 
            });
        });

        function filterUsers(role) {
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.closest('.filter-tab').classList.add('active');

            // Filter table
            if(role === 'all') {
                table.column(3).search('').draw();
            } else {
                table.column(3).search(role).draw();
            }
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('formAction').value = 'add';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('password').required = true;
            document.getElementById('passwordLabel').textContent = '*';
            document.getElementById('passwordHint').textContent = 'Minimum 6 characters';
            document.getElementById('userModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        function editUser(id) {
            fetch(`admin_users.php?get=1&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = 'Edit User';
                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('userId').value = data.id;
                    document.getElementById('name').value = data.name;
                    document.getElementById('email').value = data.email;
                    document.getElementById('role').value = data.role;
                    document.getElementById('password').value = '';
                    document.getElementById('password').required = false;
                    document.getElementById('passwordLabel').textContent = '(leave blank to keep current)';
                    document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
                    document.getElementById('userModal').classList.add('active');
                });
        }

        function deleteUser(id) {
            if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('admin_users.php', { method: 'POST', body: formData })
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

        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate password length if provided
            const password = document.getElementById('password').value;
            const isEdit = document.getElementById('formAction').value === 'edit';
            
            if(password && password.length < 6) {
                showAlert('errorAlert', 'Password must be at least 6 characters long');
                return;
            }
            
            if(!isEdit && !password) {
                showAlert('errorAlert', 'Password is required for new users');
                return;
            }

            const formData = new FormData(this);

            fetch('admin_users.php', { method: 'POST', body: formData })
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