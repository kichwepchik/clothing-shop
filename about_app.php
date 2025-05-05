<?php
session_start();
if (isset($_GET['user_id'])) {
    if (is_numeric($_GET['user_id'])) {
        $_SESSION['user_id'] = $_GET['user_id'];
    } else {
        redirectToError("Invalid user ID.");
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>

    <link rel="stylesheet" href="static/styles/styles_for_html.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Title</title>
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <h1>Всё о приложении</h1>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>
<nav class="menu" id="mobileMenu">
    <a href="index.php">Главная страница</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="container">
    <p>
        Мы рады представить наше новое приложение – мини сайт в Telegram! Теперь покупки качественной и оригинальной спортивной обуви, одежды и футбольной экипировки стали еще проще и удобнее.

        Наше приложение в Telegram позволяет вам:

        Просматривать и выбирать товары из широкого ассортимента оригинальной спортивной продукции от ведущих европейских брендов.
        Получать актуальные обновления о новинках и специальных предложениях.
        Легко и быстро оформлять заказы прямо в мессенджере.
        Получать консультации и поддержку от нашей команды в режиме реального времени.
        Присоединяйтесь к нам в Telegram и наслаждайтесь простыми и удобными покупками качественной спортивной продукции!
    </p>
</div>
<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/menu.js"></script>
</body>
</html>