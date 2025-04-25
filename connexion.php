<?php
$host = "localhost";
$dbname = "t_ches";
$username = "root";
$password = "";

// Connexion avec MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Vérification des erreurs
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Définir le charset en UTF-8
$conn->set_charset("utf8");
?>
