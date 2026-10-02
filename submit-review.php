<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$movie_id = (int) ($_POST['movie_id'] ?? 0);

if (!is_logged_in()) {
    header('Location: login.php?redirect=movie-details.php?id=' . $movie_id);
    exit;
}

$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 10) {
    flash('review_error', 'Please provide a rating between 1 and 10.');
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO reviews (movie_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iiis', $movie_id, $_SESSION['user_id'], $rating, $comment);
    if (mysqli_stmt_execute($stmt)) {
        flash('review_success', 'Thanks for your review!');
    } else {
        flash('review_error', 'Something went wrong submitting your review.');
    }
}

header('Location: movie-details.php?id=' . $movie_id);
exit;
