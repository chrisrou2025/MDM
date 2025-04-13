<?php
// C:\wamp64\www\MDM\index.php
header("Content-Type: text/html; charset=utf-8");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    <link rel="stylesheet" href="/MDM/css/styles.css">
</head>
<body>
    <div class="header-container">
        <h1>Gestion des Produits</h1>
    </div>
    <div class="button-group">
        <button class="import-btn" onclick="importFile()">IMPORTER le fichier XLSX</button>
        <button class="export-btn" onclick="exportFile()">Exporter</button>
    </div>

    <div class="filter">
        <label>Catégorie : </label>
        <select id="categoryFilter">
            <option value="">Toutes</option>
        </select>
        <label>Statut : </label>
        <select id="statusFilter">
            <option value="">Tous</option>
        </select>
        <label>Recherche : </label>
        <input type="text" id="searchFilter" placeholder="Description...">
    </div>

    <div class="table-container">
        <table id="productTable">
            <thead>
                <tr>
                    <th>Visuel</th>
                    <th>Description</th>
                    <th>Prix (€)</th>
                    <th>Date d'achat</th>
                    <th>Date de Péremption</th>
                    <th>Catégorie(s)</th>
                    <th>Fournisseur</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="overlay" id="overlay"></div>
    <div class="popup" id="imagePopup">
        <span class="close-btn" onclick="closePopup('imagePopup')">✖</span>
        <img id="fullImage" src="" alt="Image pleine taille">
    </div>
    <div class="popup" id="deletePopup">
        <p>Confirmer la suppression du produit ?</p>
        <div class="delete-btns">
            <button id="confirmDelete">Oui</button>
            <button onclick="closePopup('deletePopup')">Non</button>
        </div>
    </div>

    <script>
        "use strict";

        const apiUrl = "http://localhost/MDM/api/product.php";
        const importUrl = "http://localhost/MDM/api/import.php";
        const exportUrl = "http://localhost/MDM/api/export.php";

        function loadProducts() {
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector("#productTable tbody");
                    tbody.innerHTML = "";
                    data.forEach(product => {
                        const imageSrc = product.chemin;
                        const purchaseDate = new Date(product.purchase_date).toLocaleDateString("fr-FR");
                        const expirationDate = new Date(product.expiration_date).toLocaleDateString("fr-FR");
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td><img src="${imageSrc}" class="thumbnail" onclick="showImage('${imageSrc}')"></td>
                            <td>${product.description}</td>
                            <td>${(product.price / 100).toFixed(2)}</td>
                            <td>${purchaseDate}</td>
                            <td>${expirationDate}</td>
                            <td>${product.category_names || "Aucune"}</td>
                            <td>${product.supplier_name}</td>
                            <td>
                                <select onchange="updateStatus(${product.id_product}, this.value)">
                                    <option value="1" ${product.statut === "En cours d’approvisionnement" ? "selected" : ""}>En cours d’approvisionnement</option>
                                    <option value="2" ${product.statut === "En stock" ? "selected" : ""}>En stock</option>
                                    <option value="3" ${product.statut === "Epuisé" ? "selected" : ""}>Epuisé</option>
                                    <option value="4" ${product.statut === "Retiré des rayons" ? "selected" : ""}>Retiré des rayons</option>
                                </select>
                            </td>
                            <td><button onclick="showDeletePopup(${product.id_product})">Supprimer</button></td>
                        `;
                        tbody.appendChild(tr);
                    });
                    loadFilters(data);
                })
                .catch(error => console.error("Erreur lors du chargement des produits:", error));
        }

        function loadFilters(products) {
            const categories = [...new Set(products.flatMap(p => p.category_names ? p.category_names.split(",") : []))];
            const statusIds = [
                { id: "1", name: "En cours d’approvisionnement" },
                { id: "2", name: "En stock" },
                { id: "3", name: "Epuisé" },
                { id: "4", name: "Retiré des rayons" }
            ];
            const categoryFilter = document.getElementById("categoryFilter");
            const statusFilter = document.getElementById("statusFilter");
            categoryFilter.innerHTML = "<option value=\"\">Toutes</option>";
            statusFilter.innerHTML = "<option value=\"\">Tous</option>";
            categories.forEach(cat => {
                categoryFilter.innerHTML += `<option value="${cat}">${cat}</option>`;
            });
            statusIds.forEach(stat => {
                statusFilter.innerHTML += `<option value="${stat.id}">${stat.name}</option>`;
            });
        }

        function filterProducts() {
            const category = document.getElementById("categoryFilter").value;
            const status = document.getElementById("statusFilter").value;
            const search = document.getElementById("searchFilter").value.toLowerCase();
            const rows = document.querySelectorAll("#productTable tbody tr");
            rows.forEach(row => {
                const cats = row.cells[5].textContent.split(",");
                const stat = row.cells[7].querySelector("select").value;
                const desc = row.cells[1].textContent.toLowerCase();
                row.style.display = (
                    (!category || cats.includes(category) || (category === "" && row.cells[5].textContent === "Aucune")) &&
                    (!status || stat === status) &&
                    (!search || desc.includes(search))
                ) ? "" : "none";
            });
        }

        function updateStatus(id, status) {
            fetch(`${apiUrl}/${id}`, {
                method: "PUT",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `status=${status}`
            })
                .then(() => loadProducts())
                .catch(error => console.error("Erreur lors de la mise à jour du statut:", error));
        }

        function showImage(src) {
            document.getElementById("fullImage").src = src;
            document.getElementById("imagePopup").style.display = "block";
            document.getElementById("overlay").style.display = "block";
        }

        function showDeletePopup(id) {
            document.getElementById("deletePopup").style.display = "block";
            document.getElementById("overlay").style.display = "block";
            document.getElementById("confirmDelete").onclick = function() {
                deleteProduct(id);
            };
        }

        function closePopup(id) {
            document.getElementById(id).style.display = "none";
            document.getElementById("overlay").style.display = "none";
        }

        function deleteProduct(id) {
            fetch(`${apiUrl}/${id}`, { method: "DELETE" })
                .then(() => {
                    closePopup("deletePopup");
                    loadProducts();
                })
                .catch(error => console.error("Erreur lors de la suppression du produit:", error));
        }

        function importFile() {
            fetch(importUrl, { method: "POST" })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(data => {
                    console.log("Réponse import:", data);
                    loadProducts();
                    alert("Importation réussie !");
                })
                .catch(error => {
                    console.error("Erreur lors de l'importation:", error);
                    alert("Échec de l'importation : " + error.message);
                });
        }

        function exportFile() {
            fetch(exportUrl, { method: "POST" })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(data => {
                    console.log("Réponse export:", data);
                    loadProducts();
                    alert("Exportation réussie !");
                })
                .catch(error => {
                    console.error("Erreur exportation:", error);
                    alert("Échec de l'exportation : " + error.message);
                });
        }

        document.getElementById("categoryFilter").addEventListener("change", filterProducts);
        document.getElementById("statusFilter").addEventListener("change", filterProducts);
        document.getElementById("searchFilter").addEventListener("input", filterProducts);

        loadProducts();
    </script>
</body>
</html>