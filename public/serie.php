<?php
require __DIR__ . '/../src/bootstrap.php';

$serie = serie_by_slug(input('s'));
if (!$serie) {
    not_found('Cette série n\'existe pas.');
}

$user = current_user();
$saisons = episodes_par_saison($serie['id']);
$saison = (int) input('saison', '1');
if (!isset($saisons[$saison])) {
    $saison = (int) array_key_first($saisons);
}
$vus = $user ? episodes_vus($user['id'], $serie['id']) : [];
$favori = $user ? est_favori($user['id'], $serie['id']) : false;
$creneaux = planning_serie($serie['id']);
$suivant = $user ? prochain_episode($user['id'], $serie['id']) : null;
$premier = $saisons ? reset($saisons)[0] : null;
$similaires = array_values(array_filter(series_list(['genre' => $serie['genre']]), fn ($s) => $s['id'] !== $serie['id']));
if (count($similaires) < 4) {
    $autres = array_filter(series_list(['tri' => 'recent']), fn ($s) => $s['id'] !== $serie['id'] && $s['genre'] !== $serie['genre']);
    $similaires = array_slice(array_merge($similaires, $autres), 0, 6);
}
$progression = $serie['nb_episodes'] ? (int) round(count($vus) / $serie['nb_episodes'] * 100) : 0;

$title = $serie['titre'];
require __DIR__ . '/../templates/header.php';
?>
<section class="hero hero-serie" style="--h:<?= (int) $serie['teinte'] ?>">
    <div class="hero-inner">
        <?= poster($serie, 'poster-hero') ?>
        <div class="hero-text">
            <p class="eyebrow"><a href="catalogue.php?genre=<?= urlencode($serie['genre']) ?>"><?= e($serie['genre']) ?></a></p>
            <h1><?= e($serie['titre']) ?></h1>
            <p class="hero-meta">
                <span><?= e($serie['annee']) ?></span>
                <span><?= (int) $serie['nb_saisons'] ?> saison<?= $serie['nb_saisons'] > 1 ? 's' : '' ?></span>
                <span><?= (int) $serie['nb_episodes'] ?> épisodes</span>
                <span class="<?= $serie['statut'] === 'En cours' ? 'is-live' : '' ?>"><?= e($serie['statut']) ?></span>
            </p>
            <p class="hero-synopsis"><?= e($serie['synopsis']) ?></p>

            <?php if ($user && $vus): ?>
                <div class="serie-progress">
                    <span class="progress"><span style="width:<?= $progression ?>%"></span></span>
                    <span class="muted"><?= count($vus) ?> / <?= (int) $serie['nb_episodes'] ?> épisodes vus</span>
                </div>
            <?php endif; ?>

            <div class="actions">
                <?php if ($suivant && $vus): ?>
                    <a class="btn btn-lg" href="regarder.php?e=<?= (int) $suivant['id'] ?>">▶ Reprendre <?= e(ep_code($suivant)) ?></a>
                <?php elseif ($premier && (!$user || !$vus)): ?>
                    <a class="btn btn-lg" href="regarder.php?e=<?= (int) $premier['id'] ?>">▶ Regarder S1 · É1</a>
                <?php elseif ($premier): ?>
                    <a class="btn btn-lg" href="regarder.php?e=<?= (int) $premier['id'] ?>">↺ Revoir depuis le début</a>
                <?php endif; ?>

                <?php if ($user): ?>
                    <form action="favori.php" method="post" data-favori>
                        <?= csrf_field() ?>
                        <input type="hidden" name="serie_id" value="<?= (int) $serie['id'] ?>">
                        <input type="hidden" name="retour" value="serie.php?s=<?= e($serie['slug']) ?>">
                        <button class="btn btn-ghost btn-lg" type="submit" aria-pressed="<?= $favori ? 'true' : 'false' ?>"><?= $favori ? '✓ Dans ma liste' : '+ Ma liste' ?></button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-ghost btn-lg" href="connexion.php?retour=<?= urlencode('serie.php?s=' . $serie['slug']) ?>">+ Ma liste</a>
                <?php endif; ?>
            </div>

            <?php if ($creneaux): ?>
                <p class="airing">
                    <strong>Nouveaux épisodes :</strong>
                    <?= e(implode(' · ', array_map(fn ($c) => JOURS[$c['jour']] . ' ' . $c['heure'], $creneaux))) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="page">
    <section class="episodes" id="episodes">
        <div class="row-head">
            <h2>Épisodes</h2>
            <?php if (count($saisons) > 1): ?>
                <nav class="tabs" aria-label="Saisons">
                    <?php foreach (array_keys($saisons) as $n): ?>
                        <a href="serie.php?s=<?= e($serie['slug']) ?>&amp;saison=<?= $n ?>#episodes" <?= $n === $saison ? 'aria-current="true"' : '' ?>>Saison <?= $n ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>

        <ol class="episode-list">
            <?php foreach ($saisons[$saison] ?? [] as $ep): $vu = isset($vus[(int) $ep['id']]); ?>
                <li class="episode <?= $vu ? 'is-seen' : '' ?>">
                    <a class="episode-thumb" href="regarder.php?e=<?= (int) $ep['id'] ?>" style="--h:<?= (int) $serie['teinte'] ?>" aria-label="Regarder l'épisode <?= (int) $ep['numero'] ?>">
                        <span class="episode-num"><?= (int) $ep['numero'] ?></span><span class="play">▶</span>
                    </a>
                    <div class="episode-info">
                        <a href="regarder.php?e=<?= (int) $ep['id'] ?>" class="episode-title"><?= e($ep['titre']) ?></a>
                        <span class="muted"><?= e(ep_code($ep)) ?> · <?= (int) $ep['duree_min'] ?> min</span>
                    </div>
                    <?php if ($user): ?>
                        <form action="vu.php" method="post" data-vu>
                            <?= csrf_field() ?>
                            <input type="hidden" name="episode_id" value="<?= (int) $ep['id'] ?>">
                            <input type="hidden" name="vu" value="<?= $vu ? '0' : '1' ?>">
                            <input type="hidden" name="retour" value="serie.php?s=<?= e($serie['slug']) ?>&saison=<?= $saison ?>">
                            <button type="submit" class="seen-toggle" aria-pressed="<?= $vu ? 'true' : 'false' ?>" title="<?= $vu ? 'Marquer comme non vu' : 'Marquer comme vu' ?>">
                                <span class="sr-only"><?= $vu ? 'Vu' : 'Non vu' ?></span>✓
                            </button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <?= rangee('Vous aimerez aussi', $similaires) ?>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
