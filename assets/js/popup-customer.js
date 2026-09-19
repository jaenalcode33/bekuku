document.addEventListener("DOMContentLoaded", function () {

    const trigger =
        document.querySelector(
            "[data-popup-customer]"
        );


    if (!trigger) {
        return;
    }


    function closePopup() {

        const overlay =
            document.querySelector(
                ".popup-customer-overlay"
            );


        if (overlay) {

            overlay.remove();

        }


        document.body.classList.remove(
            "popup-customer-open"
        );

    }


    function resizeIframe(iframe) {

        try {

            const doc =
                iframe.contentDocument ||
                iframe.contentWindow.document;


            if (!doc) {
                return;
            }


            const body = doc.body;

            const html = doc.documentElement;


            const height =
                Math.max(
                    body
                        ? body.scrollHeight
                        : 0,

                    body
                        ? body.offsetHeight
                        : 0,

                    html
                        ? html.scrollHeight
                        : 0,

                    html
                        ? html.offsetHeight
                        : 0
                );


            const finalHeight =
    Math.min(
        Math.max(
            height + 2,
            150
        ),
        250
    );

            iframe.style.height =
                finalHeight + "px";


        } catch (error) {

            iframe.style.height =
    "165px";

        }

    }


    function openPopup() {

        if (
            document.querySelector(
                ".popup-customer-overlay"
            )
        ) {

            return;

        }


        const overlay =
            document.createElement(
                "div"
            );

        overlay.className =
            "popup-customer-overlay";


        const modal =
            document.createElement(
                "div"
            );

        modal.className =
            "popup-customer-modal";


        const header =
            document.createElement(
                "div"
            );

        header.className =
            "popup-customer-modal-header";


        const title =
            document.createElement(
                "div"
            );

        title.className =
            "popup-customer-modal-title";

        title.textContent =
            "Tambah Customer";


        const closeButton =
            document.createElement(
                "button"
            );

        closeButton.type =
            "button";

        closeButton.className =
            "popup-customer-modal-close";

        closeButton.innerHTML =
            "&times;";

        closeButton.setAttribute(
            "aria-label",
            "Tutup"
        );


        const body =
            document.createElement(
                "div"
            );

        body.className =
            "popup-customer-modal-body";


        const iframe =
            document.createElement(
                "iframe"
            );

        iframe.className =
            "popup-customer-iframe";

        iframe.src =
            trigger.dataset.popupUrl;

        iframe.setAttribute(
            "frameborder",
            "0"
        );

        iframe.setAttribute(
            "scrolling",
            "no"
        );


        header.appendChild(title);

        header.appendChild(
            closeButton
        );


        body.appendChild(iframe);


        modal.appendChild(header);

        modal.appendChild(body);


        overlay.appendChild(modal);


        document.body.appendChild(
            overlay
        );


        document.body.classList.add(
            "popup-customer-open"
        );


        closeButton.addEventListener(
            "click",
            closePopup
        );


        overlay.addEventListener(
            "click",
            function (event) {

                if (
                    event.target === overlay
                ) {

                    closePopup();

                }

            }
        );


        iframe.addEventListener(
            "load",
            function () {

                resizeIframe(iframe);


                setTimeout(
                    function () {

                        resizeIframe(
                            iframe
                        );

                    },
                    100
                );


                setTimeout(
                    function () {

                        resizeIframe(
                            iframe
                        );

                    },
                    300
                );

            }
        );

    }


    trigger.addEventListener(
        "click",
        function (event) {

            event.preventDefault();

            openPopup();

        }
    );


    /*
     * Pesan dari popup
     */

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


            if (
                event.data.type ===
                "customer-close"
            ) {

                closePopup();

            }


            if (
                event.data.type ===
                "customer-saved"
            ) {

                closePopup();

                window.location.reload();

            }

        }
    );


    /*
     * ESC
     */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape"
            ) {

                closePopup();

            }

        }
    );

});