<?php
session_start();
require('config/dbcon.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$result = mysqli_query($conn, "SELECT * FROM items WHERE id='$id'");
$item   = mysqli_fetch_array($result);
if (!$item) { header("Location: index.php"); exit(); }

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$qty        = (int)$item['quantity'];
$sold       = (int)($item['sold'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['name']); ?> — Gaming Zone PH</title>
    <script>(function(){var t=localStorage.getItem('gz_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/item.css">
</head>
<body>

<nav class="navbar navbar-gz navbar-dark px-3 px-md-4 sticky-top">
    <a href="index.php" class="brand-wrap">
        <img src="assets/images/logo.png" alt="Gaming Zone PH Logo" class="brand-logo">
        <div class="brand-name">
            Gaming Zone <span>PH</span>
            <small class="brand-sub">Your Gaming Gear Store</small>
        </div>
    </a>
    <div class="d-flex align-items-center gap-2">

        <button class="btn btn-outline-secondary btn-theme" id="themeToggle" title="Toggle theme">
            <img src="assets/icons/moon.svg" class="icon-sm icon-white" id="iconMoon" alt="Dark mode">
            <img src="assets/icons/sun.svg"  class="icon-sm icon-white" id="iconSun"  alt="Light mode" style="display:none;">
        </button>

        <a href="index.php" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <img src="assets/icons/arrow-left.svg" class="icon-sm icon-white" alt="">
            Shop
        </a>

        <a href="cart.php" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <img src="assets/icons/shopping-cart.svg" class="icon-sm icon-white" alt="Cart">
            Cart<?php if ($cart_count > 0) echo " ($cart_count)"; ?>
        </a>

    </div>
</nav>

<div class="container mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="index.php" class="text-primary text-decoration-none">Shop</a>
            </li>
            <li class="breadcrumb-item active text-muted">
                <?php echo htmlspecialchars($item['name']); ?>
            </li>
        </ol>
    </nav>
</div>

<div class="container mb-5">
    <div class="card border shadow-sm overflow-hidden">
        <div class="row g-0">

            <div class="col-md-5">
                <?php if ($item['image']): ?>
                    <div class="product-img-wrap">
                        <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>"
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                <?php else: ?>
                    <div class="no-img-box">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        No Image Available
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-7">
                <div class="card-body p-4 d-flex flex-column gap-3">

                    <div>
                        <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="price-display">₱<?php echo number_format($item['price'], 2); ?></div>
                        <div class="sold-count">
                            <img src="assets/icons/flame.svg" class="icon" alt="">
                            <span><?php echo number_format($sold); ?></span> sold
                        </div>
                    </div>

                    <?php if ($qty <= 0): ?>
                        <span class="badge bg-danger fs-6 w-auto align-self-start px-3 py-2 d-flex align-items-center gap-1">
                            <img src="assets/icons/x-circle.svg" class="icon-sm" alt=""> Out of Stock
                        </span>
                    <?php elseif ($qty <= 5): ?>
                        <span class="badge bg-warning text-dark fs-6 w-auto align-self-start px-3 py-2 d-flex align-items-center gap-1">
                            <img src="assets/icons/alert-triangle.svg" class="icon-sm" alt=""> Low Stock — <?php echo $qty; ?> left
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success fs-6 w-auto align-self-start px-3 py-2 d-flex align-items-center gap-1">
                            <img src="assets/icons/check-circle.svg" class="icon-sm icon-white" alt=""> In Stock (<?php echo $qty; ?>)
                        </span>
                    <?php endif; ?> 

                    <hr class="my-0">

                    <?php if ($item['description']): ?>
                    <div>
                        <p class="text-uppercase fw-bold small text-muted mb-1" style="letter-spacing:1px;">Description</p>
                        <p class="text-secondary mb-0" style="line-height:1.7; font-size:14px;">
                            <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                        </p>
                    </div>
                    <hr class="my-0">
                    <?php endif; ?>

                    <?php if ($qty > 0): ?>

                        <label class="text-uppercase fw-bold small text-muted mb-0 d-block" style="letter-spacing:1px;">Quantity</label>

                        <div class="input-group qty-controls">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)">−</button>
                            <input type="number" id="qtyInput" class="form-control text-center"
                                   value="1" min="1" max="<?php echo $qty; ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)">+</button>
                        </div>

                        <form method="post" action="config/add_to_cart.php">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="quantity" id="addCartQty" value="1">
                            <button type="submit" class="btn btn-primary btn-action w-100 d-flex align-items-center justify-content-center gap-2">
                                <img src="assets/icons/shopping-cart.svg" class="icon-md icon-white" alt="">
                                Add to Cart
                            </button>
                        </form>

                        <form method="post" action="config/add_to_cart.php">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="quantity" id="buyNowQty" value="1">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="btn btn-warning btn-action w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                                <img src="assets/icons/zap.svg" class="icon-md icon-white" alt="">
                                Buy Now
                            </button>
                        </form>

                    <?php else: ?>
                        <button class="btn btn-secondary btn-action w-100" disabled>Out of Stock</button>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<footer class="border-top py-3 text-center mt-4">
    <small class="text-muted">Gaming Zone PH &copy; 2026</small><br>
    <small><strong>Developed by: Manabat, Aljon P.</strong></small>
</footer> 

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/scripts/item.js"></script>
</body>
</html>