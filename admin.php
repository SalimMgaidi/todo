<?php
    // Démarrer la session
    require("connexion.php");
    session_start();


   

    // Vérifier si l'utilisateur est connecté et a le rôle d'admin
    if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit;
    }



    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('Échec de la connexion : ' . $e->getMessage());
    }

    // Récupérer les statistiques
    $sql = "SELECT COUNT(*) AS total_users FROM utilisateur WHERE role = 'utilisateur'";
    $total_users = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC)['total_users'];

    $sql_admins = "SELECT COUNT(*) AS total_admins FROM utilisateur WHERE role = 'admin'";
    $total_admins = $pdo->query($sql_admins)->fetch(PDO::FETCH_ASSOC)['total_admins'];

    $sql_taches = "SELECT COUNT(*) AS total_tasks FROM tâches";
    $total_tasks = $pdo->query($sql_taches)->fetch(PDO::FETCH_ASSOC)['total_tasks'];

    $sql_done = "SELECT COUNT(*) AS totalTasksCompleted FROM tâches WHERE statut = ' Terminée'";
    $total_completed = $pdo->query($sql_done)->fetch(PDO::FETCH_ASSOC)['totalTasksCompleted'];

    $sql_progress = "SELECT COUNT(*) AS totalTasksInprogress FROM tâches WHERE statut = 'En cours'";
    $total_progress = $pdo->query($sql_progress)->fetch(PDO::FETCH_ASSOC)['totalTasksInprogress'];

    $sql_todo = "SELECT COUNT(*) AS totalTasksTodo FROM tâches WHERE statut = ' À faire'";
    $total_todo = $pdo->query($sql_todo)->fetch(PDO::FETCH_ASSOC)['totalTasksTodo'];

    $sql_cancel = "SELECT COUNT(*) AS totalTasksCanceled FROM tâches WHERE statut = 'Annulée'";
    $total_canceled = $pdo->query($sql_cancel)->fetch(PDO::FETCH_ASSOC)['totalTasksCanceled'];

    $sql_urg = "SELECT COUNT(*) AS totalHighPriority FROM tâches WHERE statut = ' À faire' AND priorité = 'Haute'";
    $total_priority = $pdo->query($sql_urg)->fetch(PDO::FETCH_ASSOC)['totalHighPriority'];

    // Récupérer la liste des utilisateurs
    $sql_users = "SELECT id, nom, email, role FROM utilisateur";
    $users = $pdo->query($sql_users)->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si la requête AJAX demande les statistiques
    if (isset($_GET['stats'])) {
        header('Content-Type: application/json');
        echo json_encode([
            "success" => true,
            "stats" => [
                "total_users" => $total_users,
                "total_tasks" => $total_tasks,
                "total_admins" => $total_admins,
                "totalTasksCompleted"=> $total_completed,
                "totalTasksInprogress"=> $total_completed,
                "totalTasksTodo"=> $total_todo,
                "totalTasksCanceled"=> $total_canceled,
                "totalHighPriority"=> $total_priority
    
            ]
        ]);
        exit;
    }


?> 

<style>
    :root {
    --primary-color: #fd79a8;
    --secondary-color: #a29bfe;
    --accent-color: #6c5ce7;
    --background-color: #f5f6fa;
    --card-color: #ffffff;
    --text-color: #2d3436;
    --light-text: #636e72;
    --success-color: rgb(91, 166, 240);
    --warning-color: rgb(247, 210, 141);
    --danger-color: #d63031;
}
            /* ✅ Style général */
            body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    background-color: var(--background-color); /* ✅ Couleur de fond générale */
}

    /* ✅ Barre latérale */
.sidebar {
    width: 250px;
    background: var(--accent-color); /* Couleur accentuée pour la sidebar */
    color: white;
    padding: 20px;
    position: fixed;
    height: 100%;
}

    .sidebar h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .sidebar ul li {
        margin: 20px 0;
    }

    .sidebar ul li a {
        color: white;
        text-decoration: none;
        display: block;
        padding: 10px;
        transition: 0.3s;
    }

    .sidebar ul li a:hover {
    background: var(--secondary-color); /* ✅ Couleur au survol */
    border-radius: 5px;
}


.logout {
    color: var(--danger-color); /* ✅ Bouton déconnexion en rouge */
    font-weight: bold;
}


    /* ✅ Contenu principal */
    .content {
        margin-left: 280px; /* Ajusté pour s'aligner avec la sidebar */
        padding: 20px;
        flex-grow: 1;
    }

    .welcome-message {
    margin-left: 150px; /* Pour éviter que ce soit sous la sidebar */
    padding: 15px;
    color: var(--primary-color);
    font-size: 30px;
}
    /* ✅ Section statistiques */
    .stats {
        display: flex;
        flex-wrap: wrap; /* S'adapte aux petits écrans */
        justify-content: space-between;
        gap: 20px;
        margin: 20px 0;
    }

    .stat-box {
    flex: 1;
    min-width: 200px;
    max-width: 280px;
    background: var(--card-color);
    color: var(--text-color);
    padding: 15px;
    text-align: center;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
}

    .stat-box:hover {
        transform: scale(1.05); /* Animation au survol */
    }

    /* Boîte spéciale pour les tâches urgentes */
    .urgent-task-box {
    background-color: var(--danger-color);
    color: white;
    padding: 25px;
    border-radius: 12px;
    width: 200px;
    margin: 0 auto; /* Centrer */
    text-align: center;
    font-weight: bold;
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}
    .urgent-task-box:hover {
            transform: scale(1.05); /* Animation au survol */
        }

    /* ✅ Table des utilisateurs */
    table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: var(--card-color);
    border-radius: 5px;
    overflow: hidden;
}

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
    background: var(--success-color); /* ✅ En-tête coloré */
    color: white;
}

    tr:hover {
        background: #f1f1f1;
    }
    .user-management h2 {
    color: var(--text-color);
}



    /* ✅ Boutons d'action */
    .action-btn {
        padding: 5px 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    .edit-btn {
    background: var(--success-color);
    color: white;
}

.delete-btn {
    background: var(--danger-color);
    color: white;
}

/* ✅ Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
        padding: 10px;
    }

    .content {
        margin-left: 200px;
        padding: 15px;
    }

    .stats {
        flex-direction: column; /* Stats en colonne sur petits écrans */
        align-items: center;
    }

    .stat-box {
        width: 90%;
    }

    .chart-container {
        width: 100%;
    }
}
</style>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin</title>
</head>
<body>
    <nav class="sidebar">
        <h2>Espace Admin</h2>
        <ul>
            <li><a href="index.php">🏠 Acceuil</a></li>
            <li><a href="admin.php">📋 Tableau de Bord</a></li>
            <li><a href="gestion-utilisateur.php">👥 Gestion des Utilisateurs</a></li>
            <li><a href="stat.php">📊 Statistiques</a></li>
            <li><a href="logout.php" class="logout">🚪 Déconnexion</a></li>
        </ul>
    </nav>

    <main class="content">
        <div class="welcome-message">
    <?php
        if (isset($_COOKIE['nom_utilisateur']) && isset($_COOKIE['role'])) {
            echo "<p>Bienvenue, <strong>" . htmlspecialchars($_COOKIE['nom_utilisateur']) . "</strong></p>";
            echo "<p>Votre rôle : <strong>" . htmlspecialchars($_COOKIE['role']) . "</strong></p>"; 
        }
    ?>
</div>

    <section class="stats">
    <div class="stat-box"><h3>Admins</h3><p id="totalAdmins"><?php echo $total_admins; ?></p></div>
    <div class="stat-box"><h3>Utilisateurs</h3><p id="totalUsers"><?php echo $total_users; ?></p></div>
    <div class="stat-box"><h3>Tâches Créées</h3><p id="totalTasks"><?php echo $total_tasks; ?></p></div>
    <div class="stat-box"><h3>Tâches Terminées</h3><p id="totalTasksCompleted"><?php echo $total_completed; ?></p></div>
    <div class="stat-box"><h3>Tâches En cours</h3><p id="totalTasksInprogress"><?php echo $total_progress; ?></p></div>
    <div class="stat-box"><h3>Tâches à faire</h3><p id="totalTasksTodo"><?php echo $total_todo; ?></p></div>
    <div class="stat-box"><h3>Tâches Annulées</h3><p id="totalTasksCanceled"><?php echo $total_canceled; ?></p></div>
</section>

<section class="urgent-task-box">
    <h3>Tâches Urgentes</h3>
    <p id="totalHighPriority"><?php echo $total_priority; ?></p>
</section>





        <section class="user-management">
            <h2>Liste des Utilisateurs</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) {
                        echo "<tr><td>{$user['id']}</td><td>{$user['nom']}</td><td>{$user['email']}</td><td>{$user['role']}</td></tr>";
                    } ?>
                </tbody>
            </table>
        </section>
    </main>



</body>
</html>
