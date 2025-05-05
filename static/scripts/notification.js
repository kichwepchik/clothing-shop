document.addEventListener('DOMContentLoaded', function() {
    const sizeOptions = document.querySelectorAll('.size-option');
    const selectedSizeInput = document.getElementById('selected-size');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const form = document.getElementById('add-to-cart-form');
    const popupOverlay = document.getElementById('popupOverlay');
    const popup = document.getElementById('popup');

    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            sizeOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedSizeInput.value = this.getAttribute('data-size-id');
        });
    });

    function showPopup(message) {
        popup.querySelector('p').textContent = message;
        popup.classList.add('show');
        popupOverlay.classList.add('show');
    }

    function hidePopup() {
        popup.classList.remove('show');
        popupOverlay.classList.remove('show');
    }

    popupOverlay.addEventListener('click', hidePopup);

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        if (!selectedSizeInput.value) {
            showPopup('!Внимание! Вы не выбрали размер');
        } else {
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        addToCartBtn.textContent = 'Товар добавлен!';
                        addToCartBtn.style.backgroundColor = 'green';
                    } else {
                        showPopup('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    showPopup('Ошибка: ' + error.message);
                });
        }
    });
});