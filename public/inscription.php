<?php
require __DIR__ . '/../src/bootstrap.php';

if (current_user()) {
    redirect('index.php');
}

$errors = [];
$pseudo = input('pseudo');
$email = input('email');

if (is_post()) {
    verify_csrf();
    $password = $_POST['mot_de_passe'] ?? '';
    $confirm = $_POST['confirmation'] ?? '';

    $errors = array_filter([
        validate_pseudo($pseudo),
        validate_email($email),
        validate_password($password, $confirm),
        empty($_POST['cgu']) ? 'Vous devez accepter les conditions d\'utilisation.' : null,
    ]);

    if (!$errors) {
        $id = register_user($pseudo, $email, $password);
        login_user($id);
        flash('success', 'Bienvenue sur Bilibili, ' . $pseudo . ' !');
        redirect('index.php');
    }
}

$title = 'Inscription';
require __DIR__ . '/../templates/header.php';
?>
<section class="auth">
    <div class="auth-card">
        <p class="eyebrow">Créer un compte</p>
        <h1>Rejoignez Bilibili</h1>
        <p class="muted">Gardez vos séries dans votre liste, suivez votre progression et ne ratez plus aucune sortie.</p>

        <?= form_errors($errors) ?>

        <form method="post" class="form" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="pseudo">Pseudo</label>
                <input id="pseudo" name="pseudo" value="<?= e($pseudo) ?>" required minlength="3" maxlength="20" pattern="[A-Za-z0-9_\-]{3,20}" autocomplete="username">
                <small>3 à 20 caractères : lettres, chiffres, - ou _.</small>
            </div>
            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
            </div>
            <div class="field">
                <label for="mot_de_passe">Mot de passe</label>
                <div class="password">
                    <input id="mot_de_passe" type="password" name="mot_de_passe" required minlength="8" autocomplete="new-password" data-strength>
                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe">Afficher</button>
                </div>
                <div class="strength" aria-hidden="true"><span></span></div>
                <small>8 caractères minimum, avec au moins une lettre et un chiffre.</small>
            </div>
            <div class="field">
                <label for="confirmation">Confirmer le mot de passe</label>
                <input id="confirmation" type="password" name="confirmation" required autocomplete="new-password">
            </div>
            <label class="check">
                <input type="checkbox" name="cgu" value="1" <?= !empty($_POST['cgu']) ? 'checked' : '' ?>>
                <span>J'accepte les conditions d'utilisation du service.</span>
            </label>
            <button class="btn btn-block" type="submit">Créer mon compte</button>
        </form>
        <p class="auth-switch">Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
    </div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
