<?php
/**
 * Authentification : inscription, connexion, session et contrôle d'accès.
 */

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $id = $_SESSION['user_id'] ?? null;
    $user = $id ? (q('SELECT id, pseudo, email, role, cree_le FROM utilisateurs WHERE id = ?', [$id])->fetch() ?: null) : null;
    if ($id && !$user) {
        unset($_SESSION['user_id']); // compte supprimé entre-temps
    }
    return $user;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? null) === 'admin';
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('info', 'Connectez-vous pour accéder à cette page.');
        $retour = basename($_SERVER['SCRIPT_NAME']) . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']);
        redirect('connexion.php?retour=' . urlencode($retour));
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        not_found('Accès réservé aux administrateurs.', 403);
    }
    return $user;
}

function login_user(int $id): void
{
    session_regenerate_id(true); // protection contre la fixation de session
    $_SESSION['user_id'] = $id;
    unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------- Validation ---------- */

function validate_pseudo(string $pseudo, ?int $ignoreId = null): ?string
{
    if (!preg_match('/^[A-Za-z0-9_-]{3,20}$/', $pseudo)) {
        return 'Le pseudo doit contenir 3 à 20 caractères (lettres, chiffres, - ou _).';
    }
    $taken = q('SELECT id FROM utilisateurs WHERE LOWER(pseudo) = LOWER(?) AND id != ?', [$pseudo, $ignoreId ?? 0])->fetch();
    return $taken ? 'Ce pseudo est déjà pris.' : null;
}

function validate_email(string $email, ?int $ignoreId = null): ?string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        return 'Adresse e-mail invalide.';
    }
    $taken = q('SELECT id FROM utilisateurs WHERE LOWER(email) = LOWER(?) AND id != ?', [$email, $ignoreId ?? 0])->fetch();
    return $taken ? 'Un compte existe déjà avec cette adresse.' : null;
}

function validate_password(string $password, string $confirm): ?string
{
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Le mot de passe doit faire au moins 8 caractères, avec au moins une lettre et un chiffre.';
    }
    if ($password !== $confirm) {
        return 'Les deux mots de passe ne correspondent pas.';
    }
    return null;
}

/* ---------- Actions ---------- */

function register_user(string $pseudo, string $email, string $password): int
{
    q('INSERT INTO utilisateurs (pseudo, email, mot_de_passe, role, cree_le) VALUES (?, ?, ?, ?, ?)', [
        $pseudo, strtolower($email), password_hash($password, PASSWORD_DEFAULT), 'membre', db_now(),
    ]);
    return (int) db()->lastInsertId();
}

/**
 * Vérifie les identifiants (e-mail ou pseudo). Renvoie l'utilisateur ou un message d'erreur.
 * Limite le nombre d'essais par session pour freiner le brute force.
 */
function attempt_login(string $identifiant, string $password): array|string
{
    $max = (int) config('security.max_login_attempts');
    $lock = (int) config('security.lockout_seconds');

    $lockedUntil = $_SESSION['login_locked_until'] ?? 0;
    if ($lockedUntil > time()) {
        return 'Trop de tentatives. Réessayez dans ' . ($lockedUntil - time()) . ' secondes.';
    }

    $user = q('SELECT * FROM utilisateurs WHERE LOWER(email) = LOWER(?) OR LOWER(pseudo) = LOWER(?)', [$identifiant, $identifiant])->fetch();

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= $max) {
            $_SESSION['login_locked_until'] = time() + $lock;
            $_SESSION['login_attempts'] = 0;
        }
        return 'Identifiants incorrects.';
    }

    if (password_needs_rehash($user['mot_de_passe'], PASSWORD_DEFAULT)) {
        q('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    return $user;
}

function check_password(int $userId, string $password): bool
{
    $hash = q('SELECT mot_de_passe FROM utilisateurs WHERE id = ?', [$userId])->fetchColumn();
    return $hash && password_verify($password, $hash);
}
