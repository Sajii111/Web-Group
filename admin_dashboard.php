<?php include 'db.php'; include 'header.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM cars WHERE id=$id");
    header("Location: admin_dashboard.php");
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap');

    :root {
        --black: #0f0f0f;
        --dark: #1a1a1a;
        --gold: #d4af37;
        --white: #f5f5f5;
        --gray: #888888;
    }

    body { background-color: var(--black); color: var(--white); font-family: 'Montserrat', sans-serif; }
    h2, h3 { font-family: 'Playfair Display', serif; }

    .container { max-width: 1200px; margin: 0 auto; padding: 4rem 20px; }
    .section-title { color: var(--gold); border-bottom: 1px solid #333; padding-bottom: 1rem; margin-bottom: 2rem; }

    .btn {
        display: inline-block;
        padding: 10px 25px;
        border: 1px solid var(--gold);
        color: var(--gold);
        text-decoration: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        transition: 0.3s;
    }
    .btn:hover { background: var(--gold); color: #000; }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: var(--dark);
    }
    .table th {
        text-align: left;
        padding: 15px;
        border-bottom: 2px solid #333;
        color: var(--gold);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .table td {
        padding: 15px;
        border-bottom: 1px solid #333;
        font-size: 0.9rem;
    }
    .table tr:hover { background: rgba(255,255,255,0.02); }

    .action-link { color: var(--gold); text-decoration: none; font-size: 0.8rem; }
    .action-link.delete { color: #ff4d4d; margin-left: 10px; }
</style>

<div class="container">
    <h2 class="section-title">Management Console</h2>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <h3>Vehicle Inventory</h3>
        <a href="add_car.php" class="btn">+ Registry New Vehicle</a>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th><th>Vehicle</th><th>Price</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM cars");
            while($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td style="color: var(--gray);"><?php echo $row['id']; ?></td>
                <td><strong><?php echo $row['brand']; ?></strong> <?php echo $row['model']; ?></td>
                <td>$<?php echo number_format($row['price']); ?></td>
                <td><span style="color: <?php echo $row['status'] == 'Sold' ? '#ff4d4d' : 'var(--gold)'; ?>"><?php echo $row['status']; ?></span></td>
                <td>
                    <a href="add_car.php?edit=<?php echo $row['id']; ?>" class="action-link">Edit</a>
                    <a href="admin_dashboard.php?delete=<?php echo $row['id']; ?>" class="action-link delete" onclick="return confirm('Archive this record permanently?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3 style="margin-top: 5rem; margin-bottom: 1.5rem;">Client Inquiries</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Client</th><th>Interest</th><th>Message</th><th>Date Received</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT inquiries.*, users.name, cars.model, cars.brand FROM inquiries 
                    JOIN users ON inquiries.user_id = users.id 
                    JOIN cars ON inquiries.car_id = cars.id";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['brand']." ".$row['model']; ?></td>
                <td style="max-width: 300px; color: var(--gray); font-style: italic; font-size: 0.85rem;">"<?php echo $row['message']; ?>"</td>
                <td style="font-size: 0.8rem; color: var(--gray);"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>