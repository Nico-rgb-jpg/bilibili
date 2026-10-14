<?php
/**
 * Ajoute / retire une série de « Ma liste ».
 * Répond en JSON pour les appels JavaScript, sinon redirige vers la page d'origine.
 */
require __DIR__ . '/../src/bootstrap.php';

if (!is_post()) {
    redirect('index.php');
}
$user = require_login();
verify_csrf();

$serieId = (int) ($_POST['serie_id'] ?? 0);
$serie = q('SELECT id, titre FROM series WHERE id = ?', [$serieId])->fetch();
if (!$serie) {
    not_found('Série introuvable.');
}

$favori = basculer_favori($user['id'], $serieId);

if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') {
    header('Content-Type: application/json');
    echo json_encode(['favori' => $favori]);
    exit;
}

flash('success', $favori ? '« ' . $serie['titre'] . ' » ajoutée à votre liste.' : '« ' . $serie['titre'] . ' » retirée de votre liste.');
redirect(safe_return($_POST['retour'] ?? null));
