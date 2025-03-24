document.addEventListener('DOMContentLoaded', function () {
    const dropdownButtons = document.querySelectorAll('.dropdown-button');
    let openDropdown = null;

    dropdownButtons.forEach(button => {
        button.addEventListener('click', function () {
            const dropdown = this.closest('.dropdown');
            const dropdownContent = dropdown.querySelector('.dropdown-content');

            if (openDropdown && openDropdown !== dropdownContent) {
                closeDropdown(openDropdown);
            }

            if (dropdownContent.classList.contains('open')) {
                closeDropdown(dropdownContent);
                openDropdown = null;
            } else {
                openDropdown = dropdownContent;
                openDropdown.style.display = 'block';
                setTimeout(() => {
                    openDropdown.classList.add('open');
                }, 10);
            }
        });
    });

    document.addEventListener('click', function (event) {
        // Check if the click is outside of both dropdown and modal
        if (!event.target.closest('.dropdown') && !event.target.closest('.modal')) {
            if (openDropdown) {
                closeDropdown(openDropdown);
                openDropdown = null;
            }
        }
    });

    function closeDropdown(dropdownContent) {
        dropdownContent.classList.remove('open');
        dropdownContent.classList.add('closing');
        dropdownContent.addEventListener('animationend', function () {
            dropdownContent.classList.remove('closing');
            dropdownContent.style.display = 'none';
        }, { once: true });
    }
});
