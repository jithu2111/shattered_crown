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