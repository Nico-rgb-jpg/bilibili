<?php
require __DIR__ . '/../src/bootstrap.php';

$filtres = [
    'q' => mb_substr(input('q'), 0, 80),
    'genre' => input('genre'),
    'statut' => input('statut'),
    'tri' => in_array(input('tri'), ['recent', 'titre', 'annee'], true) ? input('tri') : 'titre',
];
$series = series_list($filtres);
$genres = genres();

/** Construit l'URL du catalogue en modifiant un seul filtre. */
function url_filtre(array $filtres, string $cle, string $valeur): string
{
    $filtres[$cle] = $valeur;
    return 'catalogue.php?' . http_build_query(array_filter($filtres));
}

$title = 'Catalogue';
require __DIR__ . '/../templates/header.php';
?>
<section class="page">
    <div class="page-head">
        <p class="eyebrow">Catalogue</p>
        <h1><?= $filtres['q'] !== '' ? 'Résultats pour « ' . e($filtres['q']) . ' »' : 'Toutes les séries' ?></h1>
    </div>

    <form class="filters" method="get" data-autosubmit>
        <div class="field">
            <label for="q">Recherche</label>
            <input id="q" type="search" name="q" value="<?= e($filtres['q']) ?>" placeholder="Titre, mot-clé…">
        </div>
        <div class="field">
            <label for="statut">Statut</label>
            <select id="statut" name="statut">
                <option value="">Tous</option>
                <?php foreach (STATUTS as $st): ?>
                    <option <?= $filtres['statut'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="tri">Trier par</label>
            <select id="tri" name="tri">
                <option value="titre" <?= $filtres['tri'] === 'titre' ? 'selected' : '' ?>>Titre (A → Z)</option>
                <option value="recent" <?= $filtres['tri'] === 'recent' ? 'selected' : '' ?>>Ajouts récents</option>
                <option value="annee" <?= $filtres['tri'] === 'annee' ? 'selected' : '' ?>>Année de sortie</option>
            </select>
        </div>
        <input type="hidden" name="genre" value="<?= e($filtres['genre']) ?>">
        <button class="btn btn-ghost" type="submit">Filtrer</button>
    </form>

    <nav class="chips" aria-label="Genres">
        <a class="chip" href="<?= e(url_filtre($filtres, 'genre', '')) ?>" <?= $filtres['genre'] === '' ? 'aria-current="true"' : '' ?>>Tous les genres</a>
        <?php foreach ($genres as $g): ?>
            <a class="chip" href="<?= e(url_filtre($filtres, 'genre', $g['genre'])) ?>" <?= $filtres['genre'] === $g['genre'] ? 'aria-current="true"' : '' ?>>
                <?= e($g['genre']) ?> <span><?= (int) $g['nb'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <p class="muted result-count"><?= count($series) ?> série<?= count($series) > 1 ? 's' : '' ?></p>

    <?php if ($series): ?>
        <div class="grid">
            <?php foreach ($series as $s): ?>
                <?= serie_card($s) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Aucune série ne correspond à votre recherche.</p>
            <a class="btn btn-ghost" href="catalogue.php">Réinitialiser les filtres</a>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
