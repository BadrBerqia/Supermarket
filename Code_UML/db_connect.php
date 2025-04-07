<?php
// db_connect.php

$servername = "localhost";
$username_db = "root";       // votre nom d'utilisateur MySQL
$password_db = "";           // votre mot de passe MySQL
$dbname = "supermarche";     // nom de votre base de données

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
