<?php
/**
 * Fonctions utilitaires : configuration, échappement, CSRF, messages flash…
 */

function config(string $key)
{
    $cfg = $GLOBALS['__config'] ?? [];
    foreach (explode('.', $key) as $part) {
        $cfg = $cfg[$part] ?? null;
    }
    return $cfg;
}

/** Échappe une valeur pour un affichage HTML sûr. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text));
    return trim($text, '-') ?: 'serie';
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

/** N'autorise que les redirections internes (évite les redirections ouvertes). */
function safe_return(?string $to, string $fallback = 'index.php'): string
{
    if ($to && preg_match('#^[a-z0-9\-]+\.php(\?[^\s]*)?$#i', $to)) {
        return $to;
    }
    return $fallback;
}

/* ---------- Protection CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Session expirée. Rechargez la page et réessayez.');
    }
}

/* ---------- Messages flash ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------- Formulaires ---------- */

function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

/* ---------- Dates ---------- */

const JOURS = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];

function jour_actuel(): int
{
    return (int) date('N');
}

function date_fr(string $datetime): string
{
    $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $ts = strtotime($datetime);
    return date('j', $ts) . ' ' . $mois[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

function not_found(string $message = 'Page introuvable.', int $code = 404): never
{
    http_response_code($code);
    $title = $code === 403 ? 'Accès refusé' : 'Introuvable';
    require __DIR__ . '/../templates/header.php';
    echo '<section class="page narrow empty-state"><p class="eyebrow">Erreur ' . $code . '</p><h1>' . e($message) . '</h1>'
        . '<p><a class="btn" href="index.php">Retour à l\'accueil</a></p></section>';
    require __DIR__ . '/../templates/footer.php';
    exit;
}
