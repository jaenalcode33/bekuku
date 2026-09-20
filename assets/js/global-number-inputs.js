"use strict";

(function () {
    function selectDefaultNumber(input) {
        if (input.value === "0" || input.value === "1") {
            input.select();
        }
    }

    document.addEventListener("focusin", function (event) {
        const input = event.target;

        if (input instanceof HTMLInputElement && input.type === "number") {
            selectDefaultNumber(input);
        }
    });

    document.addEventListener("click", function (event) {
        const input = event.target;

        if (input instanceof HTMLInputElement && input.type === "number") {
            selectDefaultNumber(input);
        }
    });
})();
