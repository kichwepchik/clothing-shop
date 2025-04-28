function changeQuantity(button, delta) {
    var cartItem = button.closest('.cart-item');
    var quantityDisplay = cartItem.querySelector('.quantity-display');
    var quantity = parseInt(quantityDisplay.textContent, 10);
    var newQuantity = quantity + delta;

    if (newQuantity < 1) {
        newQuantity = 1;
    }

    quantityDisplay.textContent = newQuantity;

    var price = parseFloat(cartItem.querySelector('.cart-item-details p:nth-of-type(3)').textContent.replace('Цена: ', '').replace('₽', '').replace(/\s/g, ''));
    var itemTotalPriceElem = cartItem.querySelector('.item-total-price');
    var newTotalPrice = newQuantity * price;

    itemTotalPriceElem.textContent = newTotalPrice.toFixed(2);
    updateTotalPrice();

    updateQuantityInDatabase(cartItem.getAttribute('data-item-id'), newQuantity);
}

function updateQuantityInDatabase(itemId, quantity) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_cart_quantity.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status !== 200) {
            console.error('Ошибка при обновлении количества товара: ' + xhr.responseText);
        }
    };
    xhr.send('item_id=' + itemId + '&quantity=' + quantity);
}

function updateTotalPrice() {
    var totalElem = document.getElementById('total-price');
    var total = 0;
    var deliveryFee = 500; // фиксированная стоимость доставки

    document.querySelectorAll('.cart-item').forEach(function(cartItem) {
        var itemTotalPrice = parseFloat(cartItem.querySelector('.item-total-price').textContent.replace(/\s/g, ''));
        total += itemTotalPrice;
    });

    total += deliveryFee;
    totalElem.textContent = total.toFixed(2) ;
}