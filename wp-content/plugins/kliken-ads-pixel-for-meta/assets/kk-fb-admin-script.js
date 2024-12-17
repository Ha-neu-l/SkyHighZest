jQuery(document).ready(function() {
    // Only care about those notice messages with "data-kk-fb-message-name" attribute, and "is-dimissible" class
    jQuery(".wrap").on("click", "div[data-kk-fb-message-name].is-dismissible button.notice-dismiss", function(event) {
        event.preventDefault();
        event.stopPropagation();

        if (confirm(kkfbDimissibleNotice.confirmMessage)) {
            jQuery.post( ajaxurl, {
                action: kkfbDimissibleNotice.action,
                message_name: jQuery(this).parent().attr('data-kk-fb-message-name'),
                _ajax_nonce: kkfbDimissibleNotice.nonce
            })
        }
    });
});
