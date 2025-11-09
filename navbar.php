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
