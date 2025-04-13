<?php
// C:\wamp64\www\MDM\api\export.php
header("Content-Type: text/plain; charset=utf-8");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// Charger PHPSpreadsheet et TCPDF
require dirname(__DIR__) . "/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use TCPDF;

require_once "database.php";

try {
    // Connexion DB
    $dbh = new Database();
    $dbo = $dbh->getConnection();
    if ($dbo === null) {
        throw new Exception("Erreur de connexion à la base de données");
    }

    $dbo->beginTransaction();

    // Dossiers
    $exportDir = "C:/wamp64/www/MDM/export";
    $commandesDir = "C:/wamp64/www/MDM/commandes";
    if (!is_dir($exportDir)) {
        mkdir($exportDir, 0755, true);
    }
    if (!is_dir($commandesDir)) {
        mkdir($commandesDir, 0755, true);
    }

    // Date pour noms de fichiers
    $date = date("Y_m_d_H_i_s");
    $dateForPdf = date("Y_m_d");

    // 1. Export Excel de tous les produits
    $sql = "SELECT p.code, p.description, p.price, 
                   GROUP_CONCAT(c.name) AS category_names, 
                   s.name AS statut, 
                   sup.name AS supplier_name, 
                   p.purchase_date, p.expiration_date
            FROM produits p
            LEFT JOIN suppliers sup ON p.supplier_id = sup.id
            LEFT JOIN category_product cp ON p.id_product = cp.product_id
            LEFT JOIN category c ON cp.category_id = c.id
            LEFT JOIN statut s ON p.statut_id = s.id
            GROUP BY p.id_product";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue("B1", "Code");
    $sheet->setCellValue("C1", "Description");
    $sheet->setCellValue("D1", "Prix");
    $sheet->setCellValue("E1", "Catégories");
    $sheet->setCellValue("F1", "Statut");
    $sheet->setCellValue("G1", "Fournisseur");
    $sheet->setCellValue("H1", "Date d'achat");
    $sheet->setCellValue("I1", "Date de péremption");

    $row = 2;
    foreach ($products as $product) {
        $sheet->setCellValue("B" . $row, $product["code"]);
        $sheet->setCellValue("C" . $row, $product["description"]);
        $sheet->setCellValue("D" . $row, $product["price"] / 100); // Convertir en euros
        $sheet->setCellValue("E" . $row, $product["category_names"] ?? 'Aucune');
        $sheet->setCellValue("F" . $row, $product["statut"]);
        $sheet->setCellValue("G" . $row, $product["supplier_name"]);
        $sheet->setCellValue("H" . $row, $product["purchase_date"]);
        $sheet->setCellValue("I" . $row, $product["expiration_date"]);
        $row++;
    }

    $writer = IOFactory::createWriter($spreadsheet, "Xls");
    $exportFile = "$exportDir/export_$date.xls";
    $writer->save($exportFile);

    // 2. Bons de commande PDF pour "Épuisé"
    $sql = "SELECT p.id_product, p.code, p.description, sup.name AS supplier_name
            FROM produits p
            JOIN statut s ON p.statut_id = s.id
            JOIN suppliers sup ON p.supplier_id = sup.id
            WHERE s.name = 'Épuisé'";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $epuiseProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($epuiseProducts as $product) {
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor("MDM");
        $pdf->SetTitle("Bon de commande - {$product['code']}");
        $pdf->AddPage();
        $pdf->SetFont("helvetica", "", 12);
        $pdf->Cell(0, 10, "Bon de Commande", 0, 1, "C");
        $pdf->Cell(0, 10, "Produit: {$product['code']} - {$product['description']}", 0, 1);
        $pdf->Cell(0, 10, "Fournisseur: {$product['supplier_name']}", 0, 1);
        $pdf->Cell(0, 10, "Date: " . date("Y-m-d"), 0, 1);
        $filename = str_replace([" ", "/", "\\"], "_", $product['description']);
        $pdf->Output("$commandesDir/cmd_{$filename}_$dateForPdf.pdf", "F");
    }

    // 3. Update "Épuisé" → "En cours d’approvisionnement"
    $sql = "UPDATE produits 
            SET statut_id = 1
            WHERE statut_id = 3";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();

    // 4. Update "En stock" → "Retiré des rayons" si expiration = aujourd’hui
    $sql = "UPDATE produits 
            SET statut_id = 4
            WHERE statut_id = 2
            AND expiration_date = CURDATE()";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();

    // 5. Excel pour "Retiré des rayons"
    $sql = "SELECT p.code, p.description, p.price, 
                   GROUP_CONCAT(c.name) AS category_names, 
                   s.name AS statut, 
                   sup.name AS supplier_name, 
                   p.purchase_date, p.expiration_date
            FROM produits p
            LEFT JOIN suppliers sup ON p.supplier_id = sup.id
            LEFT JOIN category_product cp ON p.id_product = cp.product_id
            LEFT JOIN category c ON cp.category_id = c.id
            LEFT JOIN statut s ON p.statut_id = s.id
            WHERE s.name = 'Retiré des rayons'
            GROUP BY p.id_product";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $retiredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue("B1", "Code");
    $sheet->setCellValue("C1", "Description");
    $sheet->setCellValue("D1", "Prix");
    $sheet->setCellValue("E1", "Catégories");
    $sheet->setCellValue("F1", "Statut");
    $sheet->setCellValue("G1", "Fournisseur");
    $sheet->setCellValue("H1", "Date d'achat");
    $sheet->setCellValue("I1", "Date de péremption");

    $row = 2;
    foreach ($retiredProducts as $product) {
        $sheet->setCellValue("B" . $row, $product["code"]);
        $sheet->setCellValue("C" . $row, $product["description"]);
        $sheet->setCellValue("D" . $row, $product["price"] / 100); // Convertir en euros
        $sheet->setCellValue("E" . $row, $product["category_names"] ?? 'Aucune');
        $sheet->setCellValue("F" . $row, $product["statut"]);
        $sheet->setCellValue("G" . $row, $product["supplier_name"]);
        $sheet->setCellValue("H" . $row, $product["purchase_date"]);
        $sheet->setCellValue("I" . $row, $product["expiration_date"]);
        $row++;
    }

    $writer = IOFactory::createWriter($spreadsheet, "Xls");
    $retiredFile = "$exportDir/retired_$date.xls";
    $writer->save($retiredFile);

    $dbo->commit();
    echo "Exportation réussie";

} catch (Exception $e) {
    if ($dbo && $dbo->inTransaction()) {
        $dbo->rollBack();
    }
    error_log("Erreur exportation : " . $e->getMessage());
    http_response_code(500);
    echo "Erreur lors de l'exportation : " . $e->getMessage();
}
?>