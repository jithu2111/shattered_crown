<?php
$page_title = $page_title ?? 'The Shattered Crown';
$body_class = $body_class ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= htmlspecialchars($body_class) ?>">
<header class="site-header">
    <div class="brand">
        <span class="brand-mark">&#9733;</span>
        <span class="brand-name">THE SHATTERED CROWN</span>
    </div>
    <div class="header-meta">
        <span class="meta-line">14 DAYS UNTIL ECLIPSE</span>
    </div>
</header>
<main class="site-main">