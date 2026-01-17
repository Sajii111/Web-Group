<?php include 'db.php'; include 'header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap');

    :root {
        --black: #0f0f0f;
        --dark: #1a1a1a;
        --gold: #d4af37;
        --gold-light: #f3e5ab;
        --white: #f5f5f5;
        --gray: #888888;
    }

    body {
        background-color: var(--black);
        color: var(--white);
        font-family: 'Montserrat', sans-serif;
    }

    h1, h2, h3 { font-family: 'Playfair Display', serif; }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 4rem 20px;
    }

    .section-title { 
        font-size: 2.5rem; 
        text-align: center; 
        margin-bottom: 3rem; 
        color: var(--gold);
    }
    
    .section-title::after {
        content: '';
        display: block;
        width: 60px;
        height: 2px;
        background-color: var(--gold);
        margin: 1rem auto 0;
    }

    .filter-container {
        text-align: center;
        margin-bottom: 4rem;
    }

    .btn{
        display: inline-block;
        padding: 12px 30px;
        border: 1px solid var(--gold);
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 2px;
        text-decoration: none;
        transition: all 0.4s ease;
        font-size: 0.8rem;
        background: transparent;
        margin: 5px;
    }

    .btn:hover, .btn.active {
        background-color: var(--gold);
        color: var(--black);
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 3rem;
    }

    .card {
        background-color: var(--dark);
        border: 1px solid #333;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .card:hover {
        border-color: var(--gold);
        transform: translateY(-5px);
    }

    .card-image-wrapper {
        height: 250px;
        overflow: hidden;
    }

    .card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .card:hover img { transform: scale(1.05); }

    .card-content { padding: 2rem; text-align: center; }

    .card-price {
        color: var(--gold);
        font-size: 1.25rem;
        font-family: 'Playfair Display', serif;
        margin: 1rem 0;
        display: block;
    }

    .status-badge {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--gray);
        margin-bottom: 0.5rem;
        display: block;
    }

@media screen and (max-width: 768px) {
    .container {
        padding: 2rem 15px;
    }

    .section-title {
        font-size: 2rem;
    }

    .filter-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-bottom: 2rem;
    }

    .btn {
        width: 100%;
        box-sizing: border-box;
        margin: 0;
    }

    .grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
    }
}
</style>

<div class="container">
    <h2 class="section-title">Our Curated Inventory</h2>
    
    <div class="filter-container">
        <a href="cars.php" class="btn <?php echo !isset($_GET['type']) ? 'active' : ''; ?>">All Masterpieces</a>
        <a href="cars.php?type=Sedan" class="btn <?php echo (isset($_GET['type']) && $_GET['type'] == 'Sedan') ? 'active' : ''; ?>">Sedan</a>
        <a href="cars.php?type=SUV" class="btn <?php echo (isset($_GET['type']) && $_GET['type'] == 'SUV') ? 'active' : ''; ?>">SUV</a>
        <a href="cars.php?type=Sports" class="btn <?php echo (isset($_GET['type']) && $_GET['type'] == 'Sports') ? 'active' : ''; ?>">Sports</a>
    </div>

    <div class="grid">
        <?php
        $where = "";
        if(isset($_GET['type'])) {
            $type = $conn->real_escape_string($_GET['type']);
            $where = "WHERE body_type = '$type'";
        }

        $sql = "SELECT * FROM cars $where";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-image-wrapper">
                        <img src="uploads/vehicles/<?php echo $row['image']; ?>" alt="Car">
                    </div>
                    <div class="card-content">
                        <span class="status-badge"><?php echo $row['status']; ?></span>
                        <h3><?php echo $row['brand'] . " " . $row['model']; ?></h3>
                        <span class="card-price">$<?php echo number_format($row['price']); ?></span>
                        <a href="car_details.php?id=<?php echo $row['id']; ?>" class="btn">Discover More</a>
                    </div>
                </div>
            <?php endwhile;
        } else {
            echo "<p style='text-align:center; grid-column: 1/-1; color: var(--gray);'>No vehicles match your selection.</p>";
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>