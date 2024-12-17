function klikenFbGetCartItems() {
    jQuery.post(
        kkfbFetchCartItems.ajax_url,
        {
            action: kkfbFetchCartItems.action,
            _ajax_nonce: kkfbFetchCartItems.nonce
        },
        function (data) {
            if (typeof sw !== 'object') return;

            // If no data is available, exit method.
            if (!data?.cart?.items) return;

            let itemsForGTAG = [];
            let swCart = [];

            sw.config.currency = kkfbFetchCartItems.currency;

            for (let item in data.cart.items) {
                itemsForGTAG.push({
                    "id": `${item.id}`,
                    "name": `${item.name}`,
                    "price": `${item.price}`,
                    "quantity": `${item.quantity}`,
                    "google_business_vertical": "retail"
                });

                swCart.push({
                    "id": `${item.id}`,
                    "name": `${item.name}`,
                    "price": `${item.price}`,
                    "quantity": `${item.quantity}`,
                    "currency": sw.config.currency
                });
            }

            sw.track(
                "AddToCart",
                data.event.custom_data,
                data.event.event_id
            );

            sw.gEvent(
                "add_to_cart",
                {
                    "items": itemsForGTAG
                }
            );

            sw.register_shopcart(
            {
                "items": swCart
            });
        }
    );
}

jQuery(document).ready(function () {
    // Listen to those events.
    // 1. added_to_cart: A product is added to cart via AJAX.
    // 2. updated_cart_totals: Cart info changed, such as product quantity changed, or items being removed (not emptied).
    jQuery('body').on('added_to_cart updated_cart_totals', function () {
        klikenFbGetCartItems();
    });
});
