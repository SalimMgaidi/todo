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

    // Ajouter un utilisateur (normal ou admin)
    if (isset($_POST['ajouter_utilisateur'])) {
        $nom = $_POST['nom'];
        $email = $_POST['email'];
        $motdepasse = $_POST['motdepasse'];
        
        // Vérifier si l'email existe déjà
        $sql_check = "SELECT id FROM utilisateur WHERE email = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$email]);
        
        if ($stmt_check->rowCount() > 0) {
            header("Location: gestion-utilisateur.php?error=email_exists");
            exit;
        }

        // Hacher le mot de passe
        $motdepasse_hache = password_hash($motdepasse, PASSWORD_BCRYPT);

        // Récupérer le rôle depuis le formulaire (par défaut 'utilisateur')
        $role = $_POST['role'] ?? 'utilisateur';

        // Insérer l'utilisateur dans la base de données
        $sql = "INSERT INTO utilisateur (nom, email, mdp, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $email, $motdepasse_hache, $role]);
        
        // Redirection avec un message de succès différent selon le rôle
        if ($role === 'admin') {
            header("Location: gestion-utilisateur.php?success=admin_added");
        } else {
            header("Location: gestion-utilisateur.php?success=user_added");
        }
        exit;
    }

    // Modifier un utilisateur
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id = $_GET['id'];

        // Récupérer les informations de l'utilisateur à modifier
        $sql = "SELECT * FROM utilisateur WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $user_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (isset($_POST['modifier_utilisateur'])) {
            $nom = $_POST['nom'];
            $email = $_POST['email'];
            $role = $_POST['role'];

            // Mettre à jour l'utilisateur dans la base de données
            $sql = "UPDATE utilisateur SET nom = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $email, $role, $id]);

            header("Location: gestion-utilisateur.php");
            exit;
        }
    }

    // Supprimer un utilisateur
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        $sql = "DELETE FROM utilisateur WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: gestion-utilisateur.php");
        exit;
    }

    // Récupérer la liste des utilisateurs (sans les admins)
    $sql_users = "SELECT id, nom, email, role FROM utilisateur WHERE role != 'admin'";
    $users = $pdo->query($sql_users)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <style>
       :root {
    --primary-color: #fd79a8;
    --secondary-color: #a29bfe;
    --accent-color: #6c5ce7;
    --background-color: #f5f6fa;
    --card-color: #ffffff;
    --text-color: #2d3436;
    --light-text: #636e72;
    --success-color: #2ecc71;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --info-color: #3498db;
    --info-dark: #2980b9;
    --hover-color: #ecf0f1;
}

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    background-color: var(--background-color);
}

.sidebar {
    width: 250px;
    background: var(--accent-color);
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
    background: var(--secondary-color);
    border-radius: 5px;
}

.logout {
    color: var(--danger-color);
    font-weight: bold;
}

main {
    margin-left: 280px;
    padding: 20px;
    flex-grow: 1;
}

h2 {
    font-size: 20px;
    color: var(--primary-color);
}

h1 {
    font-size: 28px;
    color: var(--primary-color);
}

form {
    background-color: var(--card-color);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
}

form label {
    display: block;
    font-size: 16px;
    margin-bottom: 8px;
    color: var(--light-text);
}

form input, form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

form input:focus, form select:focus {
    outline: none;
    border-color: var(--info-color);
}

form button {
    background-color: var(--info-color);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

form button:hover {
    background-color: var(--info-dark);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: var(--card-color);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

table th, table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: var(--info-color);
    color: white;
    font-size: 18px;
}

table tr:hover {
    background-color: var(--hover-color);
}

table td a {
    color: white;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 4px;
    margin-right: 5px;
}

table td a.edit {
    background-color: var(--warning-color);
}

table td a.delete {
    background-color: var(--danger-color);
}

table td a:hover {
    opacity: 0.8;
}

.success-message {
    background-color: var(--success-color);
    color: white;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
}

.error-message {
    background-color: var(--danger-color);
    color: white;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
}

#toggleAdminForm {
    background-color: var(--warning-color);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-bottom: 20px;
}

#toggleAdminForm:hover {
    background-color: #e67e22; /* slightly darker warning shade */
}

#adminFormContainer {
    background-color: var(--card-color);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
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
        <h1>Gestion des Utilisateurs</h1>

        <?php 
        if (isset($_GET['success'])): 
            $message = '';
            if ($_GET['success'] === 'user_added') {
                $message = 'L\'utilisateur a été ajouté avec succès.';
            } elseif ($_GET['success'] === 'admin_added') {
                $message = 'L\'administrateur a été ajouté avec succès.';
            }
        ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'email_exists'): ?>
            <div class="error-message">Cet email est déjà utilisé par un autre utilisateur.</div>
        <?php endif; ?>

        <!-- Formulaire d'ajout d'utilisateur normal -->
        <section class="user-management">
            <h2>Ajouter un Utilisateur</h2>
            <form action="gestion-utilisateur.php" method="POST">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>

                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>

                <label for="motdepasse">Mot de passe généré :</label>
                <input type="text" id="motdepasse" name="motdepasse" readonly required>
                <button type="button" onclick="genererMotDePasse()">Générer un mot de passe</button>

                <button type="submit" name="ajouter_utilisateur">Ajouter l'Utilisateur</button>
            </form>
        </section>

        <!-- Bouton et formulaire pour ajouter un admin -->
        <section class="user-management">
            <button id="toggleAdminForm">Ajouter un Administrateur</button>
            
            <div id="adminFormContainer" style="display: none;">
                <h2>Ajouter un Administrateur</h2>
                <form action="gestion-utilisateur.php" method="POST">
                    <label for="admin_nom">Nom :</label>
                    <input type="text" id="admin_nom" name="nom" required>

                    <label for="admin_email">Email :</label>
                    <input type="email" id="admin_email" name="email" required>

                    <label for="admin_motdepasse">Mot de passe généré :</label>
                    <input type="text" id="admin_motdepasse" name="motdepasse" readonly required>
                    <button type="button" onclick="genererMotDePasseAdmin()">Générer un mot de passe</button>

                    <input type="hidden" name="role" value="admin">
                    
                    <button type="submit" name="ajouter_utilisateur" style="background-color: #f39c12;">
                        Ajouter l'Administrateur
                    </button>
                </form>
            </div>
        </section>

        <!-- Liste des utilisateurs -->
        <section class="user-management">
            <h2>Liste des Utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td>
                                <a href="gestion-utilisateur.php?action=edit&id=<?php echo $user['id']; ?>" class="edit">Modifier</a> 
                                <a href="gestion-utilisateur.php?action=delete&id=<?php echo $user['id']; ?>" class="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Formulaire de modification d'utilisateur -->
        <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($user_to_edit)): ?>
            <section class="user-management">
                <h2>Modifier l'Utilisateur</h2>
                <form action="gestion-utilisateur.php?action=edit&id=<?php echo $user_to_edit['id']; ?>" method="POST">
                    <label for="edit_nom">Nom :</label>
                    <input type="text" id="edit_nom" name="nom" value="<?php echo htmlspecialchars($user_to_edit['nom']); ?>" required>

                    <label for="edit_email">Email :</label>
                    <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($user_to_edit['email']); ?>" required>

                    <label for="edit_role">Rôle :</label>
                    <select name="role" id="edit_role" required>
                        <option value="utilisateur" <?php echo $user_to_edit['role'] == 'utilisateur' ? 'selected' : ''; ?>>Utilisateur</option>
                        <option value="admin" <?php echo $user_to_edit['role'] == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                    </select>

                    <button type="submit" name="modifier_utilisateur">Modifier l'Utilisateur</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction de génération de mot de passe
        function genererMotDePasse(longueur, champId) {
            const caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#&!$%';
            let motdepasse = '';
            for (let i = 0; i < longueur; i++) {
                motdepasse += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            document.getElementById(champId).value = motdepasse;
        }

        // Gestion du formulaire admin
        const toggleBtn = document.getElementById('toggleAdminForm');
        const adminForm = document.getElementById('adminFormContainer');
        
        if (toggleBtn && adminForm) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = adminForm.style.display === 'none' || adminForm.style.display === '';
                adminForm.style.display = isHidden ? 'block' : 'none';
                this.textContent = isHidden ? 'Masquer le formulaire' : 'Ajouter un Administrateur';
                
                // Génère un mot de passe quand on ouvre le formulaire
                if (isHidden) {
                    genererMotDePasse(12, 'admin_motdepasse');
                }
            });
        }

        // Bouton de génération pour le formulaire admin
        const generateAdminBtn = document.querySelector('#adminFormContainer button[type="button"]');
        if (generateAdminBtn) {
            generateAdminBtn.addEventListener('click', function() {
                genererMotDePasse(12, 'admin_motdepasse');
            });
        }

        // Bouton de génération pour le formulaire utilisateur normal
        const generateUserBtn = document.querySelector('form:not(#adminFormContainer form) button[type="button"]');
        if (generateUserBtn) {
            generateUserBtn.addEventListener('click', function() {
                genererMotDePasse(10, 'motdepasse');
            });
        }

        // Faire disparaître les messages après 5 secondes
        setTimeout(function() {
            const messages = document.querySelectorAll('.success-message, .error-message');
            messages.forEach(msg => {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    });
</script>
</body>
</html>

