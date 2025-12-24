<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once 'includes/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header("location: login.php");
  exit;
}

// INITIALIZE VARIABLES
$user_id = $_SESSION['user_id'];
$cart_items = [];
$total = 0;
$item_count = 0;

// GET CART ITEMS FROM PRODUCT DETAILS
try {
  $sql = "SELECT c.*, p.name, p.price, p.image, p.category_id
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$user_id]);
  $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // CALCULATE TOTAL AND COUNT
  foreach ($cart_items as $item) {
    $item_count += $item['quantity'];
    $total += $item['price'] * $item['quantity'];
  }
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/cartStyle.css" />
  <script src="script/script.js"></script>
  <title>Thrive</title>
</head>

<body>
  <div class="container">
    <!-- fixed-top-sec -->
    <div class="fixed-top-sec">
      <!-- header and navigation sec -->
      <header class="header">
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
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
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
        <a href="product-categories/sportproduct.php" class="jersey-category">Sports</a>
        <a href="product-categories/sockproduct.php" class="socks-category">Socks</a>
      </section>
    </div>

    <!-- Cart Section -->
    <div class="cart-main">
      <!-- cart-container -->
      <div class="cart-container">
        <div class="cart-grid">
          <!-- Cart -->
          <h2>Your Cart</h2>

          <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
              <p>Your cart is empty</p>
              <a href="home.php" class="continue-shopping">Continue Shopping</a>
            </div>
          <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
              <div class="cart-item">
                <div class="item-image">
                  <?php
                  // Map category_id to folder
                  $category_id = $item['category_id'] ?? 1;
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

                  // Get image filename and build path
                  $image_name = htmlspecialchars($item['image']);
                  $image_path = "category/{$category_folder}/{$image_name}";
                  ?>
                  <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                </div>

                <div class="item-info">
                  <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                  <p>Size: <?php echo htmlspecialchars($item['size']); ?></p>
                  <p class="price">£<?php echo number_format($item['price'], 2); ?></p>
                </div>

                <div class="item-controls">
                  <form method="POST" action="update_cart.php" style="display: inline;">
                    <input type="hidden" name="cart_id" value="<?php echo $item['id'] ?>">
                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1 ?>">
                    <button type="submit" class="quantity-btn">-</button>
                  </form>

                  <span><?php echo $item['quantity']; ?></span>

                  <form method="POST" action="update_cart.php" style="display: inline;">
                    <input type="hidden" name="cart_id" value="<?php echo $item['id'] ?>">
                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1 ?>">
                    <button type="submit" class="quantity-btn">+</button>
                  </form>
                  <a href="remove_from_cart.php?id=<?php echo $item['id']; ?>" class="remove-btn">Remove</a>
                </div> <!-- item - control closed -->
              </div> <!-- cart - item closed -->
            <?php endforeach; ?> <!-- foreach closed -->
          <?php endif; ?><!-- if closed -->
        </div> <!-- cart - grid closed -->
      </div> <!-- cart - container closed -->

      <!-- summary container -->

      <!-- summary-column -->
      <div class="summary-column">
        <div class="summary-container">
          <h2>Your Summary</h2>
          <div class="summary-tab">
            <div class="summary-item">
              <p><?php echo $item_count; ?>item <?php echo $item_count != 1 ? 's' : ''; ?></p>
              <p>£<?php echo number_format($total, 2); ?></p>
            </div>
            <div class="summary-total">
              <p>Total</p>
              <p>£<?php echo number_format($total, 2); ?></p>
            </div>
            <div class="summary-promo-code">
              <input
                type="text"
                placeholder="Enter your promocode"
                alt="promo-code" />
              <button class="apply-promo-code">Apply</button>
            </div>
            <button class="checkout" onclick="window.location.href='checkout.php'">Checkout</button>
            <!-- <a href="checkout.php" class="checkout">Checkout</a> -->
          </div>
        </div>

        <!-- accepted cards tab -->
        <div class="accepted-cards">
          <div class="master-card-icon"></div>
          <div class="visa-card-icon"></div>
          <div class="american-express-card-icon"></div>
          <div class="paypal-icon"></div>
          <div class="maestro-card-icon"></div>
        </div>
      </div>
    </div>

  </div> <!-- cart - main closed -->

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
          /thrive </a><br />

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