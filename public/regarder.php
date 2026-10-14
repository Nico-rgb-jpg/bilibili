<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$ep = episode((int) input('e'));
if (!$ep) {
    not_found('Cet épisode n\'existe pas.');
}

$precedent = episode_precedent($ep);
$suivant = episode_suivant($ep);
$vus = episodes_vus($user['id'], (int) $ep['serie_id']);
$dejaVu = isset($vus[(int) $ep['id']]);
$saison = episodes_par_saison((int) $ep['serie_id'])[(int) $ep['saison']] ?? [];

$title = $ep['serie_titre'] . ' · ' . ep_code($ep);
require __DIR__ . '/../templates/header.php';
?>
<section class="page watch">
    <div class="watch-main">
        <!--
            Lecteur de démonstration : le projet ne diffuse pas de vraies vidéos.
            Pour brancher un vrai fichier, remplacer ce bloc par une balise <video>
            et conserver l'appel à vu.php à la fin de la lecture (événement "ended").
        -->
        <div class="player" style="--h:<?= (int) $ep['teinte'] ?>"
             data-player data-duration="<?= (int) $ep['duree_min'] * 60 ?>"
             data-episode="<?= (int) $ep['id'] ?>" data-csrf="<?= e(csrf_token()) ?>">
            <div class="player-screen">
                <p class="player-series"><?= e($ep['serie_titre']) ?></p>
                <p class="player-title"><?= e(ep_code($ep)) ?> — <?= e($ep['titre']) ?></p>
                <button class="player-big-play" type="button" data-play aria-label="Lecture">▶</button>
                <p class="player-note">Lecteur de démonstration · lecture accélérée</p>
            </div>
            <div class="player-controls">
                <button type="button" data-play aria-label="Lecture">▶</button>
                <span class="player-time" data-time>0:00</span>
                <div class="player-bar" data-bar role="slider" aria-label="Progression" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
                    <span data-fill></span>
                </div>
                <span class="player-time"><?= (int) $ep['duree_min'] ?>:00</span>
            </div>
        </div>

        <div class="watch-head">
            <div>
                <p class="eyebrow"><a href="serie.php?s=<?= e($ep['slug']) ?>"><?= e($ep['serie_titre']) ?></a></p>
                <h1><?= e($ep['titre']) ?></h1>
                <p class="muted"><?= e(ep_code($ep)) ?> · <?= (int) $ep['duree_min'] ?> min</p>
            </div>
            <form action="vu.php" method="post" data-vu>
                <?= csrf_field() ?>
                <input type="hidden" name="episode_id" value="<?= (int) $ep['id'] ?>">
                <input type="hidden" name="vu" value="<?= $dejaVu ? '0' : '1' ?>">
                <input type="hidden" name="retour" value="regarder.php?e=<?= (int) $ep['id'] ?>">
                <button class="btn btn-ghost seen-btn" type="submit" aria-pressed="<?= $dejaVu ? 'true' : 'false' ?>">
                    <?= $dejaVu ? '✓ Vu' : 'Marquer comme vu' ?>
                </button>
            </form>
        </div>

        <nav class="watch-nav">
            <?php if ($precedent): ?>
                <a class="btn btn-ghost" href="regarder.php?e=<?= (int) $precedent['id'] ?>">← <?= e(ep_code($precedent)) ?></a>
            <?php else: ?><span></span><?php endif; ?>
            <?php if ($suivant): ?>
                <a class="btn" href="regarder.php?e=<?= (int) $suivant['id'] ?>" data-next>Épisode suivant : <?= e(ep_code($suivant)) ?> →</a>
            <?php else: ?>
                <a class="btn btn-ghost" href="serie.php?s=<?= e($ep['slug']) ?>">Fin de la série · retour à la fiche</a>
            <?php endif; ?>
        </nav>
    </div>

    <aside class="watch-side">
        <h2>Saison <?= (int) $ep['saison'] ?></h2>
        <ol class="mini-list">
            <?php foreach ($saison as $item): ?>
                <li class="<?= (int) $item['id'] === (int) $ep['id'] ? 'is-current' : '' ?> <?= isset($vus[(int) $item['id']]) ? 'is-seen' : '' ?>">
                    <a href="regarder.php?e=<?= (int) $item['id'] ?>">
                        <span class="mini-num"><?= (int) $item['numero'] ?></span>
                        <span><?= e($item['titre']) ?></span>
                        <span class="mini-seen" aria-label="vu">✓</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </aside>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
