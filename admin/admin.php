<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth.php';

// REDIRECT TO LOGIN IF NOT ADMIN
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
  header("location: admin_login.php");
  exit();
}

// GET ADMIN USERNAME
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// HANDLE SEARCH
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = '';
$searchParams = [];

if (!empty($search)) {
  $searchCondition = "WHERE p.name LIKE :search 
                       OR p.description LIKE :search 
                       OR c.name LIKE :search";
  $searchParams[':search'] = "%$search%";
}

//FETCH PRODUCTS FROM DATABASE USING PDO
try {
  $sql = "SELECT p.*, c.name as category_name 
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          $searchCondition
          ORDER BY p.id ASC";

  $stmt = $pdo->prepare($sql);

  if (!empty($search)) {
    $stmt->execute($searchParams);
  } else {
    $stmt->execute();
  }

  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed:" . $e->getMessage());
}

//FETCH PRODUCTS FROM DATABASE USING PDO
// try {
//   $sql = "SELECT p.*, c.name as category_name 
//           FROM products p
//           LEFT JOIN categories c ON p.category_id = c.id
//           ORDER BY p.id ASC";
//   $stmt = $pdo->query($sql);
//   $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// } catch (PDOException $e) {
//   die("Query failed:" . $e->getMessage());
// }

//CONFIRM MESSAGE AFTER DELETING PRODUCT
$message = $_GET['message'] ?? '';
$product_name = $_GET['name'] ?? '';


if ($message === 'deleted' && $product_name) {
  echo '<div id="msg" style = "background: #d4edda; color:  #155724; padding: 15px; margin-bottom: 20px; border-radius: 0px; transition: opacity 0.5s ease;">✅ Product "' . htmlspecialchars($product_name) . '"  deleted successfully.
  </div>';
} elseif ($message === 'not_found') {
  echo '<div id="msg"  style = "background: #fff3cd; color:  #856404; padding: 15px; margin-bottom: 20px; border-radius: 0px; transition: opacity 0.5s ease;">⚠️ Product not found.</div>';
} elseif ($message === 'error') {
  echo '<div id="msg"  style = "background: #ffeaea; color:  #e74c3c; padding: 15px; margin-bottom: 20px; border-radius: 0px; transition: opacity 0.5s ease;">❌ Error deleting product, Please try again.</div>';
}

// HIDE DELETE AND OTHER MESSAGES
if (!empty($message)) {
  echo '<script>
        // Clear URL
        history.replaceState({}, "", "admin.php");
        
        // Smooth fade out after 5 seconds
        setTimeout(function() {
            var msg = document.getElementById("msg");
            if (msg) {
                // Fade out
                msg.style.opacity = "0";
                // Remove from page after fade completes
                setTimeout(function() {
                    msg.style.display = "none";
                }, 500); 
            }
        }, 3000);
    </script>';
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel - Product Management</title>
  <link rel="stylesheet" href="../styles/adminStyle.css" />
</head>

<body>
  <div class="admin-container">
    <!-- Header -->
    <header class="admin-header">
      <div class="admin-header-container">
        <h1><img src="../icons/for_life-removebg-preview.png" class="admin-logo"> Admin Panel</h1>
        <nav class="admin-nav">
          <button class="nav-btn active" data-tab="products">Products</button>
          <button onclick="window.location.href='admin_add_product.php'" class="nav-btn">Add Product</button>
          <button class="nav-btn" data-tab="orders">Orders</button>
          <button onclick="window.location.href='admin_logout.php'" class="nav-btn">Logout</button>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="admin-main">
      <!-- Products Management Tab -->
      <section id="products" class="tab-content active">
        <div class="section-header">
          <h2>👩🏻‍💼 Product Management</h2>
          <div class=" search-bar">
            <form method="GET" action="admin.php" style="display: flex; gap: 0.5rem; width: 100%;">

              <input
                type="text"
                name="search"
                placeholder="Search products..."
                id="search-products"
                value="<?php echo htmlspecialchars($search ?? '') ?>" />
              <button type="submit" class="search-btn">Search</button>

              <?php if (!empty($search)): ?>
                <button type="button" onclick="window.location.href = 'admin.php'" class="btn-secondary" style="background: #e74c3c; font-size: 1rem;">
                  Clear
                </button>
              <?php endif; ?>
            </form>
          </div>
        </div>




        <div class="products-list">

          <!-- products -->

          <?php if (!empty($products)) : ?>
            <?php foreach ($products as $row) : ?>

              <div class="product-admin-card">
                <div class="product-admin-image">
                  <?php
                  // Determine correct folder based on category_id
                  $category_id = $row['category_id'] ?? 1;
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

                  $image_name = htmlspecialchars($row['image']);
                  $image_path = "../category/{$category_folder}/{$image_name}";

                  // Check if file exists
                  if (file_exists($image_path)) {
                    echo '<img src="' . $image_path . '" alt="' . htmlspecialchars($row['name']) . '" />';
                  } else {
                    echo '<img src="../images/placeholder.jpg" alt="' . htmlspecialchars($row['name']) . '" style="background: #f0f0f0; padding: 10px;" />';
                  }
                  ?>
                </div>

                <div class="product-admin-info">
                  <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                  <p><?php echo htmlspecialchars($row['description']); ?></p>
                  <p><strong>Caterory: </strong><?php echo htmlspecialchars($row['category_name']); ?></p>
                </div>

                <div class="product-price-info">
                  <p class="product-price">£<?php echo number_format($row['price'], 2); ?></p>
                  <?php if ($row['original_price'] > 0): ?>
                    <span class="original-price">
                      £<?php echo number_format($row['original_price'], 2); ?>
                    </span>
                  <?php endif; ?>
                </div>

                <p class="product-sizes"><strong>Sizes: </strong><?php echo htmlspecialchars($row['size']); ?></p>

                <p class="product-stock"><strong>Stock: </strong><?php echo htmlspecialchars($row['stock']); ?></p>

                <button onclick="window.location.href='admin_edit_product.php?id=<?php echo $row['id']; ?>'"
                  class="btn-edit" style="margin: 0px;">Edit</button>

                <button onclick="if(confirm('Delete this product?')) { window.location.href='admin_delete_product.php?id=<?php echo $row['id']; ?>'; }"
                  class="btn-delete">Delete</button>

                <button onclick="window.location.href='admin_view_product.php?id=<?php echo $row['id']; ?>'"
                  class="btn-view">View</button>
              </div> <!-- close product-admin-card-->
            <?php endforeach; ?>
          <?php else: ?>
            <p>No products found</p>
          <?php endif; ?>
        </div><!-- close products -->

      </section> <!-- close section -->

    </main>
  </div> <!-- admin-container -->

  <script src="../script/admin.js"></script>
</body>

</html>