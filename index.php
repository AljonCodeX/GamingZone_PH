<?php
session_start();
require('config/dbcon.php');

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$is_logged_in = isset($_SESSION['user_id']);
$cart_count   = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

$sort  = $_GET['sort'] ?? 'latest';
$order = match($sort) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name_asc'   => 'name ASC',
    'name_desc'  => 'name DESC',
    'sold'       => 'sold DESC',
    default      => 'id DESC'
};

$cat = isset($_GET['cat']) ? mysqli_real_escape_string($conn, $_GET['cat']) : '';

$search = '';

if (isset($_POST['searchItem'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $result = mysqli_query($conn, "SELECT * FROM items
                                   WHERE name LIKE '%$search%'
                                   OR description LIKE '%$search%'
                                   ORDER BY $order");
} elseif ($cat) {
    $result = mysqli_query($conn, "SELECT * FROM items
                                   WHERE category LIKE '$cat%'
                                   ORDER BY $order");
} else {
    $result = mysqli_query($conn, "SELECT * FROM items ORDER BY $order");
}

$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (!$search && !$cat && $sort === 'latest') {
    $groups = [];
    foreach ($items as $item) {
        $prefix = strtolower(explode('-', $item['category'])[0]);
        $groups[$prefix][] = $item;
    }
    $interleaved = [];
    $hasItems    = true;
    while ($hasItems) {
        $hasItems = false;
        foreach ($groups as $prefix => &$group) {
            if (!empty($group)) {
                $interleaved[] = array_shift($group);
                $hasItems = true;
            }
        }
        unset($group);
    }
    $items = $interleaved;
}

function isCatActive(string $value): string {
    return ($_GET['cat'] ?? '') === $value ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Zone PH — Your Gaming Gear Store</title>

    <script>
        const _t = localStorage.getItem('gz_theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', _t);
    </script>

    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/index.css">
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

    <form method="post" action="index.php?sort=<?php echo $sort; ?>"
          class="d-none d-md-flex mx-3 flex-grow-1" style="max-width: 400px;">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Search products..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" name="searchItem" class="btn btn-primary d-flex align-items-center gap-1">
                <img src="assets/icons/search.svg" class="icon-sm icon-white" alt="Search">
            </button>
            <?php if ($search): ?>
                <a href="index.php" class="btn btn-outline-light d-flex align-items-center">
                    <img src="assets/icons/x-circle.svg" class="icon-sm icon-white" alt="Clear">
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="d-flex align-items-center gap-2">

        <button class="btn btn-outline-secondary btn-theme" id="themeToggle" title="Toggle theme">
            <img src="assets/icons/moon.svg" class="icon-sm icon-white" id="iconMoon" alt="Dark mode">
            <img src="assets/icons/sun.svg"  class="icon-sm icon-white" id="iconSun"  alt="Light mode" style="display:none;">
        </button>

        <?php if ($is_logged_in): ?>
            <span class="text-white-50 small d-none d-sm-inline d-flex align-items-center gap-1">
                <img src="assets/icons/user.svg" class="icon-sm icon-white" alt="">
                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline-light">Log Out</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-sm btn-outline-light">Log In</a>
        <?php endif; ?>

        <a href="cart.php" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <img src="assets/icons/shopping-cart.svg" class="icon-sm icon-white" alt="">
            Cart
            <?php if ($cart_count > 0): ?>
                <span class="badge bg-light text-dark ms-1"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>

    </div>
</nav>

<div class="d-md-none px-3 py-2" style="background: var(--nav-bg);">
    <form method="post" action="index.php">
        <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="Search products..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" name="searchItem" class="btn btn-primary d-flex align-items-center">
                <img src="assets/icons/search.svg" class="icon-sm icon-white" alt="Search">
            </button>
            <?php if ($search): ?>
                <a href="index.php" class="btn btn-outline-light d-flex align-items-center">
                    <img src="assets/icons/x.svg" class="icon-sm icon-white" alt="Clear">
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="category-nav border-bottom position-relative">
    <div class="container">
        <ul class="category-list">

            <li class="category-item">
                <a href="index.php" class="category-link <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
                    Home
                </a>
            </li>

            <li class="category-item mega-parent">
                <a href="index.php?cat=ps5" class="category-link <?php echo isCatActive('ps5'); ?>">PS5</a>
                <div class="mega-menu">
                    <div class="mega-inner">
                        <div class="mega-col">
                            <div class="mega-heading">Consoles</div>
                            <a href="index.php?cat=ps5-console" class="mega-link">PlayStation 5</a>
                            <a href="index.php?cat=ps5-vr"      class="mega-link">PlayStation VR2</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Games</div>
                            <a href="index.php?cat=ps5-action"    class="mega-link">Action</a>
                            <a href="index.php?cat=ps5-adventure" class="mega-link">Adventure</a>
                            <a href="index.php?cat=ps5-racing"    class="mega-link">Racing</a>
                            <a href="index.php?cat=ps5-sports"    class="mega-link">Sports</a>
                            <a href="index.php?cat=ps5-rpg"       class="mega-link">Role-Playing</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Accessories</div>
                            <a href="index.php?cat=ps5-controllers" class="mega-link">Controllers</a>
                            <a href="index.php?cat=ps5-headsets"    class="mega-link">Headsets</a>
                            <a href="index.php?cat=ps5-cables"      class="mega-link">Cables</a>
                            <a href="index.php?cat=ps5-cases"       class="mega-link">Cases & Storage</a>
                        </div>
                        <?php
                        $rec       = mysqli_query($conn, "SELECT * FROM items WHERE category LIKE 'ps5%' ORDER BY sold DESC LIMIT 3");
                        $rec_items = mysqli_fetch_all($rec, MYSQLI_ASSOC);
                        if (!empty($rec_items)): ?>
                        <div class="mega-col mega-products">
                            <div class="mega-heading">Recommended</div>
                            <?php foreach ($rec_items as $r): ?>
                            <a href="item.php?id=<?php echo $r['id']; ?>" class="mega-product-item">
                                <?php if ($r['image']): ?>
                                    <img src="assets/images/<?php echo htmlspecialchars($r['image']); ?>" alt="">
                                <?php else: ?>
                                    <div class="mega-no-img"></div>
                                <?php endif; ?>
                                <div>
                                    <div class="mega-product-name"><?php echo htmlspecialchars($r['name']); ?></div>
                                    <div class="mega-product-price">₱<?php echo number_format($r['price'], 2); ?></div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </li>

            <li class="category-item mega-parent">
                <a href="index.php?cat=ps4" class="category-link <?php echo isCatActive('ps4'); ?>">PS4</a>
                <div class="mega-menu">
                    <div class="mega-inner">
                        <div class="mega-col">
                            <div class="mega-heading">Consoles</div>
                            <a href="index.php?cat=ps4-console" class="mega-link">PlayStation 4</a>
                            <a href="index.php?cat=ps4-slim"    class="mega-link">PS4 Slim</a>
                            <a href="index.php?cat=ps4-pro"     class="mega-link">PS4 Pro</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Games</div>
                            <a href="index.php?cat=ps4-action"    class="mega-link">Action</a>
                            <a href="index.php?cat=ps4-adventure" class="mega-link">Adventure</a>
                            <a href="index.php?cat=ps4-racing"    class="mega-link">Racing</a>
                            <a href="index.php?cat=ps4-sports"    class="mega-link">Sports</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Accessories</div>
                            <a href="index.php?cat=ps4-controllers" class="mega-link">Controllers</a>
                            <a href="index.php?cat=ps4-headsets"    class="mega-link">Headsets</a>
                            <a href="index.php?cat=ps4-charging"    class="mega-link">Charging Docks</a>
                        </div>
                        <?php
                        $rec2       = mysqli_query($conn, "SELECT * FROM items WHERE category LIKE 'ps4%' ORDER BY sold DESC LIMIT 3");
                        $rec2_items = mysqli_fetch_all($rec2, MYSQLI_ASSOC);
                        if (!empty($rec2_items)): ?>
                        <div class="mega-col mega-products">
                            <div class="mega-heading">Recommended</div>
                            <?php foreach ($rec2_items as $r): ?>
                            <a href="item.php?id=<?php echo $r['id']; ?>" class="mega-product-item">
                                <?php if ($r['image']): ?>
                                    <img src="assets/images/<?php echo htmlspecialchars($r['image']); ?>" alt="">
                                <?php else: ?>
                                    <div class="mega-no-img"></div>
                                <?php endif; ?>
                                <div>
                                    <div class="mega-product-name"><?php echo htmlspecialchars($r['name']); ?></div>
                                    <div class="mega-product-price">₱<?php echo number_format($r['price'], 2); ?></div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </li>

            <li class="category-item mega-parent">
                <a href="index.php?cat=switch" class="category-link <?php echo isCatActive('switch'); ?>">Switch</a>
                <div class="mega-menu">
                    <div class="mega-inner">
                        <div class="mega-col">
                            <div class="mega-heading">Consoles</div>
                            <a href="index.php?cat=switch-console" class="mega-link">Nintendo Switch</a>
                            <a href="index.php?cat=switch-lite"    class="mega-link">Switch Lite</a>
                            <a href="index.php?cat=switch-oled"    class="mega-link">Switch OLED</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Games</div>
                            <a href="index.php?cat=switch-action"    class="mega-link">Action</a>
                            <a href="index.php?cat=switch-adventure" class="mega-link">Adventure</a>
                            <a href="index.php?cat=switch-family"    class="mega-link">Family</a>
                            <a href="index.php?cat=switch-rpg"       class="mega-link">Role-Playing</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Accessories</div>
                            <a href="index.php?cat=switch-joycon" class="mega-link">Joy-Con</a>
                            <a href="index.php?cat=switch-cases"  class="mega-link">Cases</a>
                            <a href="index.php?cat=switch-dock"   class="mega-link">Docking Stations</a>
                        </div>
                    </div>
                </div>
            </li>

            <li class="category-item mega-parent">
                <a href="index.php?cat=xbox" class="category-link <?php echo isCatActive('xbox'); ?>">Xbox</a>
                <div class="mega-menu">
                    <div class="mega-inner">
                        <div class="mega-col">
                            <div class="mega-heading">Consoles</div>
                            <a href="index.php?cat=xbox-series-x" class="mega-link">Xbox Series X</a>
                            <a href="index.php?cat=xbox-series-s" class="mega-link">Xbox Series S</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Games</div>
                            <a href="index.php?cat=xbox-action" class="mega-link">Action</a>
                            <a href="index.php?cat=xbox-sports" class="mega-link">Sports</a>
                            <a href="index.php?cat=xbox-racing" class="mega-link">Racing</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Accessories</div>
                            <a href="index.php?cat=xbox-controllers" class="mega-link">Controllers</a>
                            <a href="index.php?cat=xbox-headsets"    class="mega-link">Headsets</a>
                            <a href="index.php?cat=xbox-charging"    class="mega-link">Charging</a>
                        </div>
                    </div>
                </div>
            </li>

            <li class="category-item mega-parent">
                <a href="index.php?cat=pc" class="category-link <?php echo isCatActive('pc'); ?>">PC / Mac</a>
                <div class="mega-menu">
                    <div class="mega-inner">
                        <div class="mega-col">
                            <div class="mega-heading">Hardware</div>
                            <a href="index.php?cat=pc-laptops"    class="mega-link">Laptops</a>
                            <a href="index.php?cat=pc-desktops"   class="mega-link">Desktops</a>
                            <a href="index.php?cat=pc-components" class="mega-link">Components</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Peripherals</div>
                            <a href="index.php?cat=pc-keyboards" class="mega-link">Keyboards</a>
                            <a href="index.php?cat=pc-mouse"     class="mega-link">Mouse</a>
                            <a href="index.php?cat=pc-monitors"  class="mega-link">Monitors</a>
                            <a href="index.php?cat=pc-headsets"  class="mega-link">Headsets</a>
                        </div>
                        <div class="mega-col">
                            <div class="mega-heading">Accessories</div>
                            <a href="index.php?cat=pc-webcams"  class="mega-link">Webcams</a>
                            <a href="index.php?cat=pc-chairs"   class="mega-link">Gaming Chairs</a>
                            <a href="index.php?cat=pc-storage"  class="mega-link">Storage / SSD</a>
                        </div>
                    </div>
                </div>
            </li>

            <li class="category-item mega-parent">
                <a href="index.php?cat=collectibles" class="category-link <?php echo isCatActive('collectibles'); ?>">Collectibles</a>
                <div class="mega-menu">
                    <div class="mega-inner">
                        <div class="mega-col">
                            <div class="mega-heading">Types</div>
                            <a href="index.php?cat=collectibles-figures"        class="mega-link">Figures</a>
                            <a href="index.php?cat=collectibles-merchandise"    class="mega-link">Merchandise</a>
                            <a href="index.php?cat=collectibles-limited"        class="mega-link">Limited Edition</a>
                            <a href="index.php?cat=collectibles-cards"          class="mega-link">Trading Cards</a>
                        </div>
                    </div>
                </div>
            </li>

        </ul>
    </div>
</div>

<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">

    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="4"></button>
    </div>

    <div class="carousel-inner">
        <div class="carousel-item active">
            <a href="index.php?cat=pc">
                <img src="assets/images/banner_1.png" alt="Logitech Razer SteelSeries Sale" style="width:100%;">
            </a>
        </div>
        <div class="carousel-item">
            <a href="index.php?cat=switch">
                <img src="assets/images/banner_2.webp" alt="NYXI Controllers Available Now" style="width:100%;">
            </a>
        </div>
        <div class="carousel-item">
            <a href="index.php?sort=price_asc">
                <img src="assets/images/banner_3.webp" alt="Heatwave Gaming Sale" style="width:100%;">
            </a>
        </div>
        <div class="carousel-item">
            <a href="index.php?cat=pc">
                <img src="assets/images/banner_4.webp" alt="OneXPlayer APEX Available Now" style="width:100%;">
            </a>
        </div>
        <div class="carousel-item">
            <a href="index.php?cat=pc">
                <img src="assets/images/banner_5.webp" alt="Build Your Dream PC" style="width:100%;">
            </a>
        </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>

</div>

<div class="container py-4">

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
            <img src="assets/icons/check-circle.svg" class="icon icon-white" alt="">
            <span><?php echo htmlspecialchars($message); ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom section-divider flex-wrap gap-2">

        <small class="text-muted">
            <?php if ($search): ?>
                <?php echo count($items); ?> item(s) found for "<strong><?php echo htmlspecialchars($search); ?></strong>"
            <?php else: ?>
                <strong style="font-size:15px; color: inherit;">New Arrivals</strong>
            <?php endif; ?>
        </small>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small fw-semibold">Sort:</span>
            <?php
            $search_qs = $search ? '&search=' . urlencode($search) : '';
            $sorts = [
                'latest'     => 'Latest',
                'price_asc'  => 'Price ↑',
                'price_desc' => 'Price ↓',
                'name_asc'   => 'A–Z',
                'sold'       => 'Best Sold',
            ];
            ?>
            <div class="btn-group btn-group-sm" role="group">
                <?php foreach ($sorts as $key => $label): ?>
                    <a href="?sort=<?php echo $key . $search_qs; ?>"
                       class="btn <?php echo $sort === $key ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <a href="sell.php" class="btn btn-sm btn-success fw-semibold ms-1 d-flex align-items-center gap-1">
                <img src="assets/icons/plus.svg" class="icon-sm icon-white" alt="">
                Sell
            </a>
        </div>

    </div>

    <?php if (empty($items)): ?>

        <div class="text-center py-5 text-muted">
            <?php if ($search): ?>
                <p class="mb-2">No products found for "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">Clear Search</a>
            <?php else: ?>
                <img src="assets/icons/package.svg" class="icon mb-2" style="width:40px;height:40px;" alt="">
                <p class="mb-0">No products available yet.</p>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
            <?php foreach ($items as $item): ?>
            <div class="col">
                <div class="card product-card border">

                    <a href="item.php?id=<?php echo $item['id']; ?>">
                        <?php if ($item['image']): ?>
                            <div class="product-img-wrap">
                                <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>"
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                        <?php else: ?>
                            <div class="no-img">
                                <img src="assets/icons/package.svg" class="icon" style="width:28px;height:28px;opacity:0.4;" alt="">
                                No Image
                            </div>
                        <?php endif; ?>
                    </a>

                    <div class="card-body p-2 d-flex flex-column">
                        <a href="item.php?id=<?php echo $item['id']; ?>"
                           class="fw-semibold text-decoration-none text-truncate small mb-1"
                           style="color: inherit; font-size: 13px;">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </a>
                        <div class="text-truncate text-muted mb-1" style="font-size:11px;">
                            <?php echo htmlspecialchars($item['description'] ?: 'No description'); ?>
                        </div>
                        <div class="price-text mb-2">₱<?php echo number_format($item['price'], 2); ?></div>
                        <div class="stock-sold-row mt-auto">
                            <span>Stock: <?php echo $item['quantity']; ?></span>
                            <span><?php echo $item['sold'] ?? 0; ?> sold</span>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<footer class="border-top py-3 text-center mt-4">
    <small class="text-muted">Gaming Zone PH &copy; 2026</small><br>
    <small><strong>Developed by: Manabat, Aljon P.</strong></small>
</footer> 

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/scripts/index.js"></script>

</body>
</html>