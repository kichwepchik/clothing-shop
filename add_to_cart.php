<?php
session_start();
global $pdo;
require_once 'static/app/database/connect.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id']);
    $sizeId = intval($_POST['size_id']);
    $quantity = 1; // Устанавливаем количество товара равным 1
    $userId = $_SESSION['user_id']; // предположим, что ID пользователя хранится в сессии

    try {
        // Проверка наличия товара на складе
        $sql = 'SELECT p.stock, pss.stock AS size_stock 
                FROM products p
                JOIN product_size_stock pss ON p.id = pss.product_id AND pss.size_id = :size_id
                WHERE p.id = :product_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'size_id' => $sizeId
        ]);
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stock || $stock['stock'] < $quantity || $stock['size_stock'] < $quantity) {
            echo json_encode(['status' => 'error', 'message' => 'Недостаточно товара на складе.']);
            exit;
        }

        // Добавление товара в корзину
        $sql = 'INSERT INTO cart_items (user_id, product_id, size_id, quantity) VALUES (:user_id, :product_id, :size_id, :quantity)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId,
            'size_id' => $sizeId,
            'quantity' => $quantity
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Товар добавлен в корзину']);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при добавлении в корзину: ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный метод запроса.']);
    exit;
}
?>
