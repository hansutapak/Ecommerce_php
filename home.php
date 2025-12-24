<?php
session_start();

//WELCOME MESSAGE
if(isset($_SESSION['welcome_message'])) {
 $welcome_message = $_SESSION['welcome_message'];
   //CLEAR IT SO IT ONLY SHOWS ONCE
   unset($_SESSION['welcome_message']);
} else {
  $welcome_message = "";
}


$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Guest';
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="styles/style.css" />
    <script src="script/script.js"></script>
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
            <img src="icons/icons8-hamburger-menu-100.png" />
          </button>

          <div class="logo">
            <a href="home.php">
              <img
                src="icons/for_life-removebg-preview.png"
                class="logo-icon"
              />
            </a>
          </div>

          <!-- search bar -->
           <form method="GET" action="search.php" class="search-bar">
            <input
              type="text"
              name="search"
              placeholder="Search product"
              class="search-input"
              value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
            />
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
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true):?>
              <!-- show logout icon when user is login -->
                  <a href="logout.php" class="logout-icon">
              <img src="icons/logout.svg" style="width: 25px;"/>
            </a>
            <?php else:?>
            <!-- show login icon when user is logout -->
            <a href="login.php" class="login-icon">
              <img src="icons/login3.svg" />
            </a>
            <?php endif;?>
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
              <a href="product-categories/menproduct.php" class="men-category"
                >Mens</a
              >
              <a
                href="product-categories/womenproduct.php"
                class="women-category"
                >Womens</a
              >
              <a href="product-categories/kidproduct.php" class="kids-category"
                >Kids</a
              >
              <a
                href="product-categories/footwearproduct.php"
                class="footwear-category"
                >Footwear</a
              >
              <a
                href="product-categories/sportproduct.php"
                class="jersey-category"
                >Sports</a
              >
              <a
                href="product-categories/sockproduct.php"
                class="socks-category"
                >Socks</a
              >
                 <!-- Dynamic mobile login/logout -->
               <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true):?>
              <a href="logout.php" class="mobile-login">Logout</a>
               <?php else:?>
              <a href="login.php" class="mobile-login">Account</a>
              <?php endif;?>
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
          <a href="product-categories/menproduct.php" class="men-category"
            >Mens</a
          >
          <a href="product-categories/womenproduct.php" class="women-category"
            >Womens</a
          >
          <a href="product-categories/kidproduct.php" class="kids-category"
            >Kids</a
          >
          <a
            href="product-categories/footwearproduct.php"
            class="footwear-category"
            >Footwear</a
          >
          <a
            href="product-categories/sportproduct.php"
            class="jersey-category"
            >Sports</a
          >
          <a href="product-categories/sockproduct.php" class="socks-category"
            >Socks</a
          >
        </section>
      </div>

      <!-- welcome message -->

      <?php if(!empty($welcome_message)):?>
        <div id="welcomeMessage"  style="background: #4CAF50; color: white; padding: 15px; margin-top: 20px; text-align: center; border-radius: 0px; opacity: 1; transition: opacity 0.8s ease, max-height 0.8s ease, margin 0.8s ease; overflow: hidden; max-height: 200px;">
        <strong>🎉<?php echo $welcome_message;?> </strong>
        </div>

        <!-- Auto-hide welcome message after 5 seconds  -->
        
        <script>
        setTimeout(() => {
    let box = document.getElementById("welcomeMessage");
    if (box) {

        // Start fade + collapse
        box.style.opacity = "0";
        box.style.maxHeight = "0";
        box.style.margin = "0";

        // After animation finishes, remove it from layout
        setTimeout(() => {
            box.remove();
        }, 800);

    }
}, 5000); // fade after 5 seconds
     </script>
       <?php endif;?>

      <!-- Video Section -->
      <section class="sport-video">
        <video width="100%" autoplay muted loop playsinline>
          <source src="Videos/homevideo.mp4" type="video/mp4" />
        </video>
      </section>

      <!-- feature-products -->
      <section class="featured-products">
        <div class="fproduct">Running shoe</div>
        <div class="fproduct">Football boots</div>
        <div class="fproduct">Tennis racket</div>
        <div class="fproduct">Basketball shoe</div>
      </section>

      <!-- company section -->
      <section class="company">
        <div class="cproduct">
          <img src="images/company/brand-nike-svgrepo-com.svg" />
        </div>
        <div class="cproduct">
          <img src="images/company/adidas-svgrepo-com.svg" />
        </div>
        <div class="cproduct">
          <img src="images/company/puma-logo-logo-svgrepo-com.svg" />
        </div>
        <div class="cproduct">
          <img src="images/company/the-north-face-1-logo-svgrepo-com.svg" />
        </div>
        <div class="cproduct">
          <img src="images/company/under-armour-logo-svgrepo-com.svg" />
        </div>
      </section>

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
              /thrive</a
            ><br />

            <a href="#" class="socialicon">
              <img src="images/Social icons/instagram-167-svgrepo-com.svg" />
              @thrive</a
            ><br />

            <a href="#" class="socialicon">
              <img src="images/Social icons/pinterest-svgrepo-com.svg" />
              @thrivefootball</a
            ><br />

            <a href="#" class="socialicon">
              <img src="images/Social icons/tiktok-svgrepo-com.svg" />
              @thrivefootball</a
            ><br />

            <a href="#" class="socialicon">
              <img src="images/Social icons/youtube-168-svgrepo-com.svg" />
              thrive</a
            >
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
              &copy; 2025 Thrive.com Retail Ltd.<a href="#">Privacy Policy</a
              ><a href="#">Sitemap</a>
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



      
