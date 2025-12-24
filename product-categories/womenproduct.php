<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once '../includes/config.php';

try {
  // FETCH MEN'S DATA FROM DATABASE
  $category_id = 2;
  $sql = "SELECT * FROM products WHERE category_id = ?";
  $params = [$category_id];

  // ADD FILTERING FOR PRICE AND SIZE:
  $conditions = [];

  // 1. Price Filter
  if (isset($_GET['price']) && is_array($_GET['price'])) {
    $price_conditions = [];
    foreach ($_GET['price'] as $price_range) {
      if ($price_range == 'under25') {
        $price_conditions[] = "price < 25";
      } elseif ($price_range == '25-50') {
        $price_conditions[] = "price BETWEEN 25 AND 50";
      } elseif ($price_range == 'over50') {
        $price_conditions[] = "price > 50";
      }
    }
    if (!empty($price_conditions)) {
      $conditions[] = "(" . implode(" OR ", $price_conditions) . ")";
    }
  }

  // 2. Size Filter
  if (isset($_GET['size']) && is_array($_GET['size'])) {
    $size_conditions = [];
    foreach ($_GET['size'] as $size) {
      $size_conditions[] = "size LIKE ?";
      $params[] = "%$size%";
    }
    if (!empty($size_conditions)) {
      $conditions[] = "(" . implode(" OR ", $size_conditions) . ")";
    }
  }

  // 3. Add all conditions to SQL
  if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
  }

  // Handle pagination
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
  $offset = ($page - 1) * $limit;

  // Add LIMIT and OFFSET directly 
  $sql .= " LIMIT " . $limit . " OFFSET " . $offset;

  // 4. Execute query
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // GET PRODUCT COUNT (with same filters)
  $count_sql = "SELECT COUNT(*) AS total FROM products WHERE category_id = ?";
  $count_params = [$category_id];

  if (!empty($conditions)) {
    $count_sql .= " AND " . implode(" AND ", $conditions);
    // Add size parameters if they exist
    if (isset($_GET['size']) && is_array($_GET['size'])) {
      foreach ($_GET['size'] as $size) {
        $count_params[] = "%$size%";
      }
    }
  }

  $stmt = $pdo->prepare($count_sql);
  $stmt->execute($count_params);
  $product_count_result = $stmt->fetch(PDO::FETCH_ASSOC);
  $product_count = $product_count_result['total'];
} catch (Exception $e) {
  echo "Database Error:" . $e->getMessage();
  $products = [];
  $product_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles/style.css" />
  <link rel="stylesheet" href="../styles/productStyles.css" />
  <script src="../script/script.js"></script>
  <title>Thrive</title>
</head>

<body>
  <div class="container">
    <!-- fixed-top-sec -->
    <div class="fixed-top-sec">
      <!-- header and navigation sec -->
      <header class="header">
        <!-- hamburger menu -->
        <button class="mobile-menu-btn">
          <img src="../icons/icons8-hamburger-menu-100.png" />
        </button>

        <div class="logo">
          <a href="../home.php">
            <img
              src="../icons/for_life-removebg-preview.png"
              class="logo-icon" />
          </a>
        </div>

        <!-- search bar -->
        <div class="search-bar">
          <input
            type="text"
            placeholder="Search product"
            class="search-input" />
          <button class="search-icon">
            <img src="../icons/search-icon.svg" alt="search" />
          </button>
        </div>

        <!-- nav-pages -->
        <div class="nav-menu">
          <!-- wishlist -->
          <a href="../wishlist.php" class="wishlist-icon">
            <img src="../icons/icons8-heart-96.png" />
          </a>

          <!-- cart -->
          <a href="../cart.php" class="cart-icon">
            <img src="../icons/icons8-cart-96.png" alt="cart" />
          </a>

          <!-- Dynamic login/logout -->
          <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- show logout icon when user is login -->
            <a href="../logout.php" class="logout-icon">
              <img src="../icons/logout.svg" style="width: 25px;" />
            </a>
          <?php else: ?>
            <!-- show login icon when user is logout -->
            <a href="../login.php" class="login-icon">
              <img src="../icons/login3.svg" />
            </a>
          <?php endif; ?>
        </div>
      </header>

      <!-- Mobile Menu Overlay -->
      <div class="mobile-menu-overlay">
        <div class="mobile-menu-content">
          <div class="menu-header">
            <h3>Categories</h3>
            <button class="close-menu-btn">✕</button>
          </div>
          <div class="mobile-categories">
            <a href="menproduct.php" class="men-category">Mens</a>
            <a href="womenproduct.php" class="women-category">Womens</a>
            <a href="kidproduct.php" class="kids-category">Kids</a>
            <a href="footwearproduct.php" class="footwear-category">Footwear</a>
            <a href="sportproduct.php" class="jersey-category">Sports</a>
            <a href="sockproduct.php" class="socks-category">Socks</a>
            <a href="../login.php" class="mobile-login">Account</a>
          </div>
        </div>
      </div>

      <!-- hero sec -->
      <section class="hero">
        <div class="hero-content">
          <b> Cozy & Save Big - 🍁 Autumn Specials!</b>
          <a href="#">Shop now</a>
        </div>
      </section>

      <!-- categories -->
      <section class="categories">
        <a href="menproduct.php" class="men-category">Mens</a>
        <a href="womenproduct.php" class="women-category">Womens</a>
        <a href="kidproduct.php" class="kids-category">Kids</a>
        <a href="footwearproduct.php" class="footwear-category">Footwear</a>
        <a href="sportproduct.php" class="jersey-category">Sports</a>
        <a href="sockproduct.php" class="socks-category">Socks</a>
      </section>
    </div>

    <!-- product-sec -->

    <div class="products-main">
      <h2>Women's Apparel <span class="product-count">(<?php echo $product_count; ?>)</span></h2>
      <p>
        Empower your fitness journey with our carefully curated women's
        activewear collection. Designed for comfort and performance, our range
        includes breathable leggings, supportive sports bras, and flexible
        workout tops that move with you. Featuring trusted brands like Nike,
        Puma, and Reebok, each piece combines fashion with function. From
        high-intensity training to yoga sessions, discover flattering fits,
        vibrant patterns, and moisture-management fabrics that keep you
        confident and comfortable during any activity.
      </p>
      <hr />

      <!-- products-container with filter and product grid -->
      <div class="products-container">
        <!-- Add this filter button for mobile -->
        <button class="filter-btn" id="filterToggleBtn">
          <span class="filter-icon"> </span>
          Filters
        </button>
        <!-- Filter Section -->
        <div class="filter-section">
          <h2>Filters</h2>

          <!-- form -->
          <form method="GET" action="womenproduct.php">
            <input type="hidden" name="search" value="">

            <!-- <div class="filter-group">
            <h4>Category</h4>
            <label><input type="checkbox" /> Running</label>
            <label><input type="checkbox" /> Football</label>
            <label><input type="checkbox" /> Basketball</label>
             </div> -->
            <hr />

            <div class="filter-group">
              <h4>Price</h4>
              <label><input type="checkbox" name="price[]" value="under25" <?php echo (isset($_GET['price']) && in_array('under25', $_GET['price'])) ? 'checked' : ''; ?> /> Under $25</label>
              <label><input type="checkbox" name="price[]" value="25-50" <?php echo (isset($_GET['price']) && in_array('25-50', $_GET['price'])) ? 'checked' : ''; ?> /> $25 - $50</label>
              <label><input type="checkbox" name="price[]" value="over50" <?php echo (isset($_GET['price']) && in_array('over50', $_GET['price'])) ? 'checked' : ''; ?> /> Over $50</label>
            </div>
            <hr />

            <div class="filter-group">
              <h4>Size</h4>
              <label><input type="checkbox" name="size[]" value="S" <?php echo (isset($_GET['size']) && in_array('S', $_GET['size'])) ? 'checked' : ''; ?> /> S</label>
              <label><input type="checkbox" name="size[]" value="M" <?php echo (isset($_GET['size']) && in_array('M', $_GET['size'])) ? 'checked' : ''; ?> /> M</label>
              <label><input type="checkbox" name="size[]" value="L" <?php echo (isset($_GET['size']) && in_array('L', $_GET['size'])) ? 'checked' : ''; ?> /> L</label>
              <label><input type="checkbox" name="size[]" value="XL" <?php echo (isset($_GET['size']) && in_array('XL', $_GET['size'])) ? 'checked' : ''; ?> /> XL</label>
            </div>

            <!-- filter button -->
            <button type="submit" class="filter-apply-btn">Filter</button>

            <!-- Clear filters link -->
            <?php if (isset($_GET['category']) || isset($_GET['price']) || isset($_GET['size'])): ?>
              <br>
              <a href="womenproduct.php" style="text-align: center; display: block; margin-top: 10px; color: #3498db;">
                Clear All Filters
              </a>
            <?php endif; ?>
          </form>
        </div> <!--close filter sec-->


        <!-- Filter mobile Section -->
        <div class="filter-mobile-section">
          <!-- ADD THIS HEADER WITH CLOSE BUTTON -->
          <div class="filter-mobile-header">
            <h2>Filters</h2>
            <!-- ADD THIS CLOSE BUTTON -->
            <button class="close-filter-btn">✕</button>
          </div>
          <!-- mobile content wrapper -->
          <div class="filter-mobile-content">

            <form method="GET" action="womenproduct.php">
              <input type="hidden" name="search" value="">

              <!-- <div class="filter-mobile-group">
              <h4>Category</h4>
              <label><input type="checkbox" /> Running</label>
              <label><input type="checkbox" /> Football</label>
              <label><input type="checkbox" /> Basketball</label>
              </div> -->
              <hr />
              <div class="filter-mobile-group">
                <h4>Price</h4>
                <label><input type="checkbox" name="price[]" value="under25" <?php echo (isset($_GET['price']) && in_array('under25', $_GET['price'])) ? 'checked' : ''; ?> /> Under $25</label>
                <label><input type="checkbox" name="price[]" value="25-50" <?php echo (isset($_GET['price']) && in_array('25-50', $_GET['price'])) ? 'checked' : ''; ?> /> $25 - $50</label>
                <label><input type="checkbox" name="price[]" value="over50" <?php echo (isset($_GET['price']) && in_array('over50', $_GET['price'])) ? 'checked' : ''; ?> /> Over $50</label>
              </div>
              <hr />
              <div class="filter-mobile-group">
                <h4>Size</h4>
                <label><input type="checkbox" name="size[]" value="S" <?php echo (isset($_GET['size']) && in_array('S', $_GET['size'])) ? 'checked' : ''; ?> /> S</label>
                <label><input type="checkbox" name="size[]" value="M" <?php echo (isset($_GET['size']) && in_array('M', $_GET['size'])) ? 'checked' : ''; ?> /> M</label>
                <label><input type="checkbox" name="size[]" value="L" <?php echo (isset($_GET['size']) && in_array('L', $_GET['size'])) ? 'checked' : ''; ?> /> L</label>
                <label><input type="checkbox" name="size[]" value="XL" <?php echo (isset($_GET['size']) && in_array('XL', $_GET['size'])) ? 'checked' : ''; ?> /> XL</label>
              </div>

              <div class="filter-mobile-apply">
                <button type="submit" class="filter-mobile-apply-btn">Apply</button>
              </div>
              <!-- Clear filters link -->
              <?php if (isset($_GET['category']) || isset($_GET['price']) || isset($_GET['size'])): ?>
                <br>
                <a href="womenproduct.php" style="text-align: center; display: block; margin-top: 10px; color: #3498db;">
                  Clear All Filters
                </a>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <!-- product-list -->
        <div class="products-grid">
          <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
              <div class="product-card">
                <div class="product-image">
                  <a href="../product_preview.php?id=<?php echo $product['id'] ?>">
                    <img src="../category/women/<?php echo $product['image']; ?>" />
                  </a>
                </div>
                <div class="product-info">
                  <h3 style="margin-left: 0px; margin-top:20px;"><?php echo htmlspecialchars($product['name']); ?></h3>
                  <p style="margin-top: 10px;"><?php echo htmlspecialchars($product['description']); ?></p>
                  <p style="margin-top: 10px;"><?php echo htmlspecialchars($product['size']); ?></p>
                  <p class="product-price">
                    £<?php echo number_format($product['price'], 2); ?>
                    <?php if ($product['price'] < 45): ?>
                      <span class="original-price">£45.00</span>
                    <?php endif; ?>
                  </p>
                  <div class="product-btn">
                    <button class="wishlist-btn">Wishlist</button>

                    <!-- ADD TO CART FORM -->
                    <form method="POST" action="../add_to_cart.php">
                      <input type="hidden" name="product_id" value="<?php echo $product['id'] ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button class="add-cart-btn">Add to cart</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No products found in Women's category. Please add products to the database.☹️</p>
          <?php endif; ?>
        </div>
        <!-- close product-list -->

        <!-- loadmore button -->
        <div class="load-more-container">
          <button class="load-more-btn" onclick="loadMoreProducts()">
            Load More Products
          </button>
        </div>
      </div>
    </div>

    <!-- footer -->
    <footer class="footer">
      <!-- newsletter sec -->
      <div class="newsletter">
        <div class="newsletter-form">
          <input type="email" placeholder="Enter your email address" />
          <button>Sign up</button>
        </div>
        <p class="newsletter-terms">
          By submitting your email address, you agree to receive emails from
          us about latest arrivals and promotions.
        </p>
      </div>

      <!-- main-footer links -->
      <div class="footer-links">
        <div class="footer-column">
          <h4>Customer Service</h4>
          <a href="#">FAQs</a><br />
          <a href="#">Orders & payments</a><br />
          <a href="#">Delivery</a><br />
          <a href="#">Returns</a><br />
          <a href="#">Track orders</a>
        </div>

        <div class="footer-column">
          <h4>Information</h4>
          <a href="#">My account</a><br />
          <a href="#">Membership</a><br />
          <a href="#">Personalisation</a><br />
          <a href="#">Find your local store</a><br />
          <a href="#">The Zone</a>
        </div>

        <div class="footer-column">
          <h4>Information</h4>
          <a href="#">My account</a><br />
          <a href="#">Membership</a><br />
          <a href="#">Personalisation</a><br />
          <a href="#">Find your local store</a><br />
          <a href="#">The Zone</a>
        </div>

        <div class="footer-column">
          <h4>Information</h4>
          <a href="#" class="socialicon">
            <img src="../images/Social icons/facebook-svgrepo-com.svg" />
            /thrive</a><br />

          <a href="#" class="socialicon">
            <img src="../images/Social icons/instagram-167-svgrepo-com.svg" />
            @thrive</a><br />

          <a href="#" class="socialicon">
            <img src="../images/Social icons/pinterest-svgrepo-com.svg" />
            @thrivefootball</a><br />

          <a href="#" class="socialicon">
            <img src="../images/Social icons/tiktok-svgrepo-com.svg" />
            @thrivefootball</a><br />

          <a href="#" class="socialicon">
            <img src="../images/Social icons/youtube-168-svgrepo-com.svg" />
            thrive</a>
        </div>
      </div>

      <!-- footer-bottom -->
      <div class="footer-bottom">
        <div class="footer-info">
          <span>United Kingdom (£ GBP)</span>
        </div>
        <div class="footer-link">
          <a href="#">Store finder</a>
        </div>
        <div class="footer-link">
          <a href="#">Customer Service</a>
        </div>
      </div>

      <!-- Copyright -->
      <div class="footer-copyright">
        <div class="legal-links">
          <p>
            &copy; 2025 Thrive.com Retail Ltd.<a href="#">Privacy Policy</a><a href="#">Sitemap</a>
          </p>
        </div>
        <div class="footer-cards">
          <div class="footer-card">
            <img src="../images/Cards/visa-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="../images/Cards/maestro-subtext-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="../images/Cards/paypal-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="../images/Cards/american-express-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="../images/Cards/amazon-pay-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="../images/Cards/apple-pay-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="../images/Cards/bancontact-svgrepo-com.svg" />
          </div>
        </div>
      </div>

      <!-- Legal Notice -->
      <div class="footer-legal">
        <p>
          <strong>Thrive.com Retail Ltd</strong> (FRN: 980401), trading as
          'Sports Direct', is an appointed representative of Frasers Group
          Credit Broking Limited (FRN: 947961) who are authorised and
          regulated by the Financial Conduct Authority as a credit broker not
          a lender. Frasers Plus is a credit product provided by Frasers Group
          Financial Services Limited (FRN: 311908) and is subject to your
          financial circumstances. Missed payments may affect your credit
          score. * Exclusive access to discounts on selected products for
          Frasers Plus customers before everyone else. For regulated payment
          services, Frasers Group Financial Services Limited is a payment
          agent of Transact Payments Limited, a company authorised and
          regulated by the Gibraltar Financial Services Commission as an
          electronic money institution.
        </p>
      </div>
      <!-- last-div -->
      <div class="footer-last-div">
        <div class="left-end"></div>
        <div class="right-end"></div>
      </div>
    </footer>
  </div>
</body>

</html>