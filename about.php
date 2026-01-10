<?php include 'db.php'; include 'header.php'; ?>

<style>
    
    :root {
        --black: #0f0f0f;
        --dark: #1a1a1a;
        --gold: #d4af37;
        --gold-light: #f3e5ab;
        --white: #f5f5f5;
        --gray: #888888;
    }

    body {
        color: whitesmoke;
        font-family: 'Montserrat', sans-serif;
    }

    .about-hero {
        height: 60vh;
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1920&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    h1,h2,h3{
        font-family: 'Playfair Display', serif;
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
        margin-top: 2rem;
    }

    .about-image {
        position: relative;
    }

    .about-image img {
        width: 100%;
        border-radius: 2px;
        box-shadow: -20px 20px 0px rgba(212, 175, 55, 0.1);
        filter: grayscale(20%);
    }

    .stat-box {
        border: 1px solid #333;
        padding: 2rem;
        margin-top: 2rem;
        background: var(--dark);
    }
    
    .stat-list {
        list-style: none;
        padding: 0;
    }
    
    .stat-list li {
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        color: var(--gray);
    }
    
    .stat-list li strong {
        color: var(--gold);
        margin-right: 15px;
        font-size: 1.2rem;
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

    @media(max-width: 768px) {
        .about-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="about-hero">
    <div>
        <p style="color: var(--gold); letter-spacing: 3px; text-transform: uppercase;">Est. 1998</p>
        <h1>Our Legacy</h1>
    </div>
</div>

<div class="container">
    <div style="text-align: center; max-width: 800px; margin: 0 auto 5rem auto;">
        <h2 style="color: var(--gold); margin-bottom: 1.5rem;">The Pursuit of Perfection</h2>
        <p style="font-size: 1.1rem; color: var(--gray);">
            Since 1998, Elite Autos has been the premier destination for enthusiasts seeking the finest automotive engineering. We don't just sell cars; we curate a lifestyle of performance, prestige, and unparalleled luxury.
        </p>
    </div>


    <div class="about-grid">
        <div class="about-image">
            <img src="https://images.cdn.autocar.co.uk/sites/autocar.co.uk/files/images/car-reviews/first-drives/legacy/3-supercar-showroom.jpg" alt="Showroom">
        </div>
        <div>
            <h2 style="color: var(--white); font-size: 2.2rem;">The Elite Standard</h2>
            <div style="width: 50px; height: 2px; background: var(--gold); margin: 1rem 0 2rem 0;"></div>
            
            <p style="color: var(--gray); margin-bottom: 2rem;">
                Every vehicle in our showroom is more than a machine; it is a masterpiece. Each unit undergoes a rigorous 150-point technical inspection by our certified master technicians before it earns its place on our floor.
            </p>

            <ul class="stat-list">
                <li><strong>25+</strong> Years of Excellence</li>
                <li><strong>150+</strong> Point Inspections</li>
                <li><strong>100%</strong> Certified Authentic</li>
                <li><strong>Global</strong> Sourcing Network</li>
            </ul>
        </div>
    </div>


    <div style="margin-top: 6rem; text-align: center; background: linear-gradient(45deg, #111, #1a1a1a); padding: 5rem 2rem; border: 1px solid #222;">
        <h2 style="color: var(--white);">Experience It Yourself</h2>
        <p style="color: var(--gray); margin-bottom: 2rem;">Visit our showroom to smell the fine leather and hear the roar of precision engines.</p>
        <a href="cars.php" class="btn">View Our Collection</a>
    </div>
</div>

<?php include 'footer.php'; ?>