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
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }

    h1, h2, h3 {
        font-family: 'Playfair Display', serif;
        font-weight: 400;
        letter-spacing: 0.5px;
        margin-top: 0;
    }

    h1 { font-size: 3.5rem; color: var(--white); margin-bottom: 0.5rem; }
    h2.section-title { 
        font-size: 2.5rem; 
        text-align: center; 
        margin-bottom: 3rem; 
        color: var(--gold);
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    h2.section-title::after {
        content: '';
        display: block;
        width: 60px;
        height: 2px;
        background-color: var(--gold);
        margin: 1rem auto 0;
    }

    .hero {
        height: 85vh;
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1920&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 0 20px;
    }

    .hero p {
        font-size: 1.2rem;
        color: var(--gold-light);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 2rem;
    }

    .btn {
        display: inline-block;
        padding: 15px 40px;
        border: 1px solid var(--gold);
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 2px;
        text-decoration: none;
        transition: all 0.4s ease;
        font-size: 0.9rem;
        background: transparent;
    }

    .btn:hover {
        background-color: var(--gold);
        color: var(--black);
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 6rem 20px;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 3rem;
    }

    .card {
        background-color: var(--dark);
        border: 1px solid #333;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-5px);
        border-color: var(--gold);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .card-image-wrapper {
        height: 250px;
        overflow: hidden;
        position: relative;
    }

    .card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .card:hover img {
        transform: scale(1.05);
    }

    .card-content {
        padding: 2rem;
        text-align: center;
    }

    .card-content h3 {
        color: var(--white);
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .card-price {
        color: var(--gold);
        font-size: 1.25rem;
        font-family: 'Playfair Display', serif;
        margin-bottom: 1.5rem;
        display: block;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .feature-item {
        text-align: center;
        padding: 2rem;
        border: 1px solid rgba(255,255,255,0.05);
        transition: background 0.3s ease;
    }

    .feature-item:hover {
        background: rgba(255,255,255,0.02);
        border-color: var(--gold);
    }

    .feature-item h3 {
        color: var(--gold);
        font-size: 1.2rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .feature-item p {
        color: var(--gray);
        font-size: 0.95rem;
    }

@media screen and (max-width: 768px) {
    h1 { font-size: 2.2rem; }
    
    .hero p { 
        font-size: 0.9rem; 
        letter-spacing: 1px; 
    }

    .hero {
        height: 70vh;
        background-attachment: scroll;
    }

    .container {
        padding: 3rem 15px;
    }

    .grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
    }

    .features-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .feature-item {
        padding: 1.5rem;
    }
}
</style>

<div class="hero">
    <p>The Art of Automotive Excellence</p>
    <h1>Experience the Extraordinary</h1>
    <a href="cars.php" class="btn">Browse Collection</a>
</div>

<div class="container">
    <h2 class="section-title">Featured Collection</h2>
    <div class="grid">
        <?php
        $sql = "SELECT * FROM cars WHERE is_featured=1 LIMIT 3";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $price_display = number_format($row['price']);
                
                echo "
                <div class='card'>
                    <div class='card-image-wrapper'>
                        <img src='{$row['image_url']}' alt='{$row['brand']} {$row['model']}'>
                    </div>
                    <div class='card-content'>
                        <h3>{$row['brand']} {$row['model']}</h3>
                        <span class='card-price'>$$price_display</span>
                        <br>
                        <a href='car_details.php?id={$row['id']}' class='btn' style='padding: 10px 25px; font-size: 0.8rem;'>View Details</a>
                    </div>
                </div>";
            }
        } else {
            echo "<p style='text-align:center; width:100%; color: var(--gray);'>No featured vehicles available at this time.</p>";
        }
        ?>
    </div>
</div>

<div class="container" style="background-color: #0a0a0a;" id="about">
    <h2 class="section-title">Why Elite Auto?</h2>
    <div class="features-grid">
        <div class="feature-item">
            <h3>Quality Guaranteed</h3>
            <p>Every vehicle passes a rigorous 150-point mechanical and aesthetic inspection.</p>
        </div>
        <div class="feature-item">
            <h3>Certified Vehicles</h3>
            <p>Our 100% Certified pre-owned selection ensures peace of mind with every mile.</p>
        </div>
        <div class="feature-item">
            <h3>24/7 Support</h3>
            <p>Dedicated concierge support whenever you need assistance.</p>
        </div>
        <div class="feature-item">
            <h3>Easy Financing</h3>
            <p>Bespoke financial solutions with low interest rates and rapid approval.</p>
        </div>
        <div class="feature-item">
            <h3>Complimentary Service</h3>
            <p>Enjoy your first three scheduled maintenance services on the house.</p>
        </div>
        <div class="feature-item">
            <h3>White Glove Delivery</h3>
            <p>We deliver your dream car directly to your doorstep, anywhere in the country.</p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>