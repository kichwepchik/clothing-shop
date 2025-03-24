<?php
session_start();

function logToFile($message) {
    $logFile = 'debug.log';
    if (is_writable($logFile)) {
        $current = file_get_contents($logFile);
        $current .= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "\n";
        file_put_contents($logFile, $current);
    }
}

function redirectToError($message) {
    logToFile("Redirecting to error: $message");
    header("Location: error.php?message=" . urlencode($message));
    exit;
}

if (isset($_GET['user_id'])) {
    if (is_numeric($_GET['user_id'])) {
        $_SESSION['user_id'] = $_GET['user_id'];
    } else {
        redirectToError("Invalid user ID.");
    }
} elseif (!isset($_SESSION['user_id'])) {
    echo '<script type="text/javascript" src="https://telegram.org/js/telegram-web-app.js"></script>';
    echo '<script type="text/javascript">
        Telegram.WebApp.ready();
        Telegram.WebApp.expand();
        const initData = Telegram.WebApp.initDataUnsafe;

        if (initData.user && initData.user.id) {
            const userId = initData.user.id;
            const userName = initData.user.first_name;
            const userPhotoUrl = initData.user.photo_url || "";

            fetch("set_user_data.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "user_id=" + encodeURIComponent(userId) + "&user_name=" + encodeURIComponent(userName) + "&user_photo_url=" + encodeURIComponent(userPhotoUrl)
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    fetch("set_user_data.php?log=" + encodeURIComponent("Failed to set user data."));
                    window.location.href = "error.php?message=" + encodeURIComponent("Failed to set user data.");
                }
            }).catch(error => {
                fetch("set_user_data.php?log=" + encodeURIComponent("Error: " + error.message));
                window.location.href = "error.php?message=" + encodeURIComponent("Error: " + error.message);
            });
        } else {
            fetch("set_user_data.php?log=" + encodeURIComponent("User data not available from Telegram Web App."));
            window.location.href = "error.php?message=" + encodeURIComponent("User data not available from Telegram Web App.");
        }
    </script>';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Score - Спортивная атрибутика</title>
    <link rel="stylesheet" href="static/styles/styles.css">
    <link rel="stylesheet" href="static/styles/styles_for_index.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-bold-straight/css/uicons-bold-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-bold-rounded/css/uicons-bold-rounded.css'>
</head>
<body>
<header>
    <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
    <div class="profile">
        <div class="notification-icon-container">
            <i class="fi fi-br-shopping-bag" onclick="window.location.href='cart.php'"></i>
            <i class="fi fi-br-heart" onclick="window.location.href='like_items.php'"></i>
            <i class="fi fi-bs-bell" onclick="window.location.href='notifications.php'"></i>
            <div class="notification-dot" id="notificationDot"></div>
        </div>
        <div>
            <?php if (isset($_SESSION['user_name']) && isset($_SESSION['user_photo_url'])): ?>
                <a href="profile.php" class="user-profile">
                    <img src="<?= htmlspecialchars($_SESSION['user_photo_url'], ENT_QUOTES, 'UTF-8') ?>" alt="User Photo" class="user-photo">
                    <span class="user-name" style="text-decoration: none">Добро пожаловать,<br><?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            <?php else: ?>
                <?php logToFile("Session user_name or user_photo_url not set"); ?>
            <?php endif; ?>
        </div>
    </div>
</header>
<div class="interesting-section">
    <h2>Интересное</h2>
    <div class="cards-container">
        <button onclick="window.location.href='about_us.php'" class="card" id="card1">
            <p>Рассказываем <br>о себе</p>
            <div class="arrow"><span>→</span></div>
        </button>
        <button onclick="window.location.href='about_app.php'" class="card" id="card2">
            <p>Всё <br>о приложении</p>
            <div class="arrow"><span>→</span></div>
        </button>
        <button onclick="window.location.href='https://t.me/+y5GUVC_wtdY0Y2Uy'" class="card" id="card3">
            <p>Новости и <br> изменения</p>
            <div class="arrow"><span>→</span></div>
        </button>
    </div>
</div>
<div class="container">
    <h2>Привествуем Вас в нашем магазине спортивной атрибутики!</h2>
    <p>Выберите категорию:</p>
    <button onclick="window.location.href='items.php?category=1'" class="category-button" id="button-card1"><p>Футбол</p></button>
    <button onclick="window.location.href='items.php?category=2'" class="category-button" id="button-card2"><p>LifeStyle</p></button>
</div>

<nav class="menu" id="mobileMenu">
    <a href="items.php">Каталог</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="dropdown-menus">
    <div class="dropdown">
        <button class="dropdown-button">Покупателям</button>
        <div class="dropdown-content">
            <a href="#" data-content="privacy">Политика конфиденциальности</a>
            <a href="#" data-content="agreement">Пользовательское соглашение</a>
            <a href="#" data-content="offer">Публичная оферта</a>
            <a href="#" data-content="delivery">Доставка</a>
            <a href="#" data-content="return">Возврат</a>
        </div>
    </div>
    <div class="dropdown">
        <button class="dropdown-button">Контакты</button>
        <div class="dropdown-content">
            <a href="mailto:highscorechat@gmail.com" class="link">highscorechat@gmail.com</a>
            <a href="https://t.me/TonyYakim" class="link">t.me/TonyYakim</a>
            <a href="https://t.me/highscore_tech" class="link">t.me/highscore_tech</a>
        </div>
    </div>
    <div class="dropdown">
        <button class="dropdown-button">Cookie</button>
        <div class="dropdown-content">
            <p>
                HighScore использует файлы "cookie", с целью
                персонализации сервисов и повышения удобства
                пользования веб-сайтом. "cookie" это
                небольшие файлы, представляющие из себя
                информацию о предыдущих посещениях веб-сайта.
                Если вы не хотите использовать файлы
                "cookie", прекратите использование
                веб-сайта.
            </p>
        </div>
    </div>
</div>

<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">Закрыть</span>
        <div id="modal-text"></div>
    </div>
</div>
<footer>
    <p class="down">2023-2024 Ⓒ HIGH SCORE</p>
</footer>

<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="static/scripts/menu.js"></script>
<script src="static/scripts/dropdown.js"></script>
<script src="static/scripts/swipe_index.js"></script>
<script src="static/scripts/modal_for_index.js"></script>
<script>
    document.getElementById('card1').addEventListener('click', function() {
        window.location.href = 'about_us.php';
    });

    document.getElementById('card2').addEventListener('click', function() {
        window.location.href = 'about_app.php';
    });

    document.getElementById('card3').addEventListener('click', function() {
        window.location.href = 'https://t.me/+y5GUVC_wtdY0Y2Uy';
    });
</script>

<script>
    function checkNotifications() {
        fetch('get_notifications.php?telegram_id=<?= $_SESSION['user_id'] ?>')
            .then(response => response.json())
            .then(data => {
                const notificationDot = document.getElementById('notificationDot');
                if (data.unread_count > 0) {
                    notificationDot.style.display = 'block';
                } else {
                    notificationDot.style.display = 'none';
                }
            });
    }

    window.onload = checkNotifications;
    setInterval(checkNotifications, 60000); // Проверка уведомлений каждую минуту</script>
</body>
</html>
