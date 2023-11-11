jQuery(function ($) {
    // Function to handle the cart update.
    function update_cart() {
        $.ajax({
            type: 'POST',
            url: wine_shop_minimum_order_ajax_object.ajax_url,
            data: {
                action: 'wine_shop_minimum_order_update_cart',
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('.woocommerce-error, .woocommerce-message').remove();
                    $('.woocommerce').prepend('<div class="woocommerce-message" role="alert">' + response.message + '</div>');
                } else {
                    $('.woocommerce-error, .woocommerce-message').remove();
                    $('.woocommerce').prepend('<div class="woocommerce-error" role="alert">' + response.message + '</div>');
                }
            },
        });
    }

    // Update the cart when the quantity is changed.
    $('body').on('change', 'input.qty', function () {
        update_cart();
    });

    // Update the cart when an item is removed.
    $('body').on('click', 'a.remove', function () {
        $(document.body).on('wc_fragments_refreshed', function () {
            update_cart();
        });
    });
});
