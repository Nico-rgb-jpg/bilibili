<?php
require __DIR__ . '/../src/bootstrap.php';

$retour = safe_return(input('retour'));

if (current_user()) {
    redirect($retour);
}

$error = null;
$identifiant = input('identifiant');

if (is_post()) {
    verify_csrf();
    $result = attempt_login($identifiant, $_POST['mot_de_passe'] ?? '');
    if (is_array($result)) {
        login_user((int) $result['id']);
        flash('success', 'Content de vous revoir, ' . $result['pseudo'] . ' !');
        redirect($retour);
    }
    $error = $result;
}

$title = 'Connexion';
require __DIR__ . '/../templates/header.php';
?>
<section class="auth">
    <div class="auth-card">
        <p class="eyebrow">Connexion</p>
        <h1>Bon retour parmi nous</h1>

        <?= form_errors($error ? [$error] : []) ?>

        <form method="post" class="form" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="retour" value="<?= e($retour) ?>">
            <div class="field">
                <label for="identifiant">E-mail ou pseudo</label>
                <input id="identifiant" name="identifiant" value="<?= e($identifiant) ?>" required autocomplete="username" autofocus>
            </div>
            <div class="field">
                <label for="mot_de_passe">Mot de passe</label>
                <div class="password">
                    <input id="mot_de_passe" type="password" name="mot_de_passe" required autocomplete="current-password">
                    <button type="button" class="password-toggle" aria-label="Afficher le mot de passe">Afficher</button>
                </div>
            </div>
            <button class="btn btn-block" type="submit">Se connecter</button>
        </form>

        <div class="demo-hint">
            <strong>Comptes de démonstration</strong>
            <span>demo@bilibili.local · Demo1234</span>
            <span>admin@bilibili.local · Admin1234</span>
        </div>

        <p class="auth-switch">Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
    </div>
</section>
<?php require __DIR__ . '/../templates/footer.php'; ?>
