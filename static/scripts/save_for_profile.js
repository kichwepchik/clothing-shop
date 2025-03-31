document.addEventListener('DOMContentLoaded', () => {
    const saveButton = document.getElementById('save-button');
    const form = document.getElementById('personal-info-form');

    function validateForm() {
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

        return isValid;
    }

    saveButton.addEventListener('click', (event) => {
        event.preventDefault();

        if (!validateForm()) {
            return;
        }

        saveButton.style.transform = 'scale(1.1)';
        saveButton.style.backgroundColor = '#00FF00';
        setTimeout(() => {
            saveButton.style.transform = '';
            saveButton.style.backgroundColor = '#FF0000';
            form.submit();  // Отправляем форму вручную после анимации
        }, 300);
    });

    // Закрытие клавиатуры при нажатии на любую часть экрана
    document.addEventListener('click', function (event) {
        if (!form.contains(event.target)) {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.blur) {
                activeElement.blur();
            }
        }
    });

    // Перемещение по строкам формы при нажатии Enter и закрытие клавиатуры на последнем поле
    const inputs = document.querySelectorAll('#personal-info-form input');
    inputs.forEach((input, index) => {
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Предотвращаем отправку формы при нажатии Enter
                const nextInput = inputs[index + 1];
                if (nextInput) {
                    nextInput.focus();
                } else {
                    input.blur(); // Закрыть клавиатуру если это последнее поле
                }
            }
        });
    });
});
