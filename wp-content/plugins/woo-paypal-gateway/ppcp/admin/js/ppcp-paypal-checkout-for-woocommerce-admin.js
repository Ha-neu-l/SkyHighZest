window.onboardingCallback = function (authCode, sharedId) {
    const is_sandbox = document.querySelector('#woocommerce_wpg_paypal_checkout_sandbox');
    window.onbeforeunload = '';
    jQuery('#wpbody').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
    fetch(ppcp_param.wpg_onboarding_endpoint, {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify({
            authCode: authCode,
            sharedId: sharedId,
            nonce: ppcp_param.wpg_onboarding_endpoint_nonce,
            env: is_sandbox && is_sandbox.value === 'yes' ? 'sandbox' : 'production'
        })
    });
};
(function ($) {
    'use strict';
    $(function () {
        var ppcp_production_fields = $('#woocommerce_wpg_paypal_checkout_rest_client_id_live, #woocommerce_wpg_paypal_checkout_rest_secret_id_live').closest('tr');
        var ppcp_sandbox_fields = $('#woocommerce_wpg_paypal_checkout_rest_client_id_sandbox, #woocommerce_wpg_paypal_checkout_rest_secret_id_sandbox').closest('tr');
        $('#woocommerce_wpg_paypal_checkout_sandbox').change(function () {
            ppcp_production_fields.hide();
            ppcp_sandbox_fields.hide();
            $('#woocommerce_wpg_paypal_checkout_sandbox_disconnect').closest('tr').hide();
            $('#woocommerce_wpg_paypal_checkout_live_disconnect').closest('tr').hide();
            $('#wpg_guide').hide();
            if ($(this).val() === 'yes') {
                $('#woocommerce_wpg_paypal_checkout_live_onboarding').closest('tr').hide();
                if (ppcp_param.is_sandbox_connected === 'yes') {
                    $('#woocommerce_wpg_paypal_checkout_sandbox_onboarding').closest('tr').hide();
                    $('#woocommerce_wpg_paypal_checkout_sandbox_disconnect').closest('tr').show();
                } else {
                    $('#woocommerce_wpg_paypal_checkout_sandbox_onboarding').closest('tr').show();
                    $('#woocommerce_wpg_paypal_checkout_sandbox_disconnect').closest('tr').hide();
                }
            } else {
                $('#woocommerce_wpg_paypal_checkout_sandbox_onboarding').closest('tr').hide();
                if (ppcp_param.is_live_connected === 'yes') {
                    $('#woocommerce_wpg_paypal_checkout_live_disconnect').closest('tr').show();
                    $('#woocommerce_wpg_paypal_checkout_live_onboarding').closest('tr').hide();
                } else {
                    $('#woocommerce_wpg_paypal_checkout_live_onboarding').closest('tr').show();
                    $('#woocommerce_wpg_paypal_checkout_live_disconnect').closest('tr').hide();
                }
            }
        }).change();

        $(".wpg_paypal_checkout_gateway_manual_credential_input").on('click', function (e) {
            e.preventDefault();
            if ($('#woocommerce_wpg_paypal_checkout_sandbox').val() === 'yes') {
                ppcp_sandbox_fields.toggle();
                $('#wpg_guide').toggle();
                $('#woocommerce_paypal_smart_checkout_sandbox_api_credentials, #woocommerce_paypal_smart_checkout_sandbox_api_credentials + p').toggle();
            } else {
                ppcp_production_fields.toggle();
                $('#wpg_guide').toggle();
                $('#woocommerce_paypal_smart_checkout_api_credentials, #woocommerce_paypal_smart_checkout_api_credentials + p').toggle();
            }
        });

        $(".button.wpg-ppcp-disconnect").click(function () {
            $(".woocommerce-save-button").prop("disabled", false);
            if ($('#woocommerce_wpg_paypal_checkout_sandbox').val() === 'yes') {
                $('#woocommerce_wpg_paypal_checkout_rest_client_id_sandbox').val('');
                $('#woocommerce_wpg_paypal_checkout_rest_secret_id_sandbox').val('');
            } else {
                $('#woocommerce_wpg_paypal_checkout_rest_client_id_live').val('');
                $('#woocommerce_wpg_paypal_checkout_rest_secret_id_live').val('');
            }
            $('.woocommerce-save-button').click();
        });

        $('#woocommerce_wpg_paypal_checkout_show_on_product_page').change(function () {
            if ($(this).is(':checked')) {
                $('.wpg_paypal_checkout_product_button_settings, .ppcp_product_button_settings').closest('tr').show();
            } else {
                $('.wpg_paypal_checkout_product_button_settings, .ppcp_product_button_settings').closest('tr').hide();
            }
        }).change();
        $('#woocommerce_wpg_paypal_checkout_show_on_cart').change(function () {
            if ($(this).is(':checked')) {
                $('.wpg_paypal_checkout_cart_button_settings, .ppcp_cart_button_settings').closest('tr').show();
            } else {
                $('.wpg_paypal_checkout_cart_button_settings, .ppcp_cart_button_settings').closest('tr').hide();
            }
        }).change();
        $('#woocommerce_wpg_paypal_checkout_show_on_checkout_page').change(function () {
            if ($(this).is(':checked')) {
                $('.wpg_paypal_checkout_checkout_button_settings, .ppcp_checkout_button_settings').closest('tr').show();
            } else {
                $('.wpg_paypal_checkout_checkout_button_settings, .ppcp_checkout_button_settings').closest('tr').hide();
            }
        }).change();
        $('#woocommerce_wpg_paypal_checkout_enable_checkout_button_top').change(function () {
            if ($(this).is(':checked')) {
                $('.wpg_paypal_checkout_checkout_button_settings, .ppcp_express_checkout_button_settings').closest('tr').show();
            } else {
                $('.wpg_paypal_checkout_checkout_button_settings, .ppcp_express_checkout_button_settings').closest('tr').hide();
            }
        }).change();
        $('#woocommerce_wpg_paypal_checkout_show_on_mini_cart').change(function () {
            if ($(this).is(':checked')) {
                $('.wpg_paypal_checkout_mini_cart_button_settings, .ppcp_mini_cart_button_settings').closest('tr').show();
            } else {
                $('.wpg_paypal_checkout_mini_cart_button_settings, .ppcp_mini_cart_button_settings').closest('tr').hide();
            }
        }).change();
        jQuery('#woocommerce_wpg_paypal_checkout_enable_advanced_card_payments').change(function () {
            if (jQuery(this).is(':checked')) {
                jQuery('#woocommerce_wpg_paypal_checkout_3d_secure_contingency, #woocommerce_wpg_paypal_checkout_disable_cards, #woocommerce_wpg_paypal_checkout_advanced_card_payments_title').closest('tr').show();
            } else {
                jQuery('#woocommerce_wpg_paypal_checkout_3d_secure_contingency, #woocommerce_wpg_paypal_checkout_disable_cards, #woocommerce_wpg_paypal_checkout_advanced_card_payments_title').closest('tr').hide();
            }
        }).change();


        // Define page types
        const pageTypes = ['home', 'category', 'product', 'cart', 'payment'];

        // Helper function to toggle visibility
        const toggleVisibility = (selector, condition) => {
            const element = $(selector);
            if (condition) {
                element.closest('tr').show(); // Show the closest table row
                element.closest('tr').closet('table').show();
                element.show(); // Ensure the element itself is visible
            } else {
                element.closest('tr').closet('table').hide(); // Hide the closest table row
                element.hide(); // Ensure the element itself is hidden
            }
        };

        // Check if messaging is enabled
        const isMessagingEnabled = () => $('#woocommerce_wpg_paypal_checkout_enabled_pay_later_messaging').is(':checked');

        // Check if the current page type is enabled
        const isPageEnabled = (pageType) => {
            const selectedPages = $('#woocommerce_wpg_paypal_checkout_pay_later_messaging_page_type').val() || [];
            return isMessagingEnabled() && selectedPages.includes(pageType);
        };

        // Hide all Pay Later fields
        const hideAllPayLaterFields = () => {
            $('.pay_later_messaging_field').closest('tr').hide(); // Hide all generic fields
            pageTypes.forEach((type) => {
                $(`.pay_later_messaging_${type}_field`).closest('tr').hide();
                $(`.pay_later_messaging_${type}_field`).closest('tr').closest('table').hide();
                $(`#woocommerce_wpg_paypal_checkout_pay_later_messaging_${type}_page_settings`).hide(); // Hide headers
            });
        };

        // Update visibility of page-specific fields and headers
        const updatePageTypeVisibility = () => {
            const selectedPages = $('#woocommerce_wpg_paypal_checkout_pay_later_messaging_page_type').val() || [];
            pageTypes.forEach((type) => {
                const pageFieldSelector = `.pay_later_messaging_${type}_field`;
                const pageSettingSelector = `#woocommerce_wpg_paypal_checkout_pay_later_messaging_${type}_page_settings`;

                if (selectedPages.includes(type) && isMessagingEnabled()) {
                    $(pageFieldSelector).closest('tr').show(); // Show the row containing the field
                    $(pageFieldSelector).closest('tr').closest('table').show();
                    $(pageSettingSelector).show(); // Show the header
                } else {
                    $(pageFieldSelector).closest('tr').hide(); // Hide the row containing the field
                    $(pageFieldSelector).closest('tr').closest('table').hide();
                    $(pageSettingSelector).hide(); // Hide the header
                }
            });
        };

        // Event listeners for Pay Later Messaging
        const initializePayLaterMessaging = () => {
            $('#woocommerce_wpg_paypal_checkout_enabled_pay_later_messaging').change(function () {
                if ($(this).is(':checked')) {
                    $('.pay_later_messaging_field').closest('tr').show(); // Show "Page Type" field
                    $('.pay_later_messaging_field').closest('tr').closest('table').show();
                    updatePageTypeVisibility();
                } else {
                    hideAllPayLaterFields();
                }
            });

            $('#woocommerce_wpg_paypal_checkout_pay_later_messaging_page_type').change(function () {
                updatePageTypeVisibility();
            });

            // Initial visibility setup
            if ($('#woocommerce_wpg_paypal_checkout_enabled_pay_later_messaging').is(':checked')) {
                $('.pay_later_messaging_field').closest('tr').show(); // Show "Page Type" field
                $('.pay_later_messaging_field').closest('tr').closest('table').show();
                updatePageTypeVisibility();
            } else {
                hideAllPayLaterFields();
            }
        };

        // Collapsible sections functionality
        const initializeCollapsibleSections = () => {
            // Collapse all sections initially
            $('h3.ppcp-collapsible-section').each(function () {
                $(this).nextUntil('h3.ppcp-collapsible-section').hide();
            });

            // Make the first section active by default
            const firstSection = $('h3.ppcp-collapsible-section').first();
            firstSection.addClass('active');
            firstSection.nextUntil('h3.ppcp-collapsible-section').show();

            // Toggle sections on header click
            $('h3.ppcp-collapsible-section').on('click', function () {
                if (!$(this).hasClass('active')) {
                    // Collapse other sections
                    $('h3.ppcp-collapsible-section')
                            .removeClass('active')
                            .nextUntil('h3.ppcp-collapsible-section')
                            .slideUp(1);
                }
                //$(this).toggleClass('active').next('table').css('display', 'block').find('tr').css('display', 'block');
                //$(this).toggleClass('active').nextUntil('h3.ppcp-collapsible-section').slideToggle(1);

                $(this).toggleClass('active').next('table').slideToggle(1).find('tr').css('display', 'block');

            });
        };


        $('table').each(function () {
            if ($(this).find('tbody').length === 0) {
                $(this).hide(); // Hide the table if no <tbody> is found
            }
        });

        // Initialize all functionality on document ready
        $(document).ready(function () {
            initializePayLaterMessaging();
            initializeCollapsibleSections();
        });


    });
})(jQuery);
