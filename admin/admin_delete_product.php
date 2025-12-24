<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth.php';

//CHECK IF THE PRODUCT ID IS PROVIDED
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: admin.php");
    exit();
}

$product_id = intval($_GET['id']);
$error = '';
$success = '';


//FETCH PRODUCT DETAILS TO GET IMAGE PATH BEFORE DELETING
try {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("location: admin.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

//HANDLE DELETE CONFIRMATION
try {
    //TEMPORARY DELETION FROM ORDER ITEMS ON
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    //DELETE FROM DATABASE
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute([$product_id]);

    //TEMPORARY DELETION FROM ORDER ITEMS OFF
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    //DELETE THE IMAGE FILE IF EXIST
    if (!empty($product['image'])) {
        //TRY DIFFERENT CATEGORY FOLDERS
        $folders = ['men', 'women', 'kids', 'footwear', 'sport', 'socks'];
        foreach ($folders as $folder) {
            $image_path = "../category/{$folder}/" . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
                break;
            }
        }
    }

    //REDIRECT THE SUCCESS MESSAGE
    header("location: admin.php?message=deleted&name=" . urlencode($product['name']));
    exit();
} catch (PDOException $e) {
    // Redirect with error message
    //TEMPORARY DELETION FROM ORDER ITEMS OFF
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    header("location: admin.php?message=error&name=" . urlencode($product['name']));
    exit();
}
