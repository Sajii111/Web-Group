<?php include 'db.php'; include 'header.php'; 

if(!isset($_GET['id'])) header("Location: cars.php");
$id = $_GET['id'];
$sql = "SELECT * FROM cars WHERE id = $id";
$result = $conn->query($sql);
$car = $result->fetch_assoc();

if(isset($_POST['send_inquiry'])) {
    if(!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please login first!'); window.location='login.php';</script>";
    } else {
        $user_id = $_SESSION['user_id'];
        $msg = $conn->real_escape_string($_POST['message']);
        $conn->query("INSERT INTO inquiries (user_id, car_id, message) VALUES ($user_id, $id, '$msg')");
        $success_msg = "Your inquiry has been received. Our concierge will contact you shortly.";
    }
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
    h1, h2, h3 { font-family: 'Playfair Display', serif; }

    .container { max-width: 1200px; margin: 0 auto; padding: 4rem 20px; }

    .details-layout {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 3rem;
    }

    .image-showcase img {
        width: 100%;
        border: 1px solid #333;
        box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    }

    .info-pane {
        background: var(--dark);
        padding: 2.5rem;
        border: 1px solid #333;
    }

    .car-title { font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--white); }
    .car-price-large { color: var(--gold); font-size: 2rem; margin-bottom: 2rem; display: block; }

    .spec-list { list-style: none; padding: 0; margin: 0 0 2rem 0; }
    .spec-list li {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        font-size: 0.9rem;
    }
    .spec-list li span:first-child { color: var(--gray); text-transform: uppercase; letter-spacing: 1px; font-size: 0.75rem; }

    .description-box {
        margin-top: 3rem;
        padding: 2.5rem;
        background: var(--dark);
        border: 1px solid #333;
    }
    .description-box h3 { color: var(--gold); margin-bottom: 1.5rem; }

    .inquiry-form textarea {
        width: 100%;
        background: #000;
        border: 1px solid #333;
        color: #fff;
        padding: 15px;
        margin-bottom: 15px;
        font-family: inherit;
        resize: none;
    }
    .inquiry-form textarea:focus { border-color: var(--gold); outline: none; }

    .btn{
        display: block;
        width: 100%;
        padding: 15px;
        border: 1px solid var(--gold);
        color: var(--gold);
        background: transparent;
        text-transform: uppercase;
        letter-spacing: 2px;
        cursor: pointer;
        transition: 0.3s;
        text-align: center;
        text-decoration: none;
    }
    .btn:hover { background: var(--gold); color: #000; }
    
    .success-notif { color: var(--gold); border: 1px solid var(--gold); padding: 10px; margin-bottom: 20px; text-align: center; font-size: 0.85rem; }
</style>

<div class="container">
    <div class="details-layout">
        <div class="image-showcase">
            <img src="uploads/vehicles/<?php echo $car['image']; ?>" alt="Vehicle Image">
            <div class="description-box">
                <h3>Vehicle Description</h3>
                <p style="color: var(--gray); line-height: 1.8;"><?php echo nl2br($car['description']); ?></p>
            </div>
        </div>

        <div class="info-pane">
            <h1 class="car-title"><?php echo $car['brand'] . " " . $car['model']; ?></h1>
            <span class="car-price-large">Rs . <?php echo number_format($car['price']); ?></span>
            
            <ul class="spec-list">
                <li><span>Year</span> <span><?php echo $car['year']; ?></span></li>
                <li><span>Condition</span> <span><?php echo $car['vehicle_condition']; ?></span></li>
                <li><span>Transmission</span> <span><?php echo $car['transmission']; ?></span></li>
                <li><span>Body Style</span> <span><?php echo $car['body_type']; ?></span></li>
                <li><span>Fuel Type</span> <span><?php echo $car['fuel_type']; ?></span></li>
                <li><span>Mileage</span> <span><?php echo number_format($car['mileage']); ?> KM</span></li>
                <li><span>Status</span> <span><?php echo $car['status']; ?></span></li>
            </ul>
            
            <div class="inquiry-form">
                <h3>Private Inquiry</h3>
                <?php if(isset($success_msg)) echo "<div class='success-notif'>$success_msg</div>"; ?>
                <form method="POST">
                    <textarea name="message" rows="5" placeholder="Inquire about this masterpiece..." required></textarea>
                    <button type="submit" name="send_inquiry" class="btn">Send Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>