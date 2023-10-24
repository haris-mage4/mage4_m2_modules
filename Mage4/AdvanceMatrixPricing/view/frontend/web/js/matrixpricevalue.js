define([
    'ko',
    'papaParse',
    'jquery',
    'Magento_Catalog/js/price-utils',
    'Magento_Catalog/js/price-options',
    'Magento_Customer/js/customer-data'
], function (ko, papaparse, $, priceUtils, price_options, customerData) {
    'use strict';
    return function (config) {
        var media = $('#media-path').val();
        var labelWidth = '';
        var labelHeight = '';
        var widthClass = '';
        var heightClass = '';
        var labels = [];
        var productId = config.product_id;
        Object.entries($("label[for^='select_'] > span")).map(function (value) {
            value.map(function (valueText) {
                if (valueText.innerText !== undefined) {
                    labels.push(valueText.innerText);
                }
            });
        });

        labelWidth = labels.filter(e => e === "Width (In Inches)")[0];
        labelHeight = labels.filter(e => e === "Height (In Inches)")[0];

        widthClass = (labelWidth === "Width (In Inches)") ? '.width' : null;
        heightClass = (labelHeight === "Height (In Inches)") ? '.height' : null;

        $(document).ready(function (event) {
            var currenntPath = window.location.pathname;
            if (window.location.pathname.includes('checkout/cart/configure')) {
                var cartItems = customerData.get('cart')().items;
                if (cartItems && cartItems.length) {
                    cartItems.map(
                        function (cartItem) {
                            var explodedString = window.location.pathname.replace(/^\/|\/$/g, '').split('/');
                            if (explodedString[4] && explodedString[4] === cartItem.item_id) {
                                // $('#product-price-'+cartItem.product_id).html(parseFloat(cartItem.product_price_value).toFixed(2));
                                var pricE = parseFloat(cartItem.product_price_value).toFixed(2);
                                $('#product-price-' + cartItem.product_id).html('$' + pricE);
                                $('.price').html('$' + pricE);
                                $('#matrix_price').val(pricE);
                            }
                        }
                    );
                }
            }

            var selectedWidth = $(widthClass + ' > select :selected').attr('value');
            //   $(heightClass + ' > select :selected').prop("selected", false)
            var selectedHeight = $(heightClass + ' > select :selected').attr('value');
            var selectedWidthValue = (selectedWidth) ? $('option[value=' + selectedWidth + ']').text() : 0;
            var selectedHeightValue = (selectedHeight) ? $('option[value=' + selectedHeight + ']').text() : 0;
            // calculateMatrixPrice(selectedWidthValue, selectedHeightValue, event);
            $(widthClass + ' > select').change(function (event) {

                var selectedWidth = $(widthClass + ' > select :selected').attr('value');
                $(heightClass + ' > select :selected').prop("selected", false)
                var selectedHeight = $(heightClass + ' > select :selected').attr('value');
                var selectedWidthValue = (selectedWidth) ? $('option[value=' + selectedWidth + ']').text() : 0;
                var selectedHeightValue = (selectedHeight) ? $('option[value=' + selectedHeight + ']').text() : 0;
                calculateMatrixPrice(selectedWidthValue, selectedHeightValue, event);
            });
            $(heightClass + ' > select').change(function (event) {

                var selectedWidth = $(widthClass + ' > select :selected').attr('value');
                var selectedHeight = $(heightClass + ' > select :selected').attr('value');
                var selectedWidthValue = (selectedWidth) ? $('option[value=' + selectedWidth + ']').text() : 0;
                var selectedHeightValue = (selectedHeight) ? $('option[value=' + selectedHeight + ']').text() : 0;
                calculateMatrixPrice(selectedWidthValue, selectedHeightValue, event);
            });
        });

        function calculateMatrixPrice(width, height, event) {
            var optionName = $(event.target).prop('name');
            var sizes = {};
            papaparse.parse(media, {
                header: true,
                download: true,
                encoding: "default",
                error: function (error) {
                },
                complete: function (results) {
                    if (width === 0 || height === 0) {
                        //  $('#priceValue').html(0);
                        sizes[optionName] = {};
                        $("div[data-product-id^='" + productId + "'] > span").trigger('updatePrice', sizes)
                        return;
                    }
                    var multiDimension = results.data;

                    const selectedHeight = multiDimension.filter(matrix => matrix.dimensions === height);
                    Object.entries(selectedHeight[0]).map(function (price) {
                        const selectedWidth = price[0];
                        const selectedPrice = price[1];
                        if (selectedWidth === width) {
                            $('#matrix_price').val(selectedPrice);
                            sizes[optionName] = {
                                basePrice: {
                                    amount: selectedPrice,
                                    adjustments: {}
                                },
                                finalPrice: {
                                    amount: selectedPrice,
                                    adjustments: {}
                                },
                                oldPrice: {
                                    amount: selectedPrice,
                                    adjustments: {}
                                }
                            }
                            $("div[data-product-id^='" + productId + "'] > span").trigger('updatePrice', sizes)
                        }
                    });
                }
            });
        }
    }
});
