<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$liste = favoris($user['id']);
$historique = historique($user['id'], 12);

$title = 'Ma liste';
require __DIR__ . '/../templates/header.php';
?>
<section class="page">
    <div class="page-head">
        <p class="eyebrow">Ma liste</p>
        <h1>Les séries que je suis</h1>
    </div>

    <?php if ($liste): ?>
        <div class="grid">
            <?php foreach ($liste as $s): ?>
                <?php
                $vus = count(episodes_vus($user['id'], $s['id']));
                $pct = $s['nb_episodes'] ? (int) round($vus / $s['nb_episodes'] * 100) : 0;
                $extra = '<span class="progress"><span style="width:' . $pct . '%"></span></span>'
                    . '<span class="card-meta">' . $vus . ' / ' . (int) $s['nb_episodes'] . ' épisodes vus</span>';
                ?>
                <?= serie_card($s, $extra) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Votre liste est vide pour l'instant.</p>
            <a class="btn" href="catalogue.php">Parcourir le catalogue</a>
        </div>
    <?php endif; ?>

    <?php if ($historique): ?>
        <section class="history">
            <div class="row-head"><h2>Vus récemment</h2></div>
            <ol class="history-list">
                <?php foreach ($historique as $h): ?>
                    <li>
                        <span class="dot" style="--h:<?= (int) $h['teinte'] ?>"></span>
                        <a href="regarder.php?e=<?= (int) $h['episode_id'] ?>"><strong><?= e($h['serie_titre']) ?></strong> · <?= e(ep_code($h)) ?> — <?= e($h['titre']) ?></a>
                        <time class="muted" datetime="<?= e($h['vu_le']) ?>"><?= e(date_fr($h['vu_le'])) ?></time>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
