<?php
require("connexion.php");

// Récupérer les statistiques par utilisateur
$statsQuery = "SELECT u.id, u.nom, u.email, u.role,
    COUNT(t.id) AS total,
    SUM(CASE WHEN t.statut = 'Terminée' THEN 1 ELSE 0 END) AS terminees,
    SUM(CASE WHEN t.statut = 'En cours' THEN 1 ELSE 0 END) AS encours,
    SUM(CASE WHEN t.statut = 'À faire' THEN 1 ELSE 0 END) AS afaire,
    SUM(CASE WHEN t.statut = 'Annulée' THEN 1 ELSE 0 END) AS annulees
FROM utilisateur u
LEFT JOIN tâches t ON u.id = t.utilisateur_id
GROUP BY u.id";

$statsResult = $conn->query($statsQuery);
$utilisateurs = [];

if ($statsResult) {
    while ($row = $statsResult->fetch_assoc()) {
        $utilisateurs[] = $row;
    }
}

// Trouver l'utilisateur admin le plus actif
$plusActif = null;
$maxTachesTerminees = -1;

foreach ($utilisateurs as $user) {
    if ($user['role'] === 'utilisateur' && $user['terminees'] > $maxTachesTerminees) {
        $maxTachesTerminees = $user['terminees'];
        $plusActif = $user;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques par Utilisateur</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ✅ Style général */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f4f4f4;
        }

        /* ✅ Barre latérale */
        .sidebar {
            width: 250px;
            background: #6c5ce7;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            color: white;
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
            background: #34495e;
            border-radius: 5px;
        }

        /* ✅ Lien de déconnexion */
        .logout {
            color: #e74c3c;
            font-weight: bold;
        }

        /* ✅ Contenu principal */
        .content {
            margin-left: 280px; /* Alignement avec la sidebar */
            padding: 20px;
            flex-grow: 1;
        }

        /* ✅ Titre principal */
        h1 {
            color: #2c3e50;
        }

        /* ✅ Tableau */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background-color: #fff;
            box-shadow: 0 0 8px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        thead {
            background-color: #3498db;
            color: white;
        }

        th, td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* ✅ Encadré de mise en valeur */
        .highlight {
            background-color: #dff9fb;
            border-left: 5px solid #00cec9;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 5px;
        }

        /* ✅ Barre de progression */
        .progress-bar {
            background-color: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .progress {
            background-color: #2ecc71;
            height: 100%;
            transition: width 0.3s ease-in-out;
        }

        /* ✅ Section du graphique en camembert */
        .pie-chart-section {
            max-width: 400px;
            margin: 3rem auto;
            text-align: center;
        }

        .pie-chart-section h2 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .pie-chart-section canvas {
            width: 100% !important;
            height: auto !important;
            max-height: 300px;
        }

        /* ✅ Section des graphiques */
        section {
            margin-top: 3rem;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* ✅ Titres secondaires */
        h2 {
            color: #2c3e50;
        }

        /* ✅ Responsive pour mobiles */
        @media screen and (max-width: 768px) {
            main.content {
                margin-left: 0;
                padding: 1rem;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            tr {
                margin-bottom: 1rem;
                border: 1px solid #ccc;
                border-radius: 8px;
            }

            td {
                text-align: left;
                padding: 0.75rem 1rem;
                border-bottom: none;
                border-top: 1px solid #eee;
            }

            td:first-child {
                border-top: none;
            }

            th {
                display: none;
            }
        }
    </style>
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
    <h1>Statistiques par Utilisateur</h1>

    <!-- Utilisateur le plus actif -->
    <?php if ($plusActif): ?>
        <div class="highlight">
            <strong>Utilisateur le plus actif :</strong> <?php echo htmlspecialchars($plusActif['nom']); ?> <br>
            <strong>Tâches terminées :</strong> <?php echo $plusActif['terminees']; ?> <br>
            <strong>Email :</strong> <?php echo htmlspecialchars($plusActif['email']); ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques individuelles -->
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Total</th>
                <th>Terminées</th>
                <th>En cours</th>
                <th>A faire</th>
                <th>Annulées</th>
                <th>Taux de complétion</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($utilisateurs as $u): ?>
            <?php if ($u['role'] !== 'admin'): ?>
                <?php
                    $taux = ($u['total'] > 0) ? round(($u['terminees'] / $u['total']) * 100) : 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['nom']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo $u['total']; ?></td>
                    <td><?php echo $u['terminees']; ?></td>
                    <td><?php echo $u['encours']; ?></td>
                    <td><?php echo $u['afaire']; ?></td>
                    <td><?php echo $u['annulees']; ?></td>
                    <td>                        
                        <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $taux; ?>%"></div>
                        </div>
                        <?php echo $taux; ?>%
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <section class="pie-chart-section">
        <h2>Répartition des Tâches par Utilisateur</h2>
        <canvas id="userPieChart" height="100"></canvas>
    </section>

    <section>
        <h2>Tâches Terminées par Utilisateur</h2>
        <canvas id="barChart" height="100"></canvas>
    </section>
    </main>
 
    <?php
    $labels = [];
    $data = [];
    $userLabels = [];
    $userTaskCounts = [];

    foreach ($utilisateurs as $u) {
        if ($u['role'] === 'utilisateur') {
            $labels[] = $u['nom'];
            $data[] = $u['terminees'];
            $userLabels[] = $u['nom'];
            $userTaskCounts[] = $u['total'];
        }
    }
    ?>

    <script>
        const userPieCtx = document.getElementById('userPieChart').getContext('2d');
        const userLabels = <?php echo json_encode($userLabels); ?>;
        const userTaskCounts = <?php echo json_encode($userTaskCounts); ?>;

        const userPieChart = new Chart(userPieCtx, {
            type: 'pie',
            data: {
                labels: userLabels,
                datasets: [{
                    label: 'Tâches par utilisateur',
                    data: userTaskCounts,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#E7E9ED', '#76C893'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        const ctx = document.getElementById('barChart').getContext('2d');
        const labels = <?php echo json_encode($labels); ?>;
        const data = <?php echo json_encode($data); ?>;
        
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tâches terminées',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>