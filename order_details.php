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

if (!isset($_GET['id'])) {
    header("Location: error.php?message=" . urlencode("Order ID is missing."));
    exit;
}

$orderId = $_GET['id'] ?? null;
if (!$orderId || !ctype_digit($orderId)) {
    header("Location: error.php?message=" . urlencode("Invalid Order ID."));
    exit;
}

$notifId = $_GET['notif_id'] ?? null;
if ($notifId && !ctype_digit($notifId)) {
    header("Location: error.php?message=" . urlencode("Invalid Notification ID."));
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId || !ctype_digit($userId)) {
    header("Location: error.php?message=" . urlencode("Invalid User ID."));
    exit;
}


// Обновление статуса уведомления на "прочитано"
if ($notifId) {
    $stmt = $pdo->prepare("UPDATE notifications SET `read` = TRUE WHERE id = ? AND telegram_id = ?");
    $stmt->execute([$notifId, $userId]);
}

$sql = "SELECT o.id AS order_id, 
               o.total_amount, 
               o.application_status, 
               o.status, 
               oi.product_id, 
               s.size, 
               p.manufacturer, 
               p.name, 
               p.image_url, 
               oi.quantity,
               p.price
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        JOIN sizes s ON oi.size_id = s.id
        WHERE o.id = ? AND o.user_id = ?
        GROUP BY o.id, o.total_amount, o.application_status, o.status, oi.product_id, s.size, p.manufacturer, p.name, p.image_url, oi.quantity";
$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId, $userId]);
$orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orderDetails) {
    header("Location: error.php?message=" . urlencode("Order not found or you do not have permission to view it."));
    exit;
}

$order = $orderDetails[0];
$totalAmountWithDelivery = $order['total_amount']; // Add delivery cost

// Get delivery status
$sqlDelivery = "SELECT status FROM order_delivery WHERE order_id = ? AND user_id = ?";
$stmtDelivery = $pdo->prepare($sqlDelivery);
$stmtDelivery->execute([$orderId, $userId]);
$deliveryStatus = $stmtDelivery->fetch(PDO::FETCH_ASSOC);

$deliveryStages = [
    'В обработке ожидает оплаты' => 'В обработке, ожидает оплаты',
    'Отправлен на склад' => 'Отправлен на склад',
    'На складе готов к отправке' => 'На складе, готов к отправке',
    'Отправлен СДЭК' => 'Отправлен СДЭК',
    'Получен' => 'Получен покупателем'
];

$currentStage = isset($deliveryStatus['status']) ? $deliveryStatus['status'] : null;

// Fetch shipping info
$sqlShipping = "SELECT * FROM shipping_info WHERE user_id = ?";
$stmtShipping = $pdo->prepare($sqlShipping);
$stmtShipping->execute([$userId]);
$shippingInfo = $stmtShipping->fetch(PDO::FETCH_ASSOC);

if (!$shippingInfo) {
    header("Location: error.php?message=" . urlencode("Shipping info not found."));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали заказа №<?= htmlspecialchars($order['order_id']) ?></title>
    <link rel="stylesheet" href="static/styles/styles_for_order_details.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>

<nav class="menu" id="mobileMenu">
    <a href="index.php">Главное меню</a>
    <a href="items.php">Каталог</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>

<div class="order-details" id="order-details">
    <div class="order-items">
        <div class="slider">
            <?php foreach ($orderDetails as $item): ?>
                <div class="slide">
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-image">
                    <div class="order-item-info">
                        <p>
                            <strong><?= htmlspecialchars($item['manufacturer']) ?> <?= htmlspecialchars($item['name']) ?></strong><br>
                            Размер: <strong><?= htmlspecialchars($item['size']) ?></strong><br>
                            Количество: <strong><?= htmlspecialchars($item['quantity']) ?></strong><br>
                            Цена: <strong><?= htmlspecialchars($item['price']) ?>₽</strong>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="total-amount">
        <p>Итоговая сумма: <strong><?= number_format($totalAmountWithDelivery, 2, '.', '') ?>₽</strong> (включая доставку)</p>
    </div>

    <div class="order-info">
        <p>Статус заявки: <strong><?= htmlspecialchars($order['application_status']) ?></strong></p>
        <p>Статус оплаты:
            <?php if ($order['application_status'] === 'Ожидает подтверждения'): ?>
                Заявка на рассмотрении
            <?php elseif ($order['application_status'] === 'Подтверждён'): ?>
                <?php if ($order['status'] === 'Ожидает оплаты'): ?>
                    <a href="#" id="payment-link">Оплатить заказ</a>
                <?php elseif ($order['status'] === 'Оплачен'): ?>
                    Оплачен
                <?php endif; ?>
            <?php elseif ($order['application_status'] === 'Отклонён'): ?>
                Оплата невозможна
            <?php endif; ?>
        </p>
    </div>




    <!-- Popup уведомление -->
    <div id="popup" class="popup">
        <p>Сумма оплаты <?= number_format($totalAmountWithDelivery, 2, '.', '') ?>₽. <br>Оплатить заказ необходимо по следующим реквизитам:</p>
        <p class="bank-info">Сбер Банк- Номер счёта: <br><span id="account-number">40817810110003308843</span></p>
    </div>

    <!-- Уведомление о копировании -->
    <div id="copy-notification" class="copy-notification">
        Номер счёта скопирован
    </div>


    <div class="delivery-tracking">
        <ul class="delivery-stages">
            <?php foreach ($deliveryStages as $stage => $description): ?>
                <li class="<?= $currentStage === $stage ? 'active' : '' ?>">
                    <span class="stage-name"><?= htmlspecialchars($description) ?></span>
                    <?php if ($currentStage === $stage): ?>
                        <span class="status">(текущий этап)</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="shipping-info">
        <h3>Подробнее</h3>
        <div class="info-block">
            <p><strong>Получатель:</strong> <br><?= htmlspecialchars($shippingInfo['first_name']) . ' ' . htmlspecialchars($shippingInfo['last_name']) ?></p>
            <p><strong>Телефон:</strong><br> <?= htmlspecialchars($shippingInfo['phone_number']) ?></p>
            <p><strong>Адрес доставки СДЭК:</strong><br> <?= htmlspecialchars($shippingInfo['adres']) ?></p>
            <p><strong>Тип доставки:</strong><br> Обычная доставка</p>
        </div>
        <p class="support">Если какие-то данные введены неверно, обязательно обратитесь в <a href="https://t.me/TonyYakim">поддержку</a></p>
    </div>

    <?php if ($order['application_status'] !== 'Отклонён'): ?>
        <button id="cancelOrderBtn" class="cancel-order-btn">Отменить заказ</button>
    <?php endif; ?>

    <script>
        document.getElementById('cancelOrderBtn').addEventListener('click', function () {
            const orderId = <?= json_encode($order['order_id']) ?>;

            if (confirm('Вы уверены, что хотите отменить заказ?')) {
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Server response:', data); // Логирование ответа сервера
                        if (data.success) {
                            alert('Заказ был успешно отменён.');
                            // Скрытие кнопки "Отменить заказ"
                            document.getElementById('cancelOrderBtn').style.display = 'none';
                            // Перезагрузить страницу, чтобы отобразить изменения (если это необходимо)
                            // location.reload();
                        } else {
                            alert('Не удалось отменить заказ. Попробуйте еще раз.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Произошла ошибка. Попробуйте еще раз.');
                    });
            }
        });
    </script>

</div>

<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/menu.js"></script>
<script src="static/scripts/popup_for_orderdetails.js"></script>
</body>
</html>
