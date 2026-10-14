<?php
require __DIR__ . '/../src/bootstrap.php';

require_admin();
$errors = [];

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'ajouter_serie':
            $titre = input('titre');
            $genre = input('genre');
            $annee = (int) input('annee');
            $statut = input('statut');
            $synopsis = input('synopsis');
            $teinte = max(0, min(359, (int) input('teinte')));
            $nbEp = max(0, min(50, (int) input('nb_episodes')));

            if (mb_strlen($titre) < 2 || mb_strlen($titre) > 120) $errors[] = 'Le titre doit faire entre 2 et 120 caractères.';
            if (mb_strlen($genre) < 2 || mb_strlen($genre) > 40) $errors[] = 'Genre invalide.';
            if ($annee < 1950 || $annee > (int) date('Y') + 2) $errors[] = 'Année invalide.';
            if (!in_array($statut, STATUTS, true)) $errors[] = 'Statut invalide.';
            if (mb_strlen($synopsis) < 20) $errors[] = 'Le synopsis doit faire au moins 20 caractères.';

            $slug = slugify($titre);
            if (!$errors && q('SELECT 1 FROM series WHERE slug = ?', [$slug])->fetchColumn()) {
                $errors[] = 'Une série avec ce titre existe déjà.';
            }
            if (!$errors) {
                db()->beginTransaction();
                q('INSERT INTO series (slug, titre, synopsis, genre, annee, statut, teinte, cree_le) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [$slug, $titre, $synopsis, $genre, $annee, $statut, $teinte, db_now()]);
                $id = (int) db()->lastInsertId();
                for ($i = 1; $i <= $nbEp; $i++) {
                    q('INSERT INTO episodes (serie_id, saison, numero, titre, duree_min) VALUES (?, 1, ?, ?, 24)', [$id, $i, 'Épisode ' . $i]);
                }
                db()->commit();
                flash('success', "Série « $titre » ajoutée.");
                redirect('admin.php');
            }
            break;

        case 'ajouter_episode':
            $serieId = (int) input('serie_id');
            $saison = max(1, (int) input('saison'));
            $titre = input('titre');
            $duree = max(1, min(300, (int) input('duree')));
            if (!q('SELECT 1 FROM series WHERE id = ?', [$serieId])->fetchColumn()) $errors[] = 'Série introuvable.';
            if (mb_strlen($titre) < 1 || mb_strlen($titre) > 150) $errors[] = 'Titre d\'épisode invalide.';
            if (!$errors) {
                $numero = 1 + (int) q('SELECT COALESCE(MAX(numero), 0) FROM episodes WHERE serie_id = ? AND saison = ?', [$serieId, $saison])->fetchColumn();
                q('INSERT INTO episodes (serie_id, saison, numero, titre, duree_min) VALUES (?, ?, ?, ?, ?)', [$serieId, $saison, $numero, $titre, $duree]);
                flash('success', "Épisode S{$saison} · É{$numero} ajouté.");
                redirect('admin.php#episodes');
            }
            break;

        case 'ajouter_creneau':
            $serieId = (int) input('serie_id');
            $jour = (int) input('jour');
            $heure = input('heure');
            $libelle = input('libelle') ?: 'Nouvel épisode';
            if (!q('SELECT 1 FROM series WHERE id = ?', [$serieId])->fetchColumn()) $errors[] = 'Série introuvable.';
            if (!isset(JOURS[$jour])) $errors[] = 'Jour invalide.';
            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $heure)) $errors[] = 'Heure invalide (format HH:MM).';
            if (mb_strlen($libelle) > 80) $errors[] = 'Libellé trop long.';
            if (!$errors) {
                q('INSERT INTO planning (serie_id, jour, heure, libelle) VALUES (?, ?, ?, ?)', [$serieId, $jour, $heure, $libelle]);
                flash('success', 'Créneau ajouté au planning.');
                redirect('admin.php#planning');
            }
            break;

        case 'supprimer_creneau':
            q('DELETE FROM planning WHERE id = ?', [(int) input('id')]);
            flash('success', 'Créneau supprimé.');
            redirect('admin.php#planning');

        case 'supprimer_serie':
            $serie = q('SELECT titre FROM series WHERE id = ?', [(int) input('id')])->fetch();
            if ($serie) {
                q('DELETE FROM series WHERE id = ?', [(int) input('id')]);
                flash('success', '« ' . $serie['titre'] . ' » supprimée (épisodes et créneaux compris).');
            }
            redirect('admin.php');
    }
}

$stats = q('SELECT (SELECT COUNT(*) FROM utilisateurs) AS utilisateurs, (SELECT COUNT(*) FROM series) AS series,
            (SELECT COUNT(*) FROM episodes) AS episodes, (SELECT COUNT(*) FROM visionnages) AS vues')->fetch();
$series = series_list(['tri' => 'titre']);
$creneaux = q('SELECT p.*, s.titre FROM planning p JOIN series s ON s.id = p.serie_id ORDER BY p.jour, p.heure')->fetchAll();
$genres = array_column(genres(), 'genre');
// Ré-affiche la saisie du formulaire « Ajouter une série » en cas d'erreur.
$retenu = fn (string $champ) => ($_POST['action'] ?? '') === 'ajouter_serie' ? input($champ) : '';

$title = 'Administration';
require __DIR__ . '/../templates/header.php';
?>
<section class="page">
    <div class="page-head">
        <p class="eyebrow">Administration</p>
        <h1>Gérer le catalogue</h1>
    </div>

    <div class="stats">
        <div class="stat"><strong><?= (int) $stats['utilisateurs'] ?></strong><span>utilisateurs</span></div>
        <div class="stat"><strong><?= (int) $stats['series'] ?></strong><span>séries</span></div>
        <div class="stat"><strong><?= (int) $stats['episodes'] ?></strong><span>épisodes</span></div>
        <div class="stat"><strong><?= (int) $stats['vues'] ?></strong><span>visionnages</span></div>
    </div>

    <?= form_errors($errors) ?>

    <div class="admin-grid">
        <div class="panel">
            <h2>Ajouter une série</h2>
            <form method="post" class="form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="ajouter_serie">
                <div class="field"><label for="titre">Titre</label><input id="titre" name="titre" required maxlength="120" value="<?= e($retenu('titre')) ?>"></div>
                <div class="field-row">
                    <div class="field">
                        <label for="genre">Genre</label>
                        <input id="genre" name="genre" list="genres" required maxlength="40" value="<?= e($retenu('genre')) ?>">
                        <datalist id="genres"><?php foreach ($genres as $g): ?><option value="<?= e($g) ?>"><?php endforeach; ?></datalist>
                    </div>
                    <div class="field"><label for="annee">Année</label><input id="annee" type="number" name="annee" value="<?= date('Y') ?>" required></div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut"><?php foreach (STATUTS as $st): ?><option><?= e($st) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="field"><label for="nb_episodes">Épisodes (saison 1)</label><input id="nb_episodes" type="number" name="nb_episodes" min="0" max="50" value="6"></div>
                </div>
                <div class="field">
                    <label for="teinte">Couleur de l'affiche</label>
                    <input id="teinte" type="range" name="teinte" min="0" max="359" value="220" data-hue>
                    <div class="hue-preview" data-hue-preview style="--h:220"></div>
                </div>
                <div class="field"><label for="synopsis">Synopsis</label><textarea id="synopsis" name="synopsis" rows="4" required minlength="20"><?= e($retenu('synopsis')) ?></textarea></div>
                <button class="btn" type="submit">Ajouter la série</button>
            </form>
        </div>

        <div>
            <div class="panel" id="episodes">
                <h2>Ajouter un épisode</h2>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="ajouter_episode">
                    <div class="field">
                        <label for="ep-serie">Série</label>
                        <select id="ep-serie" name="serie_id"><?php foreach ($series as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['titre']) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="field-row">
                        <div class="field"><label for="ep-saison">Saison</label><input id="ep-saison" type="number" name="saison" min="1" value="1"></div>
                        <div class="field"><label for="ep-duree">Durée (min)</label><input id="ep-duree" type="number" name="duree" min="1" max="300" value="24"></div>
                    </div>
                    <div class="field"><label for="ep-titre">Titre de l'épisode</label><input id="ep-titre" name="titre" required maxlength="150"></div>
                    <button class="btn" type="submit">Ajouter l'épisode</button>
                </form>
            </div>

            <div class="panel" id="planning">
                <h2>Ajouter un créneau au planning</h2>
                <form method="post" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="ajouter_creneau">
                    <div class="field">
                        <label for="pl-serie">Série</label>
                        <select id="pl-serie" name="serie_id"><?php foreach ($series as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['titre']) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="pl-jour">Jour</label>
                            <select id="pl-jour" name="jour"><?php foreach (JOURS as $n => $j): ?><option value="<?= $n ?>"><?= e($j) ?></option><?php endforeach; ?></select>
                        </div>
                        <div class="field"><label for="pl-heure">Heure</label><input id="pl-heure" type="time" name="heure" value="20:00" required></div>
                    </div>
                    <div class="field"><label for="pl-libelle">Libellé</label><input id="pl-libelle" name="libelle" placeholder="Nouvel épisode" maxlength="80"></div>
                    <button class="btn" type="submit">Ajouter le créneau</button>
                </form>
            </div>
        </div>
    </div>

    <div class="panel">
        <h2>Créneaux du planning</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Jour</th><th>Heure</th><th>Série</th><th>Libellé</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($creneaux as $c): ?>
                    <tr>
                        <td><?= e(JOURS[$c['jour']]) ?></td><td><?= e($c['heure']) ?></td><td><?= e($c['titre']) ?></td><td><?= e($c['libelle']) ?></td>
                        <td class="right">
                            <form method="post" data-confirm="Supprimer ce créneau ?">
                                <?= csrf_field() ?><input type="hidden" name="action" value="supprimer_creneau"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button class="btn btn-ghost btn-sm" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h2>Séries</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Titre</th><th>Genre</th><th>Année</th><th>Statut</th><th>Épisodes</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($series as $s): ?>
                    <tr>
                        <td><a href="serie.php?s=<?= e($s['slug']) ?>"><?= e($s['titre']) ?></a></td>
                        <td><?= e($s['genre']) ?></td><td><?= (int) $s['annee'] ?></td><td><?= e($s['statut']) ?></td><td><?= (int) $s['nb_episodes'] ?></td>
                        <td class="right">
                            <form method="post" data-confirm="Supprimer « <?= e($s['titre']) ?> » et tous ses épisodes ?">
                                <?= csrf_field() ?><input type="hidden" name="action" value="supprimer_serie"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                <button class="btn btn-ghost btn-sm" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
