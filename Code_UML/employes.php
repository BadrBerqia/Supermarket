<?php
// index.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Employés</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Exemple de style pour centrer les boutons */
        .menu {
            text-align: center;
            margin-top: 50px;
        }
        .menu a {
            display: inline-block;
            margin: 20px;
            padding: 15px 30px;
            text-decoration: none;
            background-color: #3498db;
            color: #fff;
            border-radius: 5px;
        }
        .menu a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Gestion des Employés</h1>
        <div class="menu">
            <a href="ajout_employe.php">Ajouter un Employé</a>
            <a href="gestion_employe.php">Modifier / Supprimer un Employé</a>
        </div>
    </div>
</body>
</html>
