<?php
// Включение файла подключения к базе данных
global $pdo;
    require 'static/app/database/connect.php';

// Инициализация переменной products пустым массивом
$products = [];

// Получаем параметр сортировки из запроса
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : '';

// Формируем SQL запрос в зависимости от параметра сортировки
$sql = 'SELECT id, name, price, image_url FROM products';
switch ($sortBy) {
    case 'price_asc':
        $sql .= ' ORDER BY price ASC';
        break;
    case 'price_desc':
        $sql .= ' ORDER BY price DESC';
        break;
    case 'date_asc':
        $sql .= ' ORDER BY created_at ASC';
        break;
    case 'date_desc':
        $sql .= ' ORDER BY created_at DESC';
        break;
}

try {
    // Извлечение данных из таблицы products
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Обработка ошибки запроса
    echo "Ошибка при выполнении запроса: " . $e->getMessage();
}

// Возвращаем данные в формате JSON
header('Content-Type: application/json');
echo json_encode($products);
?>
