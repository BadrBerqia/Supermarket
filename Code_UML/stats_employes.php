<?php
// stats_employes.php

/*************************************
 * 1. Paramètres de connexion MySQL
 *************************************/
$servername = "localhost";    // Adresse du serveur MySQL
$username   = "root";         // Nom d'utilisateur MySQL
$password   = "";             // Mot de passe MySQL
$dbname     = "supermarche";   // Nom de la base de données

// 2. Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

/********************************************
 * 3. Récupération du nombre d'employés par statut
 ********************************************/
$sql = "SELECT statut, COUNT(*) AS nb_employes
        FROM employes
        GROUP BY statut";
$result = $conn->query($sql);

// Tableaux pour stocker les données pour le graphique
$statuts = [];
$counts  = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $statuts[] = $row['statut'];
        $counts[]  = $row['nb_employes'];
    }
}

/********************************************
 * 4. Récupération du nombre total d'employés
 ********************************************/
$sqlTotal = "SELECT COUNT(*) AS total FROM employes";
$resultTotal = $conn->query($sqlTotal);
$totalEmployes = 0;

if ($resultTotal && $resultTotal->num_rows > 0) {
    $rowTotal = $resultTotal->fetch_assoc();
    $totalEmployes = $rowTotal['total'];
}

// Fermeture de la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des Employés</title>
    <!-- Utilisation du même CSS que stats.php -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Inclusion de Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Styles spécifiques pour la section stats (adaptés du fichier stats.php) */
        .dashboard .stats {
            background-color: #111;
            padding: 20px;
            border: 1px solid #d32f2f;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #fff;
        }
        .dashboard h1 {
            text-align: center;
            color: #fff;
        }
        .dashboard a.nav-link {
            color: #d32f2f;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Statistiques des Employés</h1>
        <p><a href="index.php" class="nav-link">Retour au Dashboard</a></p>
        
        <!-- Graphique des employés par statut -->
        <div class="chart-container" style="width: 100%; max-width: 600px; height: 400px; margin: 20px auto;">
            <canvas id="myChart"></canvas>
        </div>
        
        <!-- Résumé textuel des statistiques dans un encart stylisé -->
        <div class="stats">
            <h2>Résumé</h2>
            <p>Nombre total d'employés : <strong><?php echo $totalEmployes; ?></strong></p>
            <ul>
                <?php
                // Affichage de la répartition par statut
                for ($i = 0; $i < count($statuts); $i++) {
                    echo "<li>Statut <strong>" . htmlspecialchars($statuts[$i]) . "</strong> : "
                       . "<strong>" . htmlspecialchars($counts[$i]) . "</strong> employé(s)</li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <script>
        // Préparation et création du graphique avec Chart.js
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',  // Vous pouvez changer en 'pie', 'line', etc.
            data: {
                labels: <?php echo json_encode($statuts); ?>,
                datasets: [{
                    label: "Nombre d'employés",
                    data: <?php echo json_encode($counts); ?>,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
    </script>
</body>
</html>
