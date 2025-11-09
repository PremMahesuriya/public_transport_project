<?php
include('db_config.php');
session_start();

if (!isset($_SESSION['passenger_id'])) {
    header("Location: login.php");
    exit();
}

$pid = $_SESSION['passenger_id'];

// Get passenger info
$user_sql = "SELECT * FROM Passenger WHERE passenger_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $pid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get booking + ticket info
$booking_sql = "
SELECT b.booking_id, b.booking_date, b.status, t.ticket_id, t.price, s.vehicle_no, s.departure_time, s.arrival_time
FROM Booking b
JOIN Ticket t ON b.ticket_id = t.ticket_id
JOIN Schedule s ON t.schedule_id = s.schedule_id
WHERE b.passenger_id = ?
";
$stmt = $conn->prepare($booking_sql);
$stmt->bind_param("i", $pid);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile | Bus Booking System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Welcome, <?= htmlspecialchars($user['name']) ?> 👋</h3>
    <a href="logout.php" class="btn btn-danger float-end">Logout</a>
    <p><strong>Email:</strong> <?= $user['email'] ?></p>
    <p><strong>Phone:</strong> <?= $user['phone_no'] ?></p>
    <p><strong>Address:</strong> <?= $user['address'] ?></p>
    <p><strong>Gender:</strong> <?= $user['gender'] ?></p>
    <p><strong>DOB:</strong> <?= $user['DOB'] ?></p>

    <h4 class="mt-4">Booking History</h4>
    <table class="table table-bordered">
        <thead><tr>
            <th>Booking ID</th><th>Ticket ID</th><th>Vehicle</th>
            <th>Departure</th><th>Arrival</th><th>Price</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php while($row = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?= $row['booking_id'] ?></td>
                <td><?= $row['ticket_id'] ?></td>
                <td><?= $row['vehicle_no'] ?></td>
                <td><?= $row['departure_time'] ?></td>
                <td><?= $row['arrival_time'] ?></td>
                <td>₹<?= $row['price'] ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
