<?php
// index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Inclusion de jQuery pour faciliter les appels AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="dashboard">
        <h1>Bienvenue, <?php echo $_SESSION['identifiant']; ?></h1>
        <p class="role">Votre rôle : <?php echo $_SESSION['role']; ?></p>

        <?php if ($_SESSION['role'] === 'admin') { ?>
            <nav>
                <ul>
                    <li><a href="employes.php">Gestion des employés</a></li>
                    <li><a href="stats.php">Statistiques de ventes</a></li>
                    <li><a href="stats_employes.php">Statistiques des employés</a></li>
                </ul>
            </nav>
        <?php } elseif ($_SESSION['role'] === 'gestionnaire') { ?>
            <nav>
                <ul>
                    <li><a href="produits.php">Gestion des produits</a></li>
                    <li><a href="fournisseurs.php">Gestion des Fournisseurs</a></li>
                </ul>
            </nav>
        <?php } elseif ($_SESSION['role'] === 'vente') { ?>
            <div class="caisse">
                <h2>Caisse</h2>
                <!-- Formulaire pour ajouter un produit via son code -->
                <form id="addProductForm">
                    <label for="codeProduit">Code du produit :</label>
                    <input type="text" id="codeProduit" name="codeProduit" required>
                    <button type="submit">Ajouter au panier</button>
                </form>
                <!-- Affichage du panier -->
                <h3>Panier</h3>
                <table id="cartTable" border="1">
                    <thead>
                        <tr>
                            <th>Nom du produit</th>
                            <th>Prix</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Les produits ajoutés seront affichés ici -->
                    </tbody>
                </table>
                <p>Total : <span id="totalPrice">0</span> €</p>
                <!-- Bouton de paiement -->
                <button id="paymentButton">Paiement</button>
            </div>
            <script>
            // Tableau JavaScript qui contiendra les articles du panier
            var cart = [];

            // Fonction pour mettre à jour l’affichage du panier et du total
            function updateCartDisplay() {
                var tbody = $("#cartTable tbody");
                tbody.empty();
                var total = 0;
                cart.forEach(function(item) {
                    var subtotal = item.prix * item.quantite;
                    total += subtotal;
                    tbody.append(
                        "<tr>" +
                        "<td>" + item.nomProduit + "</td>" +
                        "<td>" + item.prix.toFixed(2) + "</td>" +
                        "<td>" + item.quantite + "</td>" +
                        "<td>" + subtotal.toFixed(2) + "</td>" +
                        "</tr>"
                    );
                });
                $("#totalPrice").text(total.toFixed(2));
            }

            // Gestion de l’ajout d’un produit via AJAX
            $("#addProductForm").submit(function(e) {
                e.preventDefault();
                var code = $("#codeProduit").val().trim();
                if(code === "") return;
                $.ajax({
                    url: "add_to_cart.php",
                    method: "POST",
                    data: { codeProduit: code },
                    dataType: "json",
                    success: function(response) {
                        if(response.success) {
                            // Si le produit existe déjà dans le panier, incrémenter la quantité
                            var existing = cart.find(function(item) {
                                return item.idProduit == response.data.idProduit;
                            });
                            if(existing) {
                                existing.quantite += 1;
                            } else {
                                var product = response.data;
                                product.quantite = 1;
                                cart.push(product);
                            }
                            updateCartDisplay();
                            $("#codeProduit").val("").focus();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert("Erreur lors de l'ajout du produit.");
                    }
                });
            });

            // Gestion du paiement et enregistrement de la vente
            $("#paymentButton").click(function(){
                if(cart.length === 0) {
                    alert("Le panier est vide.");
                    return;
                }
                var mode = prompt("Entrez le mode de paiement : carte ou espece");
                if(mode === null) return;
                mode = mode.toLowerCase();
                if(mode !== "carte" && mode !== "espece") {
                    alert("Mode de paiement invalide.");
                    return;
                }
                $.ajax({
                    url: "process_sale.php",
                    method: "POST",
                    data: { cart: JSON.stringify(cart), mode: mode },
                    dataType: "json",
                    success: function(response) {
                        if(response.success) {
                            alert("Vente effectuée avec succès !");
                            cart = [];
                            updateCartDisplay();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert("Erreur lors du paiement.");
                    }
                });
            });
            </script>
        <?php } ?>
        <p class="logout"><a href="logout.php">Se déconnecter</a></p>
    </div>
</body>
</html>
