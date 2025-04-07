<?php
// gestion_employe.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: connexion.php");
    exit;
}
include 'db_connect.php';

$error = "";
$message = "";

// Traitement du formulaire de mise à jour (salaire / statut)
if (isset($_POST['update_employe'])) {
    $id_update   = $_POST['id_update'] ?? '';
    $new_salaire = $_POST['new_salaire'] ?? '';
    $new_statut  = $_POST['new_statut'] ?? '';

    if (empty($id_update)) {
        $error = "Veuillez spécifier l'ID de l'employé à mettre à jour.";
    } else {
        $sql_parts = [];
        $params = [];
        $types = '';

        if ($new_salaire !== '') {
            $sql_parts[] = 'salaire = ?';
            $params[] = $new_salaire;
            $types .= 'd';
        }
        if ($new_statut !== '') {
            $sql_parts[] = 'statut = ?';
            $params[] = $new_statut;
            $types .= 's';
        }

        if (!empty($sql_parts)) {
            $sql_set = implode(', ', $sql_parts);
            $sql = "UPDATE employes SET $sql_set WHERE id = ?";
            $params[] = $id_update;
            $types .= 'i';

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $message = "Employé #$id_update mis à jour avec succès.";
            } else {
                $error = "Erreur lors de la mise à jour de l'employé #$id_update.";
            }
        } else {
            $error = "Aucun champ (salaire ou statut) n'a été renseigné.";
        }
    }
}

// Traitement du formulaire de suppression
if (isset($_POST['delete_employe'])) {
    $id_delete = $_POST['id_delete'] ?? '';
    if (empty($id_delete)) {
        $error = "Veuillez spécifier l'ID de l'employé à supprimer.";
    } else {
        $sql = "DELETE FROM employes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_delete);
        if ($stmt->execute()) {
            $message = "Employé #$id_delete supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression de l'employé #$id_delete.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier/Supprimer un Employé</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <h1>Modifier / Supprimer un Employé</h1>
        <p><a href="index.php" class="nav-link">Retour au Menu Principal</a></p>
        <?php 
        if ($message !== "") { 
            echo "<p class='message'>$message</p>"; 
        }
        if ($error !== "") { 
            echo "<p class='erreur'>$error</p>"; 
        }
        ?>
        <!-- Formulaire de mise à jour -->
        <section>
            <h2>Mettre à jour un Employé (Salaire / Statut)</h2>
            <form method="post" action="gestion_employe.php">
                <label>ID de l'employé :</label>
                <input type="number" name="id_update" required>

                <label>Nouveau salaire (laisser vide si pas de changement) :</label>
                <input type="number" step="0.01" name="new_salaire">

                <label>Nouveau statut :</label>
                <select name="new_statut">
                    <option value="">(Pas de changement)</option>
                    <option value="Actif">Actif</option>
                    <option value="Arrêt maladie">Arrêt maladie</option>
                    <option value="Congé">Congé</option>
                    <option value="Démission">Démission</option>
                    <option value="Retraite">Retraite</option>
                </select>

                <button type="submit" name="update_employe">Mettre à jour</button>
            </form>
        </section>

        <!-- Formulaire de suppression -->
        <section>
            <h2>Supprimer un Employé</h2>
            <form method="post" action="gestion_employe.php">
                <label>ID de l'employé :</label>
                <input type="number" name="id_delete" required>
                <button type="submit" name="delete_employe">Supprimer</button>
            </form>
        </section>

        <!-- Liste des employés -->
        <section>
            <h2>Liste des Employés</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Poste</th>
                        <th>Salaire</th>
                        <th>Statut</th>
                        <th>Compte utilisateur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT e.*, u.identifiant 
                            FROM employes e 
                            LEFT JOIN users u ON e.user_id = u.id
                            ORDER BY e.id ASC";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()){
                        $compte = $row['identifiant'] ? $row['identifiant'] : "N/A";
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['nom']}</td>
                                <td>{$row['prenom']}</td>
                                <td>{$row['poste']}</td>
                                <td>{$row['salaire']}</td>
                                <td>{$row['statut']}</td>
                                <td>{$compte}</td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
