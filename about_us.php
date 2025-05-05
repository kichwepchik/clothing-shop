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
    <h1>Рассказываем о себе</h1>
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
        Добро пожаловать на сайт нашего магазина High Score! Мы специализируемся на выкупе оригинальной качественной спортивной обуви и одежды по всей Европе.

        Наша компания успешно работает уже 3 года и постоянно развивается. Мы стремимся предложить нашим клиентам только лучшее – качественную и оригинальную продукцию от ведущих спортивных брендов.

        Теперь мы рады сообщить, что запускаем наш сайт для покупателей из России. Это позволит вам легко и удобно приобретать оригинальную спортивную обувь и одежду, а также футбольную экипировку высокого качества. Мы гарантируем подлинность всех товаров и предлагаем широкий ассортимент для всех, кто ценит комфорт и стиль.

        Присоединяйтесь к нам и наслаждайтесь покупками!
    </p>
</div>
<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/menu.js"></script>
</body>
</html>