<?php

const USERS_FILE  = __DIR__ . '/users.json';
const SCORES_FILE = __DIR__ . '/scores.json';

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

function getAlignmentHint(int $score): string {
    if ($score >= 10) return 'The light of Valdris burns bright within you. A heroic fate beckons.';
    if ($score >= 5)  return 'Your path leans toward the just. The Crown may yet be restored.';
    if ($score >= 0)  return 'The scales are balanced. Your next choice will tip them.';
    if ($score >= -5) return 'Shadows gather at the edges of your vision. Be wary of the dark.';
    return 'The eclipse whispers your name. Power — or ruin — awaits.';
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