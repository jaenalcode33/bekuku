(function () {

    "use strict";


    var printButton =
        document.querySelector("[data-print]");


    if (!printButton) {
        return;
    }


    /*
     * Pastikan mode print tidak aktif
     * ketika halaman pertama kali dibuka.
     */

    document.body.classList.remove(
        "report-print"
    );


    /*
     * =========================================================
     * UPDATE WAKTU CETAK
     * =========================================================
     */

    function updatePrintDateTime() {

        var now = new Date();


        var parts =
            new Intl.DateTimeFormat(
                "id-ID",
                {
                    timeZone: "Asia/Jakarta",

                    day: "2-digit",

                    month: "2-digit",

                    year: "numeric",

                    hour: "2-digit",

                    minute: "2-digit",

                    hour12: false
                }
            ).formatToParts(now);


        var values = {};


        parts.forEach(function (part) {

            if (part.type !== "literal") {

                values[part.type] =
                    part.value;

            }

        });


        var date =
            values.day +
            "-" +
            values.month +
            "-" +
            values.year;


        var time =
            values.hour +
            ":" +
            values.minute;


        /*
         * Header print
         */

        var printDate =
            document.querySelector(
                ".report-print-date"
            );


        if (printDate) {

            printDate.textContent =
                "Tanggal cetak: " +
                date +
                " " +
                time +
                " WIB";

        }


        /*
         * Dokumen print
         */

        var meta =
            document.querySelector(
                ".sales-print-meta"
            );


        if (meta) {

            var spans =
                meta.querySelectorAll("span");


            if (spans.length >= 2) {

                spans[0].textContent =
                    "Tanggal Cetak: " +
                    date;

                spans[1].textContent =
                    "Waktu: " +
                    time +
                    " WIB";

            }

        }


        /*
         * Footer print
         */

        var footer =
            document.querySelector(
                ".sales-print-footer"
            );


        if (footer) {

            var footerSpans =
                footer.querySelectorAll("span");


            if (footerSpans.length >= 2) {

                footerSpans[1].textContent =
                    "Laporan Penjualan · Dicetak " +
                    date +
                    " " +
                    time +
                    " WIB";

            }

        }

    }


    /*
     * =========================================================
     * SEBELUM PRINT
     * =========================================================
     */

    window.addEventListener(
        "beforeprint",
        function () {

            updatePrintDateTime();

            document.body.classList.add(
                "report-print"
            );

        }
    );


    /*
     * =========================================================
     * SETELAH PRINT
     * =========================================================
     */

    window.addEventListener(
        "afterprint",
        function () {

            document.body.classList.remove(
                "report-print"
            );

        }
    );


    /*
     * =========================================================
     * TOMBOL CETAK
     * =========================================================
     */

    printButton.addEventListener(
        "click",
        function (event) {

            event.preventDefault();


            updatePrintDateTime();


            document.body.classList.add(
                "report-print"
            );


            setTimeout(
                function () {

                    window.print();

                },
                50
            );

        }
    );


}());