<?php include 'db.php'; include 'header.php'; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap');

    :root {
        --white: #f5f5f5;
    }


    body {
        color: whitesmoke;
        font-family: 'Montserrat', sans-serif;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 4rem;
    }

    .info-card {
        background: #1a1a1a;
        padding: 2.5rem;
        border: 1px solid #333;
        margin-bottom: 1.5rem;
        transition: transform 0.3s;
    }

    .info-card:hover {
        border-color: #d4af37;
        transform: translateY(-5px);
    }

    .info-card h3 {
        color: #d4af37;
        font-size: 1.2rem;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .info-card p {
        color: #888888;
        margin: 0.5rem 0;
    }

    .contact-form {
        background: #1a1a1a;
        padding: 3rem;
        border: 1px solid #333;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    input, textarea {
        width: 100%;
        box-sizing: border-box;
        background: #0f0f0f;
        border: 1px solid #333;
        color: whitesmoke;
    }

    .btn{
        display: block;
        width: 100%;
        padding: 15px;
        border: 1px solid #d4af37;
        background: transparent;
        color: #d4af37;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.4s ease;
        margin-top: 1rem;
        font-weight: 500;
    }

    .btn:hover {
        background-color: #d4af37;
        color: #0f0f0f;
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
    }
    .error-msg {
        background: rgba(255, 77, 77, 0.1);
        border-left: 3px solid #ff4d4d;
        color: #ffcccc;
        padding: 10px;
        font-size: 0.85rem;
        margin-bottom: 20px;
        display: none;
    }

    @media(max-width: 768px) {
        .contact-grid { grid-template-columns: 1fr; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>

<div class="container">
    <h2 class="section-title">Concierge Services</h2>
    
    <div class="contact-grid">
        <div>
            <div class="info-card">
                <h3>The Showroom</h3>
                <p>123 Peradeniya Road,</p>
                <p>Kandy, Sri lanka</p>
            </div>
            
            <div class="info-card">
                <h3>Direct Lines</h3>
                <p>Sales: <span style="color: #fff;">+12 3456 7890</span></p>
                <p>Service: <span style="color: #fff;">+12 3456 7890</span></p>
                <p>Email: concierge@eliteautos.com</p>
            </div>

            <div class="info-card">
                <h3>Private Viewings</h3>
                <p>Mon - Fri: 9:00 AM - 8:00 PM</p>
                <p>Saturday: 10:00 AM - 6:00 PM</p>
                <p>Sunday: By Appointment Only</p>
            </div>
        </div>

        <div class="contact-form">
            <h3 style="color: var(--white); margin-bottom: 2rem;">Request Assistance</h3>
            
            <div id="contact-error" class="error-msg"></div>

            <form   action="https://formspree.io/f/mgoowozr" method="POST" id="contactForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="name" id="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" id="email" placeholder="Your Email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <input type="text" name="subject" id="subject" placeholder="Subject (e.g. Inquiry about Porsche 911)">
                </div>

                <div class="form-group">
                    <textarea name="message" id="message" placeholder="How can our team assist you today?" rows="6"></textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Send Request</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();
    const errorBox = document.getElementById('contact-error');
    
    let errors = [];

    if (name.length < 2) errors.push("Name is too short.");

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) errors.push("Invalid email address.");

    if (subject.length === 0) errors.push("Subject is required.");

    if (message.length < 10) errors.push("Message must be at least 10 characters.");

    if (errors.length > 0) {
        errorBox.style.display = 'block';
        errorBox.innerHTML = errors.join('<br>');
        errorBox.scrollIntoView({behavior: 'smooth', block: 'center'});
    } else {
        errorBox.style.display = 'none';
        this.submit(); 
    }
});

document.querySelectorAll('#contactForm input, #contactForm textarea').forEach(element => {
    element.addEventListener('input', () => {
        const errorBox = document.getElementById('contact-error');
        if (errorBox.style.display === 'block') {
            errorBox.style.display = 'none';
        }
    });
});
</script>

<?php include 'footer.php'; ?>