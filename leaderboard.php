<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/story.php';

$scores = loadScores();
$username = $_SESSION['user'];

// Sort all scores descending
usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

// Player's own history
$my_games = array_values(array_filter($scores, fn($s) => strcasecmp($s['username'], $username) === 0));

// Top 10 global
$top = array_slice($scores, 0, 10);

// Summary stats
$total_games  = count($scores);
$best_score   = !empty($scores) ? $scores[0]['score'] : 0;
$my_best      = !empty($my_games) ? $my_games[0]['score'] : 0;

$ending_colors = [
    'heroic' => 'ending-tag-heroic',
    'tragic' => 'ending-tag-tragic',
    'secret' => 'ending-tag-secret',
    'death'  => 'ending-tag-death',
];

$page_title = 'Hall of Legends &middot; The Shattered Crown';
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
<body class="page-leaderboard">

<header class="lb-bar">
    <span class="lb-bar-brand">VALDRIS</span>
    <nav class="lb-bar-nav">
        <a href="character.php">Play</a>
        <a href="leaderboard.php" class="active">Hall of Legends</a>
    </nav>
    <a href="logout.php" class="lb-bar-logout">&#8617; Quit</a>
</header>

<div class="lb-layout">

    <!-- ── SIDEBAR ──────────────────────────────── -->
    <aside class="lb-sidebar">
        <span class="lb-sidebar-label">VALDRIS ECLIPSE</span>
        <nav class="lb-sidebar-nav">
            <a class="lb-nav-link active">&#9733; Hall of Legends</a>
        </nav>
    </aside>

    <!-- ── MAIN CONTENT ─────────────────────────── -->
    <main class="lb-main">

        <h1 class="lb-title">Hall of Legends</h1>

        <!-- Summary row -->
        <?php if (!empty($my_games)): ?>
        <div class="lb-summary">
            <div class="lb-summary-card">
                <span class="lb-summary-label">Latest Ending</span>
                <span class="lb-summary-value <?= $ending_colors[$my_games[0]['ending']] ?? '' ?>"><?= ucfirst($my_games[0]['ending']) ?></span>
            </div>
            <div class="lb-summary-card">
                <span class="lb-summary-label">Your Best</span>
                <span class="lb-summary-value"><?= number_format($my_best) ?></span>
            </div>
            <div class="lb-summary-card">
                <span class="lb-summary-label">Total Runs</span>
                <span class="lb-summary-value"><?= count($my_games) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Your History -->
        <?php if (!empty($my_games)): ?>
        <section class="lb-section">
            <h2 class="lb-section-title">Your Journey Archive</h2>
            <div class="lb-table-wrap">
                <table class="lb-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Hero</th>
                            <th>Class</th>
                            <th>Persona</th>
                            <th>Ending</th>
                            <th>Nodes</th>
                            <th>Score</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_games as $i => $g): ?>
                        <tr>
                            <td class="rank"><?= $i + 1 ?></td>
                            <td class="hero-cell"><?= clean($g['hero'] ?? $g['username']) ?></td>
                            <td><?= ucfirst($g['class']) ?></td>
                            <td class="persona-cell"><?= clean($g['persona'] ?? '—') ?></td>
                            <td><span class="ending-tag <?= $ending_colors[$g['ending']] ?? '' ?>"><?= ucfirst($g['ending']) ?></span></td>
                            <td><?= $g['nodes'] ?? '—' ?></td>
                            <td class="score-cell"><?= number_format($g['score']) ?></td>
                            <td class="date-cell"><?= date('M j, g:i A', strtotime($g['date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <!-- Global Leaderboard -->
        <section class="lb-section">
            <h2 class="lb-section-title">Global Rankings</h2>
            <?php if (empty($top)): ?>
                <p class="lb-empty">No legends have been recorded yet. Be the first to complete the quest.</p>
            <?php else: ?>
            <div class="lb-table-wrap">
                <table class="lb-table lb-table-global">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player</th>
                            <th>Hero</th>
                            <th>Class</th>
                            <th>Persona</th>
                            <th>Ending</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top as $i => $g): ?>
                        <tr class="<?= strcasecmp($g['username'], $username) === 0 ? 'lb-row-you' : '' ?>">
                            <td class="rank">
                                <?php if ($i === 0): ?>
                                    <span class="rank-crown">&#9813;</span>
                                <?php elseif ($i === 1): ?>
                                    <span class="rank-silver">&#9734;</span>
                                <?php elseif ($i === 2): ?>
                                    <span class="rank-bronze">&#9734;</span>
                                <?php else: ?>
                                    <?= $i + 1 ?>
                                <?php endif; ?>
                            </td>
                            <td class="player-cell"><?= clean($g['username']) ?></td>
                            <td class="hero-cell"><?= clean($g['hero'] ?? '—') ?></td>
                            <td><?= ucfirst($g['class']) ?></td>
                            <td class="persona-cell"><?= clean($g['persona'] ?? '—') ?></td>
                            <td><span class="ending-tag <?= $ending_colors[$g['ending']] ?? '' ?>"><?= ucfirst($g['ending']) ?></span></td>
                            <td class="score-cell"><?= number_format($g['score']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

        <div class="lb-actions">
            <a href="reset.php" class="btn btn-primary">Play Again</a>
            <a href="logout.php" class="btn btn-link">Leave the Hall</a>
        </div>

    </main>
</div>

</body>
</html>