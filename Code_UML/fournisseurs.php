<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: connexion.php");
    exit;
}
include 'db_connect.php';

$error = "";
$message = "";

// Ajouter un fournisseur
if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if ($nom != '' && $prenom != '' && $telephone != '' && $email != '') {
        $sql = "INSERT INTO fournisseur (nom, prenom, telephone, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nom, $prenom, $telephone, $email);
        $message = $stmt->execute() ? "Fournisseur ajouté avec succès." : "Erreur lors de l'ajout.";
    } else {
        $error = "Veuillez remplir tous les champs correctement.";
    }
}

// Mettre à jour un fournisseur
if (isset($_POST['maj'])) {
    $id = $_POST['id_maj'] ?? 0;
    $nouveau_telephone = $_POST['nouveau_telephone'] ?? '';
    $nouveau_email = $_POST['nouveau_email'] ?? '';
    
    if ($id > 0 && $nouveau_telephone != '' && $nouveau_email != '') {
        $sql = "UPDATE fournisseur SET telephone = ?, email = ? WHERE idFournisseur = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nouveau_telephone, $nouveau_email, $id);
        $message = $stmt->execute() ? "Fournisseur mis à jour." : "Erreur lors de la mise à jour.";
    } else {
        $error = "Veuillez remplir correctement les champs de mise à jour.";
    }
}

// Supprimer un fournisseur
if (isset($_POST['supprimer'])) {
    $id = $_POST['id_supprimer'] ?? 0;
    if ($id > 0) {
        $sql = "DELETE FROM fournisseur WHERE idFournisseur = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $message = $stmt->execute() ? "Fournisseur supprimé." : "Erreur lors de la suppression.";
    } else {
        $error = "ID invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Fournisseurs</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard">
    <h1>Gestion des Fournisseurs</h1>
    <p style="text-align:center;"><a class="nav-link" href="index.php">Retour au Dashboard</a></p>

    <?php 
    if ($message != "") { echo "<div class='message'>{$message}</div>"; }
    if ($error != "") { echo "<div class='erreur'>{$error}</div>"; }
    ?>

    <section>
        <h2>Ajouter un fournisseur</h2>
        <form method="post" action="fournisseur.php">
            <label>Nom :</label>
            <input type="text" name="nom" required>
            <label>Prénom :</label>
            <input type="text" name="prenom" required>
            <label>Téléphone :</label>
            <input type="text" name="telephone" required>
            <label>Email :</label>
            <input type="email" name="email" required>
            <button type="submit" name="ajouter">Ajouter</button>
        </form>
    </section>

    <section>
        <h2>Mettre à jour un fournisseur</h2>
        <form method="post" action="fournisseur.php">
            <label>ID du fournisseur :</label>
            <input type="number" name="id_maj" required>
            <label>Nouveau Téléphone :</label>
            <input type="text" name="nouveau_telephone" required>
            <label>Nouveau Email :</label>
            <input type="email" name="nouveau_email" required>
            <button type="submit" name="maj">Mettre à jour</button>
        </form>
    </section>

    <section>
        <h2>Supprimer un fournisseur</h2>
        <form method="post" action="fournisseur.php">
            <label>ID du fournisseur :</label>
            <input type="number" name="id_supprimer" required>
            <button type="submit" name="supprimer">Supprimer</button>
        </form>
    </section>

    <section>
        <h2>Liste des fournisseurs</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th>Email</th>
            </tr>
            <?php
            $sql = "SELECT * FROM fournisseur";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['idFournisseur']}</td>
                        <td>{$row['nom']}</td>
                        <td>{$row['prenom']}</td>
                        <td>{$row['telephone']}</td>
                        <td>{$row['email']}</td>
                      </tr>";
            }
            ?>
        </table>
    </section>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
