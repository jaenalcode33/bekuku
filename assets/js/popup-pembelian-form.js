document.addEventListener(
    'click',
    function (event) {

        const button =
            event.target.closest(
                '[data-purchase-close]'
            );


        if (!button) {
            return;
        }


        window.parent.postMessage(
            {
                type: 'purchase-close'
            },
            window.location.origin
        );

    }
);