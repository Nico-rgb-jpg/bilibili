<?php
/**
 * Initialise la base de données.
 *
 *   php database/init.php           crée les tables et les données de démo
 *   php database/init.php --reset   repart d'une base vide (SQLite uniquement)
 *   php database/init.php --empty   tables seules, sans données de démo
 */

if (PHP_SAPI !== 'cli') {
    exit("À lancer en ligne de commande : php database/init.php\n");
}

$GLOBALS['__config'] = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/installer.php';

$cfg = config('db');
$reset = in_array('--reset', $argv, true);
$withDemo = !in_array('--empty', $argv, true);

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

if ($cfg['driver'] === 'mysql') {
    $server = new PDO("mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4", $cfg['user'], $cfg['pass'], $options);
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = new PDO("mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4", $cfg['user'], $cfg['pass'], $options);
} else {
    if ($reset && file_exists($cfg['sqlite_path'])) {
        unlink($cfg['sqlite_path']);
        echo "Ancienne base supprimée.\n";
    }
    $pdo = new PDO('sqlite:' . $cfg['sqlite_path'], null, null, $options);
    $pdo->exec('PRAGMA foreign_keys = ON');
}

install_database($pdo, $cfg['driver'], $withDemo);

$nb = $pdo->query('SELECT COUNT(*) FROM series')->fetchColumn();
echo "Base prête ({$cfg['driver']}) : $nb séries.\n";
if ($withDemo) {
    echo "Comptes de démo : admin@bilibili.local / Admin1234  ·  demo@bilibili.local / Demo1234\n";
}
