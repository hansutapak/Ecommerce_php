<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check admin login
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("location: admin_login.php");
    exit();
}

$product_id = intval($_GET['id'] ?? 0);

// Fetch product details
try {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        die("Product not found!");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Find image paths - USE SAME LOGIC AS ADMIN.PHP
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

// Get all images
$all_images = [];
if (!empty($product['images'])) {
    $all_images = array_map('trim', explode(',', $product['images']));
} else {
    $all_images = !empty($product['image']) ? [$product['image']] : [];
}

// Find image paths
$image_paths = [];
foreach ($all_images as $img) {
    $image_path = "../category/{$category_folder}/{$img}";

    if (file_exists($image_path)) {
        $image_paths[] = $image_path;
    } else {
        // Fallback: search all folders
        $folders = ['men', 'women', 'kids', 'footwear', 'sport', 'socks'];
        $found = false;
        foreach ($folders as $folder) {
            $try_path = "../category/{$folder}/{$img}";
            if (file_exists($try_path)) {
                $image_paths[] = $try_path;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $image_paths[] = '../images/placeholder.jpg';
        }
    }
}

$current_image = !empty($image_paths[0]) ? $image_paths[0] : '../images/placeholder.jpg';

// Format sizes
$sizes = !empty($product['size']) ? explode(',', $product['size']) : ['One size'];

// Calculate discount
$discount = 0;
if ($product['original_price'] > 0 && $product['original_price'] > $product['price']) {
    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .btn-back {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 0px;
        }

        .btn-back:hover {
            background: #2980b9;
        }

        .product-view {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
        }

        .product-images {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-images img {
            max-width: 500px;
            border: 1px solid #ddd;
            border-radius: 0px;
        }

        .thumbnails {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            width: fit-content;
        }

        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 2px solid #ddd;
            cursor: pointer;
        }


        .thumbnail.active {
            border-color: #3498db;
        }

        .product-info h2 {
            margin-top: 0;
            color: #333;
        }

        .price-section {
            margin: 20px 0;
        }

        .current-price {
            font-size: 24px;
            color: #e74c3c;
            font-weight: bold;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
            margin-left: 10px;
        }

        .discount-badge {
            background: #2ecc71;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
            margin-left: 10px;
        }

        .product-details {
            margin-top: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }

        .btn-edit,
        .btn-delete,
        .btn-back-to-list {
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-back-to-list {
            background: #95a5a6;
            color: white;
        }
    </style>
    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
                if (thumb.src.includes(src)) {
                    thumb.classList.add('active');
                }
            });
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>💁🏻‍♀️ Product Details</h2>
            <a href="admin.php" class="btn-back">← Back to Products</a>
        </div>

        <div class="product-view">
            <!-- Images Section -->
            <div class="product-images">
                <img id="mainImage" src="<?php echo $current_image; ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>">

                <?php if (count($image_paths) > 1): ?>
                    <div class="thumbnails">
                        <?php foreach ($image_paths as $index => $img_path): ?>
                            <img src="<?php echo $img_path; ?>"
                                alt="Thumbnail <?php echo $index + 1; ?>"
                                class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                onclick="changeMainImage('<?php echo $img_path; ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info Section -->
            <div class="product-info">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p><strong>ID:</strong> <?php echo $product['id']; ?></p>

                <div class="price-section">
                    <span class="current-price">£<?php echo number_format($product['price'], 2); ?></span>
                    <?php if ($product['original_price'] > 0): ?>
                        <span class="original-price">£<?php echo number_format($product['original_price'], 2); ?></span>
                    <?php endif; ?>
                    <?php if ($discount > 0): ?>
                        <span class="discount-badge">Save <?php echo $discount; ?>%</span>
                    <?php endif; ?>
                </div>

                <div class="product-details">
                    <div class="detail-row">
                        <span class="detail-label">Category:</span>
                        <span><?php echo htmlspecialchars($product['category_name']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Description:</span>
                        <span><?php echo nl2br(htmlspecialchars($product['description'])); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Available Sizes:</span>
                        <span><?php echo implode(', ', $sizes); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Stock Quantity:</span>
                        <span><?php echo $product['stock']; ?> units</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Featured:</span>
                        <span><?php echo $product['featured'] ? 'Yes' : 'No'; ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Created:</span>
                        <span><?php echo date('M d, Y H:i', strtotime($product['created_at'])); ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>