<?php
session_start();
require('config/dbcon.php');

$cart = $_SESSION['cart'] ?? [];
$selected_indexes = isset($_GET['items']) ? array_map('intval', (array)$_GET['items']) : [];

$selected_items = [];
foreach ($selected_indexes as $idx) {
    if (isset($cart[$idx])) $selected_items[$idx] = $cart[$idx];
}

if (empty($selected_items) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php"); exit();
}

if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['redirect_after_login'] = 'checkout.php?' . http_build_query(['items' => $selected_indexes]);
    header("Location: login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = 'checkout.php?' . http_build_query(['items' => array_keys($selected_items)]);
        header("Location: login.php"); exit();
    }

    $post_indexes = isset($_POST['selected_indexes'])
        ? array_map('intval', explode(',', $_POST['selected_indexes'])) : [];

    foreach ($post_indexes as $idx) {
        $item = $_SESSION['cart'][$idx] ?? null;
        if ($item) {
            $id  = (int)$item['id'];
            $qty = (int)$item['quantity'];
            mysqli_query($conn, "UPDATE items SET quantity = quantity - $qty, sold = sold + $qty WHERE id = $id AND quantity >= $qty");
        }
    }

    foreach ($post_indexes as $idx) unset($_SESSION['cart'][$idx]);
    $_SESSION['cart']    = array_values($_SESSION['cart']);
    $_SESSION['message'] = "Order placed successfully! Thank you for your purchase.";
    header("Location: index.php"); exit();
}

$grand_total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $selected_items));
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Gaming Zone PH</title>
    <script>(function(){var t=localStorage.getItem('gz_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/checkout.css">
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

        <span class="text-white-50 small d-none d-sm-inline d-flex align-items-center gap-1">
            <img src="assets/icons/user.svg" class="icon-sm icon-white" alt="">
            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </span>

        <a href="logout.php" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <img src="assets/icons/log-out.svg" class="icon-sm icon-white" alt="">
            Log Out
        </a>

        <a href="cart.php" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <img src="assets/icons/arrow-left.svg" class="icon-sm icon-white" alt="">
            Cart
        </a>
    </div>
</nav>

<div class="container py-4" style="max-width: 820px;">

    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2" style="font-family:'Rajdhani',sans-serif; font-size:24px;">
        <img src="assets/icons/shopping-bag.svg" class="icon" style="width:26px;height:26px;" alt="">
        Checkout
    </h5>

    <form method="post" id="checkoutForm">
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="selected_indexes" value="<?php echo implode(',', array_keys($selected_items)); ?>">
        <input type="hidden" name="shipping_fee" id="shippingFeeInput" value="0">
        <input type="hidden" name="payment_method" id="paymentMethodInput" value="">

        <div class="row g-4">

            <div class="col-lg-7">

                <div class="card border shadow-sm mb-4">
                    <div class="card-body p-4">

                        <div class="section-title">
                            <img src="assets/icons/map-pin.svg" class="icon" alt="">
                            Delivery Address
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Full Name *</label>
                            <input type="text" name="full_name" class="form-control"
                                   placeholder="e.g. Juan dela Cruz" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Phone Number *</label>
                            <input type="text" name="phone" class="form-control"
                                   placeholder="e.g. 09xxxxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Street / Barangay / City / Province / Postal Code *</label>
                            <input type="text" name="address" class="form-control"
                                   placeholder="e.g. 123, Pinaod, San Ildefonso, Bulacan, Philippines, 3010" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Delivery Area *</label>
                            <select name="island_group" id="islandGroup" class="form-select" required onchange="updateShipping()">
                                <option value="" disabled selected>Select your delivery area</option>
                                <option value="luzon">Luzon</option>
                                <option value="visayas">Visayas</option>
                                <option value="mindanao">Mindanao</option>
                            </select>
                        </div>

                        <div id="shippingNotice" class="small mt-2 d-flex align-items-center gap-1" style="display:none !important;"></div>

                    </div>
                </div>

                <div class="card border shadow-sm mb-4">
                    <div class="card-body p-4">

                        <div class="section-title">
                            <img src="assets/icons/credit-card.svg" class="icon" alt="">
                            Payment Method
                        </div>

                        <div class="d-flex flex-column gap-2">

                            <div>
                                <input type="radio" name="payment" id="pay_gcash" value="GCash"
                                       class="payment-option" onchange="selectPayment('GCash')">
                                <label for="pay_gcash" class="payment-label w-100">
                                    <div class="payment-icon">
                                        <img src="assets/images/gcash_logo.png" class="logo-img" alt="GCash">
                                    </div>
                                    <div>
                                        <div>GCash</div>
                                        <div class="text-muted fw-normal" style="font-size:12px;">Send to 09XX-XXX-XXXX</div>
                                    </div>
                                </label>
                            </div>

                            <div>
                                <input type="radio" name="payment" id="pay_maya" value="Maya"
                                       class="payment-option" onchange="selectPayment('Maya')">
                                <label for="pay_maya" class="payment-label w-100">
                                    <div class="payment-icon">
                                        <img src="assets/images/maya_logo.jfif" class="logo-img" alt="Maya">
                                    </div>
                                    <div>
                                        <div>Maya</div>
                                        <div class="text-muted fw-normal" style="font-size:12px;">Send to 09XX-XXX-XXXX</div>
                                    </div>
                                </label>
                            </div>

                            <div>
                                <input type="radio" name="payment" id="pay_bank" value="Bank Transfer"
                                    class="payment-option" onchange="selectPayment('Bank Transfer')">
                                <label for="pay_bank" class="payment-label w-100">
                                    <div class="payment-icon">
                                        <img src="assets/images/money-transfer.png" class="logo-img" alt="Bank Transfer">
                                    </div>
                                    <div>
                                        <div>Bank Transfer</div>
                                        <div class="text-muted fw-normal" style="font-size:12px;">BDO / BPI — Acct # XXXX-XXXX</div>
                                    </div>
                                </label>
                            </div>

                            <div>
                                <input type="radio" name="payment" id="pay_cod" value="Cash on Delivery"
                                    class="payment-option" onchange="selectPayment('Cash on Delivery')">
                                <label for="pay_cod" class="payment-label w-100">
                                    <div class="payment-icon">
                                        <img src="assets/images/cash-on-delivery.png" class="logo-img" alt="Cash on Delivery">
                                    </div>
                                    <div>
                                        <div>Cash on Delivery</div>
                                        <div class="text-muted fw-normal" style="font-size:12px;">Pay when your order arrives</div>
                                    </div>
                                </label>
                            </div>

                        </div>

                        <div id="paymentError" class="text-danger small mt-2 d-flex align-items-center gap-1" style="display:none !important;">
                            <img src="assets/icons/alert-circle.svg" class="icon-sm" style="filter: brightness(0) saturate(100%) invert(27%) sepia(90%) saturate(500%) hue-rotate(330deg);" alt="">
                            Please select a payment method.
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-lg-5">
                <div class="card border shadow-sm">
                    <div class="card-body p-4">

                        <div class="section-title">
                            <img src="assets/icons/package.svg" class="icon" alt="">
                            Order Summary
                        </div>

                        <?php foreach ($selected_items as $item): ?>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <?php if (!empty($item['image'])): ?>
                                <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" class="item-img" alt="">
                            <?php else: ?>
                                <div class="no-img">No img</div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size:13px; line-height:1.3;">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>
                                <div class="text-muted" style="font-size:12px;">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="price-col" style="font-size:13px; white-space:nowrap;">
                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <hr>

                        <div class="summary-row">
                            <span class="text-muted">Subtotal</span>
                            <span>₱<?php echo number_format($grand_total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="text-muted">Shipping Fee</span>
                            <span id="shippingDisplay" class="shipping-free">FREE</span>
                        </div>
                        <div class="summary-row grand">
                            <span>Total</span>
                            <span class="price-col" id="grandTotalDisplay">
                                ₱<?php echo number_format($grand_total, 2); ?>
                            </span>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success btn-place-order w-100 d-flex align-items-center justify-content-center gap-2">
                                <img src="assets/icons/check-circle.svg" class="icon-sm icon-white" alt="">
                                Place Order
                            </button>
                        </div>

                        <p class="text-muted small text-center mt-2 mb-0 d-flex align-items-center justify-content-center gap-1">
                            <img src="assets/icons/shield.svg" class="icon-muted" style="width:13px;height:13px;" alt="">
                            Your order is securely processed
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

<footer class="border-top py-3 text-center mt-4">
    <small class="text-muted">Gaming Zone PH &copy; 2026</small><br>
    <small><strong>Developed by: Manabat, Aljon P.</strong></small>
</footer>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    const baseTotal   = <?php echo $grand_total; ?>;
    let   shippingFee = 0;
    let   selectedPay = '';

    function updateShipping() {
        const area    = document.getElementById('islandGroup').value;
        const notice  = document.getElementById('shippingNotice');
        const display = document.getElementById('shippingDisplay');
        const input   = document.getElementById('shippingFeeInput');

        if (area === 'luzon') {
            shippingFee = 0;
            notice.innerHTML  = '<img src="assets/icons/check-circle.svg" class="icon-sm" style="filter:brightness(0) saturate(100%) invert(35%) sepia(90%) saturate(400%) hue-rotate(100deg);" alt=""> <span class="shipping-free">FREE shipping</span> for Luzon deliveries!';
            display.textContent = 'FREE';
            display.className   = 'shipping-free';
        } else {
            shippingFee = 60;
            const label = area.charAt(0).toUpperCase() + area.slice(1);
            notice.innerHTML  = '<img src="assets/icons/truck.svg" class="icon-sm icon-muted" alt=""> <span class="shipping-paid">₱60.00 shipping fee</span> for ' + label + ' deliveries.';
            display.textContent = '₱60.00';
            display.className   = 'shipping-paid';
        }

        notice.style.display = 'flex';
        input.value = shippingFee;
        updateGrandTotal();
    }

    function updateGrandTotal() {
        const total = baseTotal + shippingFee;
        document.getElementById('grandTotalDisplay').textContent =
            '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function selectPayment(method) {
        selectedPay = method;
        document.getElementById('paymentMethodInput').value = method;
        document.getElementById('paymentError').style.display = 'none';
    }

    document.getElementById('checkoutForm').addEventListener('submit', function (e) {
        if (!selectedPay) {
            e.preventDefault();
            const err = document.getElementById('paymentError');
            err.style.display = 'flex';
            err.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        if (!document.getElementById('islandGroup').value) {
            e.preventDefault();
            document.getElementById('islandGroup').focus();
        }
    });

    (function () {
        var themeBtn = document.getElementById('themeToggle');
        var iconMoon = document.getElementById('iconMoon');
        var iconSun  = document.getElementById('iconSun');
        var KEY = 'gz_theme';

        function applyTheme(t) {
            document.documentElement.setAttribute('data-bs-theme', t);
            localStorage.setItem(KEY, t);
            if (t === 'dark') {
                iconMoon.style.display = 'none';
                iconSun.style.display  = 'inline-block';
            } else {
                iconMoon.style.display = 'inline-block';
                iconSun.style.display  = 'none';
            }
        }

        applyTheme(localStorage.getItem(KEY) || 'light');

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
            });
        }
    })();
</script>
</body>
</html>