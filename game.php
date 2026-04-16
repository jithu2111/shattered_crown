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

    // All 17 nodes — positioned in clear tiers, bottom to top
    // Tier 1 (y=88): Act 1 start
    // Tier 2 (y=76): Act 1 discovery
    // Tier 3 (y=64): Crossroads hub
    // Tier 4 (y=52): Act 2 branches
    // Tier 5 (y=40): Act 2 interiors
    // Tier 6 (y=28): Act 3 approach
    // Tier 7 (y=16): Tower entry
    // Tier 8 (y=5):  Throne + final
    $map_nodes = [
        // Tier 1 — bottom
        ['node_01', 'Ruined Temple',     '&#9961;',  40, 92],
        // Tier 2
        ['node_02', 'First Shard',       '&#10070;', 22, 80],
        // Tier 3
        ['node_03', 'Crossroads',        '&#10010;', 50, 68],
        // Tier 4 — three branches spread wide
        ['node_06', 'Sable',             '&#9830;',  15, 56],
        ['node_04', 'Ember Keep',        '&#9876;',  50, 56],
        ['node_05', 'Ice Caves',         '&#10052;', 85, 56],
        // Tier 5
        ['node_10', 'Revelation',        '&#9790;',  15, 44],
        ['node_08', 'Broker\'s Path',    '&#9758;',  38, 44],
        ['node_07', 'Inside Keep',       '&#9733;',  62, 44],
        ['node_09', 'Frozen Sanctum',    '&#10052;', 85, 44],
        // Tier 6
        ['node_11', 'Ridge Road',        '&#9650;',  50, 33],
        // Tier 7 — two entry points
        ['node_12', 'Tower Gate',        '&#9608;',  32, 23],
        ['node_13', 'Hidden Entry',      '&#9608;',  68, 23],
        // Tier 8 — throne room
        ['node_14', 'Throne Room',       '&#9813;',  50, 14],
        // Tier 9 — final confrontations
        ['node_15', 'The Duel',          '&#9876;',  22, 5],
        ['node_16', 'Ritual Collapse',   '&#10026;', 50, 5],
        ['node_17', 'Silent Strike',     '&#128065;',78, 5],
    ];

    // Build coordinate lookup
    $coords = [];
    foreach ($map_nodes as $mn) {
        $coords[$mn[0]] = ['x' => $mn[3], 'y' => $mn[4]];
    }

    // Connections follow actual story branching
    $map_edges = [
        ['node_01','node_02'], ['node_01','node_03'],
        ['node_02','node_03'],
        ['node_03','node_04'], ['node_03','node_05'], ['node_03','node_06'],
        ['node_06','node_10'], ['node_06','node_04'],
        ['node_04','node_07'], ['node_04','node_08'],
        ['node_08','node_07'],
        ['node_05','node_09'],
        ['node_10','node_04'], ['node_10','node_11'],
        ['node_07','node_11'], ['node_09','node_11'],
        ['node_11','node_12'], ['node_11','node_13'],
        ['node_12','node_14'], ['node_13','node_14'],
        ['node_14','node_15'], ['node_14','node_16'], ['node_14','node_17'],
    ];
    ?>
    <aside class="game-map">
        <div class="map-header">
            <span class="map-brand">The Shattered Crown</span>
            <span class="map-region">VALDRIS</span>
        </div>
        <div class="map-canvas">
            <svg class="map-lines" viewBox="0 0 100 95" preserveAspectRatio="none">
                <?php foreach ($map_edges as $e): ?>
                    <?php
                        $a_vis = in_array($e[0], $visited, true) || $e[0] === $node_id;
                        $b_vis = in_array($e[1], $visited, true) || $e[1] === $node_id;
                        $cls = ($a_vis && $b_vis) ? 'map-line-walked'
                             : (($a_vis || $b_vis) ? 'map-line-visible' : 'map-line-faded');
                    ?>
                    <line class="<?= $cls ?>"
                          x1="<?= $coords[$e[0]]['x'] ?>" y1="<?= $coords[$e[0]]['y'] ?>"
                          x2="<?= $coords[$e[1]]['x'] ?>" y2="<?= $coords[$e[1]]['y'] ?>" />
                <?php endforeach; ?>
            </svg>

            <?php foreach ($map_nodes as $mn): ?>
                <?php
                    [$mid, $mlabel, $micon, $mx, $my] = $mn;
                    $is_current = ($mid === $node_id);
                    $is_visited = in_array($mid, $visited, true);
                    $state = $is_current ? 'current' : ($is_visited ? 'visited' : 'hidden');
                ?>
                <div class="map-node <?= $state ?>" style="left:<?= $mx ?>%;top:<?= $my ?>%">
                    <span class="map-icon"><?= $micon ?></span>
                    <?php if ($is_current): ?>
                        <span class="map-badge">CURRENT</span>
                    <?php endif; ?>
                    <?php if ($is_current || $is_visited): ?>
                        <span class="map-label"><?= clean($mlabel) ?></span>
                    <?php else: ?>
                        <span class="map-label dim">???</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="map-footer">
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

        <?php if (!empty($hero['inventory'])): ?>
        <div class="choices-inv">
            <span class="choices-inv-title">Relics</span>
            <?php foreach ($hero['inventory'] as $item): ?>
                <span class="choices-inv-item">&#9670; <?= clean($item) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>

</div>

</body>
</html>