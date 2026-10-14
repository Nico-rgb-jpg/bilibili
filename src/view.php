<?php
/**
 * Petits composants d'affichage réutilisés dans plusieurs pages.
 */

/** Affiche générée en CSS à partir de la teinte de la série (aucune image externe). */
function poster(array $serie, string $class = ''): string
{
    $h = (int) $serie['teinte'];
    $initiale = mb_strtoupper(mb_substr(preg_replace('/^(Les?|La|L\')\s*/u', '', $serie['titre']), 0, 1));
    return sprintf(
        '<div class="poster %s" style="--h:%d" aria-hidden="true"><span class="poster-mark">%s</span>'
        . '<span class="poster-genre">%s</span><span class="poster-title">%s</span></div>',
        e($class), $h, e($initiale), e($serie['genre']), e($serie['titre'])
    );
}

function serie_card(array $serie, string $extra = ''): string
{
    $meta = e($serie['annee']) . ' · ' . (int) $serie['nb_saisons'] . ' saison' . ($serie['nb_saisons'] > 1 ? 's' : '');
    $badge = $serie['statut'] === 'En cours' ? '<span class="badge badge-live">En cours</span>' : '';
    return '<a class="card" href="serie.php?s=' . e($serie['slug']) . '">'
        . poster($serie) . $badge
        . '<span class="card-body"><span class="card-title">' . e($serie['titre']) . '</span>'
        . '<span class="card-meta">' . $meta . '</span>' . $extra . '</span></a>';
}

function ep_code(array $ep): string
{
    return 'S' . (int) $ep['saison'] . ' · É' . (int) $ep['numero'];
}

function rangee(string $titre, array $series, string $lien = '', string $lienTexte = 'Tout voir'): string
{
    if (!$series) {
        return '';
    }
    $html = '<section class="row"><div class="row-head"><h2>' . e($titre) . '</h2>';
    if ($lien) {
        $html .= '<a class="link" href="' . e($lien) . '">' . e($lienTexte) . ' →</a>';
    }
    $html .= '</div><div class="row-track">';
    foreach ($series as $s) {
        $html .= serie_card($s);
    }
    return $html . '</div></section>';
}

/** Messages d'erreur de formulaire. */
function form_errors(array $errors): string
{
    if (!$errors) {
        return '';
    }
    $html = '<div class="alert alert-error" role="alert"><ul>';
    foreach ($errors as $err) {
        $html .= '<li>' . e($err) . '</li>';
    }
    return $html . '</ul></div>';
}
