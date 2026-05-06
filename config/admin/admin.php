<?php
session_start();
require('../dbcon.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); exit();
}

$conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1) DEFAULT 0");
$ag = $conn->prepare("SELECT must_change_password FROM admins WHERE id=?");
if ($ag) {
    $ag->bind_param("i", $_SESSION['admin_id']); $ag->execute();
    $ag_row = $ag->get_result()->fetch_assoc(); $ag->close();
    if ($ag_row && $ag_row['must_change_password']) {
        session_destroy(); header("Location: admin_login.php"); exit();
    }
}

$admin_username = $_SESSION['admin_username'];
$tab = $_GET['tab'] ?? 'products';

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

if (isset($_GET['approve'])) {
    $pid = (int)$_GET['approve'];
    $s   = $conn->prepare("SELECT * FROM pending_items WHERE id=?");
    $s->bind_param("i", $pid); $s->execute();
    $p   = $s->get_result()->fetch_assoc(); $s->close();
    if ($p) {
        $ins = $conn->prepare("INSERT INTO items (name,description,price,quantity,image) VALUES (?,?,?,?,?)");
        $ins->bind_param("ssdis", $p['name'], $p['description'], $p['price'], $p['quantity'], $p['image']);
        $ins->execute(); $ins->close();
        $upd = $conn->prepare("UPDATE pending_items SET status='approved' WHERE id=?");
        $upd->bind_param("i", $pid); $upd->execute(); $upd->close();
        $_SESSION['admin_msg'] = ['type'=>'success','text'=>'Product "'.$p['name'].'" approved and added to the shop.'];
    }
    header("Location: admin.php?tab=pending"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reject') {
    $pid    = (int)$_POST['pending_id'];
    $reason = mysqli_real_escape_string($conn, $_POST['reject_reason']);
    $conn->query("UPDATE pending_items SET status='rejected', reject_reason='$reason' WHERE id='$pid'");
    $_SESSION['admin_msg'] = ['type'=>'danger','text'=>'Submission rejected.'];
    header("Location: admin.php?tab=pending"); exit();
}

if (isset($_GET['delete_product'])) {
    $id = (int)$_GET['delete_product'];
    $conn->query("DELETE FROM items WHERE id='$id'");
    $_SESSION['admin_msg'] = ['type'=>'danger','text'=>'Product deleted.'];
    header("Location: admin.php?tab=products"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price       = (float)$_POST['price'];
    $quantity    = (int)$_POST['quantity'];
    $image       = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $dir = "../../assets/images/";
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp']) && getimagesize($_FILES['image']['tmp_name'])) {
            $fname = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname);
            $image = $fname;
        }
    }
    $conn->query("INSERT INTO items (name,description,price,quantity,image) VALUES ('$name','$description','$price','$quantity','$image')");
    $_SESSION['admin_msg'] = ['type'=>'success','text'=>"Product \"$name\" added."];
    header("Location: admin.php?tab=products"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id          = (int)$_POST['id'];
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price       = (float)$_POST['price'];
    $quantity    = (int)$_POST['quantity'];
    $cur         = $conn->query("SELECT image FROM items WHERE id='$id'");
    $image       = $cur->fetch_assoc()['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $dir = "../../assets/images/";
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp']) && getimagesize($_FILES['image']['tmp_name'])) {
            $fname = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname);
            $image = $fname;
        }
    }
    $conn->query("UPDATE items SET name='$name',description='$description',price='$price',quantity='$quantity',image='$image' WHERE id='$id'");
    $_SESSION['admin_msg'] = ['type'=>'success','text'=>"Product \"$name\" updated."];
    header("Location: admin.php?tab=products"); exit();
}

if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    $conn->query("DELETE FROM users WHERE id='$id'");
    $_SESSION['admin_msg'] = ['type'=>'danger','text'=>'User deleted.'];
    header("Location: admin.php?tab=users"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_user') {
    $id       = (int)$_POST['id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = trim($_POST['password']);
    if ($password !== '') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET username='$username',email='$email',password='$hashed' WHERE id='$id'");
    } else {
        $conn->query("UPDATE users SET username='$username',email='$email' WHERE id='$id'");
    }
    $_SESSION['admin_msg'] = ['type'=>'success','text'=>"User \"$username\" updated."];
    header("Location: admin.php?tab=users"); exit();
}

$prod_search = trim($_GET['prod_search'] ?? '');
$prod_sort   = $_GET['prod_sort'] ?? 'latest';
$prod_cat    = $_GET['prod_cat'] ?? '';

$prod_order = match($prod_sort) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name_asc'   => 'name ASC',
    'name_desc'  => 'name DESC',
    'stock_asc'  => 'quantity ASC',
    'sold'       => 'sold DESC',
    default      => 'image DESC, id ASC'
};

$where_clauses = [];
if ($prod_search !== '') {
    $s = mysqli_real_escape_string($conn, $prod_search);
    $where_clauses[] = "(name LIKE '%$s%' OR description LIKE '%$s%' OR id LIKE '%$s%')";
}
$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
$items = $conn->query("SELECT * FROM items $where_sql ORDER BY $prod_order")->fetch_all(MYSQLI_ASSOC);
$total_items = $conn->query("SELECT COUNT(*) as cnt FROM items")->fetch_assoc()['cnt'];

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$users         = $conn->query("SELECT id,username,email,created_at FROM users ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$pending       = $conn->query("SELECT * FROM pending_items ORDER BY FIELD(status,'pending','rejected','approved'), submitted_at DESC")->fetch_all(MYSQLI_ASSOC);
$pending_count = count(array_filter($pending, fn($p) => $p['status'] === 'pending'));

$editItem = null;
if (isset($_GET['edit_product'])) {
    $eid      = (int)$_GET['edit_product'];
    $editItem = $conn->query("SELECT * FROM items WHERE id='$eid'")->fetch_assoc();
    $tab      = 'products';
}
$editUser = null;
if (isset($_GET['edit_user'])) {
    $eid      = (int)$_GET['edit_user'];
    $editUser = $conn->query("SELECT id,username,email FROM users WHERE id='$eid'")->fetch_assoc();
    $tab      = 'users';
}

$msg = $_SESSION['admin_msg'] ?? null;
unset($_SESSION['admin_msg']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — Gaming Zone PH</title>
    <script>(function(){var t=localStorage.getItem('gz_admin_theme')||'dark';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../styles/admin/admin.css">
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
        <a href="../../index.php" target="_blank" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
            <img src="../../assets/icons/store.svg" class="icon-sm icon-white" alt="">
            View Shop
        </a>
        <a href="../admin_logout.php" class="btn btn-sm btn-danger fw-bold d-flex align-items-center gap-1">
            <img src="../../assets/icons/log-out.svg" class="icon-sm icon-white" alt="">
            Log Out
        </a>
    </div>
</nav>

<div class="container-fluid px-4 py-3">

    <?php if ($msg): ?>
        <div class="<?php echo $msg['type']==='success' ? 'flash-success' : 'flash-danger'; ?> mb-3">
            <?php if ($msg['type']==='success'): ?>
                <img src="../../assets/icons/check-circle.svg" class="icon-sm icon-green" alt="">
            <?php else: ?>
                <img src="../../assets/icons/trash-2.svg" class="icon-sm icon-red" alt="">
            <?php endif; ?>
            <?php echo htmlspecialchars($msg['text']); ?>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
        <a href="admin.php?tab=products" class="tab-link <?php echo $tab==='products'?'active':''; ?>">
            <img src="../../assets/icons/package.svg" class="icon-sm <?php echo $tab==='products'?'icon-white':'icon-muted'; ?>" alt="">
            Products
            <span class="<?php echo $tab==='products'?'text-white':''; ?> opacity-75 small">(<?php echo $total_items; ?>)</span>
        </a>
        <a href="admin.php?tab=users" class="tab-link <?php echo $tab==='users'?'active':''; ?>">
            <img src="../../assets/icons/users.svg" class="icon-sm <?php echo $tab==='users'?'icon-white':'icon-muted'; ?>" alt="">
            Users
            <span class="<?php echo $tab==='users'?'text-white':''; ?> opacity-75 small">(<?php echo count($users); ?>)</span>
        </a>
        <a href="admin.php?tab=pending" class="tab-link <?php echo $tab==='pending'?'active':''; ?>">
            <img src="../../assets/icons/clock.svg" class="icon-sm <?php echo $tab==='pending'?'icon-white':'icon-muted'; ?>" alt="">
            Pending
            <?php if ($pending_count > 0): ?>
                <span class="pending-dot"><?php echo $pending_count; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="row g-3">

        <div class="col-md-3">

            <?php if ($tab === 'products'): ?>
            <div class="sidebar-card">
                <div class="card-header">
                    <?php if ($editItem): ?>
                        <img src="../../assets/icons/pencil.svg" class="icon-sm icon-white" alt="">
                        Edit Product
                    <?php else: ?>
                        <img src="../../assets/icons/plus-circle.svg" class="icon-sm icon-white" alt="">
                        Add Product
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editItem ? 'edit' : 'add'; ?>">
                        <?php if ($editItem): ?>
                            <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-2">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control form-control-sm" required
                                   value="<?php echo $editItem ? htmlspecialchars($editItem['name']) : ''; ?>"
                                   placeholder="e.g. Gaming Headset">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2"
                                      placeholder="Short description..."><?php echo $editItem ? htmlspecialchars($editItem['description']) : ''; ?></textarea>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <label class="form-label">Price (₱) *</label>
                                <input type="number" name="price" class="form-control form-control-sm"
                                       step="0.01" min="0" required
                                       value="<?php echo $editItem ? $editItem['price'] : ''; ?>"
                                       placeholder="0.00">
                            </div>
                            <div class="col">
                                <label class="form-label">Stock *</label>
                                <input type="number" name="quantity" class="form-control form-control-sm"
                                       min="0" required
                                       value="<?php echo $editItem ? $editItem['quantity'] : ''; ?>"
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <?php if ($editItem && $editItem['image']): ?>
                                <img src="../../assets/images/<?php echo htmlspecialchars($editItem['image']); ?>"
                                     class="img-preview" id="imgPreview" alt="">
                            <?php else: ?>
                                <img src="" class="img-preview" id="imgPreview" style="display:none;" alt="">
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control form-control-sm"
                                   accept="image/*" id="imgInput">
                            <?php if ($editItem): ?>
                                <small class="td-muted" style="font-size:11px;">Leave empty to keep current image.</small>
                            <?php endif; ?>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold d-flex align-items-center justify-content-center gap-1">
                                <?php if ($editItem): ?>
                                    <img src="../../assets/icons/save.svg" class="icon-sm icon-white" alt="">
                                    Save Changes
                                <?php else: ?>
                                    <img src="../../assets/icons/plus.svg" class="icon-sm icon-white" alt="">
                                    Add Product
                                <?php endif; ?>
                            </button>
                            <?php if ($editItem): ?>
                                <a href="admin.php?tab=products" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center gap-1">
                                    <img src="../../assets/icons/x.svg" class="icon-sm" alt="">
                                    Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <?php elseif ($tab === 'users'): ?>
            <div class="sidebar-card">
                <div class="card-header">
                    <?php if ($editUser): ?>
                        <img src="../../assets/icons/pencil.svg" class="icon-sm icon-white" alt="">
                        Edit User
                    <?php else: ?>
                        <img src="../../assets/icons/users.svg" class="icon-sm icon-white" alt="">
                        Users
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($editUser): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
                        <div class="mb-2">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control form-control-sm" required
                                   value="<?php echo htmlspecialchars($editUser['username']); ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control form-control-sm" required
                                   value="<?php echo htmlspecialchars($editUser['email']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control form-control-sm"
                                   placeholder="Leave blank to keep current">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold d-flex align-items-center justify-content-center gap-1">
                                <img src="../../assets/icons/save.svg" class="icon-sm icon-white" alt="">
                                Save Changes
                            </button>
                            <a href="admin.php?tab=users" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center gap-1">
                                <img src="../../assets/icons/x.svg" class="icon-sm" alt="">
                                Cancel
                            </a>
                        </div>
                    </form>
                    <?php else: ?>
                        <p class="td-muted mb-0" style="font-size:13px;">
                            Select a user from the table to edit or delete.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($tab === 'pending'): ?>
            <div class="sidebar-card">
                <div class="card-header">
                    <img src="../../assets/icons/clock.svg" class="icon-sm icon-white" alt="">
                    Pending Reviews
                </div>
                <div class="card-body">
                    <p class="td-muted mb-3" style="font-size:13px;">
                        Review product submissions from users. Approve to publish to the shop, or reject with a reason.
                    </p>
                    <?php if ($pending_count > 0): ?>
                        <div class="box-warning">
                            <img src="../../assets/icons/alert-triangle.svg" class="icon-sm icon-yellow" alt="">
                            <span><strong><?php echo $pending_count; ?></strong> submission<?php echo $pending_count!==1?'s':''; ?> awaiting review.</span>
                        </div>
                    <?php else: ?>
                        <div class="box-success">
                            <img src="../../assets/icons/check-circle.svg" class="icon-sm icon-green" alt="">
                            All caught up!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="col-md-9">
            <div class="table-card">

                <?php if ($tab === 'products'): ?>

                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div>
                            <h5>
                                <img src="../../assets/icons/package.svg" class="icon" alt="">
                                Products
                            </h5>
                            <p>
                                <?php echo count($items); ?> of <?php echo $total_items; ?> item(s)
                                <?php if ($prod_search): ?>
                                    — search: "<strong><?php echo htmlspecialchars($prod_search); ?></strong>"
                                <?php endif; ?>
                                <?php if ($prod_cat): ?>
                                    — category: <strong><?php echo htmlspecialchars($prod_cat); ?></strong>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($prod_search || $prod_cat || $prod_sort !== 'latest'): ?>
                            <a href="admin.php?tab=products" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <img src="../../assets/icons/x.svg" class="icon-sm" alt="">
                                Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>

                    <form method="get" action="admin.php" class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="hidden" name="tab" value="products">

                        <div class="input-group input-group-sm" style="max-width:210px;">
                            <input type="text" name="prod_search" class="form-control form-control-sm"
                                   placeholder="Search by name or ID..."
                                   value="<?php echo htmlspecialchars($prod_search); ?>">
                            <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center px-2">
                                <img src="../../assets/icons/search.svg" class="icon-sm icon-white" alt="">
                            </button>
                        </div>

                        <select name="prod_sort" class="form-select form-select-sm" style="max-width:135px;" onchange="this.form.submit()">
                            <option value="latest"     <?php echo $prod_sort==='latest'?'selected':''; ?>>ID: Low–High</option>
                            <option value="name_asc"   <?php echo $prod_sort==='name_asc'?'selected':''; ?>>Name A–Z</option>
                            <option value="price_asc"  <?php echo $prod_sort==='price_asc'?'selected':''; ?>>Price ↑</option>
                            <option value="price_desc" <?php echo $prod_sort==='price_desc'?'selected':''; ?>>Price ↓</option>
                            <option value="stock_asc"  <?php echo $prod_sort==='stock_asc'?'selected':''; ?>>Low Stock</option>
                            <option value="sold"       <?php echo $prod_sort==='sold'?'selected':''; ?>>Best Sold</option>
                        </select>

                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Sold</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 td-muted">
                                    <?php if ($prod_search || $prod_cat): ?>
                                        No products found. <a href="admin.php?tab=products">Clear filters</a>
                                    <?php else: ?>
                                        No products yet. Add one using the form.
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($items as $i => $item): ?>
                            <tr class="<?php echo ($editItem && $editItem['id']==$item['id']) ? 'row-editing' : ''; ?>">
                                <td class="td-num">
                                    <?php echo $i+1; ?>
                                </td>
                                <td>
                                    <?php if ($item['image']): ?>
                                        <img src="../../assets/images/<?php echo htmlspecialchars($item['image']); ?>" class="item-img" alt="">
                                    <?php else: ?>
                                        <div class="no-img">No img</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size:13px;"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="text-truncate td-muted" style="max-width:160px; font-size:11px;">
                                        <?php echo htmlspecialchars($item['description'] ?: '—'); ?>
                                    </div>
                                </td>
                                <td class="td-price">₱<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="td-muted"><?php echo $item['sold'] ?? 0; ?></td>
                                <td>
                                    <?php $q = (int)$item['quantity'];
                                    if      ($q <= 0) echo '<span class="badge bg-danger">Out of Stock</span>';
                                    elseif  ($q <= 5) echo '<span class="badge bg-warning text-dark">Low Stock</span>';
                                    else              echo '<span class="badge bg-success">In Stock</span>'; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="view_item.php?id=<?php echo $item['id']; ?>"
                                           class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                                           style="font-size:12px;">
                                            <img src="../../assets/icons/eye.svg" class="icon-sm icon-white" alt="">
                                            View
                                        </a>
                                        <a href="admin.php?edit_product=<?php echo $item['id']; ?>"
                                           class="btn btn-sm btn-warning d-flex align-items-center gap-1"
                                           style="font-size:12px;">
                                            <img src="../../assets/icons/pencil.svg" class="icon-sm icon-white" alt="">
                                            Edit
                                        </a>
                                        <a href="admin.php?delete_product=<?php echo $item['id']; ?>"
                                           class="btn btn-sm btn-danger d-flex align-items-center gap-1"
                                           style="font-size:12px;"
                                           onclick="return confirm('Delete \'<?php echo addslashes($item['name']); ?>\'?')">
                                            <img src="../../assets/icons/trash-2.svg" class="icon-sm icon-white" alt="">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($tab === 'users'): ?>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5>
                            <img src="../../assets/icons/users.svg" class="icon" alt="">
                            Users
                        </h5>
                        <p><?php echo count($users); ?> registered users</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 td-muted">No registered users yet.</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($users as $i => $user): ?>
                            <tr class="<?php echo ($editUser && $editUser['id']==$user['id']) ? 'row-editing' : ''; ?>">
                                <td class="td-num"><?php echo $i+1; ?></td>
                                <td>
                                    <div class="fw-semibold d-flex align-items-center gap-1" style="font-size:13px;">
                                        <img src="../../assets/icons/user.svg" class="icon-sm icon-muted" alt="">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </div>
                                </td>
                                <td class="td-muted"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="td-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="admin.php?edit_user=<?php echo $user['id']; ?>&tab=users"
                                           class="btn btn-sm btn-warning d-flex align-items-center gap-1"
                                           style="font-size:12px;">
                                            <img src="../../assets/icons/pencil.svg" class="icon-sm icon-white" alt="">
                                            Edit
                                        </a>
                                        <a href="admin.php?delete_user=<?php echo $user['id']; ?>"
                                           class="btn btn-sm btn-danger d-flex align-items-center gap-1"
                                           style="font-size:12px;"
                                           onclick="return confirm('Delete user \'<?php echo addslashes($user['username']); ?>\'?')">
                                            <img src="../../assets/icons/trash-2.svg" class="icon-sm icon-white" alt="">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php elseif ($tab === 'pending'): ?>
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5>
                            <img src="../../assets/icons/clock.svg" class="icon" alt="">
                            Pending Submissions
                        </h5>
                        <p><?php echo $pending_count; ?> awaiting review</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Submitted By</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pending)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 td-muted">No submissions yet.</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($pending as $i => $p): ?>
                            <tr>
                                <td class="td-num"><?php echo $i+1; ?></td>
                                <td>
                                    <?php if ($p['image']): ?>
                                        <img src="../../assets/images/<?php echo htmlspecialchars($p['image']); ?>" class="item-img" alt="">
                                    <?php else: ?>
                                        <div class="no-img">No img</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size:13px;"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div class="text-truncate td-muted" style="max-width:160px; font-size:11px;">
                                        <?php echo htmlspecialchars($p['description'] ?: '—'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="td-muted d-flex align-items-center gap-1" style="font-size:12px;">
                                        <img src="../../assets/icons/user.svg" class="icon-sm icon-muted" alt="">
                                        <?php echo htmlspecialchars($p['username']); ?>
                                    </div>
                                </td>
                                <td class="td-price">₱<?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo $p['quantity']; ?></td>
                                <td>
                                    <?php if ($p['status']==='pending'): ?>
                                        <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                                            <img src="../../assets/icons/clock.svg" class="icon-sm" style="filter:brightness(0);" alt="">
                                            Pending
                                        </span>
                                    <?php elseif ($p['status']==='approved'): ?>
                                        <span class="badge bg-success d-inline-flex align-items-center gap-1">
                                            <img src="../../assets/icons/check-circle.svg" class="icon-sm icon-white" alt="">
                                            Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                            <img src="../../assets/icons/x-circle.svg" class="icon-sm icon-white" alt="">
                                            Rejected
                                        </span>
                                        <?php if ($p['reject_reason']): ?>
                                            <div class="td-muted mt-1" style="font-size:11px;">
                                                <?php echo htmlspecialchars($p['reject_reason']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['status']==='pending'): ?>
                                    <div class="d-flex gap-1">
                                        <a href="admin.php?approve=<?php echo $p['id']; ?>"
                                           class="btn btn-sm btn-success d-flex align-items-center gap-1"
                                           style="font-size:12px;"
                                           onclick="return confirm('Approve and publish this product?')">
                                            <img src="../../assets/icons/check-circle.svg" class="icon-sm icon-white" alt="">
                                            Approve
                                        </a>
                                        <button class="btn btn-sm btn-danger d-flex align-items-center gap-1"
                                                style="font-size:12px;"
                                                onclick="openReject(<?php echo $p['id']; ?>,'<?php echo addslashes($p['name']); ?>')">
                                            <img src="../../assets/icons/x-circle.svg" class="icon-sm icon-white" alt="">
                                            Reject
                                        </button>
                                    </div>
                                    <?php else: ?>
                                        <span class="td-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<footer class="text-center py-3 mt-4">
    <small>Gaming Zone PH — Admin Panel &copy; 2026</small><br>
    <small><strong>Developed by: Manabat, Aljon P.</strong></small>
</footer>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-danger">
                <h5 class="modal-title text-white d-flex align-items-center gap-2">
                    <img src="../../assets/icons/x-circle.svg" class="icon-sm icon-white" alt="">
                    Reject Submission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="pending_id" id="rejectId">
                    <p class="fw-semibold mb-3" id="rejectName"></p>
                    <div class="mb-3">
                        <label class="form-label">Reason (optional)</label>
                        <input type="text" name="reject_reason" class="form-control"
                               placeholder="e.g. Missing image, invalid price...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold d-flex align-items-center gap-1">
                        <img src="../../assets/icons/x-circle.svg" class="icon-sm icon-white" alt="">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../scripts/admin/admin.js"></script>
</body>
</html>