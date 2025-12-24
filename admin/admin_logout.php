<?php
session_start();


// DSTROY SESSION
unset($_SESSION['admin_loggedin']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_id']);


//REDIRECT TO ADMIN LOGIN PAGE
header("location: admin_login.php");
exit();
