<?php
session_start();
require('../dbcon.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin.php"); exit();
}

$id     = (int)$_GET['id'];
$result = $conn->query("SELECT * FROM items WHERE id='$id'");
$item   = $result->fetch_assoc();

if (!$item) {
    header("Location: admin.php"); exit();
}

$qty = (int)$item['quantity'];
if ($qty <= 0)       { $status = 'Out of Stock'; $badge = 'danger'; }
elseif ($qty <= 5)   { $status = 'Low Stock';    $badge = 'warning'; }
else                 { $status = 'In Stock';      $badge = 'success'; }

$admin_username = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['name']); ?> — Admin View</title>

    <script>(function(){var t=localStorage.getItem('gz_admin_theme')||'dark';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../styles/admin/view_item.css">
</head>
<body>

<nav class="navbar navbar-gz navbar-dark px-3 px-md-4 sticky-top">

    <a href="admin.php" class="brand-wrap">
        <img src="../../assets/images/logo.png" alt="Logo" class="brand-logo">
        <div class="brand-name">
            Gaming Zone <span>PH</span>
            <small class="brand-sub">Admin Panel</small>
        </div>
    </a>

    <div class="d-flex align-items-center gap-2">

        <button class="btn btn-outline-secondary btn-theme" id="themeToggle" title="Toggle theme">
            <img src="../../assets/icons/moon.svg" class="icon-sm icon-white" id="iconMoon" alt="Dark mode">
            <img src="../../assets/icons/sun.svg"  class="icon-sm icon-white" id="iconSun"  alt="Light mode" style="display:none;">
        </button>

        <span class="text-white-50 small d-none d-sm-inline d-flex align-items-center gap-1">
            <img src="../../assets/icons/settings.svg" class="icon-sm icon-white" alt="">
            <?php echo htmlspecialchars($admin_username); ?>
        </span>
>
        <a href="admin.php?tab=products" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <img src="../../assets/icons/arrow-left.svg" class="icon-sm icon-white" alt="">
            Back
        </a>

        <a href="../admin_logout.php" class="btn btn-sm btn-danger fw-bold d-flex align-items-center gap-1">
            <img src="../../assets/icons/log-out.svg" class="icon-sm icon-white" alt="">
            Log Out
        </a>
    </div>
</nav>

<div class="container py-4" style="max-width: 860px;">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="admin.php">Admin Panel</a>
            </li>
            <li class="breadcrumb-item">
                <a href="admin.php?tab=products">Products</a>
            </li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($item['name']); ?></li>
        </ol>
    </nav>

    <div class="main-card">

        <div class="card-top-bar">
            <img src="../../assets/icons/package.svg" class="icon-sm icon-white" alt="">
            <h5>Product Details</h5>
            <span class="ms-auto text-white-50 small">ID #<?php echo $item['id']; ?></span>
        </div>

        <div class="p-4">
            <div class="row g-4">

                <div class="col-md-4">
                    <?php if ($item['image']): ?>
                        <div class="product-img-wrap">
                            <img src="../../assets/images/<?php echo htmlspecialchars($item['image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="no-img-box">
                            <img src="../../assets/icons/image.svg" class="icon" style="width:36px;height:36px;opacity:0.3;" alt="">
                            <span>No Image</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-8">

                    <div class="section-label">
                        <img src="../../assets/icons/info.svg" class="icon-sm icon-muted" alt="">
                        Product Info
                    </div>

                    <div class="mb-3">
                        <div class="field-label">
                            <img src="../../assets/icons/tag.svg" class="icon-sm icon-muted" alt="">
                            Product Name
                        </div>
                        <div class="field-value fw-semibold">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="field-label">
                            <img src="../../assets/icons/align-left.svg" class="icon-sm icon-muted" alt="">
                            Description
                        </div>
                        <div class="field-value desc-field" style="color: var(--muted-text);">
                            <?php echo htmlspecialchars($item['description'] ?: '— No description provided —'); ?>
                        </div>
                    </div>

                    <div class="section-label">
                        <img src="../../assets/icons/bar-chart-2.svg" class="icon-sm icon-muted" alt="">
                        Pricing & Stock
                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-sm-4">
                            <div class="field-label">
                                <img src="../../assets/icons/peso-sign.svg" class="icon-sm icon-muted" alt="">
                                Price
                            </div>
                            <div class="field-value price-field">
                                ₱<?php echo number_format($item['price'], 2); ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="field-label">
                                <img src="../../assets/icons/layers.svg" class="icon-sm icon-muted" alt="">
                                Quantity
                            </div>
                            <div class="field-value fw-bold" style="font-size:18px;">
                                <?php echo $item['quantity']; ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="field-label">
                                <img src="../../assets/icons/activity.svg" class="icon-sm icon-muted" alt="">
                                Status
                            </div>
                            <div class="field-value">
                                <?php if ($badge === 'success'): ?>
                                    <span class="badge bg-success d-flex align-items-center gap-1 px-2 py-1" style="font-size:12px;">
                                        <img src="../../assets/icons/check-circle.svg" class="icon-sm icon-white" alt="">
                                        In Stock
                                    </span>
                                <?php elseif ($badge === 'warning'): ?>
                                    <span class="badge bg-warning text-dark d-flex align-items-center gap-1 px-2 py-1" style="font-size:12px;">
                                        <img src="../../assets/icons/alert-triangle.svg" class="icon-sm" style="filter:brightness(0);" alt="">
                                        Low Stock
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger d-flex align-items-center gap-1 px-2 py-1" style="font-size:12px;">
                                        <img src="../../assets/icons/x-circle.svg" class="icon-sm icon-white" alt="">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="admin.php?edit_product=<?php echo $item['id']; ?>"
                           class="btn btn-warning fw-bold d-flex align-items-center gap-1">
                            <img src="../../assets/icons/pencil.svg" class="icon-sm icon-white" alt="">
                            Edit Product
                        </a>
                        <a href="admin.php?delete_product=<?php echo $item['id']; ?>"
                           class="btn btn-danger fw-bold d-flex align-items-center gap-1"
                           onclick="return confirm('Delete \'<?php echo addslashes($item['name']); ?>\'? This cannot be undone.')">
                            <img src="../../assets/icons/trash-2.svg" class="icon-sm icon-white" alt="">
                            Delete Product
                        </a>
                        <a href="admin.php?tab=products"
                           class="btn btn-outline-secondary d-flex align-items-center gap-1">
                            <img src="../../assets/icons/arrow-left.svg" class="icon-sm" alt="">
                            Back to Products
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<footer class="text-center py-3 mt-4">
    <small>Gaming Zone PH — Admin Panel &copy; 2026</small><br>
    <small><strong>Developed by: Manabat, Aljon P.</strong></small>
</footer>

<script src="../../bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../scripts/admin/view_item.js"></script>
</body>
</html>
