<?php
require __DIR__ . '/../src/bootstrap.php';

// La déconnexion se fait en POST avec jeton CSRF : un simple lien externe ne peut pas déconnecter l'utilisateur.
if (!is_post()) {
    redirect('index.php');
}
verify_csrf();
logout_user();

session_start();
flash('info', 'Vous êtes déconnecté. À bientôt !');
redirect('index.php');
