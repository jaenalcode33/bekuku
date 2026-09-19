(function () {

    'use strict';


    function closePopup() {

        const overlay = document.querySelector(
            '.bekuku-purchase-overlay'
        );

        if (overlay) {
            overlay.remove();
        }

        document.body.classList.remove(
            'bekuku-purchase-popup-open'
        );

    }


    function openPopup(url) {

        closePopup();


        const overlay = document.createElement('div');

        overlay.className =
            'bekuku-purchase-overlay';


        overlay.innerHTML = `

            <div
                class="bekuku-purchase-modal"
                role="dialog"
                aria-modal="true"
                aria-label="Pembelian Baru"
            >

                <div class="bekuku-purchase-modal-header">

                    <div class="bekuku-purchase-modal-title">

                        <i class="bi bi-cart-plus me-2"></i>

                        Pembelian Baru

                    </div>


                    <button
                        type="button"
                        class="bekuku-purchase-close"
                        aria-label="Tutup"
                    >

                        &times;

                    </button>

                </div>


                <div class="bekuku-purchase-modal-body">

                    <iframe
                        title="Form Pembelian Baru"
                        src="${url}"
                        loading="eager"
                    ></iframe>

                </div>

            </div>

        `;


        document.body.appendChild(overlay);


        document.body.classList.add(
            'bekuku-purchase-popup-open'
        );


        const closeButton =
            overlay.querySelector(
                '.bekuku-purchase-close'
            );


        if (closeButton) {

            closeButton.addEventListener(
                'click',
                closePopup
            );

        }


        overlay.addEventListener(
            'click',
            function (event) {

                if (event.target === overlay) {
                    closePopup();
                }

            }
        );

    }


    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-popup-pembelian]'
                );


            if (!button) {
                return;
            }


            event.preventDefault();


            const url =
                button.dataset.popupUrl;


            if (!url) {
                return;
            }


            openPopup(url);

        }
    );


    window.addEventListener(
        'message',
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
                'purchase-close'
            ) {

                closePopup();

            }


            if (
                event.data.type ===
                'purchase-saved'
            ) {

                closePopup();

                window.location.reload();

            }

        }
    );


})();