<?php
/**
 * Vérifie l'environnement avant de charger l'application et affiche
 * une page d'aide claire si quelque chose manque.
 * (Fichier volontairement compatible avec les anciennes versions de PHP.)
 */

function bilibili_check_requirements($config)
{
    $problems = array();

    if (version_compare(PHP_VERSION, '8.1.0', '<')) {
        $problems[] = array(
            'Version de PHP trop ancienne (' . PHP_VERSION . ')',
            'Le projet demande PHP 8.1 ou plus. Installe une version récente de PHP, ou une version récente de XAMPP (PHP 8.2).',
        );
    }

    if (!extension_loaded('pdo')) {
        $problems[] = array('Extension PDO manquante', 'Active l\'extension <code>pdo</code> dans ton fichier <code>php.ini</code>.');
    }

    if ($config['db']['driver'] === 'sqlite') {
        if (!extension_loaded('pdo_sqlite')) {
            $ini = php_ini_loaded_file();
            $problems[] = array(
                'Extension SQLite non activée',
                'Ouvre ton fichier <code>php.ini</code>' . ($ini ? ' (<code>' . htmlspecialchars($ini) . '</code>)' : ' (aucun php.ini chargé : copie <code>php.ini-development</code> en <code>php.ini</code> dans le dossier de PHP)')
                . ', retire le <code>;</code> au début des lignes <code>extension=pdo_sqlite</code> et <code>extension=sqlite3</code>'
                . ', vérifie que <code>extension_dir = "ext"</code> n\'est pas commentée, enregistre, puis relance le serveur.',
            );
        }
        $dir = dirname($config['db']['sqlite_path']);
        if (!is_dir($dir) || !is_writable($dir)) {
            $problems[] = array(
                'Dossier storage/ inaccessible en écriture',
                'Le dossier <code>' . htmlspecialchars($dir) . '</code> doit exister et être modifiable : c\'est là que la base de données est créée.',
            );
        }
    }

    if (!$problems) {
        return;
    }

    http_response_code(500);
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Bilibili · configuration requise</title><style>'
        . 'body{margin:0;background:#0c0d11;color:#f2f1ee;font:16px/1.6 system-ui,sans-serif;padding:40px 16px}'
        . 'main{max-width:680px;margin:0 auto}h1{font-size:1.8rem;margin:0 0 .3em}p{color:#b9bac2}'
        . '.box{background:#13151b;border:1px solid #2a2f3b;border-left:4px solid #ff5a3c;border-radius:10px;padding:16px 20px;margin:16px 0}'
        . '.box strong{display:block;margin-bottom:4px;color:#fff}code{background:#20242e;padding:2px 6px;border-radius:5px;font-size:.9em}'
        . '</style></head><body><main><h1>Il manque quelque chose pour lancer Bilibili</h1>'
        . '<p>PHP ' . htmlspecialchars(PHP_VERSION) . ' · ' . htmlspecialchars(PHP_OS_FAMILY ?? PHP_OS) . '</p>';
    foreach ($problems as $p) {
        echo '<div class="box"><strong>' . htmlspecialchars($p[0]) . '</strong>' . $p[1] . '</div>';
    }
    echo '<p>Une fois corrigé, recharge cette page.</p></main></body></html>';
    exit;
}
