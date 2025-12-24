<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once 'includes/config.php';



// INITIALIZE VARIABLES
$search_term = "";
$search_results = [];
$result_count = 0;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
  $search_term = trim($_GET['search']);


  if (!empty($search_term)) {
    // BUILD SQL QUERY WITH FILTER
    try {
      $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE (p.name LIKE ? OR p.description LIKE ?)";
      $params = ["%$search_term%", "%$search_term%"];


      // CATEGORY FILTER
      if (isset($_GET['category']) && is_array($_GET['category'])) {
        $category_conditions = [];
        $category_params = [];

        foreach ($_GET['category'] as $category) {
          if ($category == 'mens') {
            $category_conditions[] = "p.category_id = ?";
            $category_params[] = 1;
          } elseif ($category == 'womens') {
            $category_conditions[] = "p.category_id = ?";
            $category_params[] = 2;
          } elseif ($category == 'kids') {
            $category_conditions[] = "p.category_id = ?";
            $category_params[] = 3;
          } elseif ($category == 'footwear') {
            $category_conditions[] = "p.category_id = ?";
            $category_params[] = 4;
          } elseif ($category == 'sports') {
            $category_conditions[] = "p.category_id = ?";
            $category_params[] = 5;
          } elseif ($category == 'socks') {
            $category_conditions[] = "p.category_id = ?";
            $category_params[] = 6;
          }
        }

        if (!empty($category_conditions)) {
          $sql .= " AND (" . implode(" OR ", $category_conditions) . ")";
          $params = array_merge($params, $category_params);
        }
      }

      // PRICE FILTER
      if (isset($_GET['price']) && is_array($_GET['price'])) {
        $price_conditions = [];
        foreach ($_GET['price'] as $price) {
          if ($price == 'under25') {
            $price_conditions[] = "p.price < 25";
          } elseif ($price == '25-50') {
            $price_conditions[] = "p.price BETWEEN 25 AND 50";
          } elseif ($price == 'over50') {
            $price_conditions[] = "p.price > 50";
          }
        }
        if (!empty($price_conditions)) {
          $sql .= " AND (" . implode(" OR ", $price_conditions) . ")";
        }
      }


      // SIZE FILTER
      if (isset($_GET['size']) && is_array($_GET['size'])) {
        $size_conditions = [];

        foreach ($_GET['size'] as $size) {
          if (in_array($size, ['S', 'M', 'L', 'XL'])) {
            $size_conditions[] = "p.size = ?";
            $params[] = $size;
          }
        }

        if (!empty($size_conditions)) {
          $sql .= " AND (" . implode(" OR ", $size_conditions) . ")";
        }
      }

      $sql .= " ORDER BY p.name";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $result_count = count($search_results);
    } catch (PDOException $e) {
      echo "Databse error:" . $e->getMessage();
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/productStyles.css" />
  <script src="script/script.js"></script>
  <style>
    /* INLINE FIX FOR SEARCH PAGE BUTTONS */
    .products-grid {
      display: grid !important;
      grid-template-columns: repeat(4, 1fr) !important;

    }

    .product-btn {
      position: absolute;
      display: none;
      left: 35px;
      right: auto;
      gap: 10px;
      bottom: 30px;
      top: auto;
      transform: none;
      z-index: 10;
    }

    .product-card:hover .product-btn {
      display: flex;
    }
  </style>
  <title>Search results - Thrive</title>
</head>

<body>
  <div class="container">
    <!-- fixed-top-sec -->
    <div class="fixed-top-sec">
      <!-- header and navigation sec -->
      <header class="header">
        <!-- hamburger menu -->
        <button class="mobile-menu-btn">
          <img src="icons/icons8-hamburger-menu-100.png" />
        </button>

        <div class="logo">
          <a href="home.php">
            <img
              src="icons/for_life-removebg-preview.png"
              class="logo-icon" />
          </a>
        </div>

        <!-- search bar -->
        <form method="GET" action="search.php" class="search-bar">
          <input
            type="text"
            name="search"
            placeholder="Search product"
            class="search-input"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />
          <button type="submit" class="search-icon">
            <img src="icons/search-icon.svg" alt="search" />
          </button>
        </form>

        <!-- nav-pages -->
        <div class="nav-menu">
          <!-- wishlist -->
          <a href="wishlist.php" class="wishlist-icon">
            <img src="icons/icons8-heart-96.png" />
          </a>

          <!-- cart -->
          <a href="cart.php" class="cart-icon">
            <img src="icons/icons8-cart-96.png" alt="cart" />
          </a>

          <!-- Dynamic login/logout -->
          <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- show logout icon when user is login -->
            <a href="logout.php" class="logout-icon">
              <img src="icons/logout.svg" style="width: 25px;" />
            </a>
          <?php else: ?>
            <!-- show login icon when user is logout -->
            <a href="login.php" class="login-icon">
              <img src="icons/login3.svg" />
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
            <a href="product-categories/menproduct.php" class="men-category">Mens</a>
            <a
              href="product-categories/womenproduct.php"
              class="women-category">Womens</a>
            <a href="product-categories/kidproduct.php" class="kids-category">Kids</a>
            <a
              href="product-categories/footwearproduct.php"
              class="footwear-category">Footwear</a>
            <a
              href="product-categories/sportproduct.php"
              class="jersey-category">Sports</a>
            <a
              href="product-categories/sockproduct.php"
              class="socks-category">Socks</a>
            <!-- Dynamic mobile login/logout -->
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
              <a href="logout.php" class="mobile-login">Logout</a>
            <?php else: ?>
              <a href="login.php" class="mobile-login">Account</a>
            <?php endif; ?>
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
        <a href="product-categories/menproduct.php" class="men-category">Mens</a>
        <a href="product-categories/womenproduct.php" class="women-category">Womens</a>
        <a href="product-categories/kidproduct.php" class="kids-category">Kids</a>
        <a
          href="product-categories/footwearproduct.php"
          class="footwear-category">Footwear</a>
        <a
          href="product-categories/sportproduct.php"
          class="jersey-category">Sports</a>
        <a href="product-categories/sockproduct.php" class="socks-category">Socks</a>
      </section>
    </div>

    <!-- product - main -->
    <div class="products-main">
      <h2>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h2>
      <p><?php echo $result_count; ?> product(s) found</p>
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
          <form method="GET" action="search.php">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">

            <div class="filter-group">
              <h4>Category</h4>
              <label><input type="checkbox" name="category[]" value="mens" <?php echo (isset($_GET['category']) && in_array('mens', $_GET['category'])) ? 'checked' : ''; ?> /> Men's</label>
              <label><input type="checkbox" name="category[]" value="womens" <?php echo (isset($_GET['category']) && in_array('womens', $_GET['category'])) ? 'checked' : ''; ?> /> Women's</label>
              <label><input type="checkbox" name="category[]" value="kids" <?php echo (isset($_GET['category']) && in_array('kids', $_GET['category'])) ? 'checked' : ''; ?> /> Kids</label>
              <label><input type="checkbox" name="category[]" value="footwear" <?php echo (isset($_GET['category']) && in_array('footwear', $_GET['category'])) ? 'checked' : ''; ?> /> Footwear</label>
              <label><input type="checkbox" name="category[]" value="sports" <?php echo (isset($_GET['category']) && in_array('sports', $_GET['category'])) ? 'checked' : ''; ?> /> Sports</label>
              <label><input type="checkbox" name="category[]" value="socks" <?php echo (isset($_GET['category']) && in_array('socks', $_GET['category'])) ? 'checked' : ''; ?> /> Socks</label>
            </div>
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
              <a href="search.php?search=<?php echo urlencode($search_term); ?>" style="text-align: center; display: block; margin-top: 10px; color: #3498db;">
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
            <form method="GET" action="search.php">
              <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">

              <div class="filter-group">
                <h4>Category</h4>
                <label><input type="checkbox" name="category[]" value="mens" <?php echo (isset($_GET['category']) && in_array('mens', $_GET['category'])) ? 'checked' : ''; ?> /> Men's</label>
                <label><input type="checkbox" name="category[]" value="womens" <?php echo (isset($_GET['category']) && in_array('womens', $_GET['category'])) ? 'checked' : ''; ?> /> Women's</label>
                <label><input type="checkbox" name="category[]" value="kids" <?php echo (isset($_GET['category']) && in_array('kids', $_GET['category'])) ? 'checked' : ''; ?> /> Kids</label>
                <label><input type="checkbox" name="category[]" value="footwear" <?php echo (isset($_GET['category']) && in_array('footwear', $_GET['category'])) ? 'checked' : ''; ?> /> Footwear</label>
                <label><input type="checkbox" name="category[]" value="sports" <?php echo (isset($_GET['category']) && in_array('sports', $_GET['category'])) ? 'checked' : ''; ?> /> Sports</label>
                <label><input type="checkbox" name="category[]" value="socks" <?php echo (isset($_GET['category']) && in_array('socks', $_GET['category'])) ? 'checked' : ''; ?> /> Socks</label>
              </div>
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
                <a href="search.php?search=<?php echo urlencode($search_term); ?>" style="text-align: center; display: block; margin-top: 10px; color: #3498db;">
                  Clear All Filters
                </a>
              <?php endif; ?>
            </form>
          </div>
        </div> <!--close filter mobile sec-->



        <!-- product list -->
        <div class="products-grid ">
          <?php if ($result_count > 0): ?>
            <?php foreach ($search_results as $product): ?>
              <div class="product-card">
                <div class="product-image">
                  <!-- image path based on category-->
                  <?php
                  $image_path = "category/";
                  if ($product['category_id'] == 1) $image_path .= "men/";
                  elseif ($product['category_id'] == 2) $image_path .= "women/";
                  elseif ($product['category_id'] == 3) $image_path .= "kids/";
                  elseif ($product['category_id'] == 4) $image_path .= "footwear/";
                  elseif ($product['category_id'] == 5) $image_path .= "sport/";
                  elseif ($product['category_id'] == 6) $image_path .= "socks/";
                  else $image_path .= "men/"; //default

                  ?>
                  <img src="<?php echo $image_path . $product['image']; ?>" />
                  <div class="product-btn">
                    <button class="wishlist-btn">Wishlist</button>

                    <!-- ADD TO CART FORM -->
                    <form method="POST" action="add_to_cart.php">
                      <input type="hidden" name="product_id" value="<?php echo $product['id'] ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button class="add-cart-btn">Add to cart</button>
                    </form>
                  </div> <!--close product btn-->
                </div> <!--close product image-->

                <div class="product-info">
                  <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                  <p><?php echo htmlspecialchars($product['description']); ?></p>
                  <!-- <p>S, M, L, XL</p> -->
                  <p>Size:<?php echo isset($product['size']) ? htmlspecialchars($product['size']) : 'M'; ?></p>
                  <p class="product-price">
                    £<?php echo number_format($product['price'], 2); ?>
                    <?php if ($product['price'] < 45): ?>
                      <span class="original-price">£45.00</span>
                    <?php endif; ?>
                  </p>

                </div> <!--close product info-->
              </div> <!--close product card-->
            <?php endforeach; ?>
          <?php else: ?>
            <p>No products found matching your search <?php echo htmlspecialchars($search_term) ?> Try another keywords.☹️</p>
          <?php endif; ?>

        </div> <!--close product grid-->


      </div><!--close product container-->
    </div> <!--close product main-->

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
            <img src="images/Social icons/facebook-svgrepo-com.svg" />
            /thrive</a><br />

          <a href="#" class="socialicon">
            <img src="images/Social icons/instagram-167-svgrepo-com.svg" />
            @thrive</a><br />

          <a href="#" class="socialicon">
            <img src="images/Social icons/pinterest-svgrepo-com.svg" />
            @thrivefootball</a><br />

          <a href="#" class="socialicon">
            <img src="images/Social icons/tiktok-svgrepo-com.svg" />
            @thrivefootball</a><br />

          <a href="#" class="socialicon">
            <img src="images/Social icons/youtube-168-svgrepo-com.svg" />
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
            <img src="images/Cards/visa-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="images/Cards/maestro-subtext-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="images/Cards/paypal-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="images/Cards/american-express-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="images/Cards/amazon-pay-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="images/Cards/apple-pay-svgrepo-com.svg" />
          </div>
          <div class="footer-card">
            <img src="images/Cards/bancontact-svgrepo-com.svg" />
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