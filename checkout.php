<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/config.php';

// REDIRECT TO LOGIN PAGE
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];
$user_name = $_SESSION['username'];

// GET CART ITEMS
$cart_items = [];
$total_amount = 0;
$item_count = 0;
$error = '';

try {
    $sql = "SELECT c.*, p.name, p.price, p.image, p.stock
         FROM cart c
         JOIN products p ON c.product_id = p.id
         WHERE c.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //CALCULATE TOTAL AND CHECK STOCK
    foreach ($cart_items as $item) {
        $item_count = $item_count + $item['quantity'];
        $total_amount = $total_amount + $item['price'] * $item['quantity'];

        //CHECK ENOUGH STOCK
        if ($item['quantity'] > $item['stock']) {
            $error = "Sorry,'{$item['name']}' only has {$item['stock']} items in the stock.";
            break;
        }
    }
} catch (PDOException $e) {
    $error = "Could not load cart items" . $e->getMessage();
}


//HANDLE CHEKOUT FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($cart_items) && empty($error)) {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $billing_address = trim($_POST['billing_address'] ?? $shipping_address);

    if (empty($shipping_address)) {
        $error = "Please enter your shipping address";
    } else {
        try {
            // START TRANSACTION
            $pdo->beginTransaction();

            // CREATE ORDER
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, billing_address) VALUES(?,?,?,?)");
            $stmt->execute([$user_id, $total_amount, $shipping_address, $billing_address]);
            $order_id = $pdo->lastInsertId();

            //ADD ORDER ITEMS AND UPDATE STOCK
            foreach ($cart_items as $item) {

                //ADD TO ORDER ITEMS
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES(?,?,?,?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);

                //UPDATE PRODUCT STOCK
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            //CLEAR CART
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $pdo->commit();

            //REDIRECT TO ORDER CONFIRMATION
            header("location: order_confirmation.php?order_id=" . $order_id);
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Checkout failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Thrive</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 400px 350px;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .checkout-form h2 {
            margin-bottom: 40px;
        }

        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
        }

        .order-summary h3 {
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .btn-place-order {
            width: 100%;
            padding: 15px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 0;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-place-order:hover {
            background: #27ae60;
        }

        .error-message {
            background: #ffeaea;
            color: #e74c3c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success-message {
            background: #e8f6f3;
            color: #27ae60;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .order-total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
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



        <!-- checkout -->
        <div class="checkout-container">
            <div class="checkout-form">
                <h2>Checkout</h2>

                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (empty($cart_items)): ?>
                    <div class="error-message">Your cart is empty. <a href="home.php">Continue shopping</a></div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="shipping_address">Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="4" required placeholder="Enter your full shipping address"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="billing_address">Billing Address (if different)</label>
                            <textarea id="billing_address" name="billing_address" rows="4" placeholder="Enter billing address (leave blank if same as shipping)"></textarea>
                        </div>

                        <button type="submit" class="btn-place-order">Place Order</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>

                <?php if (!empty($cart_items)): ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                            <span>£<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="order-total">
                        <span>Total</span>
                        <span>£<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                <?php else: ?>
                    <p>No items in cart</p>
                <?php endif; ?>
            </div>
        </div><!-- Close checkout -->

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