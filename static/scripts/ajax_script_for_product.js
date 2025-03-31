document.addEventListener('DOMContentLoaded', function() {
    const sizeOptions = document.querySelectorAll('.size-option');
    const selectedSizeInput = document.getElementById('selected-size');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const form = document.getElementById('add-to-cart-form');
    const popupOverlay = document.getElementById('popupOverlay');
    const popup = document.getElementById('popup');
    let itemAddedToCart = false;
    let isSubmitting = false; // Флаг для предотвращения повторной отправки
    // Функция для показа всплывающего сообщения
    function showPopup(message) {
        popup.querySelector('p').textContent = message;
        popup.classList.add('showpopup');
        popupOverlay.classList.add('showpopup');
    }

    // Функция для скрытия всплывающего сообщения
    function hidePopup() {
        popup.classList.remove('showpopup');
        popupOverlay.classList.remove('showpopup');
    }

    popupOverlay.addEventListener('click', hidePopup);

    // Обработчик клика по опциям размера
    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            sizeOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedSizeInput.value = this.getAttribute('data-size-id');
        });
    });

    // Обработчик отправки формы
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        if (itemAddedToCart) {
            window.location.href = 'cart.php';
            return;
        }

        if (!selectedSizeInput.value) {
            showPopup('Внимание! Вы не выбрали размер');
            return;
        }

        if (isSubmitting) return; // Проверяем, отправляется ли форма уже
        isSubmitting = true; // Устанавливаем флаг в true

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    addToCartBtn.textContent = 'Товар добавлен!';
                    addToCartBtn.style.backgroundColor = 'green';
                    itemAddedToCart = true;
                } else {
                    showPopup('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                showPopup('Ошибка: ' + error.message);
            })
            .finally(() => {
                isSubmitting = false; // Сбрасываем флаг после завершения запроса
            });
    });

    // Обработчик клика по кнопке добавления в корзину
    addToCartBtn.addEventListener('click', function(event) {
        if (itemAddedToCart) {
            event.preventDefault();
            window.location.href = 'cart.php';
        }
    });
});