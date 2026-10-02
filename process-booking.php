<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

$show_id = (int) ($_POST['show_id'] ?? 0);
$seat_class = $_POST['seat_class'] ?? '';
$adult_tickets = max(0, (int) ($_POST['adult_tickets'] ?? 0));
$kid_tickets = max(0, (int) ($_POST['kid_tickets'] ?? 0));
$total_tickets = $adult_tickets + $kid_tickets;

$valid_classes = ['gold', 'platinum', 'box'];

if (!in_array($seat_class, $valid_classes, true) || $total_tickets < 1) {
    flash('booking_error', 'Please select a seat class and at least one ticket.');
    header('Location: book-ticket.php?show_id=' . $show_id);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM shows WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $show_id);
mysqli_stmt_execute($stmt);
$show = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$show) {
    header('Location: index.php');
    exit;
}

$total_col = $seat_class . '_seats_total';
$booked_col = $seat_class . '_seats_booked';
$price_col = $seat_class . '_price';

$seats_left = $show[$total_col] - $show[$booked_col];

if ($total_tickets > $seats_left) {
    flash('booking_error', 'Only ' . $seats_left . ' ' . ucfirst($seat_class) . ' seats are left for this show.');
    header('Location: book-ticket.php?show_id=' . $show_id);
    exit;
}

$price = (float) $show[$price_col];
$total_amount = ($adult_tickets * $price) + ($kid_tickets * $price * 0.5);
$reference = generate_booking_reference();

mysqli_begin_transaction($conn);
try {
    $insert = mysqli_prepare($conn, "
        INSERT INTO bookings (user_id, show_id, seat_class, adult_tickets, kid_tickets, total_amount, booking_reference)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($insert, 'iisiids', $_SESSION['user_id'], $show_id, $seat_class, $adult_tickets, $kid_tickets, $total_amount, $reference);
    mysqli_stmt_execute($insert);

    $update = mysqli_prepare($conn, "UPDATE shows SET $booked_col = $booked_col + ? WHERE id = ? AND ($total_col - $booked_col) >= ?");
    mysqli_stmt_bind_param($update, 'iii', $total_tickets, $show_id, $total_tickets);
    mysqli_stmt_execute($update);

    if (mysqli_stmt_affected_rows($update) === 0) {
        throw new Exception('Seats no longer available.');
    }

    mysqli_commit($conn);
    header('Location: booking-confirmation.php?ref=' . urlencode($reference));
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    flash('booking_error', 'Sorry, those seats were just taken. Please try again.');
    header('Location: book-ticket.php?show_id=' . $show_id);
    exit;
}
