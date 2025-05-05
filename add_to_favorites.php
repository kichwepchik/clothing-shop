<?php
session_start();
global $pdo;
require 'static/app/database/connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Необходимо войти в систему.']);
        exit;
    }

    try {
        // Проверка, есть ли товар уже в понравившихся
        $checkSql = 'SELECT COUNT(*) FROM like_items WHERE user_id = :user_id AND product_id = :product_id';
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        $isLiked = $checkStmt->fetchColumn() > 0;

        if ($isLiked) {
            // Удаляем товар из понравившихся
            $deleteSql = 'DELETE FROM like_items WHERE user_id = :user_id AND product_id = :product_id';
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->execute([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            echo json_encode(['status' => 'removed']);
        } else {
            // Добавляем товар в понравившиеся
            $insertSql = 'INSERT INTO like_items (user_id, product_id) VALUES (:user_id, :product_id)';
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            echo json_encode(['status' => 'added']);
        }
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при добавлении в понравившиеся: ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный метод запроса.']);
    exit;
}
?>
