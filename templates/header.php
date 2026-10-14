<?php
/** @var string $title  Titre de la page */
$user = current_user();
$page = basename($_SERVER['SCRIPT_NAME'], '.php');
$nav = [
    'index' => 'Accueil',
    'catalogue' => 'Catalogue',
    'planning' => 'Planning',
];
if ($user) {
    $nav['ma-liste'] = 'Ma liste';
}
if ($user && $user['role'] === 'admin') {
    $nav['admin'] = 'Admin';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? '') ? $title . ' · ' . config('app.name') : config('app.name') . ' · ' . config('app.tagline')) ?></title>
    <meta name="description" content="Bilibili, plateforme de streaming de séries : catalogue, planning de diffusion et suivi de visionnage.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23ff5a3c'/><path d='M12 9l11 7-11 7z' fill='%23fff'/></svg>">
</head>
<body>
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="site-header">
    <div class="header-inner">
        <a class="logo" href="index.php"><span class="logo-mark">▶</span>bilibili</a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu" aria-label="Ouvrir le menu">
            <span></span><span></span><span></span>
        </button>

        <nav id="menu" class="nav">
            <ul class="nav-links">
                <?php foreach ($nav as $slug => $label): ?>
                    <li><a href="<?= $slug ?>.php" <?= $page === $slug ? 'aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>

            <form class="search" action="catalogue.php" method="get" role="search">
                <label class="sr-only" for="q-header">Rechercher une série</label>
                <input id="q-header" type="search" name="q" placeholder="Rechercher une série…" value="<?= $page === 'catalogue' ? e($_GET['q'] ?? '') : '' ?>">
            </form>

            <div class="nav-user">
                <?php if ($user): ?>
                    <a class="avatar-link" href="compte.php" <?= $page === 'compte' ? 'aria-current="page"' : '' ?>>
                        <span class="avatar"><?= e(mb_strtoupper(mb_substr($user['pseudo'], 0, 1))) ?></span>
                        <span><?= e($user['pseudo']) ?></span>
                    </a>
                    <form action="deconnexion.php" method="post">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost btn-sm" type="submit">Déconnexion</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-ghost btn-sm" href="connexion.php">Connexion</a>
                    <a class="btn btn-sm" href="inscription.php">S'inscrire</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<main id="contenu">
<?php $flashes = take_flashes(); if ($flashes): ?>
    <div class="flashes">
        <?php foreach ($flashes as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
