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
    $map_locations = [
        // [id, label, x%, y%, icon]
        ['node_01',              'Ruined Temple',       30, 72, '&#9961;'],
        ['node_02',              'First Shard',         18, 62, '&#10070;'],
        ['node_03',              'Crossroads',          50, 58, '&#10010;'],
        ['node_05',              'Ice Caves',           72, 68, '&#10052;'],
        ['node_06',              'Sable',               38, 48, '&#9830;'],
        ['node_04',              'Ember Keep',          55, 38, '&#9876;'],
        ['node_08',              'Broker\'s Passage',   40, 32, '&#9758;'],
        ['node_07',              'Inside Keep',         62, 28, '&#9733;'],
        ['node_09',              'Frozen Sanctum',      78, 50, '&#10052;'],
        ['node_10',              'Sable\'s Revelation', 32, 38, '&#9790;'],
        ['node_11',              'Ridge Road',          52, 22, '&#9650;'],
        ['node_12',              'Tower Gate',          42, 14, '&#9608;'],
        ['node_13',              'Hidden Entry',        62, 14, '&#9608;'],
        ['node_14',              'Throne Room',         52,  6, '&#9813;'],
        ['node_15',              'The Duel',            40,  0, '&#9876;'],
        ['node_16',              'Ritual Collapse',     52,  0, '&#10026;'],
        ['node_17',              'Silent Strike',       64,  0, '&#128065;'],
    ];
    $map_paths = [
        [30,72, 18,62], [30,72, 50,58], // temple → shard, temple → crossroads
        [18,62, 50,58],                   // shard → crossroads
        [50,58, 55,38],                   // crossroads → ember keep
        [50,58, 72,68],                   // crossroads → ice caves
        [50,58, 38,48],                   // crossroads → sable
        [55,38, 62,28], [55,38, 40,32],  // ember keep → inside, → broker
        [40,32, 62,28],                   // broker → inside keep
        [72,68, 78,50],                   // ice caves → frozen sanctum
        [38,48, 32,38],                   // sable → revelation
        [32,38, 55,38], [32,38, 52,22],  // revelation → ember keep, → ridge
        [62,28, 52,22], [78,50, 52,22],  // inside keep → ridge, sanctum → ridge
        [52,22, 42,14], [52,22, 62,14],  // ridge → tower gate, → hidden entry
        [42,14, 52,6], [62,14, 52,6],    // gate → throne, hidden → throne
        [52,6, 40,0], [52,6, 52,0], [52,6, 64,0], // throne → duel, ritual, silent
    ];
    ?>
    <aside class="game-map">
        <div class="map-header">
            <span class="map-brand">The Shattered Crown</span>
            <span class="map-region">VALDRIS</span>
        </div>
        <div class="map-canvas">
            <svg class="map-lines" viewBox="0 0 100 80" preserveAspectRatio="none">
                <?php foreach ($map_paths as $p): ?>
                    <line x1="<?= $p[0] ?>" y1="<?= $p[1] ?>" x2="<?= $p[2] ?>" y2="<?= $p[3] ?>" />
                <?php endforeach; ?>
            </svg>
            <?php foreach ($map_locations as $loc): ?>
                <?php
                    [$mid, $mlabel, $mx, $my, $micon] = $loc;
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