<?php
// process_sale.php
session_start();
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if(!isset($_POST['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Panier vide']);
    exit;
}

$cart = json_decode($_POST['cart'], true);
if(empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Panier vide']);
    exit;
}

require 'connect.php';

// Démarrer une transaction
$conn->autocommit(false);
$errors = [];

foreach($cart as $item) {
    // Vérifier l'existence du produit et sa quantité disponible
    $stmt = $conn->prepare("SELECT quantite, prix FROM produits WHERE idProduit = ?");
    $stmt->bind_param("s", $item['idProduit']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 0) {
        $errors[] = "Produit ID " . $item['idProduit'] . " introuvable.";
        continue;
    }
    $product = $result->fetch_assoc();
    if($product['quantite'] < $item['quantite']) {
        $errors[] = "Quantité insuffisante pour le produit " . $item['nomProduit'];
        continue;
    }
    $stmt->close();
    
    $prix_total = $product['prix'] * $item['quantite'];
    $date_vente = date("Y-m-d H:i:s");

    // Insertion dans la table ventes
    $stmt = $conn->prepare("INSERT INTO ventes (produit_id, quantite, prix_total, date_vente) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sids", $item['idProduit'], $item['quantite'], $prix_total, $date_vente);
    if(!$stmt->execute()){
        $errors[] = "Erreur lors de l'insertion pour le produit " . $item['nomProduit'];
    }
    $stmt->close();

    // Mise à jour du stock dans la table produits (optionnel)
    $newQuantity = $product['quantite'] - $item['quantite'];
    $stmt = $conn->prepare("UPDATE produits SET quantite = ? WHERE idProduit = ?");
    $stmt->bind_param("is", $newQuantity, $item['idProduit']);
    if(!$stmt->execute()){
        $errors[] = "Erreur lors de la mise à jour du stock pour " . $item['nomProduit'];
    }
    $stmt->close();
}

if(empty($errors)) {
    $conn->commit();
    echo json_encode(['success' => true]);
} else {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
}

$conn->close();
?>
