var { createElement } = wp.element;
var { registerPlugin } = wp.plugins;
var { ExperimentalOrderMeta } = wc.blocksCheckout;
var { registerExpressPaymentMethod, registerPaymentMethod } = wc.wcBlocksRegistry;
var { addAction } = wp.hooks;

(function (e) {
    var t = {};
    function n(o) {
        if (t[o]) return t[o].exports;
        var r = (t[o] = { i: o, l: !1, exports: {} });
        return e[o].call(r.exports, r, r.exports, n), (r.l = !0), r.exports;
    }
    n.m = e;
    n.c = t;
    n.d = function (e, t, o) {
        if (!n.o(e, t)) {
            Object.defineProperty(e, t, { enumerable: !0, get: o });
        }
    };
    n.r = function (e) {
        if (typeof Symbol !== "undefined" && Symbol.toStringTag) {
            Object.defineProperty(e, Symbol.toStringTag, { value: "Module" });
        }
        Object.defineProperty(e, "__esModule", { value: !0 });
    };
    n.t = function (e, t) {
        if (1 & t && (e = n(e)), 8 & t) return e;
        if (4 & t && typeof e === "object" && e && e.__esModule) return e;
        var o = Object.create(null);
        if (n.r(o), Object.defineProperty(o, "default", { enumerable: !0, value: e }), 2 & t && typeof e !== "string") {
            for (var r in e) n.d(o, r, function (t) { return e[t]; }.bind(null, r));
        }
        return o;
    };
    n.n = function (e) {
        var t = e && e.__esModule ? function () { return e.default; } : function () { return e; };
        return n.d(t, "a", t), t;
    };
    n.o = function (e, t) {
        return Object.prototype.hasOwnProperty.call(e, t);
    };
    n.p = "";
    n(n.s = 6);
})([
    function (e, t) {
        e.exports = window.wp.element;
    },
    function (e, t) {
        e.exports = window.wp.htmlEntities;
    },
    function (e, t) {
        e.exports = window.wp.i18n;
    },
    function (e, t) {
        e.exports = window.wc.wcSettings;
    },
    function (e, t) {
        e.exports = window.wc.wcBlocksRegistry;
    },
    ,
    function (e, t, n) {
        "use strict";
        n.r(t);
        var o = n(0),
            r = n(4),
            c = n(2),
            i = n(3),
            u = n(1);
    
        if (typeof wpg_paypal_checkout_manager_block === 'undefined') {
            return false;
        }
        const l = Object(i.getSetting)("wpg_paypal_checkout_data", {});
        const p = () => Object(u.decodeEntities)(l.description || "");
        const { useEffect } = wp.element;

        const Content_PPCP_Smart_Button_Checkout_Top = (props) => {
            const { billing, shippingData } = props;
            useEffect(() => {
                jQuery(document.body).trigger("ppcp_checkout_updated");
            }, []);
            return createElement("div", { id: "ppcp_checkout_top" });
        };

        const Content_PPCP_Smart_Button_Cart_Bottom = (props) => {
            const { billing, shippingData } = props;
            useEffect(() => {
                jQuery(document.body).trigger("ppcp_checkout_updated");
            }, []);
            return createElement("div", { id: "ppcp_cart" });
        };

        const ContentPPCPCheckout = (props) => {
            const { billing, shippingData } = props;
            return createElement(
                "div",
                { className: "ppcp_checkout_parent" },
                createElement("input", { type: "hidden", name: "form", value: 'checkout' }),
                createElement("div", { id: "ppcp_checkout" })
            );
        };
        const s = {
            name: "wpg_paypal_checkout",
            label: createElement("span",{style: { width: "100%" }},l.title,createElement("img", {src: l.icons,style: { float: "right", marginLeft: "20px" }})),
            placeOrderButtonLabel: Object(c.__)(wpg_paypal_checkout_manager_block.placeOrderButtonLabel),
            content: createElement(ContentPPCPCheckout, null),
            edit: Object(o.createElement)(p, null),
            canMakePayment: () => Promise.resolve(true),
            ariaLabel: Object(u.decodeEntities)(l.title || Object(c.__)("Payment via PayPal", "woo-gutenberg-products-block")),
            supports: {
                features: l.supports || [],
                showSavedCards: false,
                showSaveOption: false
            }
        };
        Object(r.registerPaymentMethod)(s);

        const ppcp_settings = wpg_paypal_checkout_manager_block.settins;
        const { is_order_confirm_page, is_paylater_enable_incart_page, page } = wpg_paypal_checkout_manager_block;

        if (page === "checkout" && is_order_confirm_page === "no" && ppcp_settings && ppcp_settings.enable_checkout_button_top === "yes") {
            const commonExpressPaymentMethodConfig = {
                name: "wpg_paypal_checkout_top",
                label: Object(u.decodeEntities)(l.title || Object(c.__)("Payment via PayPal", "woo-gutenberg-products-block")),
                content: createElement(Content_PPCP_Smart_Button_Checkout_Top, null),
                edit: Object(o.createElement)(p, null),
                ariaLabel: Object(u.decodeEntities)(l.title || Object(c.__)("Payment via PayPal", "woo-gutenberg-products-block")),
                canMakePayment: () => true,
                paymentMethodId: "wpg_paypal_checkout",
                supports: { features: l.supports || [] }
            };
            Object(r.registerExpressPaymentMethod)(commonExpressPaymentMethodConfig);
        } else if (page === "cart" && ppcp_settings && ppcp_settings.show_on_cart === "yes") {
            const commonExpressPaymentMethodConfig = {
                name: "wpg_paypal_checkout_top",
                label: Object(u.decodeEntities)(l.title || Object(c.__)("Payment via PayPal", "woo-gutenberg-products-block")),
                content: createElement(Content_PPCP_Smart_Button_Cart_Bottom, null),
                edit: Object(o.createElement)(p, null),
                ariaLabel: Object(u.decodeEntities)(l.title || Object(c.__)("Payment via PayPal", "woo-gutenberg-products-block")),
                canMakePayment: () => true,
                paymentMethodId: "wpg_paypal_checkout",
                supports: { features: l.supports || [] }
            };
            Object(r.registerExpressPaymentMethod)(commonExpressPaymentMethodConfig);
        }
    }
]);

document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        jQuery(document.body).trigger("ppcp_block_ready");
    }, 3);
});

const ppcp_uniqueEvents = new Set([
    "experimental__woocommerce_blocks-checkout-set-shipping-address",
    "experimental__woocommerce_blocks-checkout-set-billing-address",
    "experimental__woocommerce_blocks-checkout-set-email-address",
    "experimental__woocommerce_blocks-checkout-render-checkout-form",
    "experimental__woocommerce_blocks-checkout-set-active-payment-method",
]);

ppcp_uniqueEvents.forEach(function (action) {
    addAction(action, "c", function () {
        setTimeout(function () {
            jQuery(document.body).trigger("ppcp_checkout_updated");
        }, 3);
    });
});

function showErrorUsingShowNotice(error_message) {
    wp.data.dispatch('core/notices').createNotice(
        'error',
        error_message,
        {
            isDismissible: true,
            context: 'wc/checkout'
        }
    );
}

jQuery(document.body).on('ppcp_checkout_error', function (event, errorMessages) {
    showErrorUsingShowNotice(errorMessages);
});