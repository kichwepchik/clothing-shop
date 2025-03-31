<?php
global $pdo;
require 'static/app/database/connect.php';

$category = $_POST['category'] ?? null;
$color = $_POST['color'] ?? null;
$size = $_POST['size'] ?? null;
$manufacturer = $_POST['manufacturer'] ?? null;
$priceMin = floatval($_POST['priceMin'] ?? 0);
$priceMax = floatval($_POST['priceMax'] ?? 10000);
$order = in_array($_POST['order'], ['asc', 'desc']) ? $_POST['order'] : 'asc';

$sql = "SELECT products.id, products.manufacturer, products.name, products.description, products.price, products.image_url 
        FROM products 
        JOIN product_colors ON products.id = product_colors.product_id
        JOIN product_sizes ON products.id = product_sizes.product_id
        WHERE products.price BETWEEN ? AND ?";

$params = [$priceMin, $priceMax];

if ($category) {
    $sql .= " AND products.category_id = ?";
    $params[] = intval($category);
}

if ($color) {
    $sql .= " AND product_colors.color_id = ?";
    $params[] = intval($color);
}

if ($size) {
    $sql .= " AND product_sizes.size_id = ?";
    $params[] = intval($size);
}

if ($manufacturer) {
    $sql .= " AND products.manufacturer = ?";
    $params[] = $manufacturer;
}

$sql .= " ORDER BY products.price $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($products);
?>
