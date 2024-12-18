var { createElement } = wp.element;
var { registerPlugin } = wp.plugins;
var { ExperimentalOrderMeta } = wc.blocksCheckout;
var { registerExpressPaymentMethod, registerPaymentMethod } = wc.wcBlocksRegistry;

(function (e) {
    var t = {};

    function n(o) {
        if (t[o]) return t[o].exports;
        var r = (t[o] = {
            i: o,
            l: !1,
            exports: {},
        });
        return e[o].call(r.exports, r, r.exports, n), (r.l = !0), r.exports;
    }

    n.m = e;
    n.c = t;
    n.d = function (e, t, o) {
        if (!n.o(e, t)) {
            Object.defineProperty(e, t, {
                enumerable: !0,
                get: o,
            });
        }
    };
    n.r = function (e) {
        if (typeof Symbol !== "undefined" && Symbol.toStringTag) {
            Object.defineProperty(e, Symbol.toStringTag, {
                value: "Module",
            });
        }
        Object.defineProperty(e, "__esModule", {
            value: !0,
        });
    };
    n.t = function (e, t) {
        if (1 & t && (e = n(e)), 8 & t) return e;
        if (4 & t && typeof e === "object" && e && e.__esModule) return e;
        var o = Object.create(null);
        if (n.r(o), Object.defineProperty(o, "default", { enumerable: !0, value: e }), 2 & t && typeof e !== "string") {
            for (var r in e) {
                n.d(o, r, function (t) {
                    return e[t];
                }.bind(null, r));
            }
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
        var o, r = n(0), c = n(4), i = n(2), u = n(3), a = n(1);
        if (typeof wpg_paypal_checkout_cc_manager_block === 'undefined') {
            return false;
        }
        const l = Object(u.getSetting)("wpg_paypal_checkout_cc_data", {});
        const iconsElements = l.icons.map(icon => (
                            createElement("img", {src: icon, style: {float: "right", marginRight: "3px"}})
                            ));
        const p = () => Object(a.decodeEntities)(l.description || "");
        const { is_order_confirm_page, is_paylater_enable_incart_page, page } = wpg_paypal_checkout_cc_manager_block;
        const { useEffect } = window.wp.element;

        const Content_PPCP_CC = (props) => {
            const { eventRegistration, emitResponse, onSubmit, billing, shippingData } = props;
            const { onPaymentSetup } = eventRegistration;
            useEffect(() => {
                jQuery(document.body).trigger('ppcp_cc_block_ready');
                const unsubscribe = onPaymentSetup(async () => {
                    jQuery(document.body).trigger('submit_paypal_cc_form');
                    jQuery('.wc-block-components-checkout-place-order-button').append('<span class="wc-block-components-spinner" aria-hidden="true"></span>');
                    jQuery('.wc-block-components-checkout-place-order-button, .wp-block-woocommerce-checkout-fields-block #contact-fields, .wp-block-woocommerce-checkout-fields-block #billing-fields, .wp-block-woocommerce-checkout-fields-block #payment-method').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
                });
            }, [onPaymentSetup]);

            return createElement(
                "div",
                { id: "wc-wpg_paypal_checkout_cc-form", className: "wc-credit-card-form wc-payment-form" },
                createElement("div", { id: "wpg_paypal_checkout_cc-card-number" }),
                createElement("div", { id: "wpg_paypal_checkout_cc-card-expiry" }),
                createElement("div", { id: "wpg_paypal_checkout_cc-card-cvc" })
            );
        };
        const s = {
            name: "wpg_paypal_checkout_cc",label: createElement("span",{style: {width: "100%"}},l.cc_title,iconsElements),icons: ["https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png"],
            content: createElement(Content_PPCP_CC, null),
            edit: Object(r.createElement)(p, null),
            canMakePayment: () => Promise.resolve(true),
            ariaLabel: Object(a.decodeEntities)(l.cc_title || Object(i.__)("Payment via PayPal", "woo-gutenberg-products-block")),
            supports: {
                features: o != null ? o : l.supports,
                showSavedCards: false,
                showSaveOption: false
            }
        };

        Object(c.registerPaymentMethod)(s);

        
    }
]);

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        jQuery(document.body).trigger('ppcp_cc_block_ready');
    }, 3);
});
