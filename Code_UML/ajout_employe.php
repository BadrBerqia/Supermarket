<?php
// ajout_employe.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion.php");
    exit;
}
include 'db_connect.php';

$error = "";
$message = "";

// Traitement du formulaire d'ajout d'employé
if (isset($_POST['ajouter'])) {
    $nom     = trim($_POST['nom'] ?? '');
    $prenom  = trim($_POST['prenom'] ?? '');
    $poste   = trim($_POST['poste'] ?? '');
    $salaire = trim($_POST['salaire'] ?? '');
    $statut  = $_POST['statut'] ?? 'Actif';

    if ($nom === '' || $prenom === '' || $poste === '' || $salaire === '') {
        $error = "Veuillez remplir tous les champs de l'employé.";
    } else {
        $create_account = isset($_POST['create_account']);
        if ($create_account) {
            $identifiant  = trim($_POST['identifiant'] ?? '');
            $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
            $confirm      = trim($_POST['confirm'] ?? '');

            if ($identifiant === '' || $mot_de_passe === '' || $confirm === '') {
                $error = "Pour la création du compte, veuillez remplir tous les champs requis.";
            } elseif ($mot_de_passe !== $confirm) {
                $error = "Les mots de passe ne correspondent pas pour le compte gestionnaire.";
            } else {
                $sql = "SELECT id FROM users WHERE identifiant = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $identifiant);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $error = "Cet identifiant existe déjà.";
                } else {
                    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                    $role = 'gestionnaire';
                    $sql = "INSERT INTO users (identifiant, mot_de_passe, role) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $identifiant, $hash, $role);
                    if ($stmt->execute()) {
                        $user_id = $stmt->insert_id;
                        $sql = "INSERT INTO employes (nom, prenom, poste, salaire, statut, user_id) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssdsi", $nom, $prenom, $poste, $salaire, $statut, $user_id);
                        if ($stmt->execute()) {
                            $message = "Employé et compte gestionnaire créés avec succès.";
                        } else {
                            $error = "Erreur lors de l'enregistrement de l'employé.";
                        }
                    } else {
                        $error = "Erreur lors de la création du compte gestionnaire.";
                    }
                }
            }
        } else {
            $sql = "INSERT INTO employes (nom, prenom, poste, salaire, statut) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssds", $nom, $prenom, $poste, $salaire, $statut);
            if ($stmt->execute()) {
                $message = "Employé créé avec succès.";
            } else {
                $error = "Erreur lors de l'enregistrement de l'employé.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Employé</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
    // Script pour afficher/cacher les champs de création de compte
    function toggleAccountFields() {
        var checkBox = document.getElementById("create_account");
        var accountFields = document.getElementById("account_fields");
        accountFields.style.display = checkBox.checked ? "block" : "none";
    }
    </script>
</head>
<body>
    <div class="dashboard">
        <h1>Ajouter un Employé</h1>
        <p><a href="index.php" class="nav-link">Retour au Menu Principal</a></p>
        <?php 
        if ($message !== "") { 
            echo "<p class='message'>$message</p>"; 
        }
        if ($error !== "") { 
            echo "<p class='erreur'>$error</p>"; 
        }
        ?>
        <form method="post" action="ajout_employe.php">
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" required>
            
            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" id="prenom" required>
            
            <label for="poste">Poste :</label>
            <input type="text" name="poste" id="poste" required>
            
            <label for="salaire">Salaire :</label>
            <input type="number" step="0.01" name="salaire" id="salaire" required>

            <label for="statut">Statut :</label>
            <select name="statut" id="statut">
                <option value="Actif">Actif</option>
            </select>
            
            <label for="create_account">
                <input type="checkbox" id="create_account" name="create_account" onclick="toggleAccountFields()">
                Créer un compte gestionnaire pour cet employé
            </label>
            
            <div id="account_fields" style="display: none;">
                <fieldset>
                    <legend>Informations du compte</legend>
                    <label for="identifiant">Identifiant :</label>
                    <input type="text" id="identifiant" name="identifiant">
                    
                    <label for="mot_de_passe">Mot de passe :</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe">
                    
                    <label for="confirm">Confirmer le mot de passe :</label>
                    <input type="password" id="confirm" name="confirm">
                </fieldset>
            </div>
            <button type="submit" name="ajouter">Créer l'employé</button>
        </form>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
