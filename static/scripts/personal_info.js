document.addEventListener("DOMContentLoaded", function() {
    fetch("get_personal_info.php")
        .then(response => response.json())
        .then(data => {
            if (data) {
                document.getElementById("first_name").value = data.first_name || '';
                document.getElementById("last_name").value = data.last_name || '';
                document.getElementById("phone").value = data.phone_number || '';
                document.getElementById("email").value = data.email || '';
                document.getElementById("address").value = data.adres || '';
            }
        });

    const form = document.getElementById("personal-info-form");
    const inputs = form.querySelectorAll("input");

    inputs.forEach(input => {
        input.addEventListener("input", function() {
            const formData = new FormData(form);
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            fetch("update_personal_info.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(jsonData)
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert("Ошибка при сохранении данных");
                    }
                });
        });
    });
});
