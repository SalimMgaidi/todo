<?php
// Démarrer la session
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 't_ches';  // Nom de ta base de données
$username = 'root';  // Nom d'utilisateur MySQL (par défaut sur MAMP)
$password = '';  // Mot de passe MySQL (par défaut sur MAMP)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Échec de la connexion : ' . $e->getMessage();
}

// Vérifier si le formulaire est soumis
if (isset($_POST['submit'])) {
    // Récupérer les valeurs du formulaire
    $nom = $_POST['username'];
    $mdp = $_POST['password'];
    $email= $_POST['email'];
    $confirm_mdp = $_POST['confirmPassword'];

        

    // Vérifier si le mot de passe et la confirmation du mot de passe correspondent
    if ($mdp !== $confirm_mdp) {
        echo "Les mots de passe ne correspondent pas.";
        exit;
    }

    // Vérifier si l'email existe déjà dans la base de données
    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Cet email est déjà utilisé.";
        $_SESSION['old'] = [
            'nom' => $nom,
            'email' => $email
        ];
        
        header('Location: register.php');
        exit;
    }

    // Hacher le mot de passe avant de l'enregistrer
    $hashed_mdp = password_hash($mdp, PASSWORD_DEFAULT);

    // Préparer la requête d'insertion dans la base de données
    $sql = "INSERT INTO utilisateur (nom, email, mdp, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $role = 'utilisateur'; // Par défaut, le rôle de l'utilisateur est "utilisateur"
    
    if ($stmt->execute([$nom, $email, $hashed_mdp, $role])) {
        // Si l'insertion réussit, rediriger vers la page de connexion
        header('Location: login.php');
        exit;
    } else {
        echo "Erreur lors de l'inscription. Veuillez réessayer.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - To-Do List Avancée</title>
</head>
<style>
        /* Page d'inscription */
    .register-container {
        max-width: 400px;
        margin: 100px auto;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .register-container h2 {
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
        font-size: 0.85rem;
        display: none;
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

    .login-link {
        margin-top: 10px;
        font-size: 0.9rem;
    }

    .login-link a {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }

    .login-link a:hover {
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

</style>
<body>
    <div class="register-container">
        <div class="back-home">
            <a href="index.php">Retour à l'accueil</a>
        </div>
        <h2>Inscription</h2>
        <?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red; margin-bottom:15px;">' . $_SESSION['error'] . '</p>';
    unset($_SESSION['error']);
}
?>

        <form action="" method="POST" id="registerForm">
            <div class="input-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_SESSION['old']['nom']) ? htmlspecialchars($_SESSION['old']['nom']) : ''; ?>"
  required>           
 </div>
            <div class="input-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email"
  value="<?php echo isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : ''; ?>"
  required>
                <small id="emailError" class="error-message"></small>
            </div>
            <div class="input-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
                <small id="passwordError" class="error-message"></small>
            </div>
            <div class="input-group">
                <label for="confirmPassword">Confirmer le mot de passe :</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                <small id="confirmPasswordError" class="error-message"></small>
            </div>
            <button type="submit" class="btn" id="submitBtn" name="submit">S'inscrire</button>
            <p class="login-link">Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
        </form>
    </div>

<script >
            document.addEventListener('DOMContentLoaded', function () {
        const registerForm = document.getElementById('registerForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const confirmPasswordError = document.getElementById('confirmPasswordError');

        // Fonction de validation de l'email
        emailInput.addEventListener('input', function () {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailInput.value)) {
                emailError.style.display = 'block';
                emailError.textContent = 'Veuillez entrer un email valide.';
            } else {
                emailError.style.display = 'none';
            }
        });

        // Fonction de validation du mot de passe
        passwordInput.addEventListener('input', function () {
            if (passwordInput.value.length < 6) {
                passwordError.style.display = 'block';
                passwordError.textContent = 'Le mot de passe doit comporter au moins 6 caractères.';
            } else {
                passwordError.style.display = 'none';
            }
        });

        // Validation de la confirmation du mot de passe
        confirmPasswordInput.addEventListener('input', function () {
            if (confirmPasswordInput.value !== passwordInput.value) {
                confirmPasswordError.style.display = 'block';
                confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas.';
            } else {
                confirmPasswordError.style.display = 'none';
            }
        });

        // Validation avant l'envoi du formulaire
        registerForm.addEventListener('submit', function (e) {
            if (emailError.style.display === 'block' || passwordError.style.display === 'block' || confirmPasswordError.style.display === 'block') {
                e.preventDefault();
                alert('Veuillez corriger les erreurs avant de soumettre le formulaire.');
            }
        });
    });

</script>
<?php unset($_SESSION['old']); ?>

</body>
</html>