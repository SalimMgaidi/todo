<?php 
ob_start(); 
require("connexion.php");
session_start();
$_SESSION["nbnotif"] = $conn->query("select count(id) as c from notifications where is_read=0")->fetch_assoc()["c"];

// Handle profile picture initialization
if (!isset($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'rabbit.jpg'; // Default profile picture
}

// Get current user info
$id = $_SESSION["utilisateur_id"];
$stmt = $conn->query("SELECT nom, email, mdp as password FROM utilisateur WHERE id=$id");
$user = $stmt->fetch_assoc();
$_SESSION["user"] = $user;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // [Keep all your existing update profile code]
}

// Check if profile picture is set in session
$profile_pic = $_SESSION['profile_pic'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <style>
        /* [Keep all your existing CSS styles] */
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
        /* Additional styles for the profile section */
        .profile-main-container {
            display: flex;
            gap: 50px;
            align-items: flex-start;
            margin-top: 30px;
        }
        
        .profile-picture-card {
            flex: 1;
            max-width: 350px;
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .profile-image-container {
            position: relative;
            margin-bottom: 30px;
        }
        
        .profile-image {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            object-fit: cover;
            border: 8px solid #fd79a8;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .profile-upload-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: #6c5ce7;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .profile-info-card {
            flex: 2;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .profile-form-title {
            font-size: 28px;
            color: #6c5ce7;
            margin-bottom: 30px;
        }
        
        .profile-form-group {
            margin-bottom: 25px;
        }
        
        .profile-form-label {
            display: block;
            margin-bottom: 10px;
            font-size: 18px;
            color: #2d3436;
            font-weight: 600;
        }
        
        .profile-form-input {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .profile-submit-btn {
            background: #fd79a8;
            color: white;
            border: none;
            padding: 16px 40px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            width: 100%;
        }
        
        .profile-submit-btn:hover {
            background: #e66797;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h1 class="logo">Taskify</h1>
            <nav class="nav-links">
                <a href="user.php" class="nav-item">◉ Accueil</a>
                <a href="user.php#mestaches" class="nav-item">✓ Mes Taches</a>
                <a href="user.php?isread=true#notif" class="nav-item">✉ Notifications <span style="color:red;margin-left:50px"><?php echo $_SESSION["nbnotif"] ?? 0 ?></span></a>             
                <a href="user.php?dec=true" style="color:yellow" class="nav-item">⇥ Deconnexion</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="task-container">
            <header>
                <div class="profile-container">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profil" class="profile-pic" onclick="toggleMenu()">
                </div>
            </header>

            <h1 class="welcome-title">Mon Profil</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <div class="profile-main-container">
                <!-- Profile Picture Section -->
                <div class="profile-picture-card">
                    <div class="profile-image-container">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Photo de profil" class="profile-image" id="profileImageDisplay">
                        <div class="profile-upload-btn">
                            <label for="profile_pic" style="cursor: pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </label>
                        </div>
                    </div>
                    <h3 style="font-size: 24px; color: #2d3436; margin-bottom: 10px;"><?php echo htmlspecialchars($user['nom'] ?? ''); ?></h3>
                    <p style="color: #636e72; font-size: 18px;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                </div>

                <!-- Profile Info Section -->
                <div class="profile-info-card">
                    <h2 class="profile-form-title">Modifier le profil</h2>
                    
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="profile_pic" id="profile_pic" style="display: none;" accept="image/*">
                        
                        <div class="profile-form-group">
                            <label for="nom" class="profile-form-label">Nom</label>
                            <input type="text" id="nom" name="nom" class="profile-form-input" 
                                   value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="profile-form-group">
                            <label for="email" class="profile-form-label">Email</label>
                            <input type="email" id="email" name="email" class="profile-form-input" 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="profile-form-group">
                            <label for="current_password" class="profile-form-label">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" class="profile-form-input">
                        </div>
                        
                        <div class="profile-form-group">
                            <label for="new_password" class="profile-form-label">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" class="profile-form-input">
                        </div>
                        
                        <div class="profile-form-group">
                            <label for="confirm_password" class="profile-form-label">Confirmer le mot de passe</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="profile-form-input">
                        </div>
                        
                        <button type="submit" name="update_profile" class="profile-submit-btn">
                            Mettre à jour le profil
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('profile_pic').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update both the large profile image and the small one in header
                    document.getElementById('profileImageDisplay').src = e.target.result;
                    document.querySelector('.profile-pic').src = e.target.result;
                    
                    // You could also show a success message here
                    alert('Image sélectionnée! Cliquez sur "Mettre à jour le profil" pour enregistrer.');
                }
                reader.readAsDataURL(file);
            }
        });

        // Toggle profile menu
        function toggleMenu() {
            const menu = document.querySelector('.profile-menu');
            if (menu) {
                menu.classList.toggle('show');
            }
        }

        // Close profile menu when clicking outside
        document.addEventListener('click', function(event) {
            const profileContainer = document.querySelector('.profile-container');
            const menu = document.querySelector('.profile-menu');
            
            if (profileContainer && menu && !profileContainer.contains(event.target)) {
                menu.classList.remove('show');
            }
        });
    </script>
</body>
</html>