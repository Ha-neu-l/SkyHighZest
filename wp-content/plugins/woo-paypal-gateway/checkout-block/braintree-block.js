var {createElement} = wp.element;
var {registerPlugin} = wp.plugins;
var {ExperimentalOrderMeta} = wc.blocksCheckout;
var {registerExpressPaymentMethod, registerPaymentMethod} = wc.wcBlocksRegistry;

(function (e) {
    var t = {};

    function n(o) {
        if (t[o])
            return t[o].exports;
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
        if (1 & t && (e = n(e)), 8 & t)
            return e;
        if (4 & t && typeof e === "object" && e && e.__esModule)
            return e;
        var o = Object.create(null);
        if (n.r(o), Object.defineProperty(o, "default", {enumerable: !0, value: e}), 2 & t && typeof e !== "string") {
            for (var r in e) {
                n.d(o, r, function (t) {
                    return e[t];
                }.bind(null, r));
            }
        }
        return o;
    };
    n.n = function (e) {
        var t = e && e.__esModule ? function () {
            return e.default;
        } : function () {
            return e;
        };
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

                const l = Object(u.getSetting)("wpg_braintree_data", {});
                console.log(l);
                const p = () => Object(a.decodeEntities)(l.description || "");


                const {useEffect} = window.wp.element;

                const Content_Braintree = (props) => {


                    const {eventRegistration} = props;
                    const {onPaymentSetup, onPaymentProcessing} = eventRegistration;

                    useEffect(() => {
                        jQuery(document.body).trigger("wpg_braintree_block_ready");
                        const unsubscribeSetup = onPaymentSetup(() => {
                            console.log("Braintree payment setup triggered for block checkout.");

                        });

                        const unsubscribeProcessing = onPaymentProcessing((emitResponse) => {
                            console.log("Braintree payment processing triggered for block checkout.");
                            jQuery(document.body).trigger("checkout_place_order_wpg_braintree", {emitResponse});
                        });

                        return () => {
                            unsubscribeSetup();
                            unsubscribeProcessing();
                        };
                    }, [onPaymentSetup, onPaymentProcessing]);
                    return createElement(
                            "div",
                            {id: "braintree-cc-form", className: "wc-payment-form"},
                            createElement(
                                    "fieldset",
                                    null,
                                    createElement("div", {id: "wpg_dropin-container"})
                                    )
                            );
                };
                const s = {
                    name: "wpg_braintree",
                    label: createElement(
                            "span",
                            {style: {width: "100%"}},
                            l.title // Only the title is used here
                            ),
                    content: createElement(Content_Braintree, null),
                    edit: Object(r.createElement)(p, null),
                    canMakePayment: () => Promise.resolve(true),
                    ariaLabel: Object(a.decodeEntities)(
                            l.title || Object(i.__)("Payment via PayPal", "woo-gutenberg-products-block")
                            ),
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
        jQuery(document.body).trigger('wpg_braintree_block_ready');
    }, 3);
});
