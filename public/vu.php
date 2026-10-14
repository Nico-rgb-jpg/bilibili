<?php
/**
 * Marque un épisode comme vu / non vu.
 * Répond en JSON pour les appels JavaScript, sinon redirige vers la page d'origine.
 */
require __DIR__ . '/../src/bootstrap.php';

if (!is_post()) {
    redirect('index.php');
}
$user = require_login();
verify_csrf();

$episodeId = (int) ($_POST['episode_id'] ?? 0);
if (!episode($episodeId)) {
    not_found('Épisode introuvable.');
}

$vu = ($_POST['vu'] ?? '1') === '1';
marquer_vu($user['id'], $episodeId, $vu);

if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') {
    header('Content-Type: application/json');
    echo json_encode(['vu' => $vu]);
    exit;
}

redirect(safe_return($_POST['retour'] ?? null));
