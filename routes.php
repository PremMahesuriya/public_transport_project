<?php
include('db_config.php');
session_start();

// --- SEARCH FEATURE ---
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT * FROM Route WHERE origin LIKE '%$search%' OR destination LIKE '%$search%' OR route_name LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM Route";
}

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
    <title>Available Routes | Public Transport Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">



<!-- MAIN CONTENT -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Available Bus Routes</h2>

    <!-- Search Bar -->
    <form method="get" class="d-flex justify-content-center mb-4">
        <input type="text" name="search" class="form-control w-50 me-2" placeholder="Search by city or route name" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-success" type="submit">Search</button>
    </form>

    <!-- Routes Display -->
    <div class="row">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // calculate approximate duration based on distance
                $duration_est = round($row['distance_km'] / 50 * 60); // assuming avg 50 km/h
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                🚍 <?= htmlspecialchars($row['origin']) ?> → <?= htmlspecialchars($row['destination']) ?>
                            </h5>
                            <p class="card-text">
                                <strong>Route Name:</strong> <?= htmlspecialchars($row['route_name']) ?><br>
                                📏 <strong>Distance:</strong> <?= htmlspecialchars($row['distance_km']) ?> km<br>
                                ⏱️ <strong>Estimated Duration:</strong> <?= $duration_est ?> mins
                            </p>
                            <a href="booking.php?route_id=<?= $row['route_id'] ?>" class="btn btn-success w-100">Book This Route</a>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<div class='alert alert-warning text-center'>No routes found for '$search'.</div>";
        }
        ?>
    </div>
</div>

<!-- FOOTER -->
<footer class="bg-dark text-white text-center py-3 mt-4">
    <p class="mb-0">© <?= date("Y") ?> Public Transportation Management System | Developed by Prem Mahesuriya</p>
</footer>

</body>
</html>
