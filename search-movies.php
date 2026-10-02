<?php
/**
 * Live search endpoint used by the nav search bar (assets/js/nav-search.js).
 * Returns a small JSON list of movies matching the query string.
 * GET /search-movies.php?q=batman
 */
require_once 'config/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($conn,
        "SELECT id, title, genre, poster_url, release_date
         FROM movies
         WHERE title LIKE ? AND status != 'archived'
         ORDER BY release_date DESC
         LIMIT 8"
    );
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($res)) {
        $results[] = [
            'id'         => (int) $row['id'],
            'title'      => $row['title'],
            'genre'      => trim(explode(',', $row['genre'])[0]),
            'poster_url' => $row['poster_url'],
            'year'       => $row['release_date'] ? date('Y', strtotime($row['release_date'])) : null,
            'url'        => 'movie-details.php?id=' . (int) $row['id'],
        ];
    }
}

echo json_encode(['query' => $q, 'results' => $results]);
