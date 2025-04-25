<?php 
ob_start(); 
require("connexion.php");
session_start();
$_SESSION["nbnotif"] = $conn->query("select count(id) as c from notifications where is_read=0")->fetch_assoc()["c"];

//is read notifs
if (isset($_GET['isread']) && $_GET['isread'] === 'true') {
    sleep(6);
    $userId = $_SESSION['utilisateur_id']; 
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");
    header("location: user.php");
}

//DECONNEXION
if(isset($_GET["dec"]) && $_GET["dec"] == "true"){
    session_destroy();
    
    header("Location: login.php");
    exit();
}
//del compte

if(isset($_GET["delacc"]) && $_GET["delacc"] == "true" && isset($_SESSION["utilisateur_id"])){
    $id = $_SESSION["utilisateur_id"];
    echo "ID à supprimer : " . $id;

    $sql = "DELETE FROM utilisateur WHERE id = $id";
    if ($conn->query($sql)) {
        echo "Suppression réussie.";
        header("Location: index.php");

    } else {
        echo "Erreur : " . $conn->error;
    }

    session_destroy();
    exit();
}

// Handle task editing
$edit_task = null;
if (isset($_GET['edit'])) {
    $task_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tâches WHERE id = ? AND utilisateur_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['utilisateur_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_task = $result->fetch_assoc();
}

$id=$_SESSION["utilisateur_id"];
$stmt=$conn->query(query: "select nom,email from utilisateur where id=$id");
$user=$stmt->fetch_assoc();
$_SESSION["user"]=$user;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <style>
        :root {
    --primary-color: #fd79a8;
    --secondary-color: #a29bfe;
    --accent-color: #6c5ce7;
    --background-color: #f5f6fa;
    --card-color: #ffffff;
    --text-color: #2d3436;
    --light-text: #636e72;
    --success-color:rgb(91, 166, 240);
    --warning-color:rgb(247, 210, 141);
    --danger-color: #d63031;
}

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            height: 100vh;
            position: sticky;
            top: 0;
        }

        .logo {
            font-size: 24px;
            margin-bottom: 30px;
            color: white;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }

        .nav-item {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-item:hover {
            background-color: var(--secondary-color);
        }

        .inbox {
            cursor: pointer;
        }

        .inbox-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            margin-left: 20px;
        }

        .inbox-container.show {
            max-height: 500px;
        }

        .inbox-list {
            list-style-type: none;
            margin-top: 10px;
        }

        .inbox-list li {
            padding: 8px 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .divider {
            border: none;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 20px 0;
        }

        .categories {
            list-style-type: none;
        }

        .category {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category:hover {
            background-color: var(--secondary-color);
        }

        .category.active {
            background-color: var(--accent-color);
        }

        /* Main Content Styles */
        .task-container {
            flex: 1;
            padding: 30px;
        }

        header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .profile-container {
            position: relative;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            object-fit: cover;
        }

        .profile-menu {
            position: absolute;
            right: 0;
            background-color: var(--card-color);
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 150px;
            list-style-type: none;
            display: none;
            z-index: 100;
        }

        .profile-menu.show {
            display: block;
        }

        .profile-menu li a {
            display: block;
            padding: 10px 15px;
            color: var(--text-color);
            text-decoration: none;
        }

        .profile-menu li a:hover {
            background-color: var(--background-color);
        }

        .welcome-title {
            font-size: 28px;
            margin-bottom: 20px;
            color: var(--secondary-color);
        }

        /* Tasks Section */
        .tasks-section {
            margin-top: 30px;
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .task-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .task-card {
            background-color: var(--card-color);
            margin-bottom: 20px;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .task-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .task-description {
            color: var(--light-text);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .task-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--light-text);
            margin-bottom: 15px;
        }

        .task-priority {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .priority-high {
            background-color: var(--danger-color);
            color: white;
        }

        .priority-medium {
            background-color: var(--warning-color);
            color: var(--text-color);
        }

        .priority-low {
            background-color: var(--success-color);
            color: white;
        }

        .task-actions {
            display: flex;
            gap: 10px;
        }

        .task-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.3s;
        }

        .edit-btn {
            background-color: var(--secondary-color);
            color: white;
        }

        .edit-btn:hover {
            background-color: var(--primary-color);
        }

        .delete-btn {
            background-color: var(--danger-color);
            color: white;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .complete-btn {
            background-color: var(--success-color);
            color: white;
        }

        .complete-btn:hover {
            background-color: #00a884;
        }

        /* Add Task Form */
        .add-task-form {
            background-color: var(--card-color);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: var(--secondary-color);
        }

        .cancel-btn {
            background-color: var(--light-text);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
        }

        .cancel-btn:hover {
            background-color: #636e72;
        }

        /* Search Section */
        .search-section {
            margin-bottom: 30px;
        }

        #searchInput {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        #searchBtn {
            margin-top: 10px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h1 class="logo">Taskify</h1>
            
            <nav class="nav-links">
                <a href="index.php" class="nav-item">◉ Accueil</a>
                <a href="#mestaches" class="nav-item" >✓ Mes Taches</a>
                <a href="user.php?isread=true#notif" class="nav-item">✉ Notifications <span style="color:red;margin-left:50px"><?php echo $_SESSION["nbnotif"] ?? 0 ?></span></a>             
                <a href="user.php?dec=true" style="color:yellow" class="nav-item">⇥ Deconnexion</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="task-container">
            <!-- Profil en haut à droite -->
            <header>
                <div class="profile-container">
                    <img src="rabbit.jpg" alt="Profil" class="profile-pic" onclick="toggleMenu()">  
                    <ul class="profile-menu" id="profileMenu">
                        <li><a href="profile.php">Mon Profil</a></li>
                        <li><a style="color:orange" href="user.php?delacc=true">Se retirer</a></li>
                        <li><a style="color:red" href="user.php?dec=true">Déconnexion</a></li>
                    </ul>
                </div>
            </header>

            <h1 class="welcome-title">Bienvenue <?php echo $_SESSION["user"]["nom"] ." !" ?></h1>
            <p style="margin-bottom: 20px;color: var(--primary-color)"> <?php echo "--". $_SESSION["user"]["email"]."--" ?></p>

            <!-- Add/Edit Task Form -->
            <div class="add-task-form">
                <h2 class="section-title"><?php echo $edit_task ? '✏️ Modifier la tâche' : '➕ Ajouter une nouvelle tâche'; ?></h2>
                <form method="POST" action="">
                    <?php if ($edit_task): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_task['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="taskTitle">Titre</label>
                        <input type="text" id="taskTitle" name="titre" class="form-control" 
                               value="<?php echo $edit_task ? htmlspecialchars($edit_task['titre']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="taskDescription">Description</label>
                        <textarea id="taskDescription" name="description" class="form-control" required><?php 
                            echo $edit_task ? htmlspecialchars($edit_task['description']) : ''; 
                        ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="taskPriority">Priorité</label>
                        <select id="taskPriority" name="priorité" class="form-control">
                            <option value="low" <?php echo ($edit_task && $edit_task['priorité'] == 'low') ? 'selected' : ''; ?>>Faible</option>
                            <option value="medium" <?php echo ($edit_task && $edit_task['priorité'] == 'medium') ? 'selected' : ''; ?>>Moyenne</option>
                            <option value="high" <?php echo ($edit_task && $edit_task['priorité'] == 'high') ? 'selected' : ''; ?>>Élevée</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn" name="<?php echo $edit_task ? 'update' : 'create'; ?>">
                        <?php echo $edit_task ? 'Mettre à jour' : 'Ajouter la tâche'; ?>
                    </button>
                    <?php if ($edit_task): ?>
                        <a href="user.php" class="cancel-btn">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>

            <div id="mestaches">
                <h1 style="margin-bottom: 20px;color: var(--secondary-color);padding-top:10px">Mes Taches</h1>
            </div>

            <div id="search-section" class="search-section">
                <form method="post" action="">
                    <input type="text" id="searchInput" name="inbtn" placeholder="Rechercher une tâche..." />
                    <button id="searchBtn" name="sbtn">Rechercher</button>
                </form>
            </div>

<?php
// CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $priorite = $_POST['priorité'] ?? '';
    $id = $_SESSION["utilisateur_id"];
    $msg = $titre . " a été créé";

    if (!empty($titre)) {
        $stmt = $conn->prepare("INSERT INTO tâches (titre, description, priorité, utilisateur_id, date_creation) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $titre, $description, $priorite, $id);

        if ($stmt->execute()) {
            $stm = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stm->bind_param("is", $id, $msg);
            $stm->execute();
            $stm->close();
            header("Location: user.php");
            exit();
        } else {
            echo "<p>Erreur : " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Le titre est requis.</p>";
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'] ?? '';
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $priorite = $_POST['priorité'] ?? '';
    
    if (!empty($titre)) {
        $stmt = $conn->prepare("UPDATE tâches SET titre = ?, description = ?, priorité = ? WHERE id = ?");
        $stmt->bind_param("sssi", $titre, $description, $priorite, $id);

        if ($stmt->execute()) {
            $msg = $titre . " a été modifié";
            $stm = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stm->bind_param("is", $_SESSION["utilisateur_id"], $msg);
            $stm->execute();
            $stm->close();
            header("Location: user.php");
            exit();
        } else {
            echo "<p>Erreur : " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Le titre est requis.</p>";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $getTitle = $conn->prepare("SELECT titre FROM tâches WHERE id = ?");
    $getTitle->bind_param("i", $id);
    $getTitle->execute();
    $result = $getTitle->get_result();
    $row = $result->fetch_assoc();
    $titre = $row["titre"] ?? "Tâche";

    $msg = $titre . " a été supprimé";
    $getTitle->close();

    $stmt = $conn->prepare("DELETE FROM tâches WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $userId = $_SESSION["utilisateur_id"];
        $stm = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stm->bind_param("is", $userId, $msg);
        $stm->execute();
        $stm->close();
        header("Location: user.php");
        exit();
    } else {
        echo "<p>Erreur : " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// READ/SEARCH
$rows = [];
$userId = $_SESSION['utilisateur_id'];

if (isset($_POST["sbtn"])) {
    $titre = trim($_POST["inbtn"]);
    $search = "%" . $titre . "%";

    $stmt = $conn->prepare("SELECT * FROM tâches WHERE titre LIKE ? AND utilisateur_id = ? ORDER BY date_creation DESC");
    $stmt->bind_param("si", $search, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT * FROM tâches WHERE utilisateur_id = ? ORDER BY date_creation DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

if (!empty($rows)) {
    foreach ($rows as $row) {
        $priority_class = match($row['priorité']) {
            'high' => 'priority-high',
            'medium' => 'priority-medium',
            default => 'priority-low'
        };

        echo '<div class="task-card">';
        echo '<h3 class="task-title">' . htmlspecialchars($row['titre']) . '</h3>';
        echo '<p class="task-description">' . htmlspecialchars($row['description']) . '</p>';
        echo '<div class="task-meta">';
        echo '<span>📅 ' . date('d/m/Y', strtotime($row['date_creation'])) . '</span>';
        echo '<span class="task-priority ' . $priority_class . '">' . $row['priorité']. '</span>';
        echo '</div>';
        echo '<div class="task-actions">';
        echo '<a href="user.php?edit=' . $row['id'] . '" class="task-btn edit-btn">Modifier</a>';
        echo '<a href="user.php?delete=' . $row['id'] . '" class="task-btn delete-btn">Supprimer</a>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<p>Aucune tâche trouvée.</p>';
}

// READ NOTIFICATIONS 
?> 
<section id="notif">
<div  >
<h1  style="margin-bottom: 20px;color: var(--secondary-color);padding-top:10px">Notifications</h1>

<?php 
require("connexion.php");
$notifs = $conn->query("SELECT message, created_at FROM notifications WHERE is_read=0");

while ($notif = $notifs->fetch_assoc()) {
    echo '<div class="task-card">';
    echo '<h3 >' . htmlspecialchars($notif["message"]) . '</h3>';
    echo '<div class="task-meta">';
    echo '<span>📅 ' . date('d/m/Y', strtotime($notif["created_at"])) . '</span>';
    echo '</div>';
    echo '</div>';
}
?>
</div>
</section>

    <script>
        // Toggle profile menu
        function toggleMenu() {
            document.getElementById('profileMenu').classList.toggle('show');
        }

        // Close profile menu when clicking outside
        document.addEventListener('click', function(event) {
            const profileContainer = document.querySelector('.profile-container');
            if (!profileContainer.contains(event.target)) {
                document.getElementById('profileMenu').classList.remove('show');
            }
        });

        // Search functionality
        document.getElementById('searchBtn').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const taskCards = document.querySelectorAll('.task-card');
            
            let resultsFound = false;
            
            taskCards.forEach(card => {
                const title = card.querySelector('.task-title').textContent.toLowerCase();
                const description = card.querySelector('.task-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                    resultsFound = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (!resultsFound) {
                document.getElementById('mestaches').innerHTML += '<p>Aucun résultat trouvé.</p>';
            }
        });
    </script>
</body>
</html>