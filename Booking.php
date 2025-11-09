<?php
session_start();
include 'db_config.php';

/* Show mysqli errors as exceptions (great for debugging) */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$success_message = $error_message = "";

/* ----------------------------
   Load schedules for dropdown
   ---------------------------- */
$schedules_sql = "
SELECT 
    s.schedule_id,
    r.route_name, r.origin, r.destination,
    v.vehicle_no,
    s.departure_time, s.arrival_time
FROM Schedule s
JOIN Route   r ON s.route_id  = r.route_id
JOIN Vehicle v ON s.vehicle_id = v.vehicle_id
ORDER BY s.departure_time ASC
";
$schedules_stmt = $conn->prepare($schedules_sql);
$schedules_stmt->execute();
$schedules_result = $schedules_stmt->get_result();

/* ----------------------------
   Handle booking submission
   ---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Basic sanitization (we still use prepared statements below)
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $gender   = trim($_POST['gender'] ?? '');
        $dob      = trim($_POST['dob'] ?? '');
        $schedule_id = (int)($_POST['schedule_id'] ?? 0);
        $seat_no     = trim($_POST['seat_no'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $payment_method = trim($_POST['payment_method'] ?? 'UPI');

        // Quick validations
        if ($name === '' || $email === '' || $phone === '' || $address === '' || $gender === '' || $dob === '' || !$schedule_id || $seat_no === '' || $price <= 0) {
            throw new Exception("Please fill all fields correctly.");
        }

        // Ensure schedule exists
        $chk_sched = $conn->prepare("SELECT schedule_id FROM Schedule WHERE schedule_id = ?");
        $chk_sched->bind_param("i", $schedule_id);
        $chk_sched->execute();
        $rs_sched = $chk_sched->get_result();
        if ($rs_sched->num_rows === 0) {
            throw new Exception("Selected schedule does not exist.");
        }

        // Start transaction so all-or-nothing
        $conn->begin_transaction();

        // 1) Find or create passenger (as guest if not registered)
        $find_pass = $conn->prepare("SELECT passenger_id FROM Passenger WHERE email = ?");
        $find_pass->bind_param("s", $email);
        $find_pass->execute();
        $rs_pass = $find_pass->get_result();

        if ($rs_pass->num_rows > 0) {
            $passenger_id = (int)$rs_pass->fetch_assoc()['passenger_id'];
        } else {
            // Insert guest passenger (password='guest'; this does not log them in)
            $ins_pass = $conn->prepare("
                INSERT INTO Passenger (name, address, email, phone_no, gender, DOB, password)
                VALUES (?, ?, ?, ?, ?, ?, 'guest')
            ");
            $ins_pass->bind_param("ssssss", $name, $address, $email, $phone, $gender, $dob);
            $ins_pass->execute();
            $passenger_id = $ins_pass->insert_id;
        }

        // 2) Prevent duplicate seat on same schedule (simple check)
        $seat_chk = $conn->prepare("SELECT 1 FROM Ticket WHERE schedule_id = ? AND seat_no = ?");
        $seat_chk->bind_param("is", $schedule_id, $seat_no);
        $seat_chk->execute();
        if ($seat_chk->get_result()->num_rows > 0) {
            throw new Exception("Seat $seat_no is already booked for this schedule. Please choose another seat.");
        }

        // 3) Insert Ticket (initially Pending)
        $today = date('Y-m-d');
        $ins_ticket = $conn->prepare("
            INSERT INTO Ticket (passenger_id, schedule_id, seat_no, price, payment_status, ticket_date)
            VALUES (?, ?, ?, ?, 'Pending', ?)
        ");
        $ins_ticket->bind_param("iisis", $passenger_id, $schedule_id, $seat_no, $price, $today);
        $ins_ticket->execute();
        $ticket_id = $ins_ticket->insert_id;

        // 4) Insert Booking
        $ins_booking = $conn->prepare("
            INSERT INTO Booking (passenger_id, ticket_id, booking_date, status)
            VALUES (?, ?, ?, 'Confirmed')
        ");
        $ins_booking->bind_param("iis", $passenger_id, $ticket_id, $today);
        $ins_booking->execute();

        // 5) Insert Payment (Completed) and mark Ticket as Paid
        $ins_payment = $conn->prepare("
            INSERT INTO Payment (ticket_id, payment_method, payment_date, status, amount)
            VALUES (?, ?, ?, 'Completed', ?)
        ");
        $ins_payment->bind_param("issd", $ticket_id, $payment_method, $today, $price);
        $ins_payment->execute();

        $upd_ticket = $conn->prepare("UPDATE Ticket SET payment_status = 'Paid' WHERE ticket_id = ?");
        $upd_ticket->bind_param("i", $ticket_id);
        $upd_ticket->execute();

        // All good — commit
        $conn->commit();

        $success_message = "✅ Booking successful! Your Ticket ID is: <strong>{$ticket_id}</strong>";
    } catch (Exception $e) {
        // Roll back if anything failed
        if ($conn->errno || $conn->affected_rows !== 1) {
            $conn->rollback();
        }
        $error_message = "❌ " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
function checkSeatAvailability() {
  const scheduleId = document.querySelector("select[name='schedule_id']").value;
  const seatNo = document.querySelector("input[name='seat_no']").value;
  
  if (scheduleId && seatNo) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `check_seat.php?schedule_id=${scheduleId}&seat_no=${seatNo}`, true);
    xhr.onload = function() {
      if (xhr.status === 200) {
        const response = xhr.responseText.trim();
        const seatField = document.querySelector("input[name='seat_no']");
        if (response === "unavailable") {
          seatField.style.borderColor = "red";
          document.getElementById("seat-status").innerText = "❌ Seat already booked!";
          document.getElementById("seat-status").style.color = "red";
        } else {
          seatField.style.borderColor = "green";
          document.getElementById("seat-status").innerText = "✅ Seat available";
          document.getElementById("seat-status").style.color = "green";
        }
      }
    };
    xhr.send();
  }
}
</script>

  <meta charset="UTF-8">
  <title>Page Title | TransitHub</title>
  <link rel="stylesheet" href="theme.css">
<?php include 'navbar.php'; ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Ticket | TransitHub</title>
<style>
    /* ===== match index.php look & feel ===== */
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; background:#f4f4f4; }
    .navbar { background:#2563eb; color:#fff; padding:1rem 2rem; box-shadow:0 2px 4px rgba(0,0,0,0.1); }
    .navbar-container { max-width:1200px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; }
    .navbar-brand { font-size:1.5rem; font-weight:bold; text-decoration:none; color:#fff; }
    .navbar-menu { display:flex; list-style:none; gap:2rem; }
    .navbar-menu a { color:#fff; text-decoration:none; }
    .container { max-width:900px; margin:2rem auto; padding:2rem; background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    h1 { color:#1f2937; margin-bottom:1rem; text-align:center; }
    .form-container { margin-top:1rem; }
    .form-group { margin-bottom:1rem; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    label { display:block; margin-bottom:.4rem; font-weight:bold; color:#374151; }
    input, select, textarea { width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:5px; font-size:1rem; }
    textarea { min-height:90px; resize:vertical; }
    .btn { width:100%; padding:1rem; background:#2563eb; color:#fff; border:none; border-radius:5px; font-size:1rem; font-weight:bold; cursor:pointer; transition:background .3s; margin-top:.5rem; }
    .btn:hover { background:#1e40af; }
    .success-message { background:#d1fae5; color:#065f46; padding:1rem; border-radius:5px; margin:1rem 0; }
    .error-message { background:#fee2e2; color:#991b1b; padding:1rem; border-radius:5px; margin:1rem 0; }
    .footer { background:#1f2937; color:#fff; text-align:center; padding:2rem; margin-top:3rem; }
    @media (max-width:768px){ .form-row { grid-template-columns:1fr; } }
</style>
</head>
<body>

    <!-- CONTENT -->
    <div class="container">
        <h1>🎫 Book Your Ticket</h1>

        <?php if($success_message): ?>
            <div class="success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <?php if($error_message): ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" required></textarea>
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Schedule</label>
                    <select name="schedule_id" required>
                        <option value="">-- Choose Schedule --</option>
                        <?php
                        // Re-run schedules (first result set is consumed if we printed elsewhere)
                        $schedules_stmt->execute();
                        $s2 = $schedules_stmt->get_result();
                        while ($row = $s2->fetch_assoc()):
                        ?>
                            <option value="<?= (int)$row['schedule_id'] ?>">
                                <?= htmlspecialchars($row['origin']) ?> → <?= htmlspecialchars($row['destination']) ?>
                                (<?= htmlspecialchars($row['vehicle_no']) ?>)
                                — Dep: <?= htmlspecialchars($row['departure_time']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                         <label>Seat Number</label>
                        <input type="text" name="seat_no" placeholder="e.g. 12A" onblur="checkSeatAvailability()" required>
                         <small id="seat-status"></small>
                    </div>
                    <div class="form-group">
                        <label>Price (₹)</label>
                        <input type="number" name="price" min="1" step="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" required>
                        <option>UPI</option>
                        <option>Card</option>
                        <option>Cash</option>
                    </select>
                </div>

                <button class="btn">Confirm Booking</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> TransitHub. All rights reserved.</p>
    </footer>
</body>
</html>
