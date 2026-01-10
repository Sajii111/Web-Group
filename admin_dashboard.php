<?php session_start(); 
include 'db.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get statistics
$total_cars = $conn->query("SELECT COUNT(*) as count FROM cars")->fetch_assoc()['count'];
$available_cars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE status='Available'")->fetch_assoc()['count'];
$total_inquiries = $conn->query("SELECT COUNT(*) as count FROM inquiries")->fetch_assoc()['count'];
$total_brands = $conn->query("SELECT COUNT(DISTINCT brand) as count FROM cars")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Elite Auto</title>
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
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: white;
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
            color: var(--text-primary);
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .stat-title {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue { background: #dbeafe; color: var(--primary); }
        .stat-icon.green { background: #d1fae5; color: var(--success); }
        .stat-icon.orange { background: #fed7aa; color: var(--warning); }
        .stat-icon.purple { background: #e9d5ff; color: #9333ea; }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-description {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* Recent Activity */
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
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
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
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 16px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .activity-content h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .activity-content p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Elite Auto</h2>
            <p>Admin Dashboard</p>
        </div>
        <nav class="sidebar-menu">
            <a href="admin_dashboard.php" class="menu-item active">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
            <a href="admin_vehicles.php" class="menu-item">
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
            <h1>Dashboard Overview</h1>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="content-area">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Vehicles</div>
                            <div class="stat-value"><?php echo $total_cars; ?></div>
                            <div class="stat-description">All vehicles in inventory</div>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-car"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Available Vehicles</div>
                            <div class="stat-value"><?php echo $available_cars; ?></div>
                            <div class="stat-description">Ready for sale</div>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Inquiries</div>
                            <div class="stat-value"><?php echo $total_inquiries; ?></div>
                            <div class="stat-description">Customer inquiries</div>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Brands</div>
                            <div class="stat-value"><?php echo $total_brands; ?></div>
                            <div class="stat-description">Vehicle brands</div>
                        </div>
                        <div class="stat-icon purple">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Inquiries</h3>
                </div>
                <ul class="activity-list">
                    <?php
                    $sql = "SELECT inquiries.*, users.name, cars.model, cars.brand FROM inquiries 
                            JOIN users ON inquiries.user_id = users.id 
                            JOIN cars ON inquiries.car_id = cars.id
                            ORDER BY inquiries.created_at DESC LIMIT 5";
                    $result = $conn->query($sql);
                    if($result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                    ?>
                    <li class="activity-item">
                        <div class="activity-icon blue">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="activity-content">
                            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                            <p>Inquiry about <?php echo htmlspecialchars($row['brand']." ".$row['model']); ?></p>
                            <div class="activity-time">
                                <i class="far fa-clock"></i> <?php echo date('M d, Y - h:i A', strtotime($row['created_at'])); ?>
                            </div>
                        </div>
                    </li>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <li class="activity-item">
                        <p style="color: var(--text-secondary); text-align: center; width: 100%;">No recent inquiries</p>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>