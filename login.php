<?php
session_start();
require_once __DIR__ . '/functions.php';

if (isset($_SESSION['user'])) {
    header('Location: character.php');
    exit;
}

$error = null;
$old_username = '';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $old_username = clean($username);

    if ($username === '' || $password === '') {
        $error = 'Speak both your name and your vow.';
    } else {
        $user = findUser($username);
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = $user['username'];
            header('Location: character.php');
            exit;
        }
        $error = 'The name or vow is false.';
    }
}

$page_title = 'Login &middot; The Shattered Crown';
include __DIR__ . '/includes/header.php';
?>
<section class="form-card">
    <h2 class="form-title">Seeker of the Crown</h2>
    <p class="form-sub">Speak your name and bind your spirit</p>

    <?php if ($flash): ?>
        <div class="flash flash-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
        <div class="field">
            <label for="username">Hero Name</label>
            <input type="text" id="username" name="username" value="<?= $old_username ?>" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Secret Vow</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn btn-primary">Begin Your Journey</button>

        <div class="form-links">
            <a href="#">Forgotten Vow</a>
            <a href="register.php">New Blood</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>