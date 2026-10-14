<?php
/**
 * Si le projet est placé tel quel dans htdocs (XAMPP, WAMP, MAMP…),
 * http://localhost/bilibili/ redirige vers le vrai site, dans public/.
 */
header('Location: public/');
exit;
