class BraintreeIntegration {
    constructor(params) {
        this.clientToken = params.clientToken;
        this.selector = params.selector || '#wpg_dropin-container';
        this.paymentMethodFieldName = 'payment_method_nonce';
        this.paymentMethod = 'braintree';
        this.dropinInstance = null;
        this.formSelector = 'form.checkout';
        this.initialize();
        this.attachCheckoutUpdateHandler();
    }

    /**
     * Initialize the Braintree Drop-in and form submission handling.
     */
    initialize() {
        console.log('[Braintree] Initializing Braintree Drop-in...');
        this.createDropin();
        this.attachFormSubmitHandler();
    }

    /**
     * Handle WooCommerce checkout updates.
     */
    attachCheckoutUpdateHandler() {
        jQuery(document.body).on('init_checkout updated_checkout wpg_braintree_block_ready', () => {
            console.log('[Braintree] Checkout update detected. Reinitializing Drop-in...');
            this.createDropin();
        });
    }

    /**
     * Create the Braintree Drop-in.
     */
    createDropin() {
        if (this.dropinInstance) {
            console.log('[Braintree] Drop-in already exists. Reinitializing...');
            this.dropinInstance.teardown(() => {
                console.log('[Braintree] Previous drop-in instance removed.');
                this.initializeDropin();
            });
        } else {
            this.initializeDropin();
        }
    }

    /**
     * Initialize the Braintree Drop-in UI.
     */
    initializeDropin() {
        braintree.dropin.create(
            {
                authorization: this.clientToken,
                container: this.selector,
            },
            (err, instance) => {
                if (err) {
                    console.error('[Braintree] Drop-in creation error:', err);
                    return;
                }
                console.log('[Braintree] Drop-in initialized successfully.');
                this.dropinInstance = instance;

                this.dropinInstance.on('paymentMethodRequestable', () => {
                    console.log('[Braintree] Payment method is requestable.');
                    jQuery('#place_order').removeAttr('disabled');
                });

                this.dropinInstance.on('noPaymentMethodRequestable', () => {
                    console.log('[Braintree] No payment method is requestable.');
                    jQuery('#place_order').attr('disabled', true);
                });
            }
        );
    }

    /**
     * Attach a handler to intercept the form submission for Braintree payments.
     */
    attachFormSubmitHandler() {
        jQuery(this.formSelector).on('submit', (e) => {
            const selectedPaymentMethod = jQuery('input[name="payment_method"]:checked').val();

            if (selectedPaymentMethod !== this.paymentMethod) {
                console.log('[Braintree] Non-Braintree payment method selected. Form submission allowed.');
                return true;
            }

            console.log('[Braintree] Braintree payment method selected.');
            e.preventDefault(); // Prevent default WooCommerce submission
            this.handleFormSubmission();
        });
    }

    /**
     * Handle the form submission for Braintree payments.
     */
    handleFormSubmission() {
        if (!this.dropinInstance) {
            console.error('[Braintree] Drop-in is not initialized.');
            return;
        }

        console.log('[Braintree] Requesting payment method nonce...');
        this.dropinInstance.requestPaymentMethod((err, payload) => {
            if (err) {
                console.error('[Braintree] Error retrieving payment method:', err);
                return;
            }

            if (payload.nonce) {
                console.log('[Braintree] Payment method nonce retrieved:', payload.nonce);
                this.addNonceToForm(payload.nonce);
                this.submitForm();
            } else {
                console.error('[Braintree] No nonce retrieved. Form submission blocked.');
            }
        });
    }

    /**
     * Add the payment method nonce to the form as a hidden input.
     * @param {string} nonce - The Braintree payment method nonce.
     */
    addNonceToForm(nonce) {
        jQuery(this.formSelector).find(`input[name="${this.paymentMethodFieldName}"]`).remove();
        jQuery('<input>')
            .attr({
                type: 'hidden',
                name: this.paymentMethodFieldName,
                value: nonce,
            })
            .appendTo(this.formSelector);
        console.log('[Braintree] Nonce added to the form.');
    }

    /**
     * Submit the WooCommerce checkout form.
     */
    submitForm() {
        console.log('[Braintree] Submitting WooCommerce form.');
        jQuery(this.formSelector).off('submit').submit();
    }
}

// Initialize the Braintree integration when the document is ready
jQuery(document).ready(() => {
    const braintreeParams = {
        clientToken: braintree_params.clientToken,
        selector: '#wpg_dropin-container',
    };

    new BraintreeIntegration(braintreeParams);
});
