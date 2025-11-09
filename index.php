<?php
session_start();
include 'db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TransitHub - Public Transportation System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
        }
        
        .navbar {
            background-color: #2563eb;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        
        .navbar-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .navbar-menu a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .navbar-menu a:hover {
            opacity: 0.8;
        }
        
        .hero {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            margin: 0.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: white;
            color: #2563eb;
        }
        
        .btn-primary:hover {
            background-color: #f0f0f0;
        }
        
        .btn-secondary {
            background-color: #1e40af;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #1e3a8a;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .feature-card h3 {
            color: #2563eb;
            margin: 1rem 0;
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #2563eb;
        }
        
        .footer {
            background-color: #1f2937;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .navbar-menu {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">🚌 TransitHub</a>
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="routes.php">Routes</a></li>
                <li><a href="schedules.php">Schedules</a></li>
                <li><a href="booking.php">Book Ticket</a></li>
                <li><a href="tracking.php">Track Vehicle</a></li>
                <?php if(isset($_SESSION['passenger_id'])): ?>
                    <li><a href="profile.php">My Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Welcome to TransitHub</h1>
        <p>Your reliable public transportation system</p>
        <a href="booking.php" class="btn btn-primary">Book Now</a>
        <a href="routes.php" class="btn btn-secondary">View Routes</a>
    </section>

    <!-- Features Section -->
    <div class="container">
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">🗺️</div>
                <h3>Multiple Routes</h3>
                <p>Extensive network covering all major locations in the city</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏰</div>
                <h3>Real-time Tracking</h3>
                <p>Track your bus in real-time and plan your journey</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3>Easy Payment</h3>
                <p>Multiple payment options for hassle-free booking</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 TransitHub. All rights reserved.</p>
        <p>Your trusted public transportation partner</p>
    </footer>
</body>
</html>