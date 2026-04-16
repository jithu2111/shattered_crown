<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/story.php';

// If hero already in session and game is active, go to game
if (isset($_SESSION['hero'], $_SESSION['node'])
    && isset($nodes[$_SESSION['node']])
    && !$nodes[$_SESSION['node']]['is_terminal']) {
    header('Location: game.php');
    exit;
}

// Check for a saved game on disk — ignore saves stuck on a terminal node
$existing_save = loadSave($_SESSION['user']);
if ($existing_save) {
    $save_node = $existing_save['node'] ?? '';
    if (!isset($nodes[$save_node]) || $nodes[$save_node]['is_terminal']) {
        deleteSave($_SESSION['user']);
        $existing_save = null;
    }
}

// Handle continue / delete save actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'continue' && $existing_save) {
        restoreSave($existing_save);
        header('Location: game.php');
        exit;
    }
    if ($_POST['action'] === 'new_game') {
        resetGame();
        $existing_save = null;
        // Fall through to show character creation form
    }
}

$errors = [];
$old_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $hero_name = trim($_POST['hero_name'] ?? '');
    $class     = $_POST['class'] ?? '';
    $old_name  = clean($hero_name);

    if ($hero_name === '' || strlen($hero_name) > 30) {
        $errors[] = 'Your legend needs a name (1–30 characters).';
    }

    if (!isset($class_stats[$class])) {
        $errors[] = 'Choose a valid class.';
    }

    if (empty($errors)) {
        $stats = $class_stats[$class];
        $_SESSION['hero'] = [
            'name'          => clean($hero_name),
            'class'         => $class,
            'hp'            => $stats['hp'],
            'str'           => $stats['str'],
            'wis'           => $stats['wis'],
            'inventory'     => [],
            'score'         => 500,
            'nodes_visited' => [],
        ];
        $_SESSION['node']              = 'node_01';
        $_SESSION['choices_log']       = [];
        $_SESSION['locked_log']        = [];
        $_SESSION['alignment']         = 0;
        $_SESSION['alignment_history'] = [];

        saveGame();
        header('Location: game.php');
        exit;
    }
}

$page_title = 'Choose Your Adventurer &middot; The Shattered Crown';
$body_class = 'page-character';
include __DIR__ . '/includes/header.php';
?>

<section class="char-select">
    <p class="char-kicker">Choose Your Adventurer</p>
    <h1 class="char-title">The Shattered Crown</h1>

    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <?php if ($existing_save): ?>
    <div class="save-card">
        <h3 class="save-heading">Saved Journey Found</h3>
        <div class="save-details">
            <span class="save-hero"><?= clean($existing_save['hero']['name']) ?></span>
            <span class="save-meta">
                <?= ucfirst($existing_save['hero']['class']) ?> &middot;
                HP <?= $existing_save['hero']['hp'] ?> &middot;
                <?= count($existing_save['hero']['nodes_visited']) ?> nodes explored
            </span>
            <?php if (isset($existing_save['node'], $nodes[$existing_save['node']])): ?>
                <span class="save-location">Last seen: <?= clean($nodes[$existing_save['node']]['title']) ?></span>
            <?php endif; ?>
            <span class="save-time">Saved <?= date('M j, g:i A', strtotime($existing_save['saved_at'])) ?></span>
        </div>
        <div class="save-actions">
            <form method="POST" action="character.php" style="display:inline">
                <input type="hidden" name="action" value="continue">
                <button type="submit" class="btn btn-primary btn-select">Continue Journey</button>
            </form>
            <form method="POST" action="character.php" style="display:inline">
                <input type="hidden" name="action" value="new_game">
                <button type="submit" class="btn btn-danger btn-select" onclick="return confirm('This will erase your saved progress. Are you sure?')">New Game</button>
            </form>
        </div>
    </div>
    <?php else: ?>

    <form method="POST" action="character.php">
        <div class="field char-name-field">
            <label for="hero_name">Hero Name</label>
            <input type="text" id="hero_name" name="hero_name" value="<?= $old_name ?>" placeholder="Enter your legend..." required>
        </div>

        <div class="class-grid">
            <?php foreach ($class_stats as $key => $c): ?>
            <label class="class-card" data-class="<?= $key ?>">
                <input type="radio" name="class" value="<?= $key ?>" <?= ($key === 'warrior') ? 'checked' : '' ?>>
                <div class="class-card-inner">
                    <div class="class-header">
                        <span class="class-icon"><?php
                            echo match($key) {
                                'warrior' => '&#x1F6E1;',
                                'mage'    => '&#x2728;',
                                'rogue'   => '&#x1F5E1;',
                            };
                        ?></span>
                        <div>
                            <span class="class-label"><?= htmlspecialchars($c['label']) ?></span>
                            <span class="class-arch">CLASS: <?= strtoupper($c['archetype']) ?></span>
                        </div>
                    </div>
                    <div class="class-stats">
                        <?php if ($key === 'warrior'): ?>
                            <span class="stat"><em>STR</em><?= $c['str'] ?></span>
                            <span class="stat"><em>HP</em><?= $c['hp'] ?></span>
                            <span class="stat"><em>ARMOR</em>Heavy</span>
                        <?php elseif ($key === 'mage'): ?>
                            <span class="stat"><em>WIS</em><?= $c['wis'] ?></span>
                            <span class="stat"><em>HP</em><?= $c['hp'] ?></span>
                            <span class="stat"><em>MANA</em>High</span>
                        <?php else: ?>
                            <span class="stat"><em>STR</em><?= $c['str'] ?></span>
                            <span class="stat"><em>WIS</em><?= $c['wis'] ?></span>
                            <span class="stat"><em>HP</em><?= $c['hp'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-danger btn-select">Select Character</button>
        <p class="char-note">Once chosen, your destiny is bound to the relic.</p>
    </form>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>