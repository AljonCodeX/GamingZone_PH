<?php
session_start();
require('config/dbcon.php');

if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$error   = '';
$success = '';
$tab     = $_GET['tab'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    $tab      = 'register';

    if (!$username || !$email || !$password || !$confirm) {
        $error = "All fields are required.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email); $check->execute(); $check->store_result();
        if ($check->num_rows > 0) {
            $error = "An account with that email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed);
            $stmt->execute(); $stmt->close();
            $success = "Account created! You can now log in.";
            $tab = 'login';
        }
        $check->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $tab      = 'login';

    if (!$email || !$password) {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc(); $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect"); exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Gaming Zone PH</title>
    <script>(function(){var t=localStorage.getItem('gz_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/login.css">
    <style>

    </style>
</head>
<body>

<nav class="navbar navbar-gz navbar-dark px-3 px-md-4">

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

<div class="page-center">
    <div class="auth-card">

        <div class="logo-wrap">
            <img src="assets/images/logo.png" alt="Gaming Zone PH Logo" class="site-logo">
        </div>

        <div class="card border shadow-sm">

            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs" id="authTabs">
                    <li class="nav-item flex-fill text-center">
                        <button class="nav-link w-100 fw-bold <?php echo $tab==='login'?'active':''; ?>"
                                onclick="switchTab('login')">Log In</button>
                    </li>
                    <li class="nav-item flex-fill text-center">
                        <button class="nav-link w-100 fw-bold <?php echo $tab==='register'?'active':''; ?>"
                                onclick="switchTab('register')">Register</button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
                        <img src="assets/icons/x-circle.svg" class="icon-sm" style="filter: brightness(0) saturate(100%) invert(27%) sepia(90%) saturate(500%) hue-rotate(330deg);" alt="">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success py-2 small d-flex align-items-center gap-2">
                        <img src="assets/icons/check-circle.svg" class="icon-sm" style="filter: brightness(0) saturate(100%) invert(35%) sepia(90%) saturate(400%) hue-rotate(100deg);" alt="">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="tab-panel <?php echo $tab==='login'?'active':''; ?>" id="panel-login">
                    <h6 class="text-center fw-bold mb-1">Welcome Back!</h6>
                    <p class="text-center text-muted small mb-3">Log in to continue</p>

                    <form method="post">
                        <input type="hidden" name="action" value="login">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-1">
                                <img src="assets/icons/mail.svg" class="icon-sm icon-muted" alt="">
                                Email Address
                            </label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="you@example.com" required
                                   value="<?php echo $tab==='login' ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-1">
                                <img src="assets/icons/lock.svg" class="icon-sm icon-muted" alt="">
                                Password
                            </label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold w-100 d-flex align-items-center justify-content-center gap-2">
                            <img src="assets/icons/log-in.svg" class="icon-sm icon-white" alt="">
                            Log In
                        </button>
                    </form>

                    <p class="text-center text-muted small mt-3">
                        Don't have an account?
                        <a href="#" onclick="switchTab('register')" class="text-primary fw-semibold">Register here</a>
                    </p>

                    <hr class="my-2">

                    <p class="text-center small mb-0">
                        <a href="config/admin/admin_login.php"
                           class="text-muted text-decoration-none d-inline-flex align-items-center gap-1"
                           style="font-size:12px;">
                            <img src="assets/icons/settings.svg" class="icon-muted" style="width:13px;height:13px;" alt="">
                            Admin Login
                        </a>
                    </p>
                </div>

                <div class="tab-panel <?php echo $tab==='register'?'active':''; ?>" id="panel-register">
                    <h6 class="text-center fw-bold mb-1">Create Account</h6>
                    <p class="text-center text-muted small mb-3">Sign up to start shopping</p>

                    <form method="post">
                        <input type="hidden" name="action" value="register">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-1">
                                <img src="assets/icons/user.svg" class="icon-sm icon-muted" alt="">
                                Username
                            </label>
                            <input type="text" name="username" class="form-control"
                                   placeholder="e.g. gamer123" required
                                   value="<?php echo $tab==='register' ? htmlspecialchars($_POST['username'] ?? '') : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-1">
                                <img src="assets/icons/mail.svg" class="icon-sm icon-muted" alt="">
                                Email Address
                            </label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="you@example.com" required
                                   value="<?php echo $tab==='register' ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-1">
                                <img src="assets/icons/lock.svg" class="icon-sm icon-muted" alt="">
                                Password
                                <span class="text-muted fw-normal">(min. 6 chars)</span>
                            </label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="••••••••" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex align-items-center gap-1">
                                <img src="assets/icons/shield.svg" class="icon-sm icon-muted" alt="">
                                Confirm Password
                            </label>
                            <input type="password" name="confirm" class="form-control"
                                   placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold w-100 d-flex align-items-center justify-content-center gap-2">
                            <img src="assets/icons/user-plus.svg" class="icon-sm icon-white" alt="">
                            Create Account
                        </button>
                    </form>

                    <p class="text-center text-muted small mt-3">
                        Already have an account?
                        <a href="#" onclick="switchTab('login')" class="text-primary fw-semibold">Log in here</a>
                    </p>
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
<script src="/scripts/login.js"></script>
</body>
</html>
