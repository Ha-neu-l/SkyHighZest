var wc;(()=>{"use strict";var e={};(e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})})(e);const t=window.wc.__experimentalInteractivity;(0,t.store)("woocommerce/product-filter-checkbox-list",{actions:{showAllItems:()=>{(0,t.getContext)().showAll=!0},selectCheckboxItem:e=>{const o=(0,t.getContext)(),c=e.target.value;o.items=o.items.map((e=>e.value.toString()===c?{...e,checked:!e.checked}:e))}}}),(wc=void 0===wc?{}:wc)["product-filter-checkbox-list"]=e})();