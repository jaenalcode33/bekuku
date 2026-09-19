"use strict";


/*
 * =========================================================
 * POPUP TAMBAH KATEGORI
 * =========================================================
 *
 * File ini khusus kategori.
 *
 * Tidak menggunakan ui.js.
 *
 * =========================================================
 */


(function () {


    /*
     * =====================================================
     * MODE HALAMAN UTAMA
     * =====================================================
     *
     * Halaman categories/index.php mempunyai:
     *
     * #btnTambahKategori
     *
     */


    const button =
        document.getElementById(
            "btnTambahKategori"
        );


    if (button) {


        /*
         * =================================================
         * BUAT OVERLAY
         * =================================================
         */

        const overlay =
            document.createElement("div");


        overlay.className =
            "popup-kategori-overlay";


        /*
         * =================================================
         * BUAT MODAL
         * =================================================
         */

        overlay.innerHTML = `

            <div
                class="popup-kategori-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="popupKategoriTitle"
            >

                <div class="popup-kategori-modal-header">

                    <h2
                        id="popupKategoriTitle"
                        class="popup-kategori-modal-title"
                    >
                        Tambah Kategori
                    </h2>


                    <button
                        type="button"
                        id="popupKategoriClose"
                        class="popup-kategori-modal-close"
                        aria-label="Tutup"
                    >
                        &times;
                    </button>

                </div>


                <div class="popup-kategori-modal-body">

                    <iframe
                        id="popupKategoriIframe"
                        title="Form Tambah Kategori"
                        src="about:blank"
                    ></iframe>

                </div>

            </div>

        `;


        /*
         * Masukkan popup ke halaman.
         */

        document.body.appendChild(
            overlay
        );


        /*
         * =================================================
         * AMBIL ELEMEN
         * =================================================
         */

        const iframe =
            document.getElementById(
                "popupKategoriIframe"
            );


        const closeButton =
            document.getElementById(
                "popupKategoriClose"
            );


        /*
         * =================================================
         * BUKA POPUP
         * =================================================
         */

        function bukaPopup() {


            iframe.src =
                "popup-tambah.php";


            overlay.classList.add(
                "is-open"
            );


            document.body.classList.add(
                "popup-kategori-open"
            );


            /*
             * Fokus input setelah iframe selesai dimuat.
             */

            iframe.onload =
                function () {


                    try {

                        const input =
                            iframe.contentDocument
                                .getElementById(
                                    "name"
                                );


                        if (input) {

                            input.focus();

                        }

                    } catch (error) {

                        /*
                         * Abaikan jika browser
                         * belum memberikan akses iframe.
                         */

                    }

                };

        }


        /*
         * =================================================
         * TUTUP POPUP
         * =================================================
         */

        function tutupPopup() {


            overlay.classList.remove(
                "is-open"
            );


            document.body.classList.remove(
                "popup-kategori-open"
            );


            iframe.src =
                "about:blank";

        }


        /*
         * =================================================
         * TOMBOL TAMBAH
         * =================================================
         */

        button.addEventListener(
            "click",
            bukaPopup
        );


        /*
         * =================================================
         * TOMBOL X
         * =================================================
         */

        closeButton.addEventListener(
            "click",
            tutupPopup
        );


        /*
         * =================================================
         * KLIK AREA GELAP
         * =================================================
         */

        overlay.addEventListener(
            "click",
            function (event) {


                if (
                    event.target === overlay
                ) {

                    tutupPopup();

                }

            }
        );


        /*
         * =================================================
         * ESC
         * =================================================
         */

        document.addEventListener(
            "keydown",
            function (event) {


                if (
                    event.key === "Escape" &&
                    overlay.classList.contains(
                        "is-open"
                    )
                ) {

                    tutupPopup();

                }

            }
        );


        /*
         * =================================================
         * PESAN DARI FORM POPUP
         * =================================================
         */

        window.addEventListener(
            "message",
            function (event) {


                /*
                 * Pastikan berasal dari
                 * halaman yang sama.
                 */

                if (
                    event.origin !==
                    window.location.origin
                ) {

                    return;

                }


                if (
                    !event.data ||
                    !event.data.type
                ) {

                    return;

                }


                /*
                 * -----------------------------------------
                 * BATAL / KEMBALI
                 * -----------------------------------------
                 */

                if (
                    event.data.type ===
                    "tutup-popup-kategori"
                ) {

                    tutupPopup();

                }


                /*
                 * -----------------------------------------
                 * BERHASIL SIMPAN
                 * -----------------------------------------
                 */

                if (
                    event.data.type ===
                    "kategori-berhasil-disimpan"
                ) {


                    tutupPopup();


                    /*
                     * Refresh supaya:
                     *
                     * - jumlah kategori berubah
                     * - daftar kategori berubah
                     */

                    window.location.reload();

                }

            }
        );


    }



    /*
     * =====================================================
     * MODE FORM POPUP
     * =====================================================
     *
     * File JavaScript yang sama juga dimuat oleh:
     *
     * categories/popup-tambah.php
     *
     */


    const form =
        document.getElementById(
            "formTambahKategori"
        );


    if (form) {


        /*
         * =================================================
         * TOMBOL BATAL
         * =================================================
         */

        const cancelButton =
            document.getElementById(
                "btnBatalKategori"
            );


        if (cancelButton) {


            cancelButton.addEventListener(
                "click",
                function () {


                    if (
                        window.parent &&
                        window.parent !== window
                    ) {


                        window.parent.postMessage(
                            {
                                type:
                                    "tutup-popup-kategori"
                            },
                            window.location.origin
                        );


                    }

                }
            );

        }


        /*
         * =================================================
         * VALIDASI FORM
         * =================================================
         */

        form.addEventListener(
            "submit",
            function (event) {


                const input =
                    document.getElementById(
                        "name"
                    );


                if (!input) {

                    return;

                }


                const value =
                    input.value.trim();


                if (value === "") {


                    event.preventDefault();


                    input.focus();


                    return;

                }


                /*
                 * Hilangkan spasi awal/akhir.
                 */

                input.value =
                    value;


            }
        );


        /*
         * =================================================
         * AUTO FOCUS
         * =================================================
         */

        const input =
            document.getElementById(
                "name"
            );


        if (input) {


            setTimeout(
                function () {

                    input.focus();

                },
                100
            );

        }

    }


})();