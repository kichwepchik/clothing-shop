<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

function redirectToError($message) {
    header("Location: error.php?message=" . urlencode($message));
    exit;
}

if (isset($_GET['user_id'])) {
    $_SESSION['user_id'] = filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);
} elseif (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript" src="https://telegram.org/js/telegram-web-app.js"></script>';
    echo '<script type="text/javascript">
        Telegram.WebApp.ready();
        Telegram.WebApp.expand();
        const initData = Telegram.WebApp.initDataUnsafe;
        if (initData.user && initData.user.id) {
            const userId = initData.user.id;
            const userName = initData.user.first_name;
            const userPhotoUrl = initData.user.photo_url;
            fetch("set_user_data.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "user_id=" + userId + "&user_name=" + userName + "&user_photo_url=" + encodeURIComponent(userPhotoUrl)
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    window.location.href = "error.php?message=" + encodeURIComponent("Failed to set user data.");
                }
            }).catch(error => {
                window.location.href = "error.php?message=" + encodeURIComponent("Error: " + error.message);
            });
        } else {
            window.location.href = "error.php?message=" + encodeURIComponent("User data not available from Telegram Web App.");
        }
    </script>';
    exit;
}

$userId = $_SESSION['user_id'];
$personalInfo = [
    'first_name' => '',
    'last_name' => '',
    'phone_number' => '',
    'email' => '',
    'adres' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $phoneNumber = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $adres = filter_input(INPUT_POST, 'adres', FILTER_SANITIZE_STRING);

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
        header('Location: profile.php');
        exit;
    } else {
        redirectToError("All fields are required.");
    }
} else {
    // Load personal info
    $sql = "SELECT email, first_name, last_name, adres, phone_number FROM shipping_info WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $personalInfo = array_merge($personalInfo, $userData);
    }
}

function getOrders($pdo, $userId, $status = 'active') {
    $condition = '';
    if ($status === 'history') {
        $condition = "AND (o.application_status = 'отклонён' OR od.status = 'Получен')";
    } else {
        $condition = "AND o.application_status != 'отклонён' AND od.status != 'Получен'";
    }

    $sql = "SELECT o.id AS order_id, oi.price, o.application_status, oi.product_id, s.size, p.manufacturer, p.name, p.image_url, oi.quantity, od.status AS delivery_status
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.id
            JOIN sizes s ON oi.size_id = s.id
            JOIN order_delivery od ON o.id = od.order_id
            WHERE o.user_id = ? $condition
            ORDER BY o.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$activeOrders = getOrders($pdo, $userId, 'active');
$orderHistory = getOrders($pdo, $userId, 'history');
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Score - Спортивная атрибутика</title>
    <link rel="stylesheet" href="static/styles/styles_for_profile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-solid-rounded/css/uicons-solid-rounded.css'>
</head>
<body>
<header>
    <div class="user-profile">
        <?php if (isset($_SESSION['user_name']) && isset($_SESSION['user_photo_url'])): ?>
            <a href="index.php" class="user-profile-link">
                <img src="<?= htmlspecialchars($_SESSION['user_photo_url']) ?>" alt="User Photo" class="user-photo">
                <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
            </a>
        <?php endif; ?>
    </div>

</header>

<div class="container">
    <div class="orders" id="orders">
        <h2>Активные заказы</h2>
        <?php if (count($activeOrders) > 0): ?>
            <?php
            $currentOrderId = null;
            foreach ($activeOrders as $order):
                if ($currentOrderId !== $order['order_id']):
                    if ($currentOrderId !== null):
                        echo '</div>';
                    endif;
                    $currentOrderId = $order['order_id'];
                    echo '<div class="order-container">';
                    echo '<h3 class="text-order">Заказ №' . htmlspecialchars($order['order_id']) . '</h3>';
                endif; ?>
                <div class="order-item" onclick="window.location.href='order_details.php?id=<?= $order['order_id'] ?>'">
                    <img src="<?= htmlspecialchars($order['image_url']) ?>" alt="<?= htmlspecialchars($order['name']) ?>" class="order-image">
                    <div class="order-info">
                        <p><?= htmlspecialchars($order['manufacturer']) ?> <?= htmlspecialchars($order['name']) ?></p>
                        <p><?= htmlspecialchars($order['size']) ?></p>
                        <p>Статус: <?= htmlspecialchars($order['application_status']) ?></p>
                        <p><?= htmlspecialchars($order['price']) ?>₽</p>
                    </div>
                    <?php if ($order['quantity'] > 1): ?>
                        <div class="order-quantity">+<?= $order['quantity'] - 1 ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach;
            echo '</div>';
            ?>
        <?php else: ?>
            <p>У вас нет активных заказов</p>
        <?php endif; ?>
    </div>

    <div class="cont-block">
        <div class="favorites" id="favorites" onclick="window.location.href= 'like_items.php'">
            <i class="fi fi-sr-heart"></i>
            <p>Избранное</p>
        </div>
        <div class="cart" id="cart" onclick="window.location.href= 'cart.php'">
            <i class="fi fi-sr-shopping-cart"></i>
            <p>Корзина</p>
        </div>
    </div>

    <div id="personal-info">
        <h2>Персональные данные</h2>
        <form method="post" action="profile.php" id="personal-info-form">
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
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($personalInfo['email']) ?>" placeholder="Email">
            </div>
            <div class="input-container">
                <input type="text" id="adres" name="adres" value="<?= htmlspecialchars($personalInfo['adres']) ?>" placeholder="Ваш адрес СДЭК">
            </div>
            <button type="submit" id="save-button">Сохранить</button>
        </form>
    </div>

    <div class="history" id="history">
        <h2>История заказов</h2>
        <?php if (count($orderHistory) > 0): ?>
            <?php
            $currentOrderId = null;
            foreach ($orderHistory as $order):
                if ($currentOrderId !== $order['order_id']):
                    if ($currentOrderId !== null):
                        echo '</div>';
                    endif;
                    $currentOrderId = $order['order_id'];
                    echo '<div class="order-container">';
                    echo '<h3 class="text-order">Заказ №' . htmlspecialchars($order['order_id']) . '</h3>';
                endif; ?>
                <div class="order-item" onclick="window.location.href='order_details.php?id=<?= $order['order_id'] ?>'">
                    <img src="<?= htmlspecialchars($order['image_url']) ?>" alt="<?= htmlspecialchars($order['name']) ?>" class="order-image">
                    <div class="order-info">
                        <p><?= htmlspecialchars($order['manufacturer']) ?> <?= htmlspecialchars($order['name']) ?></p>
                        <p><?= htmlspecialchars($order['size']) ?></p>
                        <p>Статус: <?= htmlspecialchars($order['application_status']) ?></p>
                        <p><?= htmlspecialchars($order['price']) ?>₽</p>
                    </div>
                    <?php if ($order['quantity'] > 1): ?>
                        <div class="order-quantity">+<?= $order['quantity'] - 1 ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach;
            echo '</div>';
            ?>
        <?php else: ?>
            <p>У вас нет истории заказов</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/save_for_profile.js"></script>
</body>
</html>

