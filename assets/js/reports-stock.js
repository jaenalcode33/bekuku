(function () {
    "use strict";

    var printButtons = document.querySelectorAll("[data-print]");
    var report = document.querySelector(".print-report");

    if (!printButtons.length || !report) {
        return;
    }

    document.body.classList.remove("report-print");

    function updatePrintDateTime() {
        var now = new Date();
        var values = {};

        new Intl.DateTimeFormat("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            hour12: false
        }).formatToParts(now).forEach(function (part) {
            if (part.type !== "literal") {
                values[part.type] = part.value;
            }
        });

        var printedAt = values.day + "-" + values.month + "-" + values.year
            + " " + values.hour + ":" + values.minute + " WIB";
        var meta = report.querySelector(".print-report-meta");
        var footer = report.querySelector(".print-report-footer");

        if (meta) {
            var metaSpans = meta.querySelectorAll("span");
            if (metaSpans.length) {
                metaSpans[0].textContent = "Tanggal Cetak: " + printedAt;
            }
        }

        if (footer) {
            var footerSpans = footer.querySelectorAll("span");
            if (footerSpans.length > 1) {
                footerSpans[1].textContent = "Laporan Stok Produk · Dicetak " + printedAt;
            }
        }
    }

    function startPrint() {
        updatePrintDateTime();
        document.body.classList.add("report-print");
        window.setTimeout(function () {
            window.print();
        }, 50);
    }

    printButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            startPrint();
        });
    });

    window.addEventListener("beforeprint", function () {
        updatePrintDateTime();
        document.body.classList.add("report-print");
    });

    window.addEventListener("afterprint", function () {
        document.body.classList.remove("report-print");
    });
}());
