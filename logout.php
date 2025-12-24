<?php
session_start();

// UNSET ALL THE SESSION VARIABLES
$_SESSION = array();

// DESTROY THE SESSIONS
session_destroy();

// REDIRECT TO LOGIN PAGE
header("location: login.php");
exit();

?>