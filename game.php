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
        saveGame();
        header('Location: ending.php?reason=death');
        exit;
    }

    $_SESSION['node'] = $chosen['next'];

    saveGame();

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

    <!-- ── LEFT: WORLD MAP ──────────────────────── -->
    <?php
    $visited = $hero['nodes_visited'] ?? [];

    // 6 major landmarks — each maps to a group of story nodes
    $landmarks = [
        [
            'id'    => 'ruined_temple',
            'label' => 'Ruined Temple',
            'icon'  => '&#9961;',
            'nodes' => ['node_01', 'node_02'],
            'x' => 38, 'y' => 82,
        ],
        [
            'id'    => 'crossroads',
            'label' => 'Crossroads',
            'icon'  => '&#10010;',
            'nodes' => ['node_03', 'node_06', 'node_10'],
            'x' => 55, 'y' => 62,
        ],
        [
            'id'    => 'ice_caves',
            'label' => 'Ice Caves',
            'icon'  => '&#10052;',
            'nodes' => ['node_05', 'node_09'],
            'x' => 78, 'y' => 72,
        ],
        [
            'id'    => 'ember_keep',
            'label' => 'Ember Keep',
            'icon'  => '&#9876;',
            'nodes' => ['node_04', 'node_07', 'node_08'],
            'x' => 60, 'y' => 40,
        ],
        [
            'id'    => 'ridge_road',
            'label' => 'The Ridge',
            'icon'  => '&#9650;',
            'nodes' => ['node_11', 'node_12', 'node_13'],
            'x' => 45, 'y' => 22,
        ],
        [
            'id'    => 'malachar_tower',
            'label' => 'Malachar\'s Tower',
            'icon'  => '&#9813;',
            'nodes' => ['node_14', 'node_15', 'node_16', 'node_17'],
            'x' => 50, 'y' => 5,
        ],
    ];

    // Connections between landmarks
    $connections = [
        ['ruined_temple', 'crossroads'],
        ['crossroads',    'ice_caves'],
        ['crossroads',    'ember_keep'],
        ['ice_caves',     'ridge_road'],
        ['ember_keep',    'ridge_road'],
        ['ridge_road',    'malachar_tower'],
    ];

    // Determine state of each landmark
    $landmark_map = [];
    foreach ($landmarks as &$lm) {
        $lm['state'] = 'hidden';
        foreach ($lm['nodes'] as $nid) {
            if ($nid === $node_id) { $lm['state'] = 'current'; break; }
            if (in_array($nid, $visited, true)) { $lm['state'] = 'visited'; }
        }
        $landmark_map[$lm['id']] = $lm;
    }
    unset($lm);
    ?>
    <aside class="game-map">
        <div class="map-header">
            <span class="map-brand">The Shattered Crown</span>
            <span class="map-region">VALDRIS</span>
        </div>
        <div class="map-canvas">
            <!-- Connection lines -->
            <svg class="map-lines" viewBox="0 0 100 90" preserveAspectRatio="none">
                <?php foreach ($connections as $conn): ?>
                    <?php
                        $a = $landmark_map[$conn[0]];
                        $b = $landmark_map[$conn[1]];
                        $line_vis = ($a['state'] !== 'hidden' || $b['state'] !== 'hidden') ? 'visible' : 'faded';
                    ?>
                    <line class="map-line-<?= $line_vis ?>"
                          x1="<?= $a['x'] ?>" y1="<?= $a['y'] ?>"
                          x2="<?= $b['x'] ?>" y2="<?= $b['y'] ?>" />
                <?php endforeach; ?>
            </svg>

            <!-- Landmark nodes -->
            <?php foreach ($landmarks as $lm): ?>
                <div class="map-node <?= $lm['state'] ?>" style="left:<?= $lm['x'] ?>%;top:<?= $lm['y'] ?>%">
                    <span class="map-icon"><?= $lm['icon'] ?></span>
                    <?php if ($lm['state'] === 'current'): ?>
                        <span class="map-badge">CURRENT</span>
                    <?php endif; ?>
                    <?php if ($lm['state'] !== 'hidden'): ?>
                        <span class="map-label"><?= clean($lm['label']) ?></span>
                    <?php else: ?>
                        <span class="map-label dim">???</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="map-footer">
            <?php if (!empty($hero['inventory'])): ?>
            <div class="map-inv">
                <span class="map-inv-title">Relics</span>
                <?php foreach ($hero['inventory'] as $item): ?>
                    <span class="map-inv-item">&#9670; <?= clean($item) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <a href="logout.php" class="map-quit">&#8617; Quit</a>
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