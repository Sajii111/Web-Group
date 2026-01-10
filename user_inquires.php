<?php 
include 'db.php'; 
include 'header.php'; 

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete inquiry
if(isset($_POST['delete_inquiry'])) {
    $inquiry_id = intval($_POST['inquiry_id']);
    
    // Verify that this inquiry belongs to the logged-in user
    $check = $conn->query("SELECT id FROM inquiries WHERE id=$inquiry_id AND user_id=$user_id");
    
    if($check->num_rows > 0) {
        $conn->query("DELETE FROM inquiries WHERE id=$inquiry_id");
        $_SESSION['success_message'] = "Inquiry deleted successfully.";
    } else {
        $_SESSION['error_message'] = "You don't have permission to delete this inquiry.";
    }
    
    header("Location: user_inquiries.php");
    exit();
}

// Get user's inquiries
$sql = "SELECT inquiries.*, cars.brand, cars.model, cars.image 
        FROM inquiries 
        JOIN cars ON inquiries.car_id = cars.id 
        WHERE inquiries.user_id = $user_id 
        ORDER BY inquiries.created_at DESC";
$result = $conn->query($sql);
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap');

    :root {
        --black: #0f0f0f;
        --dark: #1a1a1a;
        --gold: #d4af37;
        --white: #f5f5f5;
        --gray: #888888;
    }

    body {
        background-color: var(--black);
        color: var(--white);
        font-family: 'Montserrat', sans-serif;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 4rem 20px;
    }

    .page-header {
        margin-bottom: 3rem;
        border-bottom: 1px solid #333;
        padding-bottom: 2rem;
    }

    .page-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: var(--gold);
        margin-bottom: 0.5rem;
    }

    .page-header p {
        color: var(--gray);
        font-size: 1rem;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 4px;
        margin-bottom: 2rem;
        animation: slideDown 0.3s ease;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid #10b981;
        color: #6ee7b7;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid #ef4444;
        color: #fca5a5;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .inquiry-card {
        background: var(--dark);
        border: 1px solid #333;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .inquiry-card:hover {
        border-color: var(--gold);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.1);
    }

    .inquiry-header {
        display: grid;
        grid-template-columns: 120px 1fr auto;
        gap: 1.5rem;
        padding: 1.5rem;
        align-items: center;
        border-bottom: 1px solid #333;
    }

    .vehicle-image {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border: 1px solid #333;
    }

    .no-image {
        width: 120px;
        height: 80px;
        background: var(--black);
        border: 1px solid #333;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray);
    }

    .vehicle-info h3 {
        color: var(--gold);
        font-size: 1.2rem;
        margin-bottom: 0.3rem;
        font-weight: 600;
    }

    .vehicle-info p {
        color: var(--gray);
        font-size: 0.85rem;
    }

    .status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #fbbf24;
        border: 1px solid #f59e0b;
    }

    .status-reviewed {
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
        border: 1px solid #3b82f6;
    }

    .status-responded {
        background: rgba(16, 185, 129, 0.1);
        color: #6ee7b7;
        border: 1px solid #10b981;
    }

    .status-closed {
        background: rgba(156, 163, 175, 0.1);
        color: #9ca3af;
        border: 1px solid #6b7280;
    }

    .inquiry-body {
        padding: 1.5rem;
    }

    .message-label {
        color: var(--gold);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .message-content {
        color: var(--gray);
        line-height: 1.8;
        font-style: italic;
        padding: 1rem;
        background: var(--black);
        border-left: 3px solid var(--gold);
        margin-bottom: 1rem;
    }

    .inquiry-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: rgba(0,0,0,0.3);
        border-top: 1px solid #333;
    }

    .inquiry-date {
        color: var(--gray);
        font-size: 0.85rem;
    }

    .inquiry-date i {
        margin-right: 8px;
        color: var(--gold);
    }

    .btn-delete {
        padding: 8px 20px;
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: var(--white);
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: var(--dark);
        border: 1px solid #333;
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray);
        margin-bottom: 1.5rem;
    }

    .empty-state h3 {
        font-family: 'Playfair Display', serif;
        color: var(--gold);
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: var(--gray);
        margin-bottom: 2rem;
    }

    .btn-primary {
        display: inline-block;
        padding: 12px 30px;
        background: transparent;
        border: 1px solid var(--gold);
        color: var(--gold);
        text-decoration: none;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .btn-primary:hover {
        background: var(--gold);
        color: var(--black);
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
    }

    .inquiry-id {
        color: var(--gray);
        font-size: 0.75rem;
        margin-bottom: 0.3rem;
    }

    @media(max-width: 768px) {
        .inquiry-header {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .vehicle-image, .no-image {
            margin: 0 auto;
        }

        .inquiry-footer {
            flex-direction: column;
            gap: 1rem;
        }

        .page-header h1 {
            font-size: 2rem;
        }
    }
</style>

<div class="container">
    <div class="page-header">
        <h1>My Inquiries</h1>
        <p>Track and manage your vehicle inquiries</p>
    </div>

    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): 
            $status = isset($row['status']) ? $row['status'] : 'Pending';
            $status_class = 'status-' . strtolower($status);
        ?>
        <div class="inquiry-card">
            <div class="inquiry-header">
                <?php if($row['image']): ?>
                    <img src="uploads/vehicles/<?php echo htmlspecialchars($row['image']); ?>" alt="Vehicle" class="vehicle-image">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-car"></i>
                    </div>
                <?php endif; ?>
                
                <div class="vehicle-info">
                    <div class="inquiry-id">Inquiry #<?php echo $row['id']; ?></div>
                    <h3><?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?></h3>
                    <p><i class="far fa-calendar"></i> Submitted on <?php echo date('F d, Y', strtotime($row['created_at'])); ?></p>
                </div>

                <div>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo $status; ?>
                    </span>
                </div>
            </div>

            <div class="inquiry-body">
                <div class="message-label">Your Message</div>
                <div class="message-content">
                    "<?php echo htmlspecialchars($row['message']); ?>"
                </div>
            </div>

            <div class="inquiry-footer">
                <div class="inquiry-date">
                    <i class="far fa-clock"></i>
                    Last updated: <?php echo date('M d, Y - h:i A', strtotime($row['created_at'])); ?>
                </div>
                
                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this inquiry? This action cannot be undone.');">
                    <input type="hidden" name="inquiry_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_inquiry" class="btn-delete">
                        <i class="fas fa-trash-alt"></i> Delete Inquiry
                    </button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="far fa-envelope"></i>
            <h3>No Inquiries Yet</h3>
            <p>You haven't submitted any vehicle inquiries. Browse our collection and reach out about vehicles that interest you.</p>
            <a href="cars.php" class="btn-primary">
                <i class="fas fa-car"></i> Browse Vehicles
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

<?php include 'footer.php'; ?>