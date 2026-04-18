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
$journey   = $_SESSION['choices_log'] ?? [];
$alignment_history = $_SESSION['alignment_history'] ?? [];
$suggestions = getAlternativePaths($nodes, $locked, $hero);

// Build alignment sparkline points (SVG 0..100 x, 0..40 y, y flipped so higher = up)
$spark_points = '';
$spark_area   = '';
if (!empty($alignment_history)) {
    $series = array_merge([0], $alignment_history);
    $n = count($series);
    $max_abs = max(1, max(array_map('abs', $series)));
    $pts = [];
    foreach ($series as $i => $v) {
        $x = $n > 1 ? round(($i / ($n - 1)) * 100, 2) : 50;
        $y = round(20 - ($v / $max_abs) * 18, 2);
        $pts[] = "$x,$y";
    }
    $spark_points = implode(' ', $pts);
    $spark_area   = "0,20 " . $spark_points . " 100,20";
}

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

        <?php if (!empty($suggestions)): ?>
        <div class="ending-alt-paths">
            <span class="alt-paths-label">PATHS NOT TAKEN</span>
            <p class="alt-paths-intro">The road you walked was one of many. Had fate been kinder — or your hand steadier — these doors might have opened:</p>
            <ul class="alt-paths-list">
                <?php
                    // Closest miss = first stat-gap suggestion with gap >= 1
                    $closest_idx = null;
                    foreach ($suggestions as $i => $s) {
                        if ($s['kind'] === 'stat' && $s['gap'] >= 1) { $closest_idx = $i; break; }
                    }
                ?>
                <?php foreach ($suggestions as $i => $s): ?>
                    <li class="alt-path <?= $i === $closest_idx ? 'alt-path-closest' : '' ?>">
                        <div class="alt-path-top">
                            <span class="alt-path-where"><?= clean($s['node_title']) ?></span>
                            <?php if ($i === $closest_idx): ?>
                                <span class="alt-path-badge">&#9733; SO CLOSE</span>
                            <?php endif; ?>
                            <?php if ($s['kind'] === 'stat'): ?>
                                <?php if ($s['gap'] > 0): ?>
                                    <span class="alt-path-gap"><?= $s['gap'] ?> <?= clean($s['stat']) ?> short</span>
                                <?php else: ?>
                                    <span class="alt-path-gap muted">requirement met &mdash; path unexplored</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="alt-path-gap">needed: <?= clean($s['item']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="alt-path-choice">&ldquo;<?= clean($s['choice_text']) ?>&rdquo;</span>
                        <?php if ($s['kind'] === 'stat'): ?>
                            <span class="alt-path-detail">You had <?= $s['have'] ?> <?= clean($s['stat']) ?> &middot; needed <?= $s['need'] ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($journey)): ?>
        <div class="ending-journey">
            <span class="journey-label">YOUR JOURNEY &mdash; <?= count($journey) ?> CHOICES</span>
            <ol class="journey-list">
                <?php foreach ($journey as $i => $step): ?>
                    <li class="journey-step">
                        <span class="journey-step-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                        <div class="journey-step-body">
                            <span class="journey-step-title"><?= clean($step['title'] ?? '—') ?></span>
                            <span class="journey-step-choice">&ldquo;<?= clean($step['choice'] ?? '') ?>&rdquo;</span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php endif; ?>

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

        <?php if (!empty($alignment_history)): ?>
        <div class="stat-card stat-card-alignment">
            <span class="stat-card-label">ALIGNMENT OVER TIME</span>
            <svg class="alignment-chart" viewBox="0 0 100 22" preserveAspectRatio="none" aria-hidden="true">
                <line class="align-axis" x1="0" y1="20" x2="100" y2="20"/>
                <polygon class="align-area" points="<?= $spark_area ?>"/>
                <polyline class="align-line" points="<?= $spark_points ?>"/>
            </svg>
            <div class="alignment-chart-foot">
                <span>Start</span>
                <span class="align-final <?= $alignment >= 0 ? 'pos' : 'neg' ?>">
                    Final: <?= $alignment > 0 ? '+' . $alignment : $alignment ?>
                </span>
                <span>End</span>
            </div>
        </div>
        <?php endif; ?>

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