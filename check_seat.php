<?php
include 'db_config.php';

if (isset($_GET['schedule_id']) && isset($_GET['seat_no'])) {
    $schedule_id = intval($_GET['schedule_id']);
    $seat_no = mysqli_real_escape_string($conn, $_GET['seat_no']);

    $query = "SELECT * FROM Ticket WHERE schedule_id = $schedule_id AND seat_no = '$seat_no'";
    $result = mysqli_query($conn, $query);

    echo (mysqli_num_rows($result) > 0) ? "unavailable" : "available";
}
?>
