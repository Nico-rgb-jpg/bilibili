<?php
/**
 * Accès aux données : séries, épisodes, planning, favoris et historique.
 */

const STATUTS = ['En cours', 'Terminée'];

const SELECT_SERIE = 'SELECT s.*,
    (SELECT COUNT(*) FROM episodes e WHERE e.serie_id = s.id) AS nb_episodes,
    (SELECT COUNT(DISTINCT e.saison) FROM episodes e WHERE e.serie_id = s.id) AS nb_saisons
    FROM series s';

/** Liste des séries avec filtres optionnels : q (recherche), genre, statut, tri. */
function series_list(array $f = [], ?int $limit = null): array
{
    $where = [];
    $params = [];

    if (!empty($f['q'])) {
        $where[] = '(LOWER(s.titre) LIKE LOWER(?) OR LOWER(s.synopsis) LIKE LOWER(?))';
        $like = '%' . $f['q'] . '%';
        array_push($params, $like, $like);
    }
    if (!empty($f['genre'])) {
        $where[] = 's.genre = ?';
        $params[] = $f['genre'];
    }
    if (!empty($f['statut']) && in_array($f['statut'], STATUTS, true)) {
        $where[] = 's.statut = ?';
        $params[] = $f['statut'];
    }

    $order = match ($f['tri'] ?? 'recent') {
        'titre' => 's.titre ASC',
        'annee' => 's.annee DESC, s.titre ASC',
        default => 's.cree_le DESC, s.id DESC',
    };

    $sql = SELECT_SERIE . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY $order";
    if ($limit) {
        $sql .= ' LIMIT ' . (int) $limit;
    }
    return q($sql, $params)->fetchAll();
}

function genres(): array
{
    return q('SELECT genre, COUNT(*) AS nb FROM series GROUP BY genre ORDER BY genre')->fetchAll();
}

function serie_by_slug(string $slug): ?array
{
    return q(SELECT_SERIE . ' WHERE s.slug = ?', [$slug])->fetch() ?: null;
}

/** Série « à la une » : change chaque jour parmi les séries en cours. */
function serie_a_la_une(): ?array
{
    $enCours = series_list(['statut' => 'En cours', 'tri' => 'titre']);
    if (!$enCours) {
        $enCours = series_list();
    }
    return $enCours ? $enCours[(int) date('z') % count($enCours)] : null;
}

/** Épisodes d'une série, groupés par saison : [saison => [épisodes…]]. */
function episodes_par_saison(int $serieId): array
{
    $saisons = [];
    foreach (q('SELECT * FROM episodes WHERE serie_id = ? ORDER BY saison, numero', [$serieId]) as $ep) {
        $saisons[(int) $ep['saison']][] = $ep;
    }
    return $saisons;
}

function episode(int $id): ?array
{
    return q('SELECT e.*, s.slug, s.titre AS serie_titre, s.teinte, s.genre
              FROM episodes e JOIN series s ON s.id = e.serie_id WHERE e.id = ?', [$id])->fetch() ?: null;
}

/** Épisode suivant dans l'ordre saison/numéro, ou null s'il s'agit du dernier. */
function episode_suivant(array $ep): ?array
{
    return q('SELECT * FROM episodes WHERE serie_id = ? AND (saison > ? OR (saison = ? AND numero > ?))
              ORDER BY saison, numero LIMIT 1', [$ep['serie_id'], $ep['saison'], $ep['saison'], $ep['numero']])->fetch() ?: null;
}

function episode_precedent(array $ep): ?array
{
    return q('SELECT * FROM episodes WHERE serie_id = ? AND (saison < ? OR (saison = ? AND numero < ?))
              ORDER BY saison DESC, numero DESC LIMIT 1', [$ep['serie_id'], $ep['saison'], $ep['saison'], $ep['numero']])->fetch() ?: null;
}

/* ---------- Planning ---------- */

function planning_serie(int $serieId): array
{
    return q('SELECT * FROM planning WHERE serie_id = ? ORDER BY jour, heure', [$serieId])->fetchAll();
}

/** Planning de la semaine : [jour => [créneaux…]], éventuellement limité aux favoris d'un utilisateur. */
function planning_semaine(?int $favorisDe = null): array
{
    $sql = 'SELECT p.*, s.titre, s.slug, s.genre, s.teinte FROM planning p JOIN series s ON s.id = p.serie_id';
    $params = [];
    if ($favorisDe) {
        $sql .= ' JOIN favoris f ON f.serie_id = s.id AND f.utilisateur_id = ?';
        $params[] = $favorisDe;
    }
    $sql .= ' ORDER BY p.jour, p.heure, s.titre';

    $semaine = array_fill_keys(array_keys(JOURS), []);
    foreach (q($sql, $params) as $creneau) {
        $semaine[(int) $creneau['jour']][] = $creneau;
    }
    return $semaine;
}

/* ---------- Favoris (« Ma liste ») ---------- */

function est_favori(int $userId, int $serieId): bool
{
    return (bool) q('SELECT 1 FROM favoris WHERE utilisateur_id = ? AND serie_id = ?', [$userId, $serieId])->fetchColumn();
}

/** Ajoute ou retire une série de la liste. Renvoie true si elle est désormais en favori. */
function basculer_favori(int $userId, int $serieId): bool
{
    if (est_favori($userId, $serieId)) {
        q('DELETE FROM favoris WHERE utilisateur_id = ? AND serie_id = ?', [$userId, $serieId]);
        return false;
    }
    q('INSERT INTO favoris (utilisateur_id, serie_id, ajoute_le) VALUES (?, ?, ?)', [$userId, $serieId, db_now()]);
    return true;
}

function favoris(int $userId): array
{
    return q(SELECT_SERIE . ' JOIN favoris f ON f.serie_id = s.id WHERE f.utilisateur_id = ? ORDER BY f.ajoute_le DESC', [$userId])->fetchAll();
}

/* ---------- Historique de visionnage ---------- */

function episodes_vus(int $userId, int $serieId): array
{
    $ids = q('SELECT v.episode_id FROM visionnages v JOIN episodes e ON e.id = v.episode_id
              WHERE v.utilisateur_id = ? AND e.serie_id = ?', [$userId, $serieId])->fetchAll(PDO::FETCH_COLUMN);
    return array_flip(array_map('intval', $ids));
}

function marquer_vu(int $userId, int $episodeId, bool $vu = true): void
{
    q('DELETE FROM visionnages WHERE utilisateur_id = ? AND episode_id = ?', [$userId, $episodeId]);
    if ($vu) {
        q('INSERT INTO visionnages (utilisateur_id, episode_id, vu_le) VALUES (?, ?, ?)', [$userId, $episodeId, db_now()]);
    }
}

/**
 * Épisode à reprendre : le premier non vu après le dernier épisode regardé,
 * sinon le premier non vu de la série. Null si tout a été vu.
 */
function prochain_episode(int $userId, int $serieId): ?array
{
    $nonVus = 'SELECT e.* FROM episodes e
               WHERE e.serie_id = ? AND NOT EXISTS (
                   SELECT 1 FROM visionnages v WHERE v.episode_id = e.id AND v.utilisateur_id = ?)';

    $dernier = q('SELECT e.saison, e.numero FROM visionnages v JOIN episodes e ON e.id = v.episode_id
                  WHERE v.utilisateur_id = ? AND e.serie_id = ?
                  ORDER BY v.vu_le DESC, e.saison DESC, e.numero DESC LIMIT 1', [$userId, $serieId])->fetch();

    if ($dernier) {
        $apres = q($nonVus . ' AND (e.saison > ? OR (e.saison = ? AND e.numero > ?)) ORDER BY e.saison, e.numero LIMIT 1',
            [$serieId, $userId, $dernier['saison'], $dernier['saison'], $dernier['numero']])->fetch();
        if ($apres) {
            return $apres;
        }
    }
    return q($nonVus . ' ORDER BY e.saison, e.numero LIMIT 1', [$serieId, $userId])->fetch() ?: null;
}

/** Séries commencées mais pas terminées, de la plus récemment regardée à la plus ancienne. */
function continuer_a_regarder(int $userId, int $limit = 8): array
{
    $rows = q('SELECT e.serie_id, MAX(v.vu_le) AS derniere, COUNT(*) AS nb_vus
               FROM visionnages v JOIN episodes e ON e.id = v.episode_id
               WHERE v.utilisateur_id = ?
               GROUP BY e.serie_id ORDER BY derniere DESC', [$userId])->fetchAll();

    $resultat = [];
    foreach ($rows as $row) {
        $suivant = prochain_episode($userId, (int) $row['serie_id']);
        if (!$suivant) {
            continue; // série terminée
        }
        $serie = q(SELECT_SERIE . ' WHERE s.id = ?', [$row['serie_id']])->fetch();
        $serie['suivant'] = $suivant;
        $serie['progression'] = (int) round($row['nb_vus'] / max(1, $serie['nb_episodes']) * 100);
        $resultat[] = $serie;
        if (count($resultat) >= $limit) {
            break;
        }
    }
    return $resultat;
}

function historique(int $userId, int $limit = 20): array
{
    return q('SELECT v.vu_le, e.id AS episode_id, e.saison, e.numero, e.titre, s.titre AS serie_titre, s.slug, s.teinte
              FROM visionnages v JOIN episodes e ON e.id = v.episode_id JOIN series s ON s.id = e.serie_id
              WHERE v.utilisateur_id = ? ORDER BY v.vu_le DESC LIMIT ' . (int) $limit, [$userId])->fetchAll();
}
