<?php
session_start();
global $pdo;
require 'static/app/database/connect.php';

$product = [];
$sizes = [];
$images = [];
$color = '';
$isFavorite = false;

function redirectToError($message) {
    header("Location: error.php?message=" . urlencode($message));
    exit;
}

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    $userId = $_SESSION['user_id']; // предположим, что ID пользователя хранится в сессии

    try {
        // Извлечение данных о продукте
        $sql = 'SELECT p.id, p.name, p.description, p.price, p.image_url, c.color_name, p.manufacturer 
                FROM products p
                JOIN product_colors pc ON p.id = pc.product_id
                JOIN colors c ON pc.color_id = c.id
                WHERE p.id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            redirectToError("Товар не найден.");
        }

        $color = $product['color_name'];

        // Проверка, находится ли товар в избранном
        $sql = 'SELECT * FROM like_items WHERE user_id = :user_id AND product_id = :product_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        $isFavorite = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;

        // Извлечение доступных размеров
        $sql = 'SELECT s.id, s.size FROM sizes s
                JOIN product_sizes ps ON s.id = ps.size_id
                WHERE ps.product_id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Извлечение изображений
        $sql = 'SELECT image_url FROM product_images WHERE product_id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        redirectToError("Ошибка при выполнении запроса: " . $e->getMessage());
    }
} else {
    redirectToError("ID товара не указан.");
}
?>