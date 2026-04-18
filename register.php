<?php
session_start();
require_once __DIR__ . '/functions.php';

if (isset($_SESSION['user'])) {
    header('Location: character.php');
    exit;
}

$errors = [];
$old = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    $old['username'] = clean($username);
    $old['email']    = clean($email);

    if ($username === '') {
        $errors['username'] = 'A seeker must bear a name.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
        $errors['username'] = '3–20 characters. Letters, numbers, underscore only.';
    } elseif (findUser($username) !== null) {
        $errors['username'] = 'That name is already bound to the oath.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid raven address is required.';
    } elseif (strlen($email) > 254) {
        $errors['email'] = 'That raven address is too long.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'The vow must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors['confirm'] = 'The vows do not match.';
    }

    if (empty($errors)) {
        $users = loadUsers();
        $users[] = [
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at'    => date('c'),
        ];
        saveUsers($users);

        $_SESSION['flash'] = 'Your oath is recorded. Speak your name to enter.';
        header('Location: login.php');
        exit;
    }
}

$page_title = 'Register &middot; The Shattered Crown';
include __DIR__ . '/includes/header.php';
?>
<section class="form-card">
    <h2 class="form-title">Join the Fallen</h2>
    <p class="form-sub">Your name shall be etched into the obsidian walls of Valdris</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="flash flash-error"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <div class="field">
            <label for="username">Hero Name</label>
            <input type="text" id="username" name="username" value="<?= $old['username'] ?>" autocomplete="username" required>
            <?php if (!empty($errors['username'])): ?><span class="field-error"><?= htmlspecialchars($errors['username']) ?></span><?php endif; ?>
        </div>
        <div class="field">
            <label for="email">Raven Address</label>
            <input type="email" id="email" name="email" value="<?= $old['email'] ?>" autocomplete="email" required>
            <?php if (!empty($errors['email'])): ?><span class="field-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
        </div>
        <div class="field">
            <label for="password">Choose a Vow</label>
            <input type="password" id="password" name="password" autocomplete="new-password" required>
            <?php if (!empty($errors['password'])): ?><span class="field-error"><?= htmlspecialchars($errors['password']) ?></span><?php endif; ?>
        </div>
        <div class="field">
            <label for="confirm">Confirm Vow</label>
            <input type="password" id="confirm" name="confirm" autocomplete="new-password" required>
            <?php if (!empty($errors['confirm'])): ?><span class="field-error"><?= htmlspecialchars($errors['confirm']) ?></span><?php endif; ?>
        </div>

        <button type="submit" class="btn btn-danger">Swear the Oath</button>

        <div class="form-links">
            <span>Already bound by oath?</span>
            <a href="login.php">Invoke Legacy</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>