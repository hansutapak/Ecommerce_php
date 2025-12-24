<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once 'includes/config.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
 header("location: login.php");
 exit; 
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {

// INITIALIZE VARIABLES
$user_id = $_SESSION['user_id'];
$cart_id= $_POST['cart_id'];
$quantity = (int) $_POST['quantity'];

if($quantity > 0) {
try{
    $sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt ->execute([$quantity,$cart_id,$user_id ]);

    } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

} else {
      //IF QUANTITY IS 0
      header("location: remove_from_cart.php?id=$cart_id");
      exit;
} header("location: cart.php");
  exit; 
  
}

?>