<?php
/**
 * Point d'entrée commun, inclus en haut de chaque page de public/.
 */

declare(strict_types=1);

$GLOBALS['__config'] = require __DIR__ . '/../config/config.php';

require_once __DIR__ . '/helpers.php';

date_default_timezone_set(config('app.timezone'));

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/series.php';
require_once __DIR__ . '/view.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_name('bilibili_session');
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
