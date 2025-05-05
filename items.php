<?php
session_start();
global $pdo;
require_once 'static/app/database/connect.php';

$products = [];
$categories = [];
$clothing_types = [];
$sizes = [];
$colors = [];
$manufacturers = [];

function redirectToError($message) {
    header("Location: error.php?message=" . urlencode($message));
    exit;
}

$sortBy = isset($_GET['sort']) ? $_GET['sort'] : '';
$categoryFilter = isset($_GET['category']) ? explode(',', $_GET['category']) : [];
$clothingTypeFilter = isset($_GET['clothing_type']) ? explode(',', $_GET['clothing_type']) : [];
$sizeFilter = isset($_GET['size']) ? explode(',', $_GET['size']) : [];
$colorFilter = isset($_GET['color']) ? explode(',', $_GET['color']) : [];
$manufacturerFilter = isset($_GET['manufacturer']) ? explode(',', $_GET['manufacturer']) : [];

$sql = 'SELECT DISTINCT p.id, p.name, p.price, p.image_url, p.manufacturer FROM products p';
$conditions = [];
$joins = [];

if ($categoryFilter) {
    $conditions[] = 'p.category_id IN (' . implode(',', array_fill(0, count($categoryFilter), '?')) . ')';
}

if ($clothingTypeFilter) {
    $joins[] = 'INNER JOIN product_clothing_types pct ON p.id = pct.product_id';
    $conditions[] = 'pct.clothing_type_id IN (' . implode(',', array_fill(0, count($clothingTypeFilter), '?')) . ')';
}

if ($sizeFilter) {
    $joins[] = 'INNER JOIN product_sizes ps ON p.id = ps.product_id';
    $conditions[] = 'ps.size_id IN (' . implode(',', array_fill(0, count($sizeFilter), '?')) . ')';
}

if ($colorFilter) {
    $joins[] = 'INNER JOIN product_colors pc ON p.id = pc.product_id';
    $conditions[] = 'pc.color_id IN (' . implode(',', array_fill(0, count($colorFilter), '?')) . ')';
}

if ($manufacturerFilter) {
    $conditions[] = 'p.manufacturer IN (' . implode(',', array_fill(0, count($manufacturerFilter), '?')) . ')';
}

if ($joins) {
    $sql .= ' ' . implode(' ', $joins);
}

if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

switch ($sortBy) {
    case 'price_asc':
        $sql .= ' ORDER BY p.price ASC';
        break;
    case 'price_desc':
        $sql .= ' ORDER BY p.price DESC';
        break;
    case 'date_asc':
        $sql .= ' ORDER BY p.created_at ASC';
        break;
    case 'date_desc':
        $sql .= ' ORDER BY p.created_at DESC';
        break;
    case 'manufacturer_asc':
        $sql .= ' ORDER BY p.manufacturer ASC';
        break;
    case 'manufacturer_desc':
        $sql .= ' ORDER BY p.manufacturer DESC';
        break;
}

try {
    $stmt = $pdo->prepare($sql);

    $params = array_merge($categoryFilter, $clothingTypeFilter, $sizeFilter, $colorFilter, $manufacturerFilter);
    foreach ($params as $index => $param) {
        $stmt->bindValue($index + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = $pdo->query('SELECT id, name FROM categories')->fetchAll(PDO::FETCH_ASSOC);
    $clothing_types = $pdo->query('SELECT id, type_name FROM clothing_types')->fetchAll(PDO::FETCH_ASSOC);
    $sizes = $pdo->query('SELECT id, size FROM sizes')->fetchAll(PDO::FETCH_ASSOC);
    $colors = $pdo->query('SELECT id, color_name FROM colors')->fetchAll(PDO::FETCH_ASSOC);
    $manufacturers = $pdo->query('SELECT DISTINCT manufacturer FROM products')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    redirectToError("Ошибка при выполнении запроса: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Score - Товары</title>
    <link rel="stylesheet" href="static/styles/styles_for_items.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-bold-rounded/css/uicons-bold-rounded.css'>
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <h1>High Score</h1>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>
<nav class="menu" id="mobileMenu">
    <a href="index.php">Главная страница</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="overlay" id="menuOverlay" onclick="toggleMenu_sort()"></div>

<div class="container">
    <h2 class="catalog">Каталог</h2>
    <button class="sort-button" onclick="toggleSortMenu()">Сортировка и фильтры</button>
    <div class="product-grid" id="productGrid">
        <?php foreach ($products as $product): ?>
            <?php
            // Проверка, добавлен ли товар в понравившиеся
            $isLiked = false;
            if (isset($_SESSION['user_id'])) {
                $checkSql = 'SELECT COUNT(*) FROM like_items WHERE user_id = :user_id AND product_id = :product_id';
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'product_id' => $product['id']
                ]);
                $isLiked = $checkStmt->fetchColumn() > 0;
            }
            $heartClass = $isLiked ? 'fi-sr-heart' : 'fi-br-heart';
            ?>
            <div class="product-card" id="card">
                <i class="<?= $heartClass ?>" onclick="addToFavorites(<?= htmlspecialchars($product['id']) ?>, this)"></i>
                <h3 class="product-name"><?= htmlspecialchars($product['manufacturer']) ?> <?= htmlspecialchars($product['name']) ?></h3>
                <div onclick="window.location.href='product.php?id=<?= htmlspecialchars($product['id']) ?>'">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                </div>
                <p class="product-price"><?= htmlspecialchars($product['price']) ?> ₽</p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="sort-menu" id="sortMenu">
    <button class="close-sort-button" onclick="toggleSortMenu()">Закрыть</button>
    <div class="sort-option">
        <h3>Цена</h3>
        <button onclick="selectSort('price_asc')">От меньшего</button>
        <button onclick="selectSort('price_desc')">От большего</button>
    </div>
    <div class="sort-option">
        <h3>Дата добавления</h3>
        <button onclick="selectSort('date_asc')">От новых к старым</button>
        <button onclick="selectSort('date_desc')">От старых к новым</button>
    </div>
    <div class="sort-option">
        <h3 id="categoryMenu">Категория</h3>
        <div id="categoryMenu">
            <?php foreach ($manufacturers as $manufacturer): ?>
                <?php if (!empty($manufacturer['manufacturer'])): ?>
                    <label>
                        <input type="checkbox" name="manufacturer" value="<?= htmlspecialchars($manufacturer['manufacturer']) ?>" <?= in_array($manufacturer['manufacturer'], $manufacturerFilter) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($manufacturer['manufacturer']) ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="sort-option_1">
        <h3>Бренд</h3>
        <div id="manufacturer">
            <?php foreach ($manufacturers as $manufacturer): ?>
                <label>
                    <input type="checkbox" name="manufacturer" value="<?= htmlspecialchars($manufacturer['manufacturer']) ?>" <?= in_array($manufacturer['manufacturer'], $manufacturerFilter) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($manufacturer['manufacturer']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>


    <div class="sort-option_1">
        <h3>Тип одежды</h3>
        <div id="clothingTypeMenu">
            <?php foreach ($clothing_types as $clothing_type): ?>
                <label>
                    <input type="checkbox" name="clothing_type" value="<?= htmlspecialchars($clothing_type['id']) ?>" <?= in_array($clothing_type['id'], $clothingTypeFilter) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($clothing_type['type_name']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="sort-option_1">
        <h3>Размер</h3>
        <div id="sizeMenu">
            <?php foreach ($sizes as $size): ?>
                <label>
                    <input type="checkbox" name="size" value="<?= htmlspecialchars($size['id']) ?>" <?= in_array($size['id'], $sizeFilter) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($size['size']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="sort-option_1">
        <h3>Цвет</h3>
        <div id="colorMenu">
            <?php foreach ($colors as $color): ?>
                <label>
                    <input type="checkbox" name="color" value="<?= htmlspecialchars($color['id']) ?>" <?= in_array($color['id'], $colorFilter) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($color['color_name']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <input type="hidden" id="selectedSort" value="<?= htmlspecialchars($sortBy) ?>">
    <input type="hidden" id="selectedCategory" value="<?= htmlspecialchars(implode(',', $categoryFilter)) ?>">
    <button class="apply-button" onclick="applyFilters()">Применить</button>
    <button type="button" class="apply-button" onclick="resetFilters()">Сбросить фильтры</button>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="static/scripts/script_for_items.js"></script>
<script src="static/scripts/menu.js"></script>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/add_to_favorite.js"></script>
</body>
</html>
