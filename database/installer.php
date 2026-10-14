<?php
/**
 * Création du schéma et insertion des données de démonstration.
 * Utilisé par database/init.php (ligne de commande) et automatiquement
 * au premier lancement quand la base SQLite n'existe pas encore.
 */

require_once __DIR__ . '/../src/helpers.php';

function install_database(PDO $pdo, string $driver, bool $withDemo = true): void
{
    $schema = file_get_contents(__DIR__ . "/schema.$driver.sql");
    foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
        $pdo->exec($statement);
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM series')->fetchColumn();
    if ($count > 0 || !$withDemo) {
        return;
    }

    $data = require __DIR__ . '/seed.php';
    $now = date('Y-m-d H:i:s');

    $pdo->beginTransaction();

    $insSerie = $pdo->prepare('INSERT INTO series (slug, titre, synopsis, genre, annee, statut, teinte, cree_le) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $insEp = $pdo->prepare('INSERT INTO episodes (serie_id, saison, numero, titre, duree_min) VALUES (?, ?, ?, ?, ?)');
    $insPlan = $pdo->prepare('INSERT INTO planning (serie_id, jour, heure, libelle) VALUES (?, ?, ?, ?)');

    $serieIds = [];
    foreach ($data['series'] as $i => $s) {
        $slug = slugify($s['titre']);
        // Dates de création décalées pour que « Nouveautés » ait un ordre stable.
        $created = date('Y-m-d H:i:s', strtotime("-{$i} days"));
        $insSerie->execute([$slug, $s['titre'], $s['synopsis'], $s['genre'], $s['annee'], $s['statut'], $s['teinte'], $created]);
        $serieId = (int) $pdo->lastInsertId();
        $serieIds[$slug] = $serieId;

        foreach ($s['saisons'] as $n => $titres) {
            foreach ($titres as $k => $titre) {
                $insEp->execute([$serieId, $n + 1, $k + 1, $titre, 22 + (($serieId + $k) % 5)]);
            }
        }
        foreach ($s['planning'] as [$jour, $heure]) {
            $insPlan->execute([$serieId, $jour, $heure, 'Nouvel épisode']);
        }
    }

    $insUser = $pdo->prepare('INSERT INTO utilisateurs (pseudo, email, mot_de_passe, role, cree_le) VALUES (?, ?, ?, ?, ?)');
    $userIds = [];
    foreach ($data['utilisateurs'] as $u) {
        $insUser->execute([$u['pseudo'], $u['email'], password_hash($u['mot_de_passe'], PASSWORD_DEFAULT), $u['role'], $now]);
        $userIds[$u['pseudo']] = (int) $pdo->lastInsertId();
    }

    $demoId = $userIds['demo'];
    $insFav = $pdo->prepare('INSERT INTO favoris (utilisateur_id, serie_id, ajoute_le) VALUES (?, ?, ?)');
    foreach ($data['demo']['favoris'] as $slug) {
        $insFav->execute([$demoId, $serieIds[$slug], $now]);
    }

    $findEp = $pdo->prepare('SELECT id FROM episodes WHERE serie_id = ? AND saison = ? AND numero <= ? ORDER BY numero');
    $insVu = $pdo->prepare('INSERT INTO visionnages (utilisateur_id, episode_id, vu_le) VALUES (?, ?, ?)');
    foreach ($data['demo']['vus'] as $k => [$slug, $saison, $jusqua]) {
        $findEp->execute([$serieIds[$slug], $saison, $jusqua]);
        foreach ($findEp->fetchAll(PDO::FETCH_COLUMN) as $j => $epId) {
            $insVu->execute([$demoId, $epId, date('Y-m-d H:i:s', strtotime("-{$k} hours +{$j} minutes"))]);
        }
    }

    $pdo->commit();
}
