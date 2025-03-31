document.querySelectorAll('.size-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.size-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('selected-size').value = this.getAttribute('data-size-id');
    });
});


document.querySelectorAll('.carousel-image').forEach(image => {
    image.addEventListener('click', function() {
        const fullScreenImage = document.getElementById('fullScreenImage');
        const fullScreenImg = document.getElementById('fullScreenImg');
        fullScreenImg.src = this.src;
        fullScreenImage.style.display = 'flex';
    });
});

document.getElementById('closeFullScreen').addEventListener('click', function() {
    document.getElementById('fullScreenImage').style.display = 'none';
});


document.addEventListener('DOMContentLoaded', function() {
    const sizeOptions = document.querySelectorAll('.size-option');
    const selectedSizeInput = document.getElementById('selected-size');

    sizeOptions.forEach(option => {
        option.addEventListener('click', function() {
            sizeOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedSizeInput.value = this.getAttribute('data-size-id');
        });
    });
});


document.addEventListener('DOMContentLoaded', function() {
    const sizePopupOverlay = document.getElementById('sizePopupOverlay');
    const sizePopup = document.getElementById('sizePopup');
    const openSizePopup = document.getElementById('openSizePopup');
    const closeSizePopup = document.getElementById('closeSizePopup');

    openSizePopup.addEventListener('click', function(event) {
        event.preventDefault();
        sizePopupOverlay.style.display = 'block';
        sizePopup.style.display = 'block';
    });

    closeSizePopup.addEventListener('click', function() {
        sizePopupOverlay.style.display = 'none';
        sizePopup.style.display = 'none';
    });

    sizePopupOverlay.addEventListener('click', function() {
        sizePopupOverlay.style.display = 'none';
        sizePopup.style.display = 'none';
    });
});
