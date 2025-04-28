document.addEventListener('DOMContentLoaded', function() {
    var paymentLink = document.getElementById('payment-link');
    var popup = document.getElementById('popup');
    var accountNumber = document.getElementById('account-number');
    var copyNotification = document.getElementById('copy-notification');

    paymentLink.addEventListener('click', function(event) {
        event.preventDefault();
        popup.style.display = 'block';
    });

    document.addEventListener('click', function(event) {
        if (!popup.contains(event.target) && event.target !== paymentLink) {
            popup.style.display = 'none';
        }
    });

    accountNumber.addEventListener('click', function() {
        var range = document.createRange();
        range.selectNode(accountNumber);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);

        try {
            document.execCommand('copy');
            copyNotification.style.display = 'block';
            setTimeout(function() {
                copyNotification.style.display = 'none';
            }, 2000);
        } catch (err) {
            console.error('Ошибка копирования', err);
        }

        window.getSelection().removeAllRanges();
    });
});
