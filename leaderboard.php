<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/data/story.php';

$scores = loadScores();
$username = $_SESSION['user'];

// Filter + sort inputs (whitelisted)
$allowed_endings = ['all', 'heroic', 'tragic', 'secret', 'pyrrhic', 'death'];
$allowed_classes = ['all', 'warrior', 'mage', 'rogue'];
$allowed_sorts   = ['score_desc', 'score_asc', 'date_desc', 'date_asc'];

$filter_ending = in_array($_GET['ending'] ?? 'all', $allowed_endings, true) ? $_GET['ending'] ?? 'all' : 'all';
$filter_class  = in_array($_GET['class']  ?? 'all', $allowed_classes, true) ? $_GET['class']  ?? 'all' : 'all';
$sort_mode     = in_array($_GET['sort']   ?? 'score_desc', $allowed_sorts, true) ? $_GET['sort'] ?? 'score_desc' : 'score_desc';

// Player's own history — score desc for the table
$my_games = array_values(array_filter($scores, fn($s) => strcasecmp($s['username'], $username) === 0));
usort($my_games, fn($a, $b) => $b['score'] <=> $a['score']);

// Latest run (by date) for the summary card
$my_latest = $my_games;
usort($my_latest, fn($a, $b) => strtotime($b['date'] ?? '0') <=> strtotime($a['date'] ?? '0'));
$my_latest = $my_latest[0] ?? null;

// Global list: apply filters, then sort
$global = $scores;
if ($filter_ending !== 'all') {
    $global = array_filter($global, fn($s) => ($s['ending'] ?? '') === $filter_ending);
}
if ($filter_class !== 'all') {
    $global = array_filter($global, fn($s) => ($s['class'] ?? '') === $filter_class);
}
$global = array_values($global);

usort($global, function ($a, $b) use ($sort_mode) {
    return match ($sort_mode) {
        'score_asc'  => $a['score'] <=> $b['score'],
        'date_desc'  => strtotime($b['date'] ?? '0') <=> strtotime($a['date'] ?? '0'),
        'date_asc'   => strtotime($a['date'] ?? '0') <=> strtotime($b['date'] ?? '0'),
        default      => $b['score'] <=> $a['score'],
    };
});

$top = array_slice($global, 0, 10);

// Summary stats
$total_games  = count($scores);
$best_score   = !empty($scores) ? $scores[0]['score'] : 0;
$my_best      = !empty($my_games) ? $my_games[0]['score'] : 0;

$ending_colors = [
    'heroic'  => 'ending-tag-heroic',
    'tragic'  => 'ending-tag-tragic',
    'secret'  => 'ending-tag-secret',
    'pyrrhic' => 'ending-tag-pyrrhic',
    'death'   => 'ending-tag-death',
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
                <span class="lb-summary-value <?= $ending_colors[$my_latest['ending']] ?? '' ?>"><?= ucfirst($my_latest['ending']) ?></span>
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

            <form method="GET" action="leaderboard.php" class="lb-filters">
                <label class="lb-filter">
                    <span class="lb-filter-label">Ending</span>
                    <select name="ending">
                        <option value="all"     <?= $filter_ending === 'all'     ? 'selected' : '' ?>>All</option>
                        <option value="heroic"  <?= $filter_ending === 'heroic'  ? 'selected' : '' ?>>Heroic</option>
                        <option value="tragic"  <?= $filter_ending === 'tragic'  ? 'selected' : '' ?>>Tragic</option>
                        <option value="secret"  <?= $filter_ending === 'secret'  ? 'selected' : '' ?>>Secret</option>
                        <option value="pyrrhic" <?= $filter_ending === 'pyrrhic' ? 'selected' : '' ?>>Pyrrhic</option>
                        <option value="death"   <?= $filter_ending === 'death'   ? 'selected' : '' ?>>Death</option>
                    </select>
                </label>
                <label class="lb-filter">
                    <span class="lb-filter-label">Class</span>
                    <select name="class">
                        <option value="all"     <?= $filter_class === 'all'     ? 'selected' : '' ?>>All</option>
                        <option value="warrior" <?= $filter_class === 'warrior' ? 'selected' : '' ?>>Warrior</option>
                        <option value="mage"    <?= $filter_class === 'mage'    ? 'selected' : '' ?>>Mage</option>
                        <option value="rogue"   <?= $filter_class === 'rogue'   ? 'selected' : '' ?>>Rogue</option>
                    </select>
                </label>
                <label class="lb-filter">
                    <span class="lb-filter-label">Sort</span>
                    <select name="sort">
                        <option value="score_desc" <?= $sort_mode === 'score_desc' ? 'selected' : '' ?>>Score &mdash; High to Low</option>
                        <option value="score_asc"  <?= $sort_mode === 'score_asc'  ? 'selected' : '' ?>>Score &mdash; Low to High</option>
                        <option value="date_desc"  <?= $sort_mode === 'date_desc'  ? 'selected' : '' ?>>Date &mdash; Newest First</option>
                        <option value="date_asc"   <?= $sort_mode === 'date_asc'   ? 'selected' : '' ?>>Date &mdash; Oldest First</option>
                    </select>
                </label>
                <button type="submit" class="lb-filter-apply">Apply</button>
                <?php if ($filter_ending !== 'all' || $filter_class !== 'all' || $sort_mode !== 'score_desc'): ?>
                    <a href="leaderboard.php" class="lb-filter-clear">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (empty($top)): ?>
                <p class="lb-empty">No legends match these filters. Try relaxing the criteria.</p>
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