<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth.php';


$error = '';
$success = '';


//HANDLE FORM SUNMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? 0;
    $original_price = $_POST['original_price'] ?? 0;
    $size = $_POST['size'] ?? 'M';
    $stock = $_POST['stock'] ?? 0;
    $category_id = $_POST['category_id'] ?? 1;

    // VALIDATE REQUIRED FIELDS
    if (empty($name) || empty($price) || empty($stock)) {
        $error = 'Name, price, and stock are required fields.';
    } else {
        //HANDLE IMAGE UPLOAD
        $image_filename = '';

        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
            $image_filename = $_FILES['image']['name'];

            //FOLDER BASED ON CATEGORY
            $category_folder = [
                1 => 'men',
                2 => 'women',
                3 => 'kids',
                4 => 'footwear',
                5 => 'sport',
                6 => 'socks'
            ];

            //GET FOLDER NAME BY CATEGORY ID DEFAULT MEN
            $folder_name = $category_folder[$category_id] ?? 'men';
            $target_path = "../category/{$folder_name}/" . $image_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                //IMAGE SAVED SUCCESSFULLY
                // Now insert into database (add this part)
                try {
                    $sql = "INSERT INTO products (name, description, price, original_price, size, stock, category_id, image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $name,
                        $description,
                        $price,
                        $original_price,
                        $size,
                        $stock,
                        $category_id,
                        $image_filename
                    ]);

                    $success = 'Product added successfully!';
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            } else {
                $error = "Image upload failed. Could not save to ../category/{$folder_name}/ folder.";
            }
        } else {
            $error = "Product image is required";
        }
    }
}

//FETCH CATEGORIES FOR DROPDOWN 

try {
    $categories_sql = "SELECT * FROM categories ORDER BY name";
    $categories_stmt = $pdo->query($categories_sql);
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = []; // Fallback empty array if query fails
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
    <link rel="stylesheet" href="../styles/adminStyle.css">
</head>

<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="admin-header-container">
                <h1> <img src="../icons/for_life-removebg-preview.png" class="admin-logo"> Add New Product</h1>
                <nav class="admin-nav">
                    <button onclick="window.location.href='admin.php'" class="nav-btn">Back to Products</button>
                    <button onclick="window.location.href='admin_logout.php'" class="nav-btn">Logout</button>
                </nav>
            </div>
        </header>

        <!-- main -->
        <main class="admin-main">
            <!-- content -->
            <div class="tab-content active" style="max-width: 900px; margin: 0 auto; padding: 10px;">
                <div class="section-header">
                    <h2>👩🏻‍💻 New Product Details</h2>
                </div>

                <!-- ERROR/SUCCESS MESSAGES -->
                <?php if ($error): ?>
                    <div style="background: #ffeaea; color: #e74c3c; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- PRODUCT FORM -->
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label required">
                                Product Name
                            </label>
                            <input style="border-radius: 0px;"
                                type="text"
                                id="name"
                                name="name"
                                value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="category_id" class="form-label required">
                                Category
                            </label>
                            <select style="border-radius: 0px;"
                                id="category_id"
                                name="category_id"
                                required>

                                <option value="">Select Category</option>

                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                            <?php echo (isset($category_id) && $category_id == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>

                                    <!-- Fallback if categories query fails -->
                                    <option value="1">Men's</option>
                                    <option value="2">Women's</option>
                                    <option value="3">Kids</option>
                                    <option value="4">Footwear</option>
                                    <option value="5">Sports</option>
                                    <option value="6">Socks</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            Description
                        </label>
                        <textarea style="border-radius: 0px;"
                            id="description"
                            name="description"
                            rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price" class="form-label required">
                                Current Price (£)
                            </label>
                            <input style="border-radius: 0px;"
                                type="number"
                                id="price"
                                name="price"
                                step="0.01"
                                min="0"
                                value="<?php echo htmlspecialchars($price ?? '0'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="original_price">
                                Original Price (£)
                            </label>
                            <input style="border-radius: 0px;"
                                type="number"
                                id="original_price"
                                name="original_price"
                                step="0.01"
                                min="0"
                                value="<?php echo htmlspecialchars($original_price ?? '0'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="stock" class="form-label required">
                                Stock Quantity
                            </label>
                            <input style=" border-radius: 0px;"
                                type="number"
                                id="stock"
                                name="stock"
                                min="0"
                                value="<?php echo htmlspecialchars($stock ?? '0'); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="size">Size</label>
                            <select id="size" name="size">
                                <option value="XS" <?php echo (isset($size) && $size == 'XS') ? 'selected' : ''; ?>>XS</option>
                                <option value="S" <?php echo (isset($size) && $size == 'S') ? 'selected' : ''; ?>>S</option>
                                <option value="M" <?php echo (isset($size) && $size == 'M') ? 'selected' : ''; ?>>M</option>
                                <option value="L" <?php echo (isset($size) && $size == 'L') ? 'selected' : ''; ?>>L</option>
                                <option value="XL" <?php echo (isset($size) && $size == 'XL') ? 'selected' : ''; ?>>XL</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label required">
                            Product Image
                        </label>
                        <input style=" border-radius: 0px;"
                            type="file"
                            id="image"
                            name="image"
                            accept="image/*"
                            required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Add Product</button>
                        <button type="reset" class="btn-secondary">Clear Form</button>
                        <a href="admin.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <!-- close content -->
        </main>
        <!-- close main -->
    </div>
</body>

</html>