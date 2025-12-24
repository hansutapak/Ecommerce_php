<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
// CONNECT TO DATABASE USING PDO
require_once "includes/config.php";

// INITIALIZE VARIBALES
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // GET THE USER DATA
  $username = trim($_POST["username"]);
  $email = trim($_POST["email"]);
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];

  //   ----- VALIDATE USERNAME -----
  if (empty($username)) {
    $username_err = "please enter a username";
  }

  //   ----- VALIDATE EMAIL -----
  if (empty($email)) {
    $email_err = "please enter a email";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email_err = "please enter a valid email address";
  }

  //   ----- VALIDATE PASSWORD -----
  if (empty($password)) {
    $password_err = "please enter a password";
  } elseif (strlen($password) < 6) {
    $password_err = "password must be atleast 6 characters";
  }
  //   ----- VALIDATE CONFIRM PASSWORD -----
  if (empty($confirm_password)) {
    $confirm_password_err = "please enter a confirm_password";
  }

  //   ----- CHECK IF PASSWORDS MATCH -----
  if (empty($password_err) && ($password != $confirm_password)) {
    $confirm_password_err = "password do not match";
  }

  //IF NO ERRORS, INSERT INTO DTABASE
  if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
    try {
      $sql = "INSERT INTO users(username, email, pin, role, created_at) VALUES(:username, :email, :pin, 'user', NOW())";

      $stmt = $pdo->prepare($sql);

      $hashed_password = password_hash($password, PASSWORD_DEFAULT);

      $stmt->bindParam(":username", $username);
      $stmt->bindParam(":email", $email);
      $stmt->bindParam(":pin", $hashed_password);

      if ($stmt->execute()) {
        // Registration successful - redirect to login
        header("location: login.php");
        exit();
      }
    } catch (PDOException $e) {
      $username_err = "Databse error: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Thrive</title>
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/loginStyle.css" />
  <script src="script/script.js"></script>
</head>

<body>
  <div class="container">
    <!-- Your existing header/navigation -->
    <div class="fixed-top-sec">
      <header class="header">
        <button class="mobile-menu-btn">
          <img src="icons/icons8-hamburger-menu-100.png" />
        </button>

        <div class="logo">
          <a href="home.php">
            <img src="icons/for_life-removebg-preview.png" class="logo-icon" />
          </a>
        </div>

        <!-- search bar -->
        <form method="GET" action="search.php" class="search-bar">
          <input
            type="text"
            name="search"
            placeholder="Search products..."
            class="search-input"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
          <button type="submit" class="search-icon">
            <img src="icons/search-icon.svg" alt="search" />
          </button>
        </form>

        <!-- nav - pages -->

        <div class="nav-menu">
          <a href="wishlist.php" class="wishlist-icon">
            <img src="icons/icons8-heart-96.png" />
          </a>
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

      <!-- Your existing mobile menu -->
      <div class="mobile-menu-overlay">
        <div class="mobile-menu-content">
          <div class="menu-header">
            <h3>Categories</h3>
            <button class="close-menu-btn">✕</button>
          </div>
          <div class="mobile-categories">
            <a href="product-categories/menproduct.php" class="men-category">Mens</a>
            <a href="product-categories/womenproduct.php" class="women-category">Womens</a>
            <a href="product-categories/kidproduct.php" class="kids-category">Kids</a>
            <a href="product-categories/footwearproduct.php" class="footwear-category">Footwear</a>
            <a href="product-categories/sportproduct.php" class="jersey-category">Sports</a>
            <a href="product-categories/sockproduct.php" class="socks-category">Socks</a>
            <!-- Dynamic mobile login/logout -->
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
              <a href="logout.php" class="mobile-login">Logout</a>
            <?php else: ?>
              <a href="login.php" class="mobile-login">Account</a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Your existing hero section -->
      <section class="hero">
        <div class="hero-content">
          <b> Cozy & Save Big - 🍁 Autumn Specials!</b>
          <a href="#">Shop now</a>
        </div>
      </section>

      <!-- Your existing categories -->
      <section class="categories">
        <a href="product-categories/menproduct.php" class="men-category">Mens</a>
        <a href="product-categories/womenproduct.php" class="women-category">Womens</a>
        <a href="product-categories/kidproduct.php" class="kids-category">Kids</a>
        <a href="product-categories/footwearproduct.php" class="footwear-category">Footwear</a>
        <a href="product-categories/sportproduct.php" class="jersey-category">Sports</a>
        <a href="product-categories/sockproduct.php" class="socks-category">Socks</a>
      </section>
    </div>

    <!-- REGISTRATION SECTION -->
    <div class="login-section">
      <div class="cards-row">
        <!-- Registration Card -->
        <div class="login-page-card">
          <div class="login-page-header">
            <h1>Create Account</h1>
            <p>Join the Thrive community</p>
          </div>

          <form class="login-page-form" method="POST" action="">
            <!-- Username Field -->
            <div class="login-page-form-group">
              <label for="username">Username</label>
              <input
                type="text"
                id="username"
                name="username"
                placeholder="Choose a username"
                value="<?php echo htmlspecialchars($username); ?>"
                required
                style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 0; font-size: 16px; box-sizing: border-box;" />
              <?php if (!empty($username_err)): ?>
                <span style=" color: red; font-size: 14px;"><?php echo $username_err; ?></span>
              <?php endif; ?>
            </div>

            <!-- Email Field -->
            <div class="login-page-form-group">
              <label for="email">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="<?php echo htmlspecialchars($email); ?>"
                required />
              <?php if (!empty($email_err)): ?>
                <span style="color: red; font-size: 14px;"><?php echo $email_err; ?></span>
              <?php endif; ?>
            </div>

            <!-- Password Field -->
            <div class="login-page-form-group">
              <label for="password">Password</label>
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Create a password (min. 6 characters)"
                required />
              <?php if (!empty($password_err)): ?>
                <span style="color: red; font-size: 14px;"><?php echo $password_err; ?></span>
              <?php endif; ?>
            </div>

            <!-- Confirm Password Field -->
            <div class="login-page-form-group">
              <label for="confirm_password">Confirm Password</label>
              <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirm your password"
                required />
              <?php if (!empty($confirm_password_err)): ?>
                <span style="color: red; font-size: 14px;"><?php echo $confirm_password_err; ?></span>
              <?php endif; ?>
            </div>

            <!-- Register Button -->
            <button type="submit" class="login-page-btn">Create Account</button>
          </form>

          <!-- Login Option -->
          <div class="login-signup-section">
            <p>
              Already have an account?
              <a href="login.php" class="login-signup-link">Sign in</a>
            </p>
          </div>
        </div>

        <!-- Membership Card (same as login page) -->
        <div class="membership-column">
          <div class="membership-login-card">
            <div class="membership-header">
              <img src="icons/for life black.png" class="membership-icon" />
              <h2>Join the Club</h2>
              <p>Become a member today!</p>
            </div>
            <div class="membership-benefits">
              <ul>
                <li>✓ Exclusive discounts</li>
                <li>✓ Free shipping</li>
                <li>✓ Early access to sales</li>
                <li>✓ Member-only events</li>
              </ul>
            </div>
            <button class="membership-btn">Join Now - It's Free!</button>
          </div>

          <!-- Terms and conditions -->
          <div class="login-footer">
            <p>
              This website is secure and your personal details are protected.
              For more information, view our<a href="#">Terms & Conditions</a>
              and <a href="#">our Security, Privacy & Cookie Policy</a>.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Your existing footer -->
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
            /thrive</a><br />
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