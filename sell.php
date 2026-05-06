<?php
session_start();
require('config/dbcon.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'sell.php';
    header("Location: login.php");
    exit();
}

$conn->query("CREATE TABLE IF NOT EXISTS pending_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    image VARCHAR(255) DEFAULT '',
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    reject_reason VARCHAR(255) DEFAULT '',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price'];
    $quantity    = (int)$_POST['quantity'];
    $user_id     = (int)$_SESSION['user_id'];
    $username    = $_SESSION['user_name'];
    $image       = '';

    if (!$name || $price <= 0 || $quantity < 1) {
        $error = "Please fill in all required fields with valid values.";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = __DIR__ . "/assets/images/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed) && getimagesize($_FILES['image']['tmp_name'])) {
                $filename = uniqid('pending_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename);
                $image = $filename;
            } else {
                $error = "Invalid image. Only JPG, PNG, GIF, or WEBP allowed.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO pending_items (user_id, username, name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssdis", $user_id, $username, $name, $description, $price, $quantity, $image);
            $stmt->execute();
            $stmt->close();
            $success = true;
        }
    }
}

$my_stmt = $conn->prepare("SELECT * FROM pending_items WHERE user_id = ? ORDER BY submitted_at DESC");
$my_stmt->bind_param("i", $_SESSION['user_id']);
$my_stmt->execute();
$my_listings = $my_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$my_stmt->close();

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell a Product — Gaming Zone PH</title>
    <script>(function(){var t=localStorage.getItem('gz_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/sell.css">
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

        <a href="index.php" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <img src="assets/icons/arrow-left.svg" class="icon-sm icon-white" alt="">
            Shop
        </a>

        <a href="cart.php" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <img src="assets/icons/shopping-cart.svg" class="icon-sm icon-white" alt="">
            Cart<?php if ($cart_count > 0) echo " <span class='badge bg-light text-dark ms-1'>$cart_count</span>"; ?>
        </a>

    </div>
</nav>

<div class="container py-4" style="max-width: 820px;">

    <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="font-family:'Rajdhani',sans-serif; font-size:22px;">
        <img src="assets/icons/tag.svg" class="icon" style="width:22px;height:22px;" alt="">
        Sell a Product
    </h5>
    <p class="text-muted small mb-4">Submit your product listing. An admin will review and approve it before it goes live in the shop.</p>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2" role="alert">
            <img src="assets/icons/check-circle.svg" class="icon-sm" style="filter: brightness(0) saturate(100%) invert(35%) sepia(90%) saturate(400%) hue-rotate(100deg); flex-shrink:0;" alt="">
            <span>Your product has been submitted! It will appear in the shop once an admin approves it.</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2" role="alert">
            <img src="assets/icons/alert-circle.svg" class="icon-sm" style="filter: brightness(0) saturate(100%) invert(27%) sepia(90%) saturate(500%) hue-rotate(330deg); flex-shrink:0;" alt="">
            <span><?php echo htmlspecialchars($error); ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border shadow-sm mb-4">
        <div class="card-header fw-bold d-flex align-items-center gap-2">
            <img src="assets/icons/package.svg" class="icon-sm" alt="">
            Product Details
        </div>
        <div class="card-body p-4">

            <div class="alert alert-warning py-2 small d-flex align-items-center gap-2 mb-3">
                <img src="assets/icons/clock.svg" class="icon-sm" style="filter: brightness(0) saturate(100%) invert(60%) sepia(50%) saturate(500%) hue-rotate(10deg); flex-shrink:0;" alt="">
                Your listing will be reviewed by an admin before it appears publicly. Track the status in the table below.
            </div>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control"
                           placeholder="e.g. PS5 DualSense Controller" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Describe your product — condition, brand, specs, etc."></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Price (₱) *</label>
                        <input type="number" name="price" class="form-control"
                               step="0.01" min="1" placeholder="0.00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" class="form-control"
                               min="1" value="1" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image" class="form-control"
                           accept="image/*" id="imgInput">
                    <img src="" class="preview-img" id="imgPreview" alt="Preview">
                </div>
                <button type="submit" class="btn btn-success fw-bold px-4 d-flex align-items-center gap-2">
                    <img src="assets/icons/send.svg" class="icon-sm icon-white" alt="">
                    Submit for Review
                </button>
            </form>
        </div>
    </div>

    <h6 class="fw-bold mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="font-family:'Rajdhani',sans-serif; font-size:18px;">
        <img src="assets/icons/list.svg" class="icon" alt="">
        My Submissions
    </h6>

    <div class="card border shadow-sm">
        <?php if (empty($my_listings)): ?>
            <div class="text-center text-muted py-5 small">
                <img src="assets/icons/inbox.svg" class="icon mb-2" style="width:36px;height:36px;opacity:0.3;" alt="">
                <p class="mb-0">You haven't submitted any products yet.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:52px;">Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_listings as $listing): ?>
                    <tr>
                        <td>
                            <?php if ($listing['image']): ?>
                                <img src="assets/images/<?php echo htmlspecialchars($listing['image']); ?>"
                                     class="item-img" alt="">
                            <?php else: ?>
                                <div class="no-img bg-secondary-subtle text-muted">No img</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($listing['name']); ?></div>
                            <?php if ($listing['description']): ?>
                                <div class="text-muted small text-truncate" style="max-width:200px;">
                                    <?php echo htmlspecialchars($listing['description']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="price-col">₱<?php echo number_format($listing['price'], 2); ?></td>
                        <td><?php echo $listing['quantity']; ?></td>
                        <td>
                            <?php if ($listing['status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                                    <img src="assets/icons/clock.svg" class="icon-sm" style="filter:brightness(0);" alt="">
                                    Pending
                                </span>
                            <?php elseif ($listing['status'] === 'approved'): ?>
                                <span class="badge bg-success d-inline-flex align-items-center gap-1">
                                    <img src="assets/icons/check-circle.svg" class="icon-sm icon-white" alt="">
                                    Approved
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                    <img src="assets/icons/x-circle.svg" class="icon-sm icon-white" alt="">
                                    Rejected
                                </span>
                                <?php if ($listing['reject_reason']): ?>
                                    <div class="small text-danger mt-1">
                                        Reason: <?php echo htmlspecialchars($listing['reject_reason']); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <?php echo date('M d, Y', strtotime($listing['submitted_at'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<footer class="border-top py-3 text-center mt-4">
    <small class="text-muted">Gaming Zone PH &copy; 2026</small><br>
    <small><strong>Developed by: Manabat, Aljon P.</strong></small>
</footer>

<script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/scripts/sell.js"></script>
</body>
</html>
