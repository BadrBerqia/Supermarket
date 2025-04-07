<?php
// register_gestionnaire.php
session_start();

// Seul l'administrateur peut accéder à cette page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion.php");
    exit;
}

include 'db_connect.php';

$error = "";
$message = "";

// Traitement du formulaire lors de la soumission
if (isset($_POST['register'])) {
    // Informations pour le compte utilisateur
    $identifiant   = trim($_POST['identifiant'] ?? '');
    $mot_de_passe  = trim($_POST['mot_de_passe'] ?? '');
    $confirm       = trim($_POST['confirm'] ?? '');
    
    // Informations pour l'employé
    $nom     = trim($_POST['nom'] ?? '');
    $prenom  = trim($_POST['prenom'] ?? '');
    $salaire = trim($_POST['salaire'] ?? '');
    
    // Vérifier que tous les champs sont renseignés
    if ($identifiant === '' || $mot_de_passe === '' || $confirm === '' || $nom === '' || $prenom === '' || $salaire === '') {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($mot_de_passe !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'identifiant existe déjà dans la table users
        $sql = "SELECT id FROM users WHERE identifiant = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $identifiant);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Cet identifiant existe déjà.";
        } else {
            // Insertion dans la table users pour créer le compte gestionnaire
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $role = 'gestionnaire'; // rôle fixé pour un gestionnaire de stock
            $sql = "INSERT INTO users (identifiant, mot_de_passe, role) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $identifiant, $hash, $role);
            if ($stmt->execute()) {
                // Récupérer l'ID du nouvel utilisateur
                $user_id = $stmt->insert_id;
                
                // Insertion dans la table employes avec les informations de l'employé
                // Le poste est fixé à "Gestionnaire de stock"
                $poste = 'Gestionnaire de stock';
                $sql = "INSERT INTO employes (nom, prenom, poste, salaire, user_id) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdi", $nom, $prenom, $poste, $salaire, $user_id);
                if ($stmt->execute()) {
                    $message = "Compte gestionnaire et employé créés avec succès.";
                } else {
                    $error = "Erreur lors de l'enregistrement de l'employé.";
                }
            } else {
                $error = "Erreur lors de la création du compte.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte gestionnaire de stock</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h1>Créer un compte gestionnaire de stock</h1>
    <p><a href="index.php">Retour au Dashboard</a></p>
    <?php 
    if ($error !== "") { 
        echo "<p class='erreur'>$error</p>"; 
    }
    if ($message !== "") { 
        echo "<p class='message'>$message</p>"; 
    }
    ?>
    <form method="post" action="register_gestionnaire.php">
        <fieldset>
            <legend>Informations du compte</legend>
            <label for="identifiant">Identifiant :</label>
            <input type="text" id="identifiant" name="identifiant" required>
            
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            
            <label for="confirm">Confirmer le mot de passe :</label>
            <input type="password" id="confirm" name="confirm" required>
        </fieldset>
        
        <fieldset>
            <legend>Informations de l'employé</legend>
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>
            
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>
            
            <label for="salaire">Salaire :</label>
            <input type="number" step="0.01" id="salaire" name="salaire" required>
        </fieldset>
        
        <button type="submit" name="register">Créer le compte gestionnaire</button>
    </form>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
