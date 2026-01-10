<?php include 'db.php'; include 'header.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') header("Location: login.php");

$is_edit = false;
$brand = $model = $price = $year = $mileage = $desc = $img = $status = "";

if(isset($_GET['edit'])) {
    $is_edit = true;
    $id = $_GET['edit'];
    $data = $conn->query("SELECT * FROM cars WHERE id=$id")->fetch_assoc();
    $brand = $data['brand']; $model = $data['model']; $price = $data['price'];
    $year = $data['year']; $mileage = $data['mileage']; $status = $data['status'];
    $desc = $data['description']; $img = $data['image_url'];
}

if(isset($_POST['submit'])) {
    $brand = $_POST['brand']; $model = $_POST['model']; $price = $_POST['price'];
    $year = $_POST['year']; $cond = $_POST['condition']; $trans = $_POST['transmission'];
    $body = $_POST['body_type']; $fuel = $_POST['fuel_type']; $mileage = $_POST['mileage'];
    $status = $_POST['status']; $desc = $conn->real_escape_string($_POST['description']);
    $img = $_POST['image_url']; $feat = isset($_POST['is_featured']) ? 1 : 0;

    if($is_edit) {
        $id = $_GET['edit'];
        $sql = "UPDATE cars SET brand='$brand', model='$model', price='$price', status='$status', 
                year='$year', vehicle_condition='$cond', transmission='$trans', 
                body_type='$body', fuel_type='$fuel', mileage='$mileage', 
                description='$desc', image_url='$img', is_featured='$feat' 
                WHERE id=$id";
    } else {
        $sql = "INSERT INTO cars (brand, model, price, status, year, vehicle_condition, transmission, body_type, fuel_type, mileage, description, image_url, is_featured) 
                VALUES ('$brand', '$model', '$price', '$status', '$year', '$cond', '$trans', '$body', '$fuel', '$mileage', '$desc', '$img', '$feat')";
    }
    if($conn->query($sql)) header("Location: admin_dashboard.php");
}
?>

<style>

    :root {
        --black: #0f0f0f;
        --dark: #1a1a1a;
        --gold: #d4af37;
        --white: #f5f5f5;
        --gray: #888888;
    }

    body { background-color: var(--black); color: var(--white); font-family: 'Montserrat', sans-serif; }
    h2 { font-family: 'Playfair Display', serif; color: var(--gold); margin-bottom: 2rem; }

    .form-container {
        max-width: 800px;
        margin: 4rem auto;
        padding: 3rem;
        background: var(--dark);
        border: 1px solid #333;
    }

    .form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .full-width { grid-column: span 2; }

    .form input, .form select, .form textarea {
        width: 100%;
        padding: 12px;
        background: #000;
        border: 1px solid #333;
        color: #fff;
        font-family: inherit;
        font-size: 0.9rem;
    }
    .form input:focus, .form select:focus, .form textarea:focus {
        border-color: var(--gold);
        outline: none;
    }

    .btn {
        padding: 15px;
        background: transparent;
        border: 1px solid var(--gold);
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 2px;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn:hover { background: var(--gold); color: #000; }

    label { font-size: 0.8rem; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; }
</style>

<div class="form-container">
    <h2><?php echo $is_edit ? "Update Registry" : "Registry New Car"; ?></h2>
    <form method="POST" class="form">
        <div>
            <label>Brand</label>
            <input type="text" name="brand" value="<?php echo $brand; ?>" required>
        </div>
        <div>
            <label>Model</label>
            <input type="text" name="model" value="<?php echo $model; ?>" required>
        </div>
        <div>
            <label>Price ($)</label>
            <input type="number" name="price" value="<?php echo $price; ?>" required>
        </div>
        <div>
            <label>Year</label>
            <input type="number" name="year" value="<?php echo $year; ?>" required>
        </div>
        
        <div>
            <label>Condition</label>
            <select name="condition">
                <option value="New">New</option>
                <option value="Used">Used</option>
            </select>
        </div>
        <div>
            <label>Transmission</label>
            <select name="transmission">
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
            </select>
        </div>
        <div>
            <label>Body Style</label>
            <select name="body_type">
                <option value="Sedan">Sedan</option>
                <option value="SUV">SUV</option>
                <option value="Sports">Sports</option>
            </select>
        </div>
        <div>
            <label>Fuel Type</label>
            <select name="fuel_type">
                <option value="Petrol">Petrol</option>
                <option value="Diesel">Diesel</option>
                <option value="Electric">Electric</option>
                <option value="Hybrid">Hybrid</option>
            </select>
        </div>

        <div>
            <label>Mileage (KM)</label>
            <input type="number" name="mileage" value="<?php echo $mileage; ?>" required>
        </div>
        <div>
            <label>Availability</label>
            <select name="status">
                <option value="In Stock" <?php if($status == 'In Stock') echo 'selected'; ?>>In Stock</option>
                <option value="Sold" <?php if($status == 'Sold') echo 'selected'; ?>>Sold</option>
            </select>
        </div>
        
        <div class="full-width">
            <label>Media URL</label>
            <input type="text" name="image_url" value="<?php echo $img; ?>" required>
        </div>
        
        <div class="full-width">
            <label>Vehicle Narrative</label>
            <textarea name="description" rows="5"><?php echo $desc; ?></textarea>
        </div>
        
        <div class="full-width">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_featured" style="width: auto;" <?php if(isset($data) && $data['is_featured']) echo "checked"; ?>>
                Promote to Home Page Feature Collection?
            </label>
        </div>
        
        <div class="full-width">
            <button type="submit" name="submit" class="btn" style="width: 100%;"><?php echo $is_edit ? "Commit Changes" : "Register Vehicle"; ?></button>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>