<?php
require_once __DIR__ . '/../config.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'Username not found.';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Incorrect password.';
            } else {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];

                header('Location: ' . BASE_URL . 'admin/index.php');
                exit;
            }
        } catch (\PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | 1001Media</title>
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <h2>1001Media</h2>
            <p>Sign in to manage your digital media portal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="badge badge-danger" style="display: block; width: 100%; text-align: center; padding: 0.75rem; margin-bottom: 1.5rem; border-radius: 4px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="admin-form-group">
                <label for="username">Username</label>
                <div style="position: relative;">
                    <i class="fa-regular fa-user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="username" name="username" class="admin-form-control" placeholder="Enter username" style="padding-left: 2.2rem;" required autofocus>
                </div>
            </div>

            <div class="admin-form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="password" id="password" name="password" class="admin-form-control" placeholder="Enter password" style="padding-left: 2.2rem;" required>
                </div>
            </div>

            <button type="submit" class="btn-admin" style="width: 100%; justify-content: center; padding: 0.8rem; margin-top: 1rem; border-radius: 4px;">
                Sign In <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; font-size: 0.85rem;">
            <a href="<?php echo BASE_URL; ?>" style="color: var(--admin-primary); text-decoration: none; font-weight: 600;">
                <i class="fa-solid fa-arrow-left"></i> Back to Live Site
            </a>
        </div>
    </div>

</body>
</html>
