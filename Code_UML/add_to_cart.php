<?php
// add_to_cart.php
session_start();
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}
if(!isset($_POST['codeProduit'])) {
    echo json_encode(['success' => false, 'message' => 'Code produit manquant']);
    exit;
}

$codeProduit = $_POST['codeProduit'];

require 'connect.php';

$stmt = $conn->prepare("SELECT idProduit, nomProduit, prix FROM produits WHERE idProduit = ?");
$stmt->bind_param("s", $codeProduit);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
}
$stmt->close();
$conn->close();
?>
