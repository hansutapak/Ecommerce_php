<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: home.php");
    exit();
}

$order_id = $_GET['order_id'] ?? 0;
$order = null;
$order_items = [];

if ($order_id) {

    try {
        $stmt = $pdo->prepare("SELECT o.*, u.username, u.email
                               FROM orders o
                               JOIN users u ON o.user_id = u.id
                               WHERE o.id = ? AND o.user_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();

        if ($order) {
            //GET ORDER ITEMS
            $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image
                                   FROM order_items oi
                                   JOIN products p ON oi.product_id = p.id
                                   WHERE oi.order_id = ?");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error = "could not load order details" . $e->getMessage();
    }
}

if (!$order) {
    header("location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Order Confirmation - Thrive</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .confirmation-icon img {
            width: 50px;
        }

        .order-details {
            text-align: left;
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .btn-continue {
            display: inline-block;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 0;
            margin-top: 20px;
        }
    </style>
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

        <!-- confirmation -->
        <div class="confirmation-container">
            <div class="confirmation-icon"><img src="icons/confirmation_tick.svg"></div>
            <h2>Order Confirmed!</h2>
            <p>Thank you for your order. Your order number is: <strong>#<?php echo $order['id']; ?></strong></p>
            <p>A confirmation email has been sent to <?php echo htmlspecialchars($order['email']); ?></p>

            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                <p><strong>Total Amount:</strong> £<?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>

                <h4>Items Ordered:</h4>
                <?php foreach ($order_items as $item): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span>£<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="home.php" class="btn-continue">Continue Shopping</a>
            <a href="order_history.php" class="btn-continue" style="background: #95a5a6; margin-left: 10px;">View Order History</a>
        </div> <!-- close confirmation-->

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
        </footer><!-- close footer -->
    </div>
</body>

</html>