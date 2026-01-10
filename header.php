<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Autos | Luxury Dealership</title>
    <style>
        :root {
            --gold: #d4af37;
            --black: #0f0f0f;
            --white: #f5f5f5;
        }
        body {
            margin: 0;
            background-color: var(--black);
            color: var(--white);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
            background: #111;
            border-bottom: 1px solid #222;
        }
        .logo {
            color: var(--gold);
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            text-decoration: none;
        }
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            margin-left: 25px;
        }
        nav ul li a {
            color: var(--white);
            text-decoration: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        nav ul li a:hover {
            color: var(--gold);
        }

    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">ELITE AUTOS</a>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Inventory</a></li>
        <li><a href="about.php">Our Legacy</a></li>
        <li><a href="contact.php">Concierge</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] == 'admin'): ?>
                <li><a href="admin_dashboard.php" style="color: var(--gold);">Management</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>