<?php
// C:\wamp64\www\MDM\api\import.php
header("Content-Type: text/plain; charset=utf-8");

// Charger la bibliothèque PhpSpreadsheet
require dirname(__DIR__) . "/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once "database.php";

// Chemin du fichier Excel
$inputFileName = "C:/wamp64/www/MDM/input/micromarket.xlsx";

try {
    // Charger le fichier Excel
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true); // Garder les valeurs brutes

    // Connexion à la base de données
    $dbh = new Database();
    $dbo = $dbh->getConnection();
    if ($dbo === null) {
        throw new Exception("Erreur de connexion à la base de données");
    }

    $dbo->beginTransaction();

    // Parcourir les lignes (sauter l'en-tête en ligne 1)
    foreach ($data as $index => $row) {
        if ($index == 1) {
            continue; // Sauter la ligne d'en-tête
        }

        // Extraire les données
        $code = $row["B"]; // Nom (ex. TVF-01)
        $description = $row["C"]; // Description
        $price = (float)$row["D"]; // Prix en centimes (ex. 149)
        $category_id = (int)$row["E"]; // Catégorie
        $statut_id = (int)$row["F"]; // Statut
        $supplier_name = $row["G"]; // Nom du fournisseur (ex. May Tea)

        // Gérer la date d'achat (colonne H)
        if (is_numeric($row["H"])) {
            $purchase_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row["H"])->format("Y-m-d");
        } else {
            $purchase_date = (new DateTime($row["H"]))->format("Y-m-d");
        }

        // Gérer la date de péremption (colonne I)
        if (is_numeric($row["I"])) {
            $expiration_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row["I"])->format("Y-m-d");
        } else {
            $expiration_date = (new DateTime($row["I"]))->format("Y-m-d");
        }

        $visuel = $row["J"]; // Visuel principal (ex. TVF-01.jpg)

        // Gérer le fournisseur
        $stmt = $dbo->prepare("SELECT id FROM suppliers WHERE name = ?");
        $stmt->execute([$supplier_name]);
        $supplier_id = $stmt->fetchColumn();

        if (!$supplier_id) {
            $stmt = $dbo->prepare("INSERT INTO suppliers (name, address) VALUES (?, ?)");
            $stmt->execute([$supplier_name, ""]);
            $supplier_id = $dbo->lastInsertId();
        }

        // Insérer ou mettre à jour le produit
        $query = "INSERT INTO produits (code, description, price, category_id, statut_id, supplier_id, purchase_date, expiration_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE 
                  description = ?, price = ?, category_id = ?, statut_id = ?, supplier_id = ?, purchase_date = ?, expiration_date = ?";
        $stmt = $dbo->prepare($query);
        $stmt->execute([
            $code, $description, $price, $category_id, $statut_id, $supplier_id, $purchase_date, $expiration_date,
            $description, $price, $category_id, $statut_id, $supplier_id, $purchase_date, $expiration_date
        ]);

        // Récupérer l'id_product (nouveau ou existant)
        $stmt = $dbo->prepare("SELECT id_product FROM produits WHERE code = ?");
        $stmt->execute([$code]);
        $product_id = $stmt->fetchColumn();

        // Supprimer les anciennes associations de catégories
        $stmt = $dbo->prepare("DELETE FROM category_product WHERE product_id = ?");
        $stmt->execute([$product_id]);

        // Lier la catégorie
        $query = "INSERT INTO category_product (product_id, category_id) VALUES (?, ?)";
        $stmt = $dbo->prepare($query);
        $stmt->execute([$product_id, $category_id]);

        // Gérer l'image
        $assetDir = "C:/wamp64/www/MDM/assets/";
        $stmt = $dbo->prepare("SELECT id FROM assets WHERE file_name = ? AND file_path = ?");
        $stmt->execute([$visuel, $assetDir]);
        $asset_id = $stmt->fetchColumn();

        if (!$asset_id) {
            $query = "INSERT INTO assets (file_path, file_name) VALUES (?, ?)";
            $stmt = $dbo->prepare($query);
            $stmt->execute([$assetDir, $visuel]);
            $asset_id = $dbo->lastInsertId();
        }

        // Lier l'image au produit
        $query = "INSERT INTO assets_product (produit_id, asset_id, is_primary) VALUES (?, ?, 1) 
                  ON DUPLICATE KEY UPDATE asset_id = ?, is_primary = 1";
        $stmt = $dbo->prepare($query);
        $stmt->execute([$product_id, $asset_id, $asset_id]);
    }

    $dbo->commit();

    // Déplacer le fichier vers archives
    $archiveDir = "C:/wamp64/www/MDM/archives";
    $archiveFile = $archiveDir . "/micromarket.xlsx";

    // Créer le dossier archives si inexistant
    if (!is_dir($archiveDir)) {
        mkdir($archiveDir, 0755, true);
    }

    // Ajouter timestamp si fichier existe déjà
    if (file_exists($archiveFile)) {
        $timestamp = date("Ymd_His");
        $archiveFile = $archiveDir . "/micromarket_{$timestamp}.xlsx";
    }

    // Déplacer le fichier
    if (!rename($inputFileName, $archiveFile)) {
        error_log("Erreur lors du déplacement de $inputFileName vers $archiveFile");
        throw new Exception("Impossible de déplacer le fichier vers les archives");
    }

    echo "Importation réussie";

} catch (Exception $e) {
    if ($dbo && $dbo->inTransaction()) {
        $dbo->rollBack();
    }
    error_log("Erreur importation : " . $e->getMessage());
    echo "Erreur lors de l'importation : " . $e->getMessage();
}
?>