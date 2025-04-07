<?php
// connect.php
$host = "localhost";
$user = "root";      // à remplacer par votre identifiant MySQL
$password = "";  // à remplacer par votre mot de passe MySQL
$database = "supermarche";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
