function toggleMenu_sort() {
    var menu = document.getElementById("mobileMenu");
    var overlay = document.getElementById("menuOverlay");
    if (menu.classList.contains("open")) {
        menu.classList.remove("open");
        overlay.classList.remove("open");
    } else {
        menu.classList.add("open");
        overlay.classList.add("open");
    }
}

function toggleSortMenu() {
    var sortMenu = document.getElementById("sortMenu");
    if (sortMenu.classList.contains("open")) {
        sortMenu.classList.remove("open");
        // Убираем анимацию через небольшую задержку, чтобы дать время завершить переход
        setTimeout(() => sortMenu.style.display = "none", 500);
    } else {
        sortMenu.style.display = "flex"; // Делаем меню видимым
        setTimeout(() => sortMenu.classList.add("open"), 10); // Добавляем класс с анимацией
    }
}

function selectSort(sortBy) {
    document.getElementById('selectedSort').value = sortBy;
}

function applyFilters() {
    var categories = Array.from(document.querySelectorAll('input[name="category"]:checked')).map(el => el.value);
    var clothingTypes = Array.from(document.querySelectorAll('input[name="clothing_type"]:checked')).map(el => el.value);
    var sizes = Array.from(document.querySelectorAll('input[name="size"]:checked')).map(el => el.value);
    var colors = Array.from(document.querySelectorAll('input[name="color"]:checked')).map(el => el.value);
    var manufacturers = Array.from(document.querySelectorAll('input[name="manufacturer"]:checked')).map(el => el.value);
    var sortBy = document.getElementById('selectedSort').value;
    var selectedCategory = document.getElementById('selectedCategory').value;

    var params = new URLSearchParams();
    if (sortBy) params.set('sort', sortBy);
    if (categories.length) {
        params.set('category', categories.join(','));
    } else if (selectedCategory) {
        params.set('category', selectedCategory);
    }
    if (clothingTypes.length) params.set('clothing_type', clothingTypes.join(','));
    if (sizes.length) params.set('size', sizes.join(','));
    if (colors.length) params.set('color', colors.join(','));
    if (manufacturers.length) params.set('manufacturer', manufacturers.join(','));

    window.location.href = 'items.php?' + params.toString();
}


function resetFilters() {
    var categoryFilter = new URLSearchParams(window.location.search).get('category');
    var params = new URLSearchParams();
    if (categoryFilter) params.set('category', categoryFilter);
    window.location.href = 'items.php?' + params.toString();
}
