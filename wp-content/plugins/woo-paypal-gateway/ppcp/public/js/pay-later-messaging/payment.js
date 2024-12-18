jQuery(function ($) {
    if (typeof ppcp_pay_later_messaging === 'undefined') {
        return false;
    }
    var front_end_payment_page_pay_later_messaging_preview = function () {
        var payment_style_object = {};
        payment_style_object['layout'] = ppcp_pay_later_messaging.pay_later_messaging_payment_layout_type;
        if (payment_style_object['layout'] === 'text') {
            payment_style_object['logo'] = {};
            payment_style_object['logo']['type'] = ppcp_pay_later_messaging.pay_later_messaging_payment_text_layout_logo_type;
            if (payment_style_object['logo']['type'] === 'primary' || payment_style_object['logo']['type'] === 'alternative') {
                payment_style_object['logo']['position'] = ppcp_pay_later_messaging.pay_later_messaging_payment_text_layout_logo_position;
            }
            payment_style_object['text'] = {};
            payment_style_object['text']['size'] = parseInt(ppcp_pay_later_messaging.pay_later_messaging_payment_text_layout_text_size);
            payment_style_object['text']['color'] = ppcp_pay_later_messaging.pay_later_messaging_payment_text_layout_text_color;
        } else {
            payment_style_object['color'] = ppcp_pay_later_messaging.pay_later_messaging_payment_flex_layout_color;
            payment_style_object['ratio'] = ppcp_pay_later_messaging.pay_later_messaging_payment_flex_layout_ratio;
        }
        if (typeof paypal !== 'undefined' && ppcp_pay_later_messaging.amount > 0) {
            paypal.Messages({
                amount: ppcp_pay_later_messaging.amount,
                placement: 'payment',
                style: payment_style_object
            }).render('.ppcp_message_payment');
        }
    };
    $(document.body).on('updated_checkout', function () {
        front_end_payment_page_pay_later_messaging_preview();
    });
});