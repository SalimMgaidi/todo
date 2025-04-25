<?php
session_start();

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['utilisateur_id']);  // Exemple d'une session, modifiez selon la manière dont vous gérez la session

// Vérifier si l'utilisateur est admin (par exemple, en vérifiant le rôle)
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';  // Vous pouvez ajuster cette logique selon vos besoins
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - To-Do List Avancée</title>
</head>
<style>

 /* ✅ Définition des couleurs avec :root */
:root {
    --primary-color: #fd79a8; /* Couleur principale */
    --secondary-color: #a29bfe; /* Couleur secondaire */
    --accent-color: #6c5ce7; /* Couleur d'accent */
    --background-color: #f5f6fa; /* Couleur de fond */
    --card-color: #ffffff; /* Couleur des cartes */
    --text-color: #2d3436; /* Couleur du texte */
    --light-text: #636e72; /* Couleur du texte secondaire */
    --success-color: rgb(91, 166, 240); /* Couleur pour les succès */
    --warning-color: rgb(247, 210, 141); /* Couleur pour les avertissements */
    --danger-color: #d63031; /* Couleur pour les erreurs */
}

/* ✅ Style global */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

/* ✅ Corps de la page */
body {
    background-color: var(--background-color);
    text-align: center;
    color: var(--text-color);
}

/* ✅ Conteneur principal */
.container {
    padding: 100px 20px;
}

/* ✅ En-tête */
header {
    background: var(--primary-color);
    color: white;
    padding: 80px 20px;
    text-align: center;
}

header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

header p {
    font-size: 1.2rem;
    margin-bottom: 20px;
}

/* ✅ Boutons */
.buttons {
    margin-top: 20px;
}

.btn {
    display: inline-block;
    background: var(--success-color);
    color: white;
    padding: 12px 25px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    margin: 5px;
    transition: 0.3s;
}

.btn:hover {
    background: #218838;
}

.btn-secondary {
    background: var(--warning-color);
    color: var(--text-color);
}

.btn-secondary:hover {
    background: #e0a800;
}

/* ✅ Section des fonctionnalités */
.features {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    padding: 50px 20px;
    background: var(--card-color);
}

.feature {
    flex: 1;
    max-width: 300px;
    margin: 20px;
    padding: 20px;
    background: var(--background-color);
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.feature h2 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.feature p {
    font-size: 1rem;
    color: var(--light-text);
}

/* ✅ Pied de page */
footer {
    background: var(--text-color);
    color: white;
    padding: 15px 0;
    font-size: 0.9rem;
    margin-top: 20px;
}
</style>
<body>
    <header>
        <div class="container">
            <h1>Bienvenue sur Tskify</h1>
            <p>Gérez vos tâches efficacement et boostez votre productivité.</p>
            <div class="buttons">
                <?php if ($is_logged_in): ?>
                    <?php if ($is_admin): ?>
                        <!-- Lien vers l'espace admin si l'utilisateur est admin -->
                        <a href="admin.php" class="btn">Accéder à l'espace Admin</a>
                    <?php else: ?>
                        <!-- Lien vers l'espace utilisateur si l'utilisateur n'est pas admin -->
                        <a href="user.php" class="btn">Accéder à votre espace utilisateur </a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Lien de connexion ou d'inscription si l'utilisateur n'est pas connecté -->
                    <a href="login.php" class="btn">Se connecter</a>
                    <a href="register.php" class="btn btn-secondary">S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="features">
        <div class="feature">
            <h2>📋 Gestion des Tâches</h2>
            <p>Ajoutez, modifiez et supprimez facilement vos tâches.</p>
        </div>
        <div class="feature">
            <h2>⏳ Priorités & Rappels</h2>
            <p>Classez vos tâches par priorité et recevez des notifications.</p>
        </div>
        <div class="feature">
            <h2>📊 Suivi & Statistiques</h2>
            <p>Consultez votre progression et améliorez votre productivité.</p>
        </div>
    </section>

    <footer>
        <p>© 2025 To-Do List Avancée - Tous droits réservés.</p>
    </footer>
</body>
</html>
