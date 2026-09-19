document.addEventListener("DOMContentLoaded", function () {

    const params =
        new URLSearchParams(
            window.location.search
        );


    /*
     * Customer berhasil disimpan
     */

    if (params.get("saved") === "1") {

        if (
            window.parent &&
            window.parent !== window
        ) {

            window.parent.postMessage(
                {
                    type: "customer-saved"
                },
                window.location.origin
            );

        }

        return;

    }


    /*
     * Tombol Kembali
     */

    const closeButton =
        document.querySelector(
            ".popup-close"
        );


    if (closeButton) {

        closeButton.addEventListener(
            "click",
            function () {

                if (
                    window.parent &&
                    window.parent !== window
                ) {

                    window.parent.postMessage(
                        {
                            type: "customer-close"
                        },
                        window.location.origin
                    );

                } else {

                    window.history.back();

                }

            }
        );

    }

});