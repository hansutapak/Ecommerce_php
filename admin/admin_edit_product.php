<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth.php';

// CHECK IF THE USER ID IS THERE
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('location: admin.php');
    exit();
}


$product_id = intval($_GET['id']);
$success_message = '';
$error_message = '';


// FETCH PRODUCT DETAILS FOR PRRE FILLING FORM
try {

    $sql = "SELECT p.*, c.name as category_name
          FROM products p
          JOIN categories c ON p.category_id = c.id
          WHERE p.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("location: admin.php?message=not_found");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

try {

    $categories_sql = "SELECT * FROM categories ORDER BY name ";
    $categories_stmt = $pdo->query($categories_sql);
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Categories query failed: " . $e->getMessage());
}

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //GET FORM DATA
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : 0;
    $category_id = intval($_POST['category_id']);
    $size = trim($_POST['size']);
    $stock = intval($_POST['stock']);

    //VALIDATE REQUUIRED FIELDS
    if (empty($name) || empty($description) || $price <= 0 || empty($category_id)) {
        $error_message = "Please fill all the required fields (Name, Description, price, category_id)";
    } else {
        try {
            //PREPARE UPDATE QUERY
            $update_sql = "UPDATE products SET
                       name = :name,
                       description = :description,
                       price = :price,
                       original_price = :original_price,
                       category_id = :category_id,
                       size = :size,
                       stock = :stock
                       WHERE id = :id ";

            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':original_price' => $original_price,
                ':category_id' => $category_id,
                ':size' => $size,
                ':stock' => $stock,
                ':id' => $product_id,
            ]);

            $success_message = "Product updated successfully";

            //REFRESH PRODUCT DATA
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error_message = "Error updating product details " . $e->getMessage();
        }
    }
}

// FIND THE CURRENT IMAGE PATH 
$category_id = $product['category_id'] ?? 1;
$category_folder = 'men'; // default

if ($category_id == 2) {
    $category_folder = 'women';
} elseif ($category_id == 3) {
    $category_folder = 'kids';
} elseif ($category_id == 4) {
    $category_folder = 'footwear';
} elseif ($category_id == 5) {
    $category_folder = 'sport';
} elseif ($category_id == 6) {
    $category_folder = 'socks';
}

$image_name = htmlspecialchars($product['image']);
$image_path = "../category/{$category_folder}/{$image_name}";

// Check if file exists
if (file_exists($image_path)) {
    $current_image_path = $image_path;
} else {
    $current_image_path = "../images/placeholder.jpg";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Panel</title>
    <link rel="stylesheet" href="../styles/adminStyle.css">
    <style>
        .admin-container {
            margin: 0;
            padding: 0;
        }

        .admin-head-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .admin-logo {
            height: auto;
            width: 40px;
            vertical-align: middle;
            margin-right: 15px;
        }


        .admin-main {
            text-align: left;
        }

        .edit-product-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 10px;

        }

        .edit-product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #222122;

        }

        .edit-product-header {
            display: flex;

            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .edit-product-card {
            background: white;
            border-radius: 0px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .form-label input {
            border-radius: 0px !important;
        }

        .form-label.required::after {
            content: " *";
            color: #dc3545;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 0px;
            font-size: 16px;
            transition: border 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .current-image {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0px;
        }

        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 0px;
        }

        .image-note {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn-submit {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 0px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .btn-submit:hover {
            background: #218838;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 0px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .price-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {

            .form-row,
            .price-inputs {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons button,
            .action-buttons a {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- admin container -->
    <div class="admin-container">

        <!-- header -->
        <header class="admin-header">
            <div class="admin-head-container">
                <h1><img src="../icons/for_life-removebg-preview.png" class="admin-logo"> Admin Panel</h1>
                <nav class="admin-nav">
                    <button onclick="window.location.href='admin.php'" class="nav-btn">← Back to Products</button>
                    <button onclick="window.location.href='admin_view_product.php?id=<?php echo $product_id; ?>'"
                        class="nav-btn">View Product</button>
                    <button onclick="window.location.href='admin_logout.php'" class="nav-btn">Logout</button>

                </nav>
            </div>
        </header>
        <!-- close header -->

        <!-- main -->
        <main class="admin-main">
            <div class="edit-product-container">

                <!-- edit product header -->
                <div class="edit-product-header">
                    <h2>👩🏻‍💻 Edit Product: <?php echo htmlspecialchars($product['name']); ?></h2>
                    <div>
                        <button onclick="window.location.href='admin_view_product.php?id=<?php echo $product_id ?>'"></button>
                    </div>
                </div>
                <!-- close edit product header -->

                <!-- success/error messages -->
                <?php if ($success_message): ?>
                    <div class="message success">
                        ✅ <?php echo htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="message error">
                        ❌ <?php echo htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
                <!-- success/error messages -->

                <!-- edit product card -->
                <div class="edit-product-card">
                    <!-- current image display -->
                    <div class="current-image">
                        <p><strong>Current Image:</strong></p>
                        <img src="<?php echo $current_image_path; ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <P class="image-note">
                            Image: <?php echo htmlspecialchars($product['image']); ?><br>
                            <small>To change the image, you need to delete and re-add the product.</small>
                        </P>
                    </div>
                    <!-- close current image display -->

                    <!-- edit form-->
                    <form method="POST" action="">

                        <!-- form row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required" for="name">
                                    Product Name
                                </label>
                                <input style="border-radius: 0px;"
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($product['name']); ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label class="form-label required" for="category_id">
                                    Category
                                </label>
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                            <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label required" for="description">
                                    Description
                                </label>
                                <textarea style="border-radius: 0px;"
                                    id="description"
                                    name="description"
                                    class="form-control"
                                    required><?php echo htmlspecialchars($product['description']); ?>
                                </textarea>
                            </div>

                        </div>


                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required" for="price">
                                    Price (£)
                                </label>
                                <input style="border-radius: 0px;"
                                    style="border-radius: 0px;" type="number"
                                    id="price"
                                    name="price"
                                    class="form-control"
                                    step="0.01"
                                    min="0.01"
                                    value="<?php echo number_format($product['price'], 2); ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label class="form-label required" for="original_price">
                                    Original price
                                </label>
                                <input style="border-radius: 0px;"
                                    type="number"
                                    id="original_price"
                                    name="original_price"
                                    class="form-control"
                                    step="0.01"
                                    min="0"
                                    value="<?php echo ($product['original_price']) > 0 ? number_format($product['original_price'], 2) : ''; ?>"
                                    placeholder="Leave empty if no discount">
                                <small style="color: #666;">For discounted products only</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required" for="size">
                                    Sizes
                                </label>
                                <input style="border-radius: 0px;"
                                    type="text"
                                    id="size"
                                    name="size"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($product['size']); ?>"
                                    placeholder="e.g., S, M, L, XL"
                                    required>
                                <small style="color: #666;">Separate sizes with commas</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label required" for="stock">
                                    Stock Quantity
                                </label>
                                <input style="border-radius: 0px;"
                                    type="number"
                                    id="stock"
                                    name="stock"
                                    class="form-control"
                                    min="0"
                                    value="<?php echo htmlspecialchars($product['stock']); ?>"
                                    required>
                            </div>

                        </div>
                        <!-- close form row -->

                        <!-- action buttons -->
                        <div class="action-buttons">
                            <button type="submit" class="btn-submit">
                                🔄 Update Product
                            </button>

                            <a href="admin.php?id=<?php echo $product_id; ?>" class="btn-cancel">
                                ← Cancel
                            </a>
                        </div>

                    </form>
                    <!-- close edit form -->

                </div>
                <!-- close edit product card -->

            </div>

        </main>
        <!-- close main -->

    </div>
    <!-- close admin container -->

</body>

</html>