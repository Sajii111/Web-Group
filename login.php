<?php include 'db.php';
 include 'header.php'; 

if(isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if(password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];
            
            if($row['role'] == 'admin') header("Location: admin_dashboard.php");
            else header("Location: index.php");
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

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
        background-color: var(--black);
        color: var(--white);
        font-family: 'Montserrat', sans-serif;
        margin: 0;
        padding: 0;
    }

    .form-wrapper {
        min-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.85)), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1920&q=80');
        background-size: cover;
        background-position: center;
        padding: 20px;
    }

    .form-container {
        background: rgba(26, 26, 26, 0.95);
        padding: 3.5rem;
        border: 1px solid #333;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        backdrop-filter: blur(5px);
        position: relative;
    }

    .form-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--gold);
    }


    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        color: var(--white);
        text-align: center;
        margin-bottom: 0.5rem;
    }

    .section-subtitle {
        text-align: center;
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 2.5rem;
        font-weight: 300;
    }


    .form label {
        display: block;
        margin-bottom: 0.8rem;
        color: var(--gold);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 500;
    }

    .form input {
        width: 100%;
        padding: 15px;
        background: #0f0f0f;
        border: 1px solid #333;
        color: #fff;
        margin-bottom: 1.5rem;
        font-family: 'Montserrat', sans-serif;
        transition: all 0.3s ease;
        box-sizing: border-box;
        font-size: 1rem;
    }

    .form input:focus {
        border-color: var(--gold);
        outline: none;
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.1);
        background: #151515;
    }

    .btn {
        display: block;
        width: 100%;
        padding: 15px;
        border: 1px solid var(--gold);
        background: transparent;
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.4s ease;
        margin-top: 1rem;
        font-weight: 500;
    }

    .btn:hover {
        background-color: var(--gold);
        color: var(--black);
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.4);
    }


    .auth-link {
        text-align: center;
        margin-top: 25px;
        font-size: 0.85rem;
        color: var(--gray);
    }

    .auth-link a {
        color: var(--white);
        text-decoration: none;
        transition: color 0.3s;
        border-bottom: 1px dotted var(--gold);
    }

    .auth-link a:hover {
        color: var(--gold);
    }
    
    .error-msg {
        background: rgba(255, 77, 77, 0.1);
        border-left: 3px solid #ff4d4d;
        color: #ffcccc;
        padding: 10px;
        font-size: 0.85rem;
        margin-bottom: 20px;
        text-align: center;
    }
</style>

<div class="form-wrapper">
    <div class="form-container">
        <h2 class="section-title">Welcome Back</h2>
        <p class="section-subtitle">Access your exclusive dashboard</p>
        
        <div id="js-error-container"></div>
        <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
        
        <form method="POST" class="form" id="loginForm" novalidate>
            <div>
                <label>Email Address</label>
                <input type="email" name="email" id="login-email" required placeholder="name@example.com">
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" id="login-pass" required placeholder="................">
            </div>
            
            <button type="submit" name="login" class="btn">Authenticate</button>
            
            <p class="auth-link">
                Not a member yet? <a href="register.php">Request Access</a>
            </p>
        </form>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('login-email').value.trim();
    const pass = document.getElementById('login-pass').value;
    const errorContainer = document.getElementById('js-error-container');
    let errors = [];

    errorContainer.innerHTML = '';

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        errors.push("Please enter a valid email address.");
    }

    if (pass === "") {
        errors.push("Password is required.");
    }

    if (errors.length > 0) {
        e.preventDefault();
        errorContainer.innerHTML = `<div class='error-msg'>${errors.join('<br>')}</div>`;
    }
});
</script>

<?php include 'footer.php'; ?>