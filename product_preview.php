<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

//CHECK IF THE PRODUCT IS IS PROVIDED
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("location: home.php");
  exit();
}

$product_id = intval($_GET['id']);

//FETCH PRODUCT DETAILS 
try {
  $sql = "SELECT P.*, c.name as category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE p.id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$product_id]);
  $product = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$product) {
    header("location: home.php");
    exit();
  }
} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}

//SET THE PAGE TITLE
$page_title = htmlspecialchars($product['name']) . " - Thrive";

//CHECK USER IS LOGGED IN
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';

//FIND IMAGE PATH
// $image_name = htmlspecialchars($product['image']);
// $folders = ['men', 'women', 'kids', 'footwear', 'sport', 'socks'];
// $current_image_path = 'images/placeholder.jpg';

// foreach ($folders as $folder) {
//   $try_path = "category/{$folder}/{$image_name}";
//   if (file_exists($try_path)) {
//     $current_image_path = $try_path;
//     break;
//   }
// }

//DETERMINE CORRECT FOLDER BASED ON CATEGORY_ID
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

//GET ALL THE IMAGES FROM DB
$all_images = [];

if (!empty($product['images']) && trim($product['images']) !== '') {
  $all_images = array_map('trim', explode(',', $product['images']));
} else {
  $all_images = !empty($product['image']) ? [trim($product['image'])] : [];
}

//BUILD IMAGE PATHS USING THE CORRECT CATEGORY FOLDER
$image_paths = [];
foreach ($all_images as $img) {
  $img = trim($img);
  $image_path = "category/{$category_folder}/{$img}";

  // Check if file exists, otherwise use placeholder
  if (file_exists($image_path)) {
    $image_paths[] = $image_path;
  } else {
    $image_paths[] = 'images/placeholder.jpg';
  }
}

//SET CURRENT IMAGE/ FIRST ONE IS DEFAULT
$current_image_path = !empty($image_paths[0]) ? $image_paths[0] : 'images/placeholder.jpg';

//IF NO IMAGE FOUND
if (empty($image_paths)) {
  $image_paths = ['images/placeholder.jpg'];
}

//DISCOUNT PERCENTAGE
$discount = 0;
if ($product['original_price'] > 0 && $product['original_price'] > $product['price']) {
  $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}

//FORMAT SIZES
$sizes = !empty($product['size']) ? explode(',', $product['size']) : ['One size'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/productPreviewStyle.css" />
  <script src="script/script.js"></script>
  <title>Thrive</title>
</head>

<body>
  <!-- container -->
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
          <a href="wishlist.php"
            class="wishlist-icon">
            <img src="icons/icons8-heart-96.png" />
          </a>

          <!-- cart -->
          <a href="cart.php"
            class="cart-icon">
            <img src="icons/icons8-cart-96.png" alt="cart" />
          </a>

          <!-- Dynamic login/logout -->
          <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- show logout icon when user is login -->
            <a href="logout.php"
              class="logout-icon">
              <img src="icons/logout.svg" style="width: 25px;" />
            </a>
          <?php else: ?>
            <!-- show login icon when user is logout -->
            <a href="login.php"
              class="login-icon">
              <img src="icons/login3.svg" />
            </a>
          <?php endif; ?>
        </div>
      </header>
      <!-- close header and navigation sec -->

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

    <!-- preview-sec -->
    <div class="product-content">

      <!-- product-images -->
      <div class="product-images">
        <!-- products-container -->
        <div class="main-image-container">
          <div class="main-image">
            <img
              src="<?php echo $current_image_path; ?>"
              alt="<?php echo htmlspecialchars($product['name']); ?>"
              id="main-product-image" />
          </div>
        </div>


        <?php if (!empty($image_paths)): ?>
          <div class="image-gallery">
            <div class="gallery-scroll-container">
              <button class="scroll-btn scroll-left">‹</button>

              <?php foreach ($image_paths as $index => $image_path): ?>

                <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                  <img
                    src="<?php echo $image_path; ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>" />
                </div>

              <?php endforeach; ?>

              <button class="scroll-btn scroll-right">›</button>

            </div>
          </div>
        <?php endif; ?>
      </div>
      <!-- close product-images -->

      <!-- product-detail -->
      <div class="product-details">

        <h2><?php echo htmlspecialchars($product['name']); ?></h2>

        <!-- product-category -->
        <p style="margin-bottom: 10px; color: #666;">
          <strong>Category: <?php echo htmlspecialchars($product['category_name']); ?></strong>
        </p>
        <!-- close product-category -->

        <!-- Price Section -->
        <div class="price-section">
          <span class="current-price">£<?php echo number_format($product['price'], 2); ?></span>

          <?php if ($product['original_price'] > 0): ?>
            <span class="original-price">RRP<?php echo number_format($product['original_price']); ?></span>
          <?php endif; ?>

          <?php if ($discount > 0): ?>
            <span class="discount-badge">Save<?php echo $discount; ?>%</span>
          <?php endif; ?>

        </div>
        <!-- close Price Section -->

        <!-- product description -->
        <div class="product-description">
          <h2>Description</h2>
          <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        <!-- close product description -->

        <!-- Color Selection -->
        <div class="color-selection">
          <h3>Colour: BLACK</h3>
        </div>
        <!-- close Color Selection -->



        <!-- Size Selection -->
        <?php if (!empty($sizes) && $sizes[0] !== 'One size'): ?>
          <div class="size-selection">
            <h3>Size:</h3>
            <div class="size-options">
              <span class="size-btn selected" data-size="S">S</span>
              <span class="size-btn" data-size="M">M</span>
              <span class="size-btn" data-size="L">L</span>
              <span class="size-btn" data-size="XL">XL</span>
            </div>

            <div class="size-help">
              <span>Select A Size</span>
              <a href="#">Size guide</a>
            </div>
          </div>


        <?php endif; ?>
        <!-- close Size Selection -->


        <!-- Action Buttons -->
        <div class="action-buttons">
          <div style="display: flex; gap: 10px; align-items: center;">
            <?php if ($product['stock'] > 0): ?>
              <form method="POST" action="add_to_cart.php" style="flex: 1; ">
                <input type="hidden"
                  name="product_id"
                  value="<?php echo $product['id']; ?>">

                <!-- add selected size to form -->
                <?php if (!empty($sizes) && $sizes[0] !== 'One size'): ?>
                  <input type="hidden" name="selected_size" id="selectedSize" value="S">
                <?php else: ?>
                  <input type="hidden" name="selected_size" value="One Size">
                <?php endif; ?>

                <button type="submit" class="add-to-bag-btn" style="width: 100%;">Add to cart</button>
              </form>
            <?php else: ?>
              <button class="add-to-bag-btn" disabled style="background: #95a5a6; flex: 1;">Out of stock</button>
            <?php endif; ?>

            <form method="POST" action="add_to_wishlist.php" style="display: flex;">
              <input type="hidden"
                name="product_id"
                value="<?php echo $product['id']; ?> ">
              <button type="submit" class="wishlist-btn">♡</button>
            </form>
          </div>
        </div>
        <!-- close Action Buttons -->

        <!-- size - selection btn -->
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const sizebtns = document.querySelectorAll('.size-btn');
            const selectedSizeInput = document.getElementById('selectedSize');

            //only run if there are sizes 
            if (sizebtns.length > 0 && selectedSizeInput) {
              sizebtns.forEach(btn => {
                btn.addEventListener('click', function() {

                  //unselect if selected
                  sizebtns.forEach(btn => btn.classList.remove('selected'));

                  //select if unselected
                  this.classList.add('selected');

                  //update hidden input with selectedSize
                  selectedSizeInput.value = this.getAttribute('data-size');

                });

              });
            }

          });
        </script>
        <!-- size - selection btn -->

        <!-- Product Features -->
        <div class=" product-features">
          <div class="feature">✓ Free Delivery Over £50</div>
          <div class="feature">✓ Free Returns</div>
          <div class="feature">✓ Secure Payment</div>
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
            <img src="images/Social icons/facebook-svgrepo-com.svg" />
            /thrive</a><br />

          <a href="#" class="socialicon">
            <img src="images/Social icons/instagram-167-svgrepo-com.svg" />
            @thrive</a><br />

          <a href="#" class="socialicon">
            <img src="images/Social icons/pinterest-svgrepo-com.svg" />
            @thrivefootball</a>
          <br />

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