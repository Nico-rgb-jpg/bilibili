<?php
/**
 * Configuration de l'application.
 *
 * Par défaut le projet utilise SQLite : aucune installation de serveur
 * de base de données n'est nécessaire. Pour passer sur MySQL (XAMPP, WAMP…),
 * définir les variables d'environnement DB_DRIVER=mysql, DB_HOST, DB_NAME,
 * DB_USER et DB_PASS, ou modifier les valeurs par défaut ci-dessous.
 */

return [
    'app' => [
        'name' => 'Bilibili',
        'tagline' => 'Vos séries, votre planning.',
        'timezone' => 'Europe/Paris',
    ],

    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'sqlite',      // 'sqlite' ou 'mysql'
        'sqlite_path' => __DIR__ . '/../storage/bilibili.sqlite',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'bilibili',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    ],

    'security' => [
        'max_login_attempts' => 5,     // tentatives avant blocage temporaire
        'lockout_seconds' => 60,
    ],
];
