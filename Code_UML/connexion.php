<?php
// connexion.php
session_start();
include 'db_connect.php';

$error = "";

if (isset($_POST['btnConnexion'])) {
    $identifiant = $_POST['identifiant'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Préparation de la requête pour récupérer l'utilisateur
    $sql = "SELECT * FROM users WHERE identifiant = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $identifiant);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Vérifier le mot de passe haché
        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['identifiant'] = $user['identifiant'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Identifiant introuvable.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-container">
    <h1>CONNEXION</h1>
    <form method="post" action="connexion.php">
        <label for="identifiant">Identifiant :</label>
        <input type="text" id="identifiant" name="identifiant" required>
        
        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        
        <button type="submit" name="btnConnexion">Se connecter</button>
    </form>
    <?php if (!empty($error)) { echo '<p class="erreur">'.$error.'</p>'; } ?>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>

