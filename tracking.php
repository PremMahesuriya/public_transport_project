<?php
include('db_config.php');
session_start();

$search_result = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keyword = $_POST['keyword'];
    $sql = "
        SELECT s.vehicle_no, s.current_location, s.departure_time, s.arrival_time, t.ticket_id
        FROM Schedule s
        LEFT JOIN Ticket t ON s.schedule_id = t.schedule_id
        WHERE s.vehicle_no = ? OR t.ticket_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $keyword, $keyword);
    $stmt->execute();
    $search_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    
  <meta charset="UTF-8">
  <title>Page Title | TransitHub</title>
  <link rel="stylesheet" href="theme.css">
<?php include 'navbar.php'; ?>
    <title>Vehicle Tracking | Bus Booking System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3 class="text-center mb-3">Track Vehicle or Ticket</h3>
        <form method="POST" class="d-flex mb-3">
            <input type="text" name="keyword" class="form-control me-2" placeholder="Enter Vehicle No or Ticket ID" required>
            <button class="btn btn-primary">Search</button>
        </form>

        <?php if ($search_result && $search_result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead><tr>
                <th>Vehicle No</th><th>Ticket ID</th><th>Current Location</th><th>Departure</th><th>Arrival</th>
            </tr></thead>
            <tbody>
            <?php while($row = $search_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['vehicle_no'] ?></td>
                    <td><?= $row['ticket_id'] ?></td>
                    <td><?= $row['current_location'] ?></td>
                    <td><?= $row['departure_time'] ?></td>
                    <td><?= $row['arrival_time'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <div class="alert alert-danger text-center">No results found for the given ID or vehicle number.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
