<?php
session_start();
require_once __DIR__ . '/functions.php';

if (isset($_SESSION['user'])) {
    header('Location: character.php');
    exit;
}

$page_title = 'The Shattered Crown';
include __DIR__ . '/includes/header.php';
?>
<section class="landing">
    <h1>The Shattered Crown</h1>
    <p class="tagline">
        King Aldric lies slain. The Crown of Binding is broken into three fragments.
        An eclipse rises in fourteen days — and with it, the reign of Malachar will become permanent.
        The kingdom of Valdris calls for a seeker bold enough to mend what was shattered.
    </p>
    <div class="landing-actions">
        <a href="login.php" class="btn btn-primary">Invoke Legacy</a>
        <a href="register.php" class="btn btn-danger">Take the Oath</a>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>