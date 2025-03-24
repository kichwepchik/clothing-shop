document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', () => {
        const url = card.getAttribute('data-url');
        window.location.href = url;
    });
});
