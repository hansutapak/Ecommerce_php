<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once 'includes/config.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
 header("location: login.php");
 exit; 
}

if(isset($_GET['id'])) {

$user_id = $_SESSION['user_id'];
$cart_id = $_GET['id'];


try{
    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt ->execute([$cart_id,$user_id ]);

    } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

 header("location: cart.php");
  exit; 
}
?>