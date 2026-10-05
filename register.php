<?php
$page_title = 'Register | Hamro Ecommerce';
require_once __DIR__ . '/includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$db_error) {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($name) < 2) $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param('s', $email); $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if ($exists) $errors[] = 'An account with that email already exists.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?, 'user')");
        $stmt->bind_param('ssss', $name, $email, $phone, $hash);
        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type'=>'success','message'=>'Registration successful. You can now log in.'];
            $stmt->close();
            redirect($base . '/login.php');
        }
        $stmt->close();
        $errors[] = 'Registration could not be completed. Please try again.';
    }
}
?>
<section class="auth-section"><div class="auth-card">
    <div class="auth-heading"><span class="eyebrow">JOIN HAMRO</span><h1>Create Account</h1><p>Register for shopping and order tracking.</p></div>
    <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" data-validate="register" novalidate>
        <label>Full Name<input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
        <label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
        <label>Phone<input type="text" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>"></label>
        <label>Password<input type="password" name="password" required minlength="6"></label>
        <label>Confirm Password<input type="password" name="confirm_password" required minlength="6"></label>
        <button class="btn btn-primary btn-block" type="submit">Create Account</button>
    </form>
    <p class="auth-footer">Already have an account? <a href="<?= $base ?>/login.php">Login</a></p>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>