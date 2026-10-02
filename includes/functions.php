<?php
/**
 * Shared helper functions used across the site.
 */

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function format_duration($minutes) {
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return $h . 'h ' . str_pad($m, 2, '0', STR_PAD_LEFT) . 'm';
}

function generate_booking_reference() {
    return 'CPX-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

function average_rating($conn, $movie_id) {
    $stmt = mysqli_prepare($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE movie_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $movie_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return [
        'avg' => $row['avg_rating'] ? round($row['avg_rating'], 1) : null,
        'total' => (int) $row['total']
    ];
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
