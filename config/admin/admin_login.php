<?php
session_start();
require('../dbcon.php');

// Create admin table if needed
$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    must_change_password TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("ALTER TABLE admins ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1) DEFAULT 0");

// Seed default admin if none exists
$check = $conn->query("SELECT COUNT(*) as cnt FROM admins");
$row   = $check->fetch_assoc();
if ($row['cnt'] == 0) {
    $hashed = password_hash('Admin@1234!', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (username, password, must_change_password) VALUES ('admin', '$hashed', 1)");
}

if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$error = '';

// Handle force password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $id       = (int)$_POST['admin_id'];
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if (strlen($new_pass) < 8) {
        $error = "Password must be at least 8 characters.";
        $force_change = true; $pending_id = $id;
    } elseif ($new_pass !== $confirm) {
        $error = "Passwords do not match.";
        $force_change = true; $pending_id = $id;
    } elseif ($new_pass === 'Admin@1234!') {
        $error = "You cannot reuse the default password.";
        $force_change = true; $pending_id = $id;
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("UPDATE admins SET password=?, must_change_password=0 WHERE id=?");
        $stmt->bind_param("si", $hashed, $id);
        $stmt->execute(); $stmt->close();

        $stmt2 = $conn->prepare("SELECT id, username FROM admins WHERE id=?");
        $stmt2->bind_param("i", $id); $stmt2->execute();
        $admin = $stmt2->get_result()->fetch_assoc(); $stmt2->close();

        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: admin.php"); exit();
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$username || !$password) {
        $error = "Please enter your username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, must_change_password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username); $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc(); $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['must_change_password']) {
                $force_change = true;
                $pending_id   = $admin['id'];
            } else {
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: admin.php"); exit();
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Gaming Zone PH</title>
    <link rel="stylesheet" href="../../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../styles/admin/admin_login.css">
</head>
<body>

<div class="login-card">

    <!-- LOGO HEADER -->
    <div class="logo-wrap">
        <img src="../../assets/images/logo.png" alt="Gaming Zone PH Logo" class="site-logo">
        <br>
        <!-- settings icon in admin badge -->
        <span class="admin-badge">
            <img src="../../assets/icons/settings.svg" class="icon-sm icon-blue" alt="">
            Admin Panel
        </span>
    </div>

    <hr class="section-divider">

    <?php if (!empty($force_change)): ?>

        <!-- ── FORCE PASSWORD CHANGE ── -->
        <div class="section-title">
            <img src="../../assets/icons/key.svg" class="icon-sm icon-muted" alt="">
            Set New Password
        </div>

        <div class="alert-warning-box">
            <img src="../../assets/icons/alert-triangle.svg" class="icon-sm icon-yellow" alt="">
            <div>
                <strong>First-time login detected.</strong><br>
                You must create a new password before continuing.
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert-error">
                <img src="../../assets/icons/x-circle.svg" class="icon-sm icon-red" alt="">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="admin_id" value="<?php echo (int)$pending_id; ?>">

            <div class="mb-3">
                <label class="form-label">
                    <img src="../../assets/icons/lock.svg" class="icon-sm icon-muted" alt="">
                    New Password
                </label>
                <input type="password" name="new_password" class="form-control"
                       placeholder="Minimum 8 characters" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <img src="../../assets/icons/shield.svg" class="icon-sm icon-muted" alt="">
                    Confirm Password
                </label>
                <input type="password" name="confirm_password" class="form-control"
                       placeholder="Repeat your new password" required>
            </div>

            <button type="submit" class="btn-login">
                <img src="../../assets/icons/check-circle.svg" class="icon-sm icon-white" alt="">
                Set Password & Continue
            </button>
        </form>

    <?php else: ?>

        <!-- ── LOGIN FORM ── -->
        <div class="section-title">
            <img src="../../assets/icons/lock.svg" class="icon-sm icon-muted" alt="">
            Admin Login
        </div>

        <?php if ($error): ?>
            <div class="alert-error">
                <img src="../../assets/icons/x-circle.svg" class="icon-sm icon-red" alt="">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="login">

            <div class="mb-3">
                <label class="form-label">
                    <img src="../../assets/icons/user.svg" class="icon-sm icon-muted" alt="">
                    Username
                </label>
                <input type="text" name="username" class="form-control"
                       placeholder="Enter your username" required autofocus
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <img src="../../assets/icons/lock.svg" class="icon-sm icon-muted" alt="">
                    Password
                </label>
                <input type="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn-login">
                <img src="../../assets/icons/log-in.svg" class="icon-sm icon-white" alt="">
                Log In
            </button>
        </form>

    <?php endif; ?>

    <!-- Back to Shop -->
    <a href="../../index.php" class="back-link">
        <img src="../../assets/icons/arrow-left.svg" class="icon-sm icon-muted" alt="">
        Back to Shop
    </a>

</div>

<script src="../../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
