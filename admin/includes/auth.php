<?php
// Include after config/db.php + includes/functions.php
if (!is_admin_logged_in()) {
    header('Location: login.php');
    exit;
}
