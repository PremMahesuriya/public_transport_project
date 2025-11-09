<?php
include('db_config.php');
session_start();

// Fetch schedule details with joins
$sql = "
SELECT 
    s.schedule_id, 
    r.route_name, 
    r.origin, 
    r.destination, 
    v.vehicle_no, 
    v.model, 
    v.capacity,
    d.name AS driver_name, 
    d.phone_no AS driver_phone,
    s.departure_time, 
    s.arrival_time, 
    s.current_location
FROM Schedule s
JOIN Route r ON s.route_id = r.route_id
JOIN Vehicle v ON s.vehicle_id = v.vehicle_id
JOIN Driver d ON s.driver_id = d.driver_id
ORDER BY s.departure_time ASC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    
  <meta charset="UTF-8">
  <title>Page Title | TransitHub</title>
  <link rel="stylesheet" href="theme.css">
<?php include 'navbar.php'; ?>  
    <meta charset="UTF-8">
    <title>Bus Schedules | Public Transport Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">



<!-- Main Content -->
<div class="container mt-5">
    <h2 class="text-center mb-4">🕒 Bus Schedules</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Schedule ID</th>
                        <th>Route</th>
                        <th>Vehicle No</th>
                        <th>Model</th>
                        <th>Driver</th>
                        <th>Contact</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Current Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['schedule_id'] ?></td>
                            <td><?= htmlspecialchars($row['origin']) ?> → <?= htmlspecialchars($row['destination']) ?></td>
                            <td><?= htmlspecialchars($row['vehicle_no']) ?></td>
                            <td><?= htmlspecialchars($row['model']) ?></td>
                            <td><?= htmlspecialchars($row['driver_name']) ?></td>
                            <td><?= htmlspecialchars($row['driver_phone']) ?></td>
                            <td><?= htmlspecialchars($row['departure_time']) ?></td>
                            <td><?= htmlspecialchars($row['arrival_time']) ?></td>
                            <td><?= htmlspecialchars($row['current_location']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">No schedules found in the database.</div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-4">
    <p class="mb-0">© <?= date("Y") ?> Public Transportation Management System | Developed by Prem Mahesuriya</p>
</footer>

</body>
</html>
