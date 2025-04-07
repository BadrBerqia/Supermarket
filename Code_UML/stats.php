<?php
// stats.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion.php");
    exit;
}
include 'db_connect.php';

// Récupérer le résumé global : nombre total de ventes et revenu total
$sql = "SELECT COUNT(*) AS total_ventes, SUM(prix_total) AS total_revenu FROM ventes";
$result = $conn->query($sql);
$stats = $result->fetch_assoc();

// Récupérer les données de ventes groupées par jour pour le graphique
$sqlChart = "SELECT DATE(date_vente) as jour, SUM(prix_total) as revenu FROM ventes GROUP BY DATE(date_vente) ORDER BY DATE(date_vente)";
$resultChart = $conn->query($sqlChart);
$chartData = [];
while ($row = $resultChart->fetch_assoc()) {
    $chartData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques de Ventes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Inclusion de Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Styles spécifiques pour la section stats (si besoin de réglages supplémentaires) */
        .dashboard .stats {
            background-color: #111;
            padding: 20px;
            border: 1px solid #d32f2f;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .dashboard .stats h2 {
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Statistiques de Ventes</h1>
        <p><a href="index.php" class="nav-link">Retour au Dashboard</a></p>
        <div class="stats">
            <h2>Résumé</h2>
            <p>Total des ventes : <?php echo $stats['total_ventes'] ?? 0; ?></p>
            <p>Revenu total : <?php echo number_format($stats['total_revenu'] ?? 0, 2, ',', ' '); ?> €</p>
        </div>
        <div class="chart-container" style="width: 100%; max-width: 600px; height: 400px; margin: 20px auto;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    <script>
        // Préparer les données pour le graphique
        const chartData = <?php echo json_encode($chartData); ?>;
        const labels = chartData.map(item => item.jour);
        const data = chartData.map(item => parseFloat(item.revenu));

        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenu par jour (€)',
                    data: data,
                    backgroundColor: 'rgb(250, 250, 250)',
                    borderColor: 'rgb(235, 54, 54)',
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
