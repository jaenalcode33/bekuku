"use strict";


/* =========================================================
   POPUP SUPPLIER
   ========================================================= */


/* =========================================================
   MODE FORM DI DALAM IFRAME
   ========================================================= */

const formTambahSupplier =
    document.getElementById("formTambahSupplier");


if (formTambahSupplier) {


    const tombolBatal =
        document.getElementById("btnBatalSupplier");


    if (tombolBatal) {

        tombolBatal.addEventListener(
            "click",
            function () {

                window.parent.postMessage(
                    {
                        type: "tutup-popup-supplier"
                    },
                    window.location.origin
                );

            }
        );

    }

}


/* =========================================================
   MODE HALAMAN PRODUK
   ========================================================= */

const tombolTambahSupplier =
    document.querySelector("[data-popup-supplier]");


if (tombolTambahSupplier) {


    /* -----------------------------------------------------
       BUAT OVERLAY
       ----------------------------------------------------- */

    const overlay =
        document.createElement("div");

    overlay.className =
        "popup-supplier-overlay";


    overlay.innerHTML = `

        <div class="popup-supplier-modal">

            <div class="popup-supplier-modal-header">

                <h2 class="popup-supplier-modal-title">
                    Tambah Supplier
                </h2>

                <button
                    type="button"
                    class="popup-supplier-modal-close"
                    id="popupSupplierClose"
                >
                    &times;
                </button>

            </div>


            <div class="popup-supplier-modal-body">

                <iframe
                    id="popupSupplierFrame"
                    src="about:blank"
                    frameborder="0"
                ></iframe>

            </div>

        </div>

    `;


    document.body.appendChild(overlay);


    const iframe =
        document.getElementById(
            "popupSupplierFrame"
        );


    const tombolClose =
        document.getElementById(
            "popupSupplierClose"
        );


    /* -----------------------------------------------------
       BUKA
       ----------------------------------------------------- */

    function bukaPopupSupplier() {

        iframe.src =
            "/Bekuku/suppliers/popup-tambah.php";


        overlay.classList.add("is-open");


        document.body.style.overflow =
            "hidden";

    }


    /* -----------------------------------------------------
       TUTUP
       ----------------------------------------------------- */

    function tutupPopupSupplier() {

        overlay.classList.remove(
            "is-open"
        );


        document.body.style.overflow =
            "";


        iframe.src =
            "about:blank";

    }


    /* -----------------------------------------------------
       BUTTON
       ----------------------------------------------------- */

    tombolTambahSupplier.addEventListener(
        "click",
        function (event) {

            event.preventDefault();

            bukaPopupSupplier();

        }
    );


    /* -----------------------------------------------------
       X
       ----------------------------------------------------- */

    tombolClose.addEventListener(
        "click",
        tutupPopupSupplier
    );


    /* -----------------------------------------------------
       BACKDROP
       ----------------------------------------------------- */

    overlay.addEventListener(
        "click",
        function (event) {

            if (event.target === overlay) {

                tutupPopupSupplier();

            }

        }
    );


    /* -----------------------------------------------------
       ESC
       ----------------------------------------------------- */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                overlay.classList.contains("is-open")
            ) {

                tutupPopupSupplier();

            }

        }
    );


    /* -----------------------------------------------------
       PESAN DARI POPUP
       ----------------------------------------------------- */

    window.addEventListener(
        "message",
        function (event) {


            if (
                event.origin !==
                window.location.origin
            ) {

                return;

            }


            if (!event.data) {

                return;

            }


            /* BERHASIL SIMPAN */

            if (
                event.data.type ===
                "supplier-berhasil-disimpan"
            ) {

                tutupPopupSupplier();

                window.location.reload();

            }


            /* BATAL */

            if (
                event.data.type ===
                "tutup-popup-supplier"
            ) {

                tutupPopupSupplier();

            }

        }
    );

}