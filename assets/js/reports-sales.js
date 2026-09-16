(function () {
    "use strict";

    var printButton = document.querySelector("[data-print]");

    if (!printButton) {
        return;
    }

    printButton.addEventListener("click", function () {
        window.print();
    });
}());
