define(function() {
    'use strict';

    return function(config, _) {
        const productData = {
            size: '',
            color: '',
        }

        console.log('config - ', config);

        window._faslet = {
            id: config.productId,
            shopUrl: config.shopUrl,
            sku: config.productSku,
            variants: config.productVariants,

            addToCart(_) {
                const optionSize = document.querySelector(`[data-option-label="${productData.size}"]`);
                const optionColor = document.querySelector(`[data-option-label="${productData.color}"]`);
                const qty = document.querySelector('input.qty');
                const addToCart = document.querySelector('button.tocart');

                optionSize.click();
                optionColor.click();
                qty.value = 1;
                addToCart.click();
            },

            onResult(result, _) {
                productData.size = result.label;
            },

            onColorSelected(colorId) {
                productData.color = colorId;
            },
        }
    }
});
