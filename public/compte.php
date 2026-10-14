<?php
require __DIR__ . '/../src/bootstrap.php';

$user = require_login();
$errors = ['profil' => [], 'mot_de_passe' => [], 'supprimer' => []];
$pseudo = $user['pseudo'];
$email = $user['email'];

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'profil') {
        $pseudo = input('pseudo');
        $email = input('email');
        $errors['profil'] = array_filter([validate_pseudo($pseudo, $user['id']), validate_email($email, $user['id'])]);
        if (!$errors['profil']) {
            q('UPDATE utilisateurs SET pseudo = ?, email = ? WHERE id = ?', [$pseudo, strtolower($email), $user['id']]);
            flash('success', 'Profil mis à jour.');
            redirect('compte.php');
        }
    }

    if ($action === 'mot_de_passe') {
        if (!check_password($user['id'], $_POST['actuel'] ?? '')) {
            $errors['mot_de_passe'][] = 'Le mot de passe actuel est incorrect.';
        } elseif ($err = validate_password($_POST['nouveau'] ?? '', $_POST['confirmation'] ?? '')) {
            $errors['mot_de_passe'][] = $err;
        } else {
            q('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?', [password_hash($_POST['nouveau'], PASSWORD_DEFAULT), $user['id']]);
            session_regenerate_id(true);
            flash('success', 'Mot de passe modifié.');
            redirect('compte.php');
        }
    }

    if ($action === 'supprimer') {
        if (!check_password($user['id'], $_POST['confirmation_suppression'] ?? '')) {
            $errors['supprimer'][] = 'Mot de passe incorrect : le compte n\'a pas été supprimé.';
        } else {
            // Favoris et historique sont supprimés en cascade par les clés étrangères.
            q('DELETE FROM utilisateurs WHERE id = ?', [$user['id']]);
            logout_user();
            session_start();
            flash('info', 'Votre compte a bien été supprimé.');
            redirect('index.php');
        }
    }
}

$stats = q('SELECT
    (SELECT COUNT(*) FROM favoris WHERE utilisateur_id = ?) AS favoris,
    (SELECT COUNT(*) FROM visionnages WHERE utilisateur_id = ?) AS episodes,
    (SELECT COALESCE(SUM(e.duree_min), 0) FROM visionnages v JOIN episodes e ON e.id = v.episode_id WHERE v.utilisateur_id = ?) AS minutes',
    array_fill(0, 3, $user['id']))->fetch();

$title = 'Mon compte';
require __DIR__ . '/../templates/header.php';
?>
<section class="page narrow">
    <div class="page-head">
        <p class="eyebrow">Mon compte</p>
        <h1><?= e($user['pseudo']) ?></h1>
        <p class="muted">Membre depuis le <?= e(date_fr($user['cree_le'])) ?><?= $user['role'] === 'admin' ? ' · administrateur' : '' ?></p>
    </div>

    <div class="stats">
        <div class="stat"><strong><?= (int) $stats['favoris'] ?></strong><span>séries dans ma liste</span></div>
        <div class="stat"><strong><?= (int) $stats['episodes'] ?></strong><span>épisodes vus</span></div>
        <div class="stat"><strong><?= intdiv((int) $stats['minutes'], 60) ?> h <?= str_pad((string) ((int) $stats['minutes'] % 60), 2, '0', STR_PAD_LEFT) ?></strong><span>de visionnage</span></div>
    </div>

    <div class="panel">
        <h2>Informations du profil</h2>
        <?= form_errors($errors['profil']) ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="profil">
            <div class="field-row">
                <div class="field">
                    <label for="pseudo">Pseudo</label>
                    <input id="pseudo" name="pseudo" value="<?= e($pseudo) ?>" required maxlength="20">
                </div>
                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="<?= e($email) ?>" required>
                </div>
            </div>
            <button class="btn" type="submit">Enregistrer</button>
        </form>
    </div>

    <div class="panel">
        <h2>Changer de mot de passe</h2>
        <?= form_errors($errors['mot_de_passe']) ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mot_de_passe">
            <div class="field">
                <label for="actuel">Mot de passe actuel</label>
                <input id="actuel" type="password" name="actuel" required autocomplete="current-password">
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="nouveau">Nouveau mot de passe</label>
                    <input id="nouveau" type="password" name="nouveau" required minlength="8" autocomplete="new-password" data-strength>
                    <div class="strength" aria-hidden="true"><span></span></div>
                </div>
                <div class="field">
                    <label for="confirmation">Confirmation</label>
                    <input id="confirmation" type="password" name="confirmation" required autocomplete="new-password">
                </div>
            </div>
            <button class="btn" type="submit">Modifier le mot de passe</button>
        </form>
    </div>

    <div class="panel panel-danger">
        <h2>Supprimer mon compte</h2>
        <p class="muted">Votre liste et votre historique seront définitivement effacés.</p>
        <?= form_errors($errors['supprimer']) ?>
        <form method="post" class="form" data-confirm="Supprimer définitivement votre compte ?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="supprimer">
            <div class="field">
                <label for="confirmation_suppression">Confirmez avec votre mot de passe</label>
                <input id="confirmation_suppression" type="password" name="confirmation_suppression" required autocomplete="current-password">
            </div>
            <button class="btn btn-danger" type="submit">Supprimer définitivement</button>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
