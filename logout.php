<?php
// Démarrer la session
session_start();
setcookie('nom_utilisateur', '', time() - 3600, "/");
setcookie('role', '', time() - 3600, "/");
setcookie('id_utilisateur', '', time() - 3600, "/");

// Détruire toutes les variables de session
session_unset();

// Détruire la session
session_destroy();

header('Location: index.php'); 
exit;
?>
