<?php
// C:\wamp64\www\MDM\api\product.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'database.php';

function getProducts($id = 0) {
    $dbh = new Database();
    $dbo = $dbh->getConnection();
    if ($dbo === null) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Erreur de connexion à la base de données"]);
        exit;
    }
    $sql = "SELECT p.id_product, p.code, p.description, p.price, 
                   GROUP_CONCAT(c.name) AS category_names, 
                   s.name AS statut, p.supplier_id, sup.name AS supplier_name, 
                   p.purchase_date, p.expiration_date, 
                   CONCAT('/MDM/assets/', a.file_name) AS chemin, 
                   a.file_name AS visuel 
            FROM produits p 
            LEFT JOIN category_product cp ON p.id_product = cp.product_id 
            LEFT JOIN category c ON cp.category_id = c.id 
            LEFT JOIN statut s ON p.statut_id = s.id 
            LEFT JOIN suppliers sup ON p.supplier_id = sup.id 
            LEFT JOIN assets_product ap ON p.id_product = ap.produit_id AND ap.is_primary = 1 
            LEFT JOIN assets a ON ap.asset_id = a.id";
    if ($id != 0) {
        $sql .= " WHERE p.id_product = " . $id . " LIMIT 1";
    }
    $sql .= " GROUP BY p.id_product";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
}

function addProduct() {
    $dbh = new Database();
    $dbo = $dbh->getConnection();
    if ($dbo === null) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Erreur de connexion à la base de données"]);
        exit;
    }
    parse_str(file_get_contents('php://input'), $POST);
    $code = $POST["code"];
    $description = $POST["description"];
    $price = $POST["price"];
    $category = $POST["category"];
    $status = $POST["statut"];
    $supplier = $POST["supplier"];
    $purchase = $POST["purchase"];
    $expire = $POST["expire"];
    $image = $POST["image"] ?? 'Visuel-non-disponible.jpg';

    $assetDir = 'C:/wamp64/www/MDM/assets/';
    $stmt = $dbo->prepare("SELECT id FROM assets WHERE file_name = ? AND file_path = ?");
    $stmt->execute([$image, $assetDir]);
    $assetId = $stmt->fetchColumn();

    if (!$assetId) {
        $query = "INSERT INTO assets (file_path, file_name) VALUES (?, ?)";
        $dbo->prepare($query)->execute([$assetDir, $image]);
        $assetId = $dbo->lastInsertId();
    }

    $query = "INSERT INTO produits (code, description, price, category_id, statut_id, supplier_id, purchase_date, expiration_date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $dbo->prepare($query);
    $stmt->execute([$code, $description, $price, $category, $status, $supplier, $purchase, $expire]);
    $productId = $dbo->lastInsertId();

    $query = "INSERT INTO category_product (product_id, category_id) VALUES (?, ?)";
    $stmt = $dbo->prepare($query);
    $stmt->execute([$productId, $category]);

    $query = "INSERT INTO assets_product (produit_id, asset_id, is_primary) VALUES (?, ?, 1)";
    $stmt = $dbo->prepare($query);
    $stmt->execute([$productId, $assetId]);

    header('Content-Type: application/json');
    echo json_encode(["message" => "Produit ajouté", "id" => $productId]);
}

function updateProduct($id) {
    $dbh = new Database();
    $dbo = $dbh->getConnection();
    if ($dbo === null) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Erreur de connexion à la base de données"]);
        exit;
    }
    parse_str(file_get_contents('php://input'), $_PUT);
    $status = $_PUT["status"];
    $image = $_PUT["image"] ?? null;

    $dbo->beginTransaction();
    $query = "UPDATE produits SET statut_id = ? WHERE id_product = ?";
    $stmt = $dbo->prepare($query);
    $stmt->execute([$status, $id]);

    if ($image) {
        $assetDir = 'C:/wamp64/www/MDM/assets/';
        $stmt = $dbo->prepare("SELECT id FROM assets WHERE file_name = ? AND file_path = ?");
        $stmt->execute([$image, $assetDir]);
        $assetId = $stmt->fetchColumn();

        if (!$assetId) {
            $query = "INSERT INTO assets (file_path, file_name) VALUES (?, ?)";
            $dbo->prepare($query)->execute([$assetDir, $image]);
            $assetId = $dbo->lastInsertId();
        }

        $query = "UPDATE assets_product SET is_primary = 0 WHERE produit_id = ? AND is_primary = 1";
        $dbo->prepare($query)->execute([$id]);

        $query = "INSERT INTO assets_product (produit_id, asset_id, is_primary) 
                  VALUES (?, ?, 1) 
                  ON DUPLICATE KEY UPDATE is_primary = 1";
        $stmt = $dbo->prepare($query);
        $stmt->execute([$id, $assetId]);
    }

    $dbo->commit();
    header('Content-Type: application/json');
    echo json_encode(["message" => "Produit mis à jour"]);
}

function deleteProduct($id) {
    $dbh = new Database();
    $dbo = $dbh->getConnection();
    if ($dbo === null) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Erreur de connexion à la base de données"]);
        exit;
    }
    $query = "DELETE FROM produits WHERE id_product = ?";
    $stmt = $dbo->prepare($query);
    $stmt->execute([$id]);
    header('Content-Type: application/json');
    echo json_encode(["message" => "Produit supprimé"]);
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim($uri, '/'));

$filename = basename($_SERVER['SCRIPT_NAME'], '.php');
if ($filename == 'product' || (isset($segments[2]) && $segments[2] == 'product')) {
    if (isset($segments[3])) {
        $id = (int)$segments[3];
        switch ($method) {
            case 'GET':
                getProducts($id);
                break;
            case 'PUT':
                updateProduct($id);
                break;
            case 'DELETE':
                deleteProduct($id);
                break;
        }
    } else {
        switch ($method) {
            case 'GET':
                getProducts();
                break;
            case 'POST':
                addProduct();
                break;
        }
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Mauvaise URL. Utilisez /api/product ou /api/product/{id}"]);
}
?>