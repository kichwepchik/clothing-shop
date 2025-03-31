function addToFavorites(productId, element) {
    $.ajax({
        url: 'add_to_favorites.php',
        method: 'POST',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'added') {
                // Меняем иконку на заполненное сердце
                $(element).removeClass('fi-br-heart').addClass('fi-sr-heart');
            } else if (response.status === 'removed') {
                // Меняем иконку на пустое сердце
                $(element).removeClass('fi-sr-heart').addClass('fi-br-heart');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown); // Отладка
        }
    });
}
