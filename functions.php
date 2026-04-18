<?php

const USERS_FILE  = __DIR__ . '/users.json';
const SCORES_FILE = __DIR__ . '/scores.json';
const SAVES_FILE  = __DIR__ . '/saves.json';

function loadUsers(): array {
    if (!file_exists(USERS_FILE)) return [];
    $raw = file_get_contents(USERS_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
}

function findUser(string $username): ?array {
    foreach (loadUsers() as $u) {
        if (strcasecmp($u['username'], $username) === 0) return $u;
    }
    return null;
}

function loadScores(): array {
    if (!file_exists(SCORES_FILE)) return [];
    $raw = file_get_contents(SCORES_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveScore(array $entry): void {
    $scores = loadScores();
    $scores[] = $entry;
    file_put_contents(SCORES_FILE, json_encode($scores, JSON_PRETTY_PRINT), LOCK_EX);
}

function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function requireHero(): void {
    if (!isset($_SESSION['hero']) || !isset($_SESSION['node'])) {
        header('Location: character.php');
        exit;
    }
}

function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function csrfCheck(): bool {
    $sent = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    return is_string($sent) && $known !== '' && hash_equals($known, $sent);
}

function getNodeText(array $node, string $class): string {
    $key = 'text_' . $class;
    return $node[$key] ?? $node['text_warrior'];
}

function applyStatChanges(array $changes): void {
    foreach ($changes as $stat => $delta) {
        if (isset($_SESSION['hero'][$stat])) {
            $_SESSION['hero'][$stat] += $delta;
        }
    }
}

function isDead(): bool {
    return ($_SESSION['hero']['hp'] ?? 1) <= 0;
}

function canChoose(array $choice): bool {
    if ($choice['required_stat'] !== null) {
        $val = $_SESSION['hero'][$choice['required_stat']] ?? 0;
        if ($val < $choice['required_val']) return false;
    }
    if ($choice['required_item'] !== null) {
        if (!in_array($choice['required_item'], $_SESSION['hero']['inventory'] ?? [], true)) return false;
    }
    return true;
}

function getLockReason(array $choice): string {
    if ($choice['required_stat'] !== null) {
        $have = $_SESSION['hero'][$choice['required_stat']] ?? 0;
        $need = $choice['required_val'];
        $stat = strtoupper($choice['required_stat']);
        return "Requires $stat $need — you have $have";
    }
    if ($choice['required_item'] !== null) {
        return "Requires: " . $choice['required_item'];
    }
    return 'Locked';
}

function getEndingPrediction(int $alignment, int $nodes_visited): array {
    $abs = abs($alignment);
    $progress = min($nodes_visited / 17, 1.0);
    $confidence = (int) round(max(20, min(95, $abs * 8 + $progress * 40)));

    if ($alignment >= 6) {
        return [
            'ending'     => 'Heroic',
            'class'      => 'predictor-heroic',
            'confidence' => $confidence,
            'text'       => 'The Crown yearns to be whole. Your valor may restore it.',
        ];
    }
    if ($alignment <= -6) {
        return [
            'ending'     => 'Tragic',
            'class'      => 'predictor-tragic',
            'confidence' => $confidence,
            'text'       => 'Darkness coils around your fate. The eclipse draws near.',
        ];
    }
    if ($abs >= 3 && $alignment > 0) {
        return [
            'ending'     => 'Heroic',
            'class'      => 'predictor-heroic',
            'confidence' => $confidence,
            'text'       => 'A glimmer of light persists. The path of the just is still open.',
        ];
    }
    if ($abs >= 3 && $alignment < 0) {
        return [
            'ending'     => 'Tragic',
            'class'      => 'predictor-tragic',
            'confidence' => $confidence,
            'text'       => 'Shadows lengthen behind you. One more step into the dark…',
        ];
    }
    return [
        'ending'     => 'Secret',
        'class'      => 'predictor-secret',
        'confidence' => $confidence,
        'text'       => 'The scales are balanced — a hidden path may reveal itself.',
    ];
}

function getAlignmentHint(int $score): string {
    if ($score >= 10) return 'The light of Valdris burns bright within you. A heroic fate beckons.';
    if ($score >= 5)  return 'Your path leans toward the just. The Crown may yet be restored.';
    if ($score >= 0)  return 'The scales are balanced. Your next choice will tip them.';
    if ($score >= -5) return 'Shadows gather at the edges of your vision. Be wary of the dark.';
    return 'The eclipse whispers your name. Power — or ruin — awaits.';
}

function getArchetypeMatch(string $persona, string $ending_type, int $alignment): array {
    // Expected ending per persona (which ending their alignment trajectory points toward)
    $expected = [
        'The Justiciar' => 'heroic',
        'The Warden'    => 'heroic',
        'The Drifter'   => 'secret',
        'The Schemer'   => 'secret',
        'The Usurper'   => 'tragic',
    ];

    $expected_ending = $expected[$persona] ?? 'secret';
    $matched = ($expected_ending === $ending_type);

    // Confidence: how strongly the alignment supports the ending outcome
    $abs = abs($alignment);
    $base = $matched ? 70 : 30;
    $adjust = min(25, $abs * 3);
    if ($ending_type === 'death') {
        $base = 15;
        $adjust = 0;
    }
    $percent = max(5, min(99, $base + ($matched ? $adjust : -$adjust)));

    if ($matched) {
        $verdict = "Your actions rang true. $persona was always the name fate wrote.";
    } elseif ($ending_type === 'death') {
        $verdict = "The ending claimed you before your persona could settle. $persona was a path cut short.";
    } else {
        $verdict = "Your path defied your nature. $persona walked a road they were not meant to travel.";
    }

    return [
        'expected' => $expected_ending,
        'matched'  => $matched,
        'percent'  => (int) round($percent),
        'verdict'  => $verdict,
    ];
}

function getAlternativePaths(array $nodes, array $locked_log, array $hero): array {
    $suggestions = [];
    foreach ($locked_log as $key) {
        [$node_id, $choice_id] = array_pad(explode(':', $key, 2), 2, '');
        if (!isset($nodes[$node_id])) continue;
        $node = $nodes[$node_id];
        foreach ($node['choices'] as $c) {
            if ($c['id'] !== $choice_id) continue;

            if (!empty($c['required_stat'])) {
                $stat = $c['required_stat'];
                $need = (int) $c['required_val'];
                $have = (int) ($hero[$stat] ?? 0);
                $gap  = max(0, $need - $have);
                $suggestions[] = [
                    'node_title'  => $node['title'],
                    'choice_text' => $c['text'],
                    'kind'        => 'stat',
                    'stat'        => strtoupper($stat),
                    'gap'         => $gap,
                    'need'        => $need,
                    'have'        => $have,
                ];
            } elseif (!empty($c['required_item'])) {
                $suggestions[] = [
                    'node_title'  => $node['title'],
                    'choice_text' => $c['text'],
                    'kind'        => 'item',
                    'item'        => $c['required_item'],
                ];
            }
            break;
        }
    }

    usort($suggestions, function ($a, $b) {
        $ga = $a['kind'] === 'stat' ? $a['gap'] : PHP_INT_MAX;
        $gb = $b['kind'] === 'stat' ? $b['gap'] : PHP_INT_MAX;
        return $ga <=> $gb;
    });

    return array_slice($suggestions, 0, 4);
}

function calculateScore(): int {
    $hero = $_SESSION['hero'];
    $base = $hero['score'] ?? 500;
    $visited_bonus = count($hero['nodes_visited'] ?? []) * 25;
    $inventory_bonus = count($hero['inventory'] ?? []) * 15;
    $alignment = abs($_SESSION['alignment'] ?? 0) * 10;
    return $base + $visited_bonus + $inventory_bonus + $alignment;
}

function resetGame(): void {
    if (isset($_SESSION['user'])) {
        deleteSave($_SESSION['user']);
    }
    unset(
        $_SESSION['hero'],
        $_SESSION['node'],
        $_SESSION['choices_log'],
        $_SESSION['locked_log'],
        $_SESSION['alignment'],
        $_SESSION['alignment_history']
    );
    setcookie('sc_node', '', time() - 3600, '/');
}

// ── SAVE / LOAD GAME ──────────────────────────────

function loadAllSaves(): array {
    if (!file_exists(SAVES_FILE)) return [];
    $raw = file_get_contents(SAVES_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveAllSaves(array $saves): void {
    file_put_contents(SAVES_FILE, json_encode($saves, JSON_PRETTY_PRINT), LOCK_EX);
}

function saveGame(): void {
    if (!isset($_SESSION['user'], $_SESSION['hero'], $_SESSION['node'])) return;

    $saves = loadAllSaves();
    $saves[$_SESSION['user']] = [
        'hero'              => $_SESSION['hero'],
        'node'              => $_SESSION['node'],
        'choices_log'       => $_SESSION['choices_log'] ?? [],
        'locked_log'        => $_SESSION['locked_log'] ?? [],
        'alignment'         => $_SESSION['alignment'] ?? 0,
        'alignment_history' => $_SESSION['alignment_history'] ?? [],
        'saved_at'          => date('c'),
    ];
    saveAllSaves($saves);
}

function loadSave(string $username): ?array {
    $saves = loadAllSaves();
    return $saves[$username] ?? null;
}

function restoreSave(array $save): void {
    $_SESSION['hero']              = $save['hero'];
    $_SESSION['node']              = $save['node'];
    $_SESSION['choices_log']       = $save['choices_log'] ?? [];
    $_SESSION['locked_log']        = $save['locked_log'] ?? [];
    $_SESSION['alignment']         = $save['alignment'] ?? 0;
    $_SESSION['alignment_history'] = $save['alignment_history'] ?? [];
}

function deleteSave(string $username): void {
    $saves = loadAllSaves();
    unset($saves[$username]);
    saveAllSaves($saves);
}