<?php
require __DIR__ . '/../src/bootstrap.php';

$user = current_user();
$une = serie_a_la_une();
$aujourdhui = planning_semaine()[jour_actuel()];
$continuer = $user ? continuer_a_regarder($user['id']) : [];
$maListe = $user ? favoris($user['id']) : [];
$nouveautes = series_list(['tri' => 'recent'], 10);

$enCours = series_list(['statut' => 'En cours', 'tri' => 'titre']);
$terminees = series_list(['statut' => 'Terminée', 'tri' => 'annee']);
$genres = genres();

$uneFavori = $user && $une ? est_favori($user['id'], $une['id']) : false;
$uneCommencee = $user && $une && episodes_vus($user['id'], $une['id']);
$uneSuivant = $une && $user ? prochain_episode($user['id'], $une['id']) : null;
if ($une && !$uneSuivant) {
    $uneSuivant = q('SELECT * FROM episodes WHERE serie_id = ? ORDER BY saison, numero LIMIT 1', [$une['id']])->fetch() ?: null;
}

$title = '';
require __DIR__ . '/../templates/header.php';
?>

<?php if ($une): ?>
<section class="hero" style="--h:<?= (int) $une['teinte'] ?>">
    <div class="hero-inner">
        <div class="hero-text">
            <p class="eyebrow">À la une aujourd'hui</p>
            <h1><?= e($une['titre']) ?></h1>
            <p class="hero-meta">
                <span><?= e($une['genre']) ?></span><span><?= e($une['annee']) ?></span>
                <span><?= (int) $une['nb_saisons'] ?> saison<?= $une['nb_saisons'] > 1 ? 's' : '' ?></span>
                <span><?= (int) $une['nb_episodes'] ?> épisodes</span>
            </p>
            <p class="hero-synopsis"><?= e($une['synopsis']) ?></p>
            <div class="actions">
                <?php if ($uneSuivant): ?>
                    <a class="btn btn-lg" href="regarder.php?e=<?= (int) $uneSuivant['id'] ?>">▶ <?= $uneCommencee ? 'Reprendre ' . e(ep_code($uneSuivant)) : 'Regarder' ?></a>
                <?php endif; ?>
                <?php if ($user): ?>
                    <form action="favori.php" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="serie_id" value="<?= (int) $une['id'] ?>">
                        <input type="hidden" name="retour" value="index.php">
                        <button class="btn btn-ghost btn-lg" type="submit"><?= $uneFavori ? '✓ Dans ma liste' : '+ Ma liste' ?></button>
                    </form>
                <?php endif; ?>
                <a class="btn btn-ghost btn-lg" href="serie.php?s=<?= e($une['slug']) ?>">Plus d'infos</a>
            </div>
        </div>
        <?= poster($une, 'poster-hero') ?>
    </div>
</section>
<?php endif; ?>

<div class="page">
    <section class="tonight">
        <div class="row-head">
            <h2>Au programme aujourd'hui <span class="muted">· <?= e(JOURS[jour_actuel()]) ?></span></h2>
            <a class="link" href="planning.php">Planning de la semaine →</a>
        </div>
        <?php if ($aujourdhui): ?>
            <ol class="timeline">
                <?php foreach ($aujourdhui as $c): ?>
                    <li class="<?= $c['heure'] <= date('H:i') ? 'is-past' : '' ?>">
                        <time><?= e($c['heure']) ?></time>
                        <a href="serie.php?s=<?= e($c['slug']) ?>" style="--h:<?= (int) $c['teinte'] ?>"><?= e($c['titre']) ?></a>
                        <span class="muted"><?= e($c['libelle']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="muted">Pas de sortie prévue aujourd'hui. Le catalogue complet reste disponible !</p>
        <?php endif; ?>
    </section>

    <?php if ($continuer): ?>
        <section class="row">
            <div class="row-head"><h2>Continuer à regarder</h2></div>
            <div class="row-track">
                <?php foreach ($continuer as $s): ?>
                    <a class="card" href="regarder.php?e=<?= (int) $s['suivant']['id'] ?>">
                        <?= poster($s) ?>
                        <span class="card-body">
                            <span class="card-title"><?= e($s['titre']) ?></span>
                            <span class="card-meta">Suivant : <?= e(ep_code($s['suivant'])) ?></span>
                            <span class="progress" role="progressbar" aria-valuenow="<?= $s['progression'] ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= $s['progression'] ?>%"></span></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?= rangee('Ma liste', $maListe, 'ma-liste.php') ?>
    <?= rangee('Nouveautés', $nouveautes, 'catalogue.php?tri=recent') ?>
    <?= rangee('En cours de diffusion', $enCours, 'catalogue.php?statut=En+cours') ?>
    <?= rangee('Séries complètes', $terminees, 'catalogue.php?statut=Termin%C3%A9e') ?>

    <section class="row">
        <div class="row-head"><h2>Parcourir par genre</h2></div>
        <nav class="genre-tiles" aria-label="Genres">
            <?php foreach ($genres as $i => $g): ?>
                <a class="genre-tile" style="--h:<?= ($i * 47 + 200) % 360 ?>" href="catalogue.php?genre=<?= urlencode($g['genre']) ?>">
                    <strong><?= e($g['genre']) ?></strong><span><?= (int) $g['nb'] ?> série<?= $g['nb'] > 1 ? 's' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>

    <?php if (!$user): ?>
        <section class="cta">
            <h2>Votre liste, votre progression, votre planning.</h2>
            <p class="muted">Créez un compte gratuit pour suivre vos séries épisode par épisode.</p>
            <a class="btn btn-lg" href="inscription.php">Créer un compte</a>
        </section>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
