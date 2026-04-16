<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/story.php';

if (!isset($_SESSION['hero'], $_SESSION['node'])) {
    header('Location: character.php');
    exit;
}

$hero      = $_SESSION['hero'];
$node_id   = $_SESSION['node'];
$class     = $hero['class'];
$alignment = $_SESSION['alignment'] ?? 0;
$visited   = $hero['nodes_visited'] ?? [];
$locked    = $_SESSION['locked_log'] ?? [];
$inventory = $hero['inventory'] ?? [];

// Determine ending
$death = isset($_GET['reason']) && $_GET['reason'] === 'death';
if ($death) {
    $ending_type  = 'death';
    $ending_title = 'Fallen in Valdris';
    $ending_text  = 'Your strength failed you. The shadows close in as the last breath leaves your body. The Crown of Binding will never be whole — Malachar\'s eclipse descends, and your name fades into silence.';
} elseif (isset($nodes[$node_id]) && $nodes[$node_id]['is_terminal']) {
    $ending_type  = $nodes[$node_id]['ending_type'];
    $ending_title = $nodes[$node_id]['title'];
    $ending_text  = getNodeText($nodes[$node_id], $class);
} else {
    header('Location: game.php');
    exit;
}

// Legacy persona based on alignment + class
if ($alignment >= 8) {
    $persona = 'The Justiciar';
} elseif ($alignment >= 3) {
    $persona = 'The Warden';
} elseif ($alignment >= -3) {
    $persona = 'The Drifter';
} elseif ($alignment >= -8) {
    $persona = 'The Schemer';
} else {
    $persona = 'The Usurper';
}

// Calculate final score
$final_score = calculateScore();

// Stats
$total_nodes   = count(array_filter($nodes, fn($n) => !$n['is_terminal']));
$nodes_pct     = $total_nodes > 0 ? round(count($visited) / $total_nodes * 100) : 0;
$locked_count  = count($locked);

// Ending label + color class
$ending_labels = [
    'heroic' => ['Heroic Victory', 'ending-heroic'],
    'tragic' => ['Tragic Failure', 'ending-tragic'],
    'secret' => ['Secret Path',    'ending-secret'],
    'death'  => ['Fallen',         'ending-death'],
];
$label_data = $ending_labels[$ending_type] ?? ['Unknown', 'ending-tragic'];

// Save score to leaderboard
saveScore([
    'username' => $_SESSION['user'],
    'score'    => $final_score,
    'ending'   => $ending_type,
    'class'    => $class,
    'persona'  => $persona,
    'hero'     => $hero['name'],
    'nodes'    => count($visited),
    'date'     => date('c'),
]);

// Clear the save file since game is over
deleteSave($_SESSION['user']);

$page_title = $ending_title . ' &middot; The Shattered Crown';
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
<body class="page-ending <?= $label_data[1] ?>">

<div class="ending-layout">

    <!-- ── LEFT: NARRATIVE ─────────────────────── -->
    <section class="ending-narrative">
        <div class="ending-sidebar-label">VALDRIS ECLIPSE</div>

        <div class="ending-outcome">
            <span class="outcome-line">&mdash;&mdash; OUTCOME: <?= strtoupper($label_data[0]) ?></span>
        </div>

        <h1 class="ending-headline"><?= clean($ending_title) ?></h1>

        <div class="ending-story">
            <p class="ending-story-main"><?= nl2br(clean($ending_text)) ?></p>
        </div>

        <div class="ending-epilogue">
            <?php if ($ending_type === 'heroic'): ?>
                <p>As the Valdris Eclipse recedes, the citizens of the lower realms emerge from their stone shelters to find a world changed. The crown is mended with threads of ethereal light, and your name is etched into the very foundation of the Hall of Legends.</p>
            <?php elseif ($ending_type === 'secret'): ?>
                <p>The world moves on, unaware of the hand now pulling its strings. In the silence of the obsidian throne room, a new age begins — one shaped not by light or shadow, but by will alone.</p>
            <?php elseif ($ending_type === 'tragic'): ?>
                <p>The eclipse becomes permanent. Valdris falls silent. Those who remember your name speak it only as a cautionary tale — the seeker who knelt when they should have stood.</p>
            <?php else: ?>
                <p>Your journey ends here, but the Crown remains shattered. Perhaps another seeker will rise where you fell.</p>
            <?php endif; ?>
        </div>

        <div class="ending-actions">
            <a href="leaderboard.php" class="btn btn-primary">Record Your Legend</a>
            <a href="reset.php" class="btn btn-link">Transcend</a>
        </div>
    </section>

    <!-- ── RIGHT: STATS ────────────────────────── -->
    <aside class="ending-stats">

        <div class="stat-card stat-card-persona">
            <div class="stat-card-row">
                <div>
                    <span class="stat-card-label">LEGACY PERSONA</span>
                    <span class="stat-card-value persona-name"><?= clean($persona) ?></span>
                </div>
                <div class="stat-card-right">
                    <span class="stat-card-label">FINAL SCORE</span>
                    <span class="stat-card-value score-value"><?= number_format($final_score) ?></span>
                </div>
            </div>
            <div class="persona-bars">
                <div class="persona-bar-row">
                    <span class="bar-name">MERCY</span>
                    <div class="bar-track"><div class="bar-fill bar-mercy" style="width:<?= min(100, max(5, 50 + $alignment * 5)) ?>%"></div></div>
                    <span class="bar-name">AUTHORITY</span>
                </div>
                <div class="persona-bar-row">
                    <span class="bar-name">STRATEGIC</span>
                    <div class="bar-track"><div class="bar-fill bar-strategy" style="width:<?= min(100, max(5, count($visited) / max(1, $total_nodes) * 100)) ?>%"></div></div>
                    <span class="bar-name">PRIMAL</span>
                </div>
            </div>
        </div>

        <div class="stat-card-row stat-card-pair">
            <div class="stat-card">
                <span class="stat-card-label">NODES EXPLORED</span>
                <span class="stat-card-big"><?= $nodes_pct ?><sup>%</sup></span>
            </div>
            <div class="stat-card">
                <span class="stat-card-label">LOCKED PATHS</span>
                <span class="stat-card-big"><?= $locked_count ?></span>
            </div>
        </div>

        <div class="stat-card stat-card-hero">
            <span class="stat-card-label">HERO</span>
            <span class="stat-hero-name"><?= clean($hero['name']) ?></span>
            <span class="stat-hero-class"><?= ucfirst($class) ?> &middot; <?= $class_stats[$class]['archetype'] ?></span>
            <div class="stat-hero-stats">
                <span>HP <?= $hero['hp'] ?>/<?= $class_stats[$class]['hp'] ?></span>
                <span>STR <?= $hero['str'] ?></span>
                <span>WIS <?= $hero['wis'] ?></span>
            </div>
        </div>

        <?php if (!empty($inventory)): ?>
        <div class="stat-card stat-card-relics">
            <span class="stat-card-label">REMAINING RELICS</span>
            <?php foreach ($inventory as $item): ?>
                <div class="relic-row">
                    <span class="relic-icon">&#9670;</span>
                    <span class="relic-name"><?= clean($item) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </aside>
</div>

</body>
</html>