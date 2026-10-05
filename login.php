<?php
$page_title = 'Login | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$db_error) {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') $errors[] = 'Please enter a valid email and password.';
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id,name,email,password,role FROM users WHERE email=?");
        $stmt->bind_param('s', $email); $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            redirect($base . '/dashboard.php');
        }
        $errors[] = 'Wrong email or password.';
    }
}
?>
<section class="auth-section"><div class="auth-card">
    <div class="auth-heading"><span class="eyebrow">WELCOME BACK</span><h1>Login</h1><p>Access your account and orders.</p></div>
    <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post">
        <label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
        <label>Password<input type="password" name="password" required></label>
        <button class="btn btn-primary btn-block" type="submit">Login</button>
    </form>
    <p class="auth-footer">New here? <a href="<?= $base ?>/register.php">Create an account</a></p>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>