<style>

    :root {
        --gold: #d4af37;
        --white: #f5f5f5;

    }

    footer {
        background-color: #0a0a0a;
        padding: 60px 20px 20px;
        border-top: 1px solid #222;
        margin-top: 80px;
        color: var(--white);
    }

    .footer-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
    }

    footer h3 {
        color: var(--gold);
        font-family: 'Playfair Display', serif;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 1.2rem;
    }

    footer p, footer a {
        color: #888;
        font-size: 0.9rem;
        text-decoration: none;
        line-height: 1.8;
        transition: 0.3s;
    }

    footer a:hover {
        color: var(--gold);
        padding-left: 5px;
    }

    .footer-bottom {
        text-align: center;
        margin-top: 50px;
        padding-top: 20px;
        border-top: 1px solid #1a1a1a;
        color: #444;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }

    .social-links {
        margin-top: 15px;
        display: flex;
        gap: 15px;
    }
</style>

<footer>
    <div class="footer-grid">
        <div>
            <h3>Elite Autos</h3>
            <p>The world's premier destination for rare automotive engineering and luxury performance. Since 1998, delivering prestige on wheels.</p>
        </div>
        <div>
            <h3>Quick Links</h3>
            <p><a href="index.php">Home</a></p>
            <p><a href="cars.php">Our Inventory</a></p>
            <p><a href="about.php">Our Legacy</a></p>
            <p><a href="contact.php">Concierge</a></p>
        </div>
        <div>
            <h3>Contact Us</h3>
            <p>123 Peradeniya Road<br>Kandy, Sri Lanka</p>
            <p>Sales: +12 345 67890</p>
            <p>Email: concierge@eliteautos.com</p>
        </div>
    </div>
    
    <div class="footer-bottom">
        &copy; 2026 ELITE AUTOS. ALL RIGHTS RESERVED.
    </div>
</footer>
</body>
</html>