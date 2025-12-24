<?php

// Don't check auth if we're on the login page
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page === 'admin_login.php') {
    return;
}


if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("location: admin_login.php");
    exit();
}
