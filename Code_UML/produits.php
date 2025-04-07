<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: connexion.php");
    exit;
}
include 'db_connect.php';

$error = "";
$message = "";

// Ajout d'un produit
if (isset($_POST['ajouter'])) {
    $nomProduit = $_POST['nomProduit'] ?? '';
    $prix = $_POST['prix'] ?? 0;
    $quantite = $_POST['quantite'] ?? 0;
    $categorie = $_POST['categorie'] ?? '';
    $MFG = $_POST['MFG'] ?? null; // Date de fabrication
    $EXP = $_POST['EXP'] ?? null; // Date d'expiration

    // Gestion de l'upload de l'image
    $imageData = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
    }

    if ($nomProduit != '' && $prix >= 0 && $quantite >= 0) {
        $sql = "INSERT INTO produits (nomProduit, prix, quantite, image, MFG, EXP, categorie) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // On prépare les variables :
        // "s" pour le nom, "d" pour le prix, "i" pour la quantité, "b" pour le blob (image),
        // et "s" pour les dates et la catégorie.
        // On utilise une variable nulle pour le blob lors du bind_param, puis on envoie les données avec send_long_data.
        $null = null;
        $stmt->bind_param("sdibsss", $nomProduit, $prix, $quantite, $null, $MFG, $EXP, $categorie);
        if ($imageData !== null) {
            $stmt->send_long_data(3, $imageData);
        }
        $message = $stmt->execute() ? "Produit ajouté avec succès." : "Erreur lors de l'ajout.";
    } else {
        $error = "Veuillez remplir tous les champs correctement.";
    }
}

// Mise à jour d'un produit (ici, on met à jour le prix et la quantité)
// Vous pourrez adapter cette partie si vous souhaitez modifier d'autres champs.
if (isset($_POST['maj'])) {
    $id = $_POST['id_maj'] ?? 0;
    $nouveau_prix = $_POST['nouveau_prix'] ?? 0;
    $nouvelle_quantite = $_POST['nouvelle_quantite'] ?? 0;
    if ($id > 0 && $nouveau_prix >= 0 && $nouvelle_quantite >= 0) {
        $sql = "UPDATE produits SET prix = ?, quantite = ? WHERE idProduit = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dii", $nouveau_prix, $nouvelle_quantite, $id);
        $message = $stmt->execute() ? "Produit mis à jour." : "Erreur lors de la mise à jour.";
    } else {
        $error = "Veuillez remplir correctement les champs de mise à jour.";
    }
}

// Suppression d'un produit
if (isset($_POST['supprimer'])) {
    $id = $_POST['id_supprimer'] ?? 0;
    if ($id > 0) {
        $sql = "DELETE FROM produits WHERE idProduit = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $message = $stmt->execute() ? "Produit supprimé." : "Erreur lors de la suppression.";
    } else {
        $error = "ID invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Produits</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="dashboard">
    <h1>Gestion des Produits</h1>
    <p style="text-align:center;"><a class="nav-link" href="index.php">Retour au Dashboard</a></p>

    <?php 
    if ($message != "") { echo "<div class='message'>{$message}</div>"; }
    if ($error != "") { echo "<div class='erreur'>{$error}</div>"; }
    ?>

    <section>
        <h2>Ajouter un produit</h2>
        <!-- N'oubliez pas l'attribut enctype pour permettre l'upload de fichiers -->
        <form method="post" action="produits.php" enctype="multipart/form-data">
            <label>Nom du produit :</label>
            <input type="text" name="nomProduit" required>
            
            <label>Prix :</label>
            <input type="number" step="0.01" name="prix" required>
            
            <label>Quantité :</label>
            <input type="number" name="quantite" required>
            
            <label>Catégorie :</label>
            <input type="text" name="categorie">
            
            <label>Date de fabrication (MFG) :</label>
            <input type="date" name="MFG">
            
            <label>Date d'expiration (EXP) :</label>
            <input type="date" name="EXP">
            
            <label>Image :</label>
            <input type="file" name="image">
            
            <button type="submit" name="ajouter">Ajouter</button>
        </form>
    </section>

    <section>
        <h2>Mettre à jour un produit</h2>
        <form method="post" action="produits.php">
            <label>ID du produit :</label>
            <input type="number" name="id_maj" required>
            
            <label>Nouveau prix :</label>
            <input type="number" step="0.01" name="nouveau_prix" required>
            
            <label>Nouvelle quantité :</label>
            <input type="number" name="nouvelle_quantite" required>
            
            <button type="submit" name="maj">Mettre à jour</button>
        </form>
    </section>

    <section>
        <h2>Supprimer un produit</h2>
        <form method="post" action="produits.php">
            <label>ID du produit :</label>
            <input type="number" name="id_supprimer" required>
            <button type="submit" name="supprimer">Supprimer</button>
        </form>
    </section>

    <section>
        <h2>Liste des produits</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Catégorie</th>
                <th>MFG</th>
                <th>EXP</th>
                <th>Image</th>
            </tr>
            <?php
            $sql = "SELECT * FROM produits";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['idProduit']}</td>
                        <td>{$row['nomProduit']}</td>
                        <td>{$row['prix']}</td>
                        <td>{$row['quantite']}</td>
                        <td>{$row['categorie']}</td>
                        <td>{$row['MFG']}</td>
                        <td>{$row['EXP']}</td>
                        <td>";
                if (!empty($row['image'])) {
                    $imgData = base64_encode($row['image']);
                    echo "<img src='data:image/jpeg;base64,{$imgData}' width='50' height='50'/>";
                }
                echo "</td>
                      </tr>";
            }
            ?>
        </table>
    </section>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
