

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// DEBUG: Check session
echo "<h3>Session Debug:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['selected_size']) ? $_POST['selected_size'] : 'M';

    // // FETCH THE PRODUCT FROM THE DATABASE
    // $product_sql = "SELECT * FROM products WHERE id = ?";
    // $product_stmt = $pdo->prepare($product_sql);
    // $product_stmt->execute([$product_id]);
    // $product = $product_stmt->fetch();
    // $image = $product['image'];


    try {
        // CHECK IF THE ITEM ALREADY EXIST
        $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$user_id, $product_id, $size]);


        if ($check_stmt->rowCount() > 0) {
            // UPDATE QUANTITY IF EXISTS
            $cart_item = $check_stmt->fetch();
            $new_quantity = $cart_item['quantity'] + $quantity;
            $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$new_quantity, $cart_item['id']]);
        } else {
            // INSERT NEW ITEM
            $insert_sql = "INSERT INTO cart (user_id, product_id, quantity, size) VALUES (?, ?, ?,?)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([$user_id, $product_id, $quantity, $size]);
        }

        header("location: cart.php");
        exit();
    } catch (Exception $e) {
        echo "Database Error:" . $e->getMessage();
    }
} else {
    header("location: home.php");
    exit();
}
