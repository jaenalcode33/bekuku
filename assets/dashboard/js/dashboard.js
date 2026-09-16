/* =========================================================
   BEKUKU POS
   DASHBOARD JAVASCRIPT
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       SALES CHART
       ===================================================== */

    const canvas = document.getElementById("salesChart");

    if (!canvas) {
        return;
    }

    /* -----------------------------------------------------
       Pastikan Chart.js sudah tersedia
       ----------------------------------------------------- */

    if (typeof Chart === "undefined") {

        console.error(
            "Chart.js belum dimuat."
        );

        return;
    }


    /* -----------------------------------------------------
       Ambil data dari HTML
       ----------------------------------------------------- */

    let chartLabels = [];
    let chartValues = [];


    try {

        chartLabels = JSON.parse(
            canvas.dataset.labels || "[]"
        );


        chartValues = JSON.parse(
            canvas.dataset.values || "[]"
        );

    } catch (error) {

        console.error(
            "Data chart tidak dapat dibaca:",
            error
        );

        return;
    }


    /* -----------------------------------------------------
       Buat Chart
       ----------------------------------------------------- */

    new Chart(canvas, {

        type: "line",

        data: {

            labels: chartLabels,

            datasets: [

                {

                    label: "Penjualan",

                    data: chartValues,

                    fill: true,

                    tension: 0.4,

                    borderWidth: 3,

                    pointRadius: 4,

                    pointHoverRadius: 6,

                    backgroundColor:
                        "rgba(98, 214, 197, 0.14)",

                    borderColor:
                        "#62d6c5",

                    pointBackgroundColor:
                        "#62d6c5",

                    pointBorderColor:
                        "#dffaf5"

                }

            ]

        },


        /* =================================================
           CHART OPTIONS
           ================================================= */

        options: {

            responsive: true,

            maintainAspectRatio: false,


            interaction: {

                intersect: false,

                mode: "index"

            },


            plugins: {

                legend: {

                    display: false

                },


                tooltip: {

                    callbacks: {

                        label: function (context) {

                            const value =
                                Number(
                                    context.raw || 0
                                );


                            return (
                                " " +
                                new Intl.NumberFormat(
                                    "id-ID",
                                    {
                                        style:
                                            "currency",

                                        currency:
                                            "IDR",

                                        maximumFractionDigits:
                                            0
                                    }
                                ).format(value)
                            );

                        }

                    }

                }

            },


            /* =================================================
               SCALES
               ================================================= */

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        color:
                            "#9fb2df",

                        callback:
                            function (value) {

                                return (
                                    "Rp " +
                                    new Intl.NumberFormat(
                                        "id-ID"
                                    ).format(value)
                                );

                            }

                    },


                    grid: {

                        color:
                            "rgba(159, 178, 223, 0.16)"

                    }

                },


                x: {

                    ticks: {

                        color:
                            "#9fb2df"

                    },


                    grid: {

                        display: false

                    }

                }

            }

        }

    });

});