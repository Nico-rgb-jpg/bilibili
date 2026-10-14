<?php
/**
 * Connexion PDO unique (SQLite ou MySQL selon la configuration).
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $cfg = config('db');
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if ($cfg['driver'] === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'], $cfg['name']);
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
    } else {
        $isNew = !file_exists($cfg['sqlite_path']);
        $pdo = new PDO('sqlite:' . $cfg['sqlite_path'], null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        // Première visite : la base SQLite est créée et remplie automatiquement.
        if ($isNew) {
            require_once __DIR__ . '/../database/installer.php';
            install_database($pdo, 'sqlite');
        }
    }

    return $pdo;
}

/** Exécute une requête préparée et renvoie le PDOStatement. */
function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_now(): string
{
    return date('Y-m-d H:i:s');
}
