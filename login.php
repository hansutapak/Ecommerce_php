<?php
session_start();

//REDIRECT TO HOME PAGE IF LOGGEDIN
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  header("Location: home.php");
  exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once 'includes/config.php';

// INITIALIZE VARIBALES
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";
$login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // GET THE USER DATA
  $email = trim($_POST["email"]);
  $password = $_POST["password"];


  //   ----- VALIDATE EMAIL -----
  if (empty($email)) {
    $email_err = "please enter a email";
  }

  //   ----- VALIDATE PASSWORD -----
  if (empty($password)) {
    $password_err = "please enter a password";
  }

  if (empty($email_err) && empty($password_err)) {
    try {
      // PPREPARE SQL TO GEIT USER BY EMAIL
      $sql = "SELECT id, username, email, pin, role FROM users WHERE email = :email";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(":email", $email);
      $stmt->execute();


      // CHECK IF THE USER EXIST
      if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // VERIFY PASSWORD(HASED_PASSWORD)
        if (password_verify($password, $user['pin'])) {
          // Password is correct - start session

          $_SESSION["user_id"] = $user['id'];
          $_SESSION["username"] = $user['username'];
          $_SESSION["email"] = $user['email'];
          $_SESSION["role"] = $user['role'];
          $_SESSION["loggedin"] = true;

          // SET WELCOME MESSAGE IN THE SESSION
          $_SESSION["welcome_message"] = "Welcome back, " . $user['username'] . "!";

          // REDIRECT TO LOGIN PAGE
          header("location: home.php");
          exit();
        } else {
          // PASSWORD IS INVALID
          $login_err = "Invalid email or password";
        }
      } else {
        // EMAIL IS INVALID
        $login_err = "Invalid email or password";
      }
    } catch (PDOException $e) {
      $login_err = "Somthing went wrong";
    }
  }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Thrive</title>
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/loginStyle.css" />
  <script src="script/script.js"></script>
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
            placeholder="Search products..."
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
        <a href="product-categories/footwearproduct.php" class="footwear-category">Footwear</a>
        <a href="product-categories/sportproduct.php" class="jersey-category">Sports</a>
        <a href="product-categories/sockproduct.php" class="socks-category">Socks</a>
      </section>
    </div>

    <!-- login section -->

    <div class="login-section">
      <!-- login card -->

      <div class="cards-row">
        <div class="login-page-card">
          <div class="login-page-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
          </div>

          <?php if (!empty($login_err)): ?>
            <div>
              <?php echo $login_err; ?>
            </div>
          <?php endif; ?>


          <form class="login-page-form" method="POST" action="">
            <!-- login form -->
            <div class="login-page-form-group">
              <label for="email">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="<?php echo htmlspecialchars($email); ?> "
                required />

              <?php if (!empty($email_err)): ?>
                <?php echo $email_err ?>
              <?php endif; ?>
            </div>

            <div class="login-page-form-group">
              <label for="password">Password</label>
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required />

              <?php if (!empty($password_err)): ?>
                <?php echo $password_err ?>
              <?php endif; ?>
            </div>

            <!-- login form options -->

            <div class="login-page-form-options">
              <div class="login-remember-me">
                <input type="checkbox" id="remember" />
                <label for="remember">Remember me</label>
              </div>
              <a href="#" class="login-forgot-link">Forgot password?</a>
            </div>

            <!-- sign in btn -->

            <button type="submit" class="login-page-btn">Sign In</button>
          </form>

          <!-- sign up option -->

          <div class="login-signup-section">
            <p>
              Don't have an account?
              <a href="register.php" class="login-signup-link">Sign up</a>
            </p>
          </div>
        </div>

        <!-- membership-card -->
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

          <!-- terms and condition -->
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