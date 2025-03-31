// Инициализация Telegram Web App
const tg = window.Telegram.WebApp;

// Отображение кнопки "Назад"
tg.BackButton.show();

// Обработка нажатия на кнопку "Назад"
tg.onEvent('backButtonClicked', function() {
    // Здесь можно добавить логику, выполняемую при нажатии на кнопку "Назад"
    window.history.back();
});

// Дополнительная логика инициализации, если необходимо
tg.ready();