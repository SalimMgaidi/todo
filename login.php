<?php
// Démarrer la session
session_start();
ob_start(); // Active le tampon de sortie

// Inclure le fichier de connexion à la base de données
include('connexion.php');

// Initialiser une variable d'erreur
$error = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sécuriser les entrées utilisateur
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Préparer la requête SQL pour chercher l'utilisateur avec l'email fourni
    $sql = "SELECT * FROM utilisateur WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email); // 's' signifie que c'est une chaîne
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérifier si l'utilisateur existe
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Vérifier si le mot de passe est correct
        if (password_verify($password, $user['mdp'])) {
            // Créer la session de l'utilisateur
            $_SESSION['utilisateur_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // 🔐 Définir les cookies (valables 1 heure)
            setcookie('nom_utilisateur', $user['nom'], time() + 3600, "/");
            setcookie('role', $user['role'], time() + 3600, "/");
            setcookie('id_utilisateur', $user['id'], time() + 3600, "/");

            // Redirection selon le rôle
            if ($user['role'] === "admin") {
                header("Location: admin.php");
            } else {
                header("Location: user.php");
            }
            exit();
        } else {
            $error = "Mot de passe incorrect !";
        }
    } else {
        $error = "Email non trouvé !";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - To-Do List Avancée</title>
</head>
<style>
    .login-container {
        max-width: 400px;
        margin: 100px auto;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .login-container h2 {
        margin-bottom: 20px;
        font-size: 1.8rem;
        color: #333;
    }

    .input-group {
        text-align: left;
        margin-bottom: 15px;
    }

    .input-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    .input-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }
  
    .error-message {
        color: red;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .btn {
        width: 100%;
        background: #007bff;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn:hover {
        background: #0056b3;
    }

    .register-link {
        margin-top: 10px;
        font-size: 0.9rem;
    }

    .register-link a {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }

    .register-link a:hover {
        text-decoration: underline;
    }
    .back-home {
        text-align: left;
        margin-bottom: 15px;
    }

    .back-home a {
        color: #888;
        font-size: 1rem;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
    }

    .back-home a:hover {
        text-decoration: underline;
        color: #007bff;
    }
    .show-password{
        text-align: left;
        margin-bottom: 5px;
        
    }
    .show-password label {
        
        display:inline;
        margin-bottom: 5px;

    }

</style>
<body>
    
    <div class="login-container">
    <div class="back-home">
            <a href="index.php">Retour à l'accueil</a>
        </div>
        <h2>Connexion</h2>
        <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="error-message">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']); // <-- celui-ci est crucial
            }
            ?>


        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="show-password">
                <input type="checkbox" id="showPassword" onclick="togglePassword()"> 
                <label for="showPassword">Afficher le mot de passe</label>
            </div>
            <button type="submit" class="btn">Se connecter</button>

            <p class="register-link">Pas encore de compte ? <a href="register.php">Inscrivez-vous</a></p>
        </form>
    </div>

<script>
    // Vérification en temps réel de l'email
    document.getElementById("email").addEventListener("input", function () {
        let emailField = this;
        let emailValue = emailField.value;
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Format email valide

        if (!emailPattern.test(emailValue)) {
            emailField.style.border = "2px solid red";
        } else {
            emailField.style.border = "2px solid green";
        }
    });

    // Afficher/Masquer le mot de passe
    /*document.getElementById("password").addEventListener("focus", function () {
        this.type = "text";
    });

    document.getElementById("password").addEventListener("blur", function () {
        this.type = "password";
    });*/
    function togglePassword() {
            var passwordField = document.getElementById("password");
            var showPasswordCheckbox = document.getElementById("showPassword");
            
            // Si la case est cochée, afficher le mot de passe, sinon le masquer
            if (showPasswordCheckbox.checked) {
                passwordField.type = "text";  // Afficher le mot de passe
            } else {
                passwordField.type = "password";  // Masquer le mot de passe
            }
        }
</script>
    
</body>
</html>
