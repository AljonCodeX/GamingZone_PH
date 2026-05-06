<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart — Gaming Zone PH</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/cart.css">
    <script>
        const _t = localStorage.getItem('gz_theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', _t);
    </script>
</head>
<body>

<nav class="navbar navbar-gz navbar-dark px-3 px-md-4 sticky-top">
    <a href="index.php" class="brand-wrap">
        <img src="assets/images/logo.png" alt="Logo" class="brand-logo">
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
    </div>
</nav>

<div class="container py-4" style="max-width: 900px;">

    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2" style="font-family:'Rajdhani',sans-serif; font-size:22px;">
        <img src="assets/icons/shopping-cart.svg" class="icon" style="width:24px;height:24px;" alt="">
        My Cart
    </h5>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="card border text-center py-5">
            <div class="card-body">
                <img src="assets/icons/shopping-cart.svg" class="icon mb-3" style="width:48px;height:48px;opacity:0.3;" alt="">
                <p class="text-muted mb-3">Your cart is empty.</p>
                <a href="index.php" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <img src="assets/icons/arrow-left.svg" class="icon-sm icon-white" alt="">
                    Browse Products
                </a>
            </div>
        </div>

    <?php else: ?>

        <form method="get" action="checkout.php" id="cartForm">

            <div class="card border shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                        <label class="form-check-label fw-semibold" for="selectAll">Select All</label>
                    </div>
                    <small class="text-muted"><?php echo count($_SESSION['cart']); ?> item(s)</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:36px;"></th>
                                <th style="width:60px;">Image</th>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <tr id="row-<?php echo $index; ?>">
                                <td>
                                    <input type="checkbox" name="items[]"
                                           value="<?php echo $index; ?>"
                                           class="form-check-input item-check"
                                           data-index="<?php echo $index; ?>"
                                           data-unit-price="<?php echo $item['price']; ?>"
                                           data-price="<?php echo $item['price'] * $item['quantity']; ?>">
                                </td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" class="item-img" alt="">
                                    <?php else: ?>
                                        <div class="no-img bg-secondary-subtle text-muted">No img</div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="price-col">₱<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <div class="input-group input-group-sm" style="width:110px;">
                                        <button type="button" class="btn btn-outline-secondary qty-btn"
                                                data-index="<?php echo $index; ?>"
                                                data-action="minus">−</button>
                                        <input type="number"
                                               class="form-control text-center qty-field"
                                               value="<?php echo $item['quantity']; ?>"
                                               min="1"
                                               data-index="<?php echo $index; ?>"
                                               data-price="<?php echo $item['price']; ?>"
                                               readonly
                                               style="font-size:13px;">
                                        <button type="button" class="btn btn-outline-secondary qty-btn"
                                                data-index="<?php echo $index; ?>"
                                                data-action="plus">+</button>
                                    </div>
                                </td>
                                <td class="fw-semibold subtotal-col" id="subtotal-<?php echo $index; ?>">
                                    ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </td>
                                <td>
                                    <a href="remove_from_cart.php?index=<?php echo $index; ?>"
                                       class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                        <img src="assets/icons/trash-2.svg" class="icon-sm" style="filter: brightness(0) saturate(100%) invert(27%) sepia(90%) saturate(500%) hue-rotate(330deg);" alt="">
                                        Remove
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
                    <div>
                        <span class="text-muted small"><span id="selectedCount">0</span> item(s) selected</span>
                        <span class="ms-2 fw-bold" style="color: var(--price-color); font-size:17px;">
                            Total: ₱<span id="selectedTotal">0.00</span>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold px-4 d-flex align-items-center gap-2" id="checkoutBtn" disabled>
                        <img src="assets/icons/shopping-cart.svg" class="icon-sm icon-white" alt="">
                        Proceed to Checkout
                    </button>
                </div>
            </div>

        </form>

        <p class="text-muted small text-center mt-2">Check the items you want to order, then click "Proceed to Checkout".</p>

    <?php endif; ?>
</div>

<footer class="border-top py-3 text-center mt-4">
    <small class="text-muted">Gaming Zone PH &copy; 2026</small><br>
    <small><strong>Developed by: Manabat, Aljon P.</strong></small>
</footer> 

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="scripts/cart.js"></script>
</body>
</html>