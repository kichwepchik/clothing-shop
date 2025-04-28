<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

function logToFile($message) {
    $logFile = 'debug.log';
    $current = file_get_contents($logFile);
    $current .= $message . "\n";
    file_put_contents($logFile, $current);
}

function redirectToError($message) {
    logToFile("Redirecting to error: $message");
    header("Location: error.php?message=" . urlencode($message));
    exit;
}

$userId = $_SESSION['user_id']; // предположим, что ID пользователя хранится в сессии

try {
    // Извлечение товаров из корзины для конкретного пользователя
    $sql = 'SELECT ci.id, ci.product_id, ci.size_id, ci.color_id, ci.quantity, p.price, p.image_url, p.manufacturer, p.name, s.size
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            JOIN sizes s ON ci.size_id = s.id
            WHERE ci.user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Ошибка при выполнении запроса: " . $e->getMessage();
    exit;
}

$deliveryFee = 500;
$totalPrice = array_reduce($cartItems, function ($carry, $item) {
    return $carry + ($item['quantity'] * $item['price']);
}, 0);

$totalPrice += $deliveryFee;

$personalInfo = [
    'first_name' => '',
    'last_name' => '',
    'phone_number' => '',
    'email' => '',
    'adres' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Сохранение персональной информации
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phoneNumber = $_POST['phone_number'];
    $email = $_POST['email'];
    $adres = $_POST['adres'];

    if ($userId && $firstName && $lastName && $phoneNumber && $email && $adres) {
        $sql = "SELECT COUNT(*) FROM shipping_info WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $userExists = $stmt->fetchColumn() > 0;

        if ($userExists) {
            $sql = "UPDATE shipping_info SET first_name = ?, last_name = ?, phone_number = ?, email = ?, adres = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$firstName, $lastName, $phoneNumber, $email, $adres, $userId]);
        } else {
            $sql = "INSERT INTO shipping_info (user_id, first_name, last_name, phone_number, email, adres) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $firstName, $lastName, $phoneNumber, $email, $adres]);
        }
    }

    // Создание заказа
    try {
        $pdo->beginTransaction();

        // Вставка в таблицу orders
        $sql = "INSERT INTO orders (user_id, total_amount, application_status, status) VALUES (?, ?, 'Ожидает подтверждения', 'Ожидает оплаты')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $totalPrice]);

        $orderId = $pdo->lastInsertId();

        // Вставка в таблицу order_items
        $sql = "INSERT INTO order_items (order_id, product_id, color_id, size_id, quantity, price) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($cartItems as $item) {
            $stmt->execute([$orderId, $item['product_id'], $item['color_id'], $item['size_id'], $item['quantity'], $item['price']]);
        }

        // Очистка корзины
        $sql = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        redirectToError("Ошибка при создании заказа: " . $e->getMessage());
    }

    echo "<script>
        alert('Заявка оформлена');
        window.location.href = 'profile.php';
    </script>";
    exit;
} else {
    // Загрузка персональной информации
    $sql = "SELECT email, first_name, last_name, adres, phone_number FROM shipping_info WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $personalInfo = array_merge($personalInfo, $userData);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Score - Спортивная атрибутика</title>
    <link rel="stylesheet" href="static/styles/styles.css">
    <link rel="stylesheet" href="static/styles/styles_for_pay.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <h1>Оформление заказа</h1>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>
<nav class="menu" id="mobileMenu">
    <a href="index.php">Главная страница</a>
    <a href="items.php">Каталог</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="container">
    <div class="orders" id="orders">
        <?php foreach ($cartItems as $item): ?>
            <div class="order-item" data-item-id="<?= $item['id'] ?>">
                <img src="<?= $item['image_url'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-image">
                <div class="order-info">
                    <h2><strong><?= htmlspecialchars($item['manufacturer']) ?> <?= htmlspecialchars($item['name']) ?></strong></h2>
                    <p>
                        Размер: <strong><?= htmlspecialchars($item['size']) ?></strong> <br>
                        Количество: <strong><?= htmlspecialchars($item['quantity']) ?></strong> <br>
                        <strong><?= htmlspecialchars($item['price']) ?> ₽</strong> <br>
                        Общая цена: <strong><span class="item-total-price"><?= number_format($item['quantity'] * $item['price'], 2) ?></span> ₽</strong>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="delivery">
        <p>Доставка СДЭК</p>
        <div class="delivery-option checked">
            <input type="radio" id="standard" name="delivery" value="standard" checked>
            <label for="standard">Стандартная 500₽</label>
            <span>до 20 дней</span>
        </div>
    </div>
</div>
<div id="personal-info">
    <h2>Персональные данные</h2>
    <form method="post" action="pay.php" id="personal-info-form">
        <div class="input-container">
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($personalInfo['first_name']) ?>" placeholder="Имя">
        </div>
        <div class="input-container">
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($personalInfo['last_name']) ?>" placeholder="Фамилия">
        </div>
        <div class="input-container">
            <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($personalInfo['phone_number']) ?>" placeholder="Телефон">
        </div>
        <div class="input-container">
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($personalInfo['email']) ?>" placeholder="Почта">
        </div>
        <div class="input-container">
            <input type="text" id="adres" name="adres" value="<?= htmlspecialchars($personalInfo['adres']) ?>" placeholder="Ваш адрес СДЭК">
        </div>
        <button type="submit" id="save-button">Оплатить: <span id="total-price"><?= number_format($totalPrice, 2) ?> ₽</span></button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let totalPriceElement = document.getElementById('total-price');
        let total = <?= number_format($totalPrice, 2) ?>;
        totalPriceElement.textContent = total.toFixed(2) + ' ₽';
    });
</script>
<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/menu.js"></script>
<script src="static/scripts/script_for_pay.js"></script>
</body>
</html>
