```javascript
/**
 * BEKUKU POS
 * Public Demo Dashboard
 *
 * File:
 * assets/js/demo.js
 */

(function () {

    "use strict";


    /* =========================================================
       DOM READY
    ========================================================= */

    document.addEventListener("DOMContentLoaded", function () {

        initMobileSidebar();

        initFeatureModal();

        initSalesChart();

        initSidebarMenu();

    });


    /* =========================================================
       MOBILE SIDEBAR
    ========================================================= */

    function initMobileSidebar() {

        const toggle =
            document.getElementById("demoMobileToggle");

        const sidebar =
            document.querySelector(".demo-sidebar");

        if (!toggle || !sidebar) {
            return;
        }


        toggle.addEventListener("click", function (event) {

            event.preventDefault();

            document.body.classList.toggle(
                "demo-sidebar-open"
            );

        });


        document.addEventListener("click", function (event) {

            if (
                !document.body.classList.contains(
                    "demo-sidebar-open"
                )
            ) {
                return;
            }


            const clickedInsideSidebar =
                sidebar.contains(event.target);

            const clickedToggle =
                toggle.contains(event.target);


            if (
                !clickedInsideSidebar &&
                !clickedToggle
            ) {

                document.body.classList.remove(
                    "demo-sidebar-open"
                );

            }

        });

    }


    /* =========================================================
       SIDEBAR MENU
    ========================================================= */

    function initSidebarMenu() {

        const menuItems =
            document.querySelectorAll(
                ".demo-menu-item"
            );


        menuItems.forEach(function (item) {

            item.addEventListener(
                "click",
                function () {

                    if (
                        item.classList.contains(
                            "demo-feature"
                        )
                    ) {
                        return;
                    }


                    menuItems.forEach(
                        function (menuItem) {

                            menuItem.classList.remove(
                                "active"
                            );

                        }
                    );


                    item.classList.add("active");


                    document.body.classList.remove(
                        "demo-sidebar-open"
                    );

                }
            );

        });

    }


    /* =========================================================
       FEATURE MODAL
    ========================================================= */

    function initFeatureModal() {

        const modal =
            document.getElementById("demoModal");

        const closeButton =
            document.getElementById("demoModalClose");

        const cancelButton =
            document.getElementById("demoModalCancel");

        const overlay =
            modal
                ? modal.querySelector(
                    ".demo-modal-overlay"
                )
                : null;

        const title =
            document.getElementById(
                "demoModalTitle"
            );

        const text =
            document.getElementById(
                "demoModalText"
            );

        const featureButtons =
            document.querySelectorAll(
                ".demo-feature"
            );


        if (
            !modal ||
            !title ||
            !text
        ) {
            return;
        }


        featureButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function (event) {

                        event.preventDefault();

                        const feature =
                            button.dataset.feature ||
                            "Fitur ini";


                        openModal(feature);

                    }
                );

            }
        );


        if (closeButton) {

            closeButton.addEventListener(
                "click",
                closeModal
            );

        }


        if (cancelButton) {

            cancelButton.addEventListener(
                "click",
                closeModal
            );

        }


        if (overlay) {

            overlay.addEventListener(
                "click",
                closeModal
            );

        }


        document.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Escape" &&
                    modal.classList.contains("show")
                ) {

                    closeModal();

                }

            }
        );


        function openModal(feature) {

            title.textContent =
                feature;


            text.textContent =
                feature +
                " tersedia pada aplikasi BEKUKU. " +
                "Mode demo hanya menampilkan contoh dashboard " +
                "tanpa mengubah data asli.";


            modal.classList.add("show");

            modal.setAttribute(
                "aria-hidden",
                "false"
            );


            document.body.style.overflow =
                "hidden";


            if (closeButton) {

                setTimeout(
                    function () {

                        closeButton.focus();

                    },
                    50
                );

            }

        }


        function closeModal() {

            modal.classList.remove("show");

            modal.setAttribute(
                "aria-hidden",
                "true"
            );


            document.body.style.overflow =
                "";

        }

    }


    /* =========================================================
       SALES CHART
    ========================================================= */

    function initSalesChart() {

        const canvas =
            document.getElementById(
                "salesChart"
            );


        if (!canvas) {
            return;
        }


        if (
            typeof Chart ===
            "undefined"
        ) {

            console.warn(
                "Chart.js belum tersedia."
            );

            return;

        }


        let labels = [];

        let values = [];


        try {

            labels =
                JSON.parse(
                    canvas.dataset.labels ||
                    "[]"
                );


            values =
                JSON.parse(
                    canvas.dataset.values ||
                    "[]"
                );

        } catch (error) {

            console.error(
                "Data grafik demo tidak valid:",
                error
            );

            return;

        }


        const ctx =
            canvas.getContext("2d");


        if (!ctx) {
            return;
        }


        new Chart(
            ctx,
            {
                type: "line",

                data: {

                    labels: labels,

                    datasets: [
                        {
                            label:
                                "Penjualan",

                            data:
                                values,

                            borderWidth: 3,

                            pointRadius: 4,

                            pointHoverRadius: 6,

                            tension: 0.35,

                            fill: true,

                            backgroundColor:
                                "rgba(245, 158, 11, 0.10)",

                            borderColor:
                                "#f59e0b",

                            pointBackgroundColor:
                                "#f59e0b",

                            pointBorderColor:
                                "#ffffff",

                            pointBorderWidth:
                                2
                        }
                    ]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio:
                        false,

                    interaction: {
                        intersect: false,
                        mode: "index"
                    },

                    plugins: {

                        legend: {
                            display: false
                        },

                        tooltip: {

                            displayColors:
                                false,

                            callbacks: {

                                label:
                                    function (
                                        context
                                    ) {

                                        const value =
                                            Number(
                                                context.raw ||
                                                0
                                            );


                                        return (
                                            " " +
                                            formatRupiah(
                                                value
                                            )
                                        );

                                    }

                            }

                        }

                    },

                    scales: {

                        x: {

                            grid: {
                                display: false
                            },

                            ticks: {

                                color:
                                    "#94a3b8",

                                font: {
                                    size: 10
                                }

                            }

                        },

                        y: {

                            beginAtZero: true,

                            grid: {

                                color:
                                    "rgba(148, 163, 184, 0.12)"

                            },

                            ticks: {

                                color:
                                    "#94a3b8",

                                font: {
                                    size: 9
                                },

                                callback:
                                    function (
                                        value
                                    ) {

                                        return formatShortRupiah(
                                            value
                                        );

                                    }

                            }

                        }

                    }

                }

            }
        );

    }


    /* =========================================================
       RUPIAH FORMAT
    ========================================================= */

    function formatRupiah(value) {

        return "Rp " +
            Number(value || 0)
                .toLocaleString(
                    "id-ID"
                );

    }


    /* =========================================================
       SHORT RUPIAH FORMAT
    ========================================================= */

    function formatShortRupiah(value) {

        value =
            Number(value || 0);


        if (value >= 1000000) {

            return (
                "Rp " +
                (value / 1000000)
                    .toFixed(1)
                    .replace(
                        ".0",
                        ""
                    ) +
                " jt"
            );

        }


        if (value >= 1000) {

            return (
                "Rp " +
                Math.round(
                    value / 1000
                ) +
                " rb"
            );

        }


        return (
            "Rp " +
            value
        );

    }

})();
```
