document.addEventListener('DOMContentLoaded', function() {
    // Обработчик отправки формы
    document.getElementById('personal-info-form').addEventListener('submit', function(event) {
        let isValid = true;
        const requiredFields = ['first_name', 'last_name', 'phone_number', 'email', 'adres'];

        requiredFields.forEach(function(field) {
            let input = document.getElementById(field);
            let errorMessage = input.nextElementSibling;

            if (input.value.trim() === '') {
                isValid = false;
                input.classList.add('error');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.classList.add('error-message');
                    errorMessage.textContent = 'Вы не заполнили поле!';
                    input.parentNode.appendChild(errorMessage);
                }
                errorMessage.style.display = 'block';
            } else {
                input.classList.remove('error');
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }
            }
        });

        if (!isValid) {
            event.preventDefault();
        }
    });

    // Обработчик клика для сворачивания клавиатуры
    document.addEventListener('click', function(event) {
        // Проверяем, был ли клик по полю ввода
        if (!event.target.closest('.input-container')) {
            // Если клик был вне поля ввода, сворачиваем клавиатуру (если она открыта)
            document.activeElement.blur(); // Сворачиваем клавиатуру
        }
    });

    // Обработчики для перехода по Enter
    const inputs = document.querySelectorAll('#personal-info-form input');
    inputs.forEach((input, index) => {
        input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Предотвращаем стандартное поведение Enter
                // Переходим к следующему полю ввода
                const nextIndex = index + 1;
                if (nextIndex < inputs.length) {
                    inputs[nextIndex].focus(); // Переводим фокус
                } else {
                    // Если это последнее поле, отправляем форму
                    document.getElementById('personal-info-form').submit();
                }
            }
        });
    });
});
