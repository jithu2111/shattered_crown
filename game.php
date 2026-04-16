<?php
require_once __DIR__ . '/includes/auth.php';
requireHero();
require_once __DIR__ . '/data/story.php';

$node_id = $_SESSION['node'];

if (!isset($nodes[$node_id])) {
    resetGame();
    header('Location: character.php');
    exit;
}

$node = $nodes[$node_id];

if ($node['is_terminal']) {
    header('Location: ending.php');
    exit;
}

if (isDead()) {
    header('Location: ending.php?reason=death');
    exit;
}

// ── POST: process a choice ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice_id = $_POST['choice_id'] ?? '';

    $chosen = null;
    foreach ($node['choices'] as $c) {
        if ($c['id'] === $choice_id) { $chosen = $c; break; }
    }

    if (!$chosen) {
        header('Location: game.php');
        exit;
    }

    if (!canChoose($chosen)) {
        header('Location: game.php');
        exit;
    }

    applyStatChanges($chosen['stat_changes'] ?? []);

    foreach ($chosen['items_granted'] ?? [] as $item) {
        if (!in_array($item, $_SESSION['hero']['inventory'], true)) {
            $_SESSION['hero']['inventory'][] = $item;
        }
    }

    $_SESSION['alignment'] += $chosen['alignment'] ?? 0;
    $_SESSION['alignment_history'][] = $_SESSION['alignment'];

    $_SESSION['choices_log'][] = [
        'node'   => $node_id,
        'choice' => $chosen['text'],
        'title'  => $node['title'],
    ];

    if (!in_array($node_id, $_SESSION['hero']['nodes_visited'], true)) {
        $_SESSION['hero']['nodes_visited'][] = $node_id;
    }

    $_SESSION['hero']['score'] += 25;

    setcookie('sc_node', $chosen['next'], time() + 3600, '/');

    if (isDead()) {
        $_SESSION['node'] = $chosen['next'];
        header('Location: ending.php?reason=death');
        exit;
    }

    $_SESSION['node'] = $chosen['next'];

    if (isset($nodes[$chosen['next']]) && $nodes[$chosen['next']]['is_terminal']) {
        header('Location: ending.php');
        exit;
    }

    header('Location: game.php');
    exit;
}

// ── GET: render current node ──────────────────────
$hero      = $_SESSION['hero'];
$class     = $hero['class'];
$story_text = getNodeText($node, $class);
$hint      = getAlignmentHint($_SESSION['alignment']);
$choices   = $node['choices'];

$locked_this_view = [];
foreach ($choices as $c) {
    if (!canChoose($c)) {
        $lock_key = $node_id . ':' . $c['id'];
        if (!in_array($lock_key, $_SESSION['locked_log'] ?? [], true)) {
            $_SESSION['locked_log'][] = $lock_key;
        }
        $locked_this_view[$c['id']] = getLockReason($c);
    }
}

$page_title = $node['title'] . ' &middot; The Shattered Crown';
$body_class = 'page-game';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="page-game">

<!-- ── TOP STATS BAR ───────────────────────────── -->
<header class="game-bar">
    <div class="bar-brand">VALDRIS</div>
    <div class="bar-stats">
        <span class="bar-stat">
            <em><?= strtoupper($class_stats[$class]['archetype']) ?></em>
            <strong>&#9829; <?= $hero['hp'] ?> / <?= $class_stats[$class]['hp'] ?> HP</strong>
        </span>
        <span class="bar-stat">
            <em>STRENGTH</em>
            <strong>&#9876; <?= $hero['str'] ?> STR</strong>
        </span>
        <span class="bar-stat">
            <em>WISDOM</em>
            <strong>&#10026; <?= $hero['wis'] ?> WIS</strong>
        </span>
    </div>
    <div class="bar-eclipse">
        <em>ECLIPSE COUNTDOWN</em>
        <strong>14 DAYS REMAINING</strong>
    </div>
</header>

<div class="game-layout">

    <!-- ── LEFT SIDEBAR ────────────────────────── -->
    <aside class="game-sidebar">
        <div class="sidebar-section">
            <h3 class="sidebar-heading">Story Path</h3>
            <span class="sidebar-sub">NODE: <?= clean($node['title']) ?></span>
        </div>

        <nav class="sidebar-nav">
            <a class="sidebar-link active">&#9998; The Path</a>
            <a class="sidebar-link" title="Inventory: <?= count($hero['inventory']) ?> items">&#9876; Inventory</a>
            <a class="sidebar-link">&#9734; Attributes</a>
            <a class="sidebar-link">&#9733; Omen Tracker</a>
        </nav>

        <?php if (!empty($_SESSION['choices_log'])): ?>
        <div class="sidebar-section path-log">
            <h4 class="sidebar-heading-sm">Journey So Far</h4>
            <?php foreach ($_SESSION['choices_log'] as $i => $entry): ?>
                <div class="path-entry">
                    <span class="path-node"><?= clean($entry['title']) ?></span>
                    <span class="path-choice"><?= clean($entry['choice']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($hero['inventory'])): ?>
        <div class="sidebar-section">
            <h4 class="sidebar-heading-sm">Inventory</h4>
            <ul class="inv-list">
                <?php foreach ($hero['inventory'] as $item): ?>
                    <li><?= clean($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="sidebar-bottom">
            <a href="logout.php" class="sidebar-link dim">&#8617; Quit</a>
        </div>
    </aside>

    <!-- ── MAIN STORY PANEL ────────────────────── -->
    <section class="game-main">
        <div class="story-panel">
            <span class="node-badge"><?= clean($node_id) ?></span>
            <div class="story-location">
                <span class="location-marker">&#9670; CURRENT NODE</span>
                <h2 class="story-title"><?= clean($node['title']) ?></h2>
            </div>

            <p class="class-insight">CLASS INSIGHT: <?= strtoupper($class) ?></p>
            <div class="story-text"><?= nl2br(clean($story_text)) ?></div>

            <div class="alignment-bar">
                <span class="alignment-label">PATH ALIGNMENT</span>
                <span class="alignment-hint"><?= clean($hint) ?></span>
            </div>
        </div>

        <!-- ── ENDING PREDICTOR ────────────────── -->
        <div class="ending-predictor">
            <span class="predictor-icon">&#9790;</span>
            <span class="predictor-label">ENDING PREDICTOR</span>
        </div>
    </section>

    <!-- ── RIGHT: CHOICES ──────────────────────── -->
    <aside class="game-choices">
        <?php foreach ($choices as $c): ?>
            <?php $is_locked = isset($locked_this_view[$c['id']]); ?>
            <?php if ($is_locked): ?>
                <div class="choice-card locked">
                    <span class="choice-text"><?= clean($c['text']) ?></span>
                    <span class="lock-reason"><?= clean($locked_this_view[$c['id']]) ?></span>
                    <span class="lock-icon">&#128274;</span>
                </div>
            <?php else: ?>
                <form method="POST" action="game.php" class="choice-form">
                    <input type="hidden" name="choice_id" value="<?= clean($c['id']) ?>">
                    <button type="submit" class="choice-card unlocked">
                        <span class="choice-text"><?= clean($c['text']) ?></span>
                        <?php if (!empty($c['stat_changes'])): ?>
                            <span class="choice-cost">
                                <?php foreach ($c['stat_changes'] as $s => $d): ?>
                                    <?= strtoupper($s) ?> <?= ($d >= 0 ? '+' : '') . $d ?>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($c['required_item']): ?>
                            <span class="choice-cost">Uses: <?= clean($c['required_item']) ?></span>
                        <?php endif; ?>
                    </button>
                </form>
            <?php endif; ?>
        <?php endforeach; ?>
    </aside>

</div>

</body>
</html>