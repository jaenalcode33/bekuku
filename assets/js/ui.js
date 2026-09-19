"use strict";

(function () {

    let modal = null;

    let backdrop = null;

    let iframe = null;


    /* =========================================================
       CREATE MODAL
       ========================================================= */

    function createModal() {

        if (modal) {
            return;
        }


        modal =
            document.createElement("div");

        modal.className =
            "bekuku-modal";

        modal.setAttribute(
            "aria-hidden",
            "true"
        );


        modal.innerHTML = `

            <div class="bekuku-modal-dialog">

                <div class="bekuku-modal-header">

                    <h5 class="bekuku-modal-title">
                        Tambah Produk
                    </h5>

                    <button
                        type="button"
                        class="bekuku-modal-close"
                        aria-label="Tutup"
                    >
                        ×
                    </button>

                </div>


                <div class="bekuku-modal-body">

                </div>

            </div>

        `;


        document.body.appendChild(
            modal
        );


        modal
            .querySelector(
                ".bekuku-modal-close"
            )
            .addEventListener(
                "click",
                closeModal
            );


        modal.addEventListener(
            "click",
            function (event) {

                if (
                    event.target === modal
                ) {

                    closeModal();

                }

            }
        );

    }


    /* =========================================================
       OPEN MODAL
       ========================================================= */

    function openModal(
        title,
        url
    ) {

        createModal();


        const titleElement =
            modal.querySelector(
                ".bekuku-modal-title"
            );


        const body =
            modal.querySelector(
                ".bekuku-modal-body"
            );


        titleElement.textContent =
            title;


        body.innerHTML = "";


        iframe =
            document.createElement(
                "iframe"
            );


        iframe.src =
            url;


        iframe.title =
            title;


        iframe.setAttribute(
            "scrolling",
            "yes"
        );


        iframe.style.setProperty(
            "display",
            "block",
            "important"
        );


        iframe.style.setProperty(
            "width",
            "100%",
            "important"
        );


        iframe.style.setProperty(
            "height",
            "100%",
            "important"
        );


        iframe.style.setProperty(
            "min-height",
            "700px",
            "important"
        );


        iframe.style.setProperty(
            "border",
            "0",
            "important"
        );


        iframe.style.setProperty(
            "overflow",
            "auto",
            "important"
        );


        body.appendChild(
            iframe
        );


        /*
        ---------------------------------------------------------
        BACKDROP
        ---------------------------------------------------------
        */

        backdrop =
            document.createElement(
                "div"
            );

        backdrop.className =
            "bekuku-modal-backdrop";


        document.body.appendChild(
            backdrop
        );


        /*
        ---------------------------------------------------------
        FORM MODAL
        ---------------------------------------------------------
        */

        modal.classList.add(
            "bekuku-form-modal"
        );


        modal.classList.add(
            "is-open"
        );


        modal.setAttribute(
            "aria-hidden",
            "false"
        );


        document.body.classList.add(
            "modal-open"
        );


        /*
        ---------------------------------------------------------
        SETTING IFRAME SETELAH LOAD
        ---------------------------------------------------------
        */

        iframe.addEventListener(
            "load",
            function () {

                try {

                    const doc =
                        iframe.contentDocument;


                    const html =
                        doc.documentElement;


                    const body =
                        doc.body;


                    if (!body) {
                        return;
                    }


                    /*
                    JANGAN beri height tetap
                    */

                    html.style.setProperty(
                        "height",
                        "auto",
                        "important"
                    );


                    body.style.setProperty(
                        "height",
                        "auto",
                        "important"
                    );


                    body.style.setProperty(
                        "min-height",
                        "100%",
                        "important"
                    );


                    /*
                    JANGAN lock overflow
                    */

                    html.style.setProperty(
                        "overflow-y",
                        "visible",
                        "important"
                    );


                    html.style.setProperty(
                        "overflow-x",
                        "hidden",
                        "important"
                    );


                    body.style.setProperty(
                        "overflow-y",
                        "visible",
                        "important"
                    );


                    body.style.setProperty(
                        "overflow-x",
                        "hidden",
                        "important"
                    );


                    body.classList.add(
                        "bekuku-product-popup-page"
                    );


                    /*
                    Tombol Batal
                    */

                    const cancel =
                        doc.querySelector(
                            "[data-popup-close]"
                        );


                    if (cancel) {

                        cancel.addEventListener(
                            "click",
                            closeModal
                        );

                    }

                } catch (error) {

                    console.warn(
                        "Popup iframe:",
                        error
                    );

                }

            }
        );

    }


    /* =========================================================
       CLOSE
       ========================================================= */

    function closeModal() {

        if (!modal) {
            return;
        }


        modal.classList.remove(
            "is-open"
        );


        modal.setAttribute(
            "aria-hidden",
            "true"
        );


        document.body.classList.remove(
            "modal-open"
        );


        if (backdrop) {

            backdrop.remove();

            backdrop = null;
        }


        iframe = null;

    }


    /* =========================================================
       OPEN PRODUCT
       ========================================================= */

    function openProductPopup(
        element
    ) {

        let url =
            element.dataset.modalUrl ||
            element.getAttribute("href");


        if (!url) {
            return;
        }


        const popupUrl =
            new URL(
                url,
                window.location.href
            );


        /*
        PASTIKAN PHP MASUK MODE POPUP
        */

        popupUrl.searchParams.set(
            "popup",
            "1"
        );


        let title =
            element.dataset.modalTitle;


        if (!title) {

            if (
                /create\.php/i.test(
                    url
                )
            ) {

                title =
                    "Tambah Produk";

            } else if (
                /edit\.php/i.test(
                    url
                )
            ) {

                title =
                    "Edit Produk";

            } else {

                title =
                    "Detail";

            }

        }


        openModal(
            title,
            popupUrl.href
        );

    }


    /* =========================================================
       CLICK
       ========================================================= */

    document.addEventListener(
        "click",
        function (event) {

            const element =
                event.target.closest(
                    "a[href], [data-modal-url]"
                );


            if (!element) {
                return;
            }


            if (
                element.dataset.noModal !== undefined
            ) {

                return;
            }


            const url =
                element.dataset.modalUrl ||
                element.getAttribute("href") ||
                "";


            /*
            Hanya popup create/edit/detail
            */

            if (
                /(?:create|edit|detail)\.php/i
                    .test(url)
            ) {

                event.preventDefault();

                openProductPopup(
                    element
                );

            }

        }
    );


    /* =========================================================
       ESC
       ========================================================= */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape"
            ) {

                closeModal();

            }

        }
    );

})();