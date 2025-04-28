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
