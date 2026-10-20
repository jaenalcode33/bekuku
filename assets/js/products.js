/* =========================================================
   BEKUKU PRODUCTS MENU SCRIPTS
   ========================================================= */

"use strict";


/* =========================================================
   PRODUCT POPUP / UI
   ========================================================= */

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

        modal = document.createElement("div");

        modal.className = "bekuku-modal";

        modal.setAttribute("aria-hidden", "true");

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
                        <i class="bi bi-x-lg"></i>
                    </button>

                </div>

                <div class="bekuku-modal-body"></div>

            </div>
        `;

        document.body.appendChild(modal);


        const closeButton =
            modal.querySelector(".bekuku-modal-close");


        if (closeButton) {

            closeButton.addEventListener(
                "click",
                closeModal
            );

        }


        modal.addEventListener(
            "click",
            function (event) {

                if (event.target === modal) {
                    closeModal();
                }

            }
        );

    }


    /* =========================================================
       OPEN MODAL
       ========================================================= */

    function openModal(title, url) {

        createModal();


        const titleElement =
            modal.querySelector(
                ".bekuku-modal-title"
            );


        const body =
            modal.querySelector(
                ".bekuku-modal-body"
            );


        if (!titleElement || !body) {
            return;
        }


        titleElement.textContent = title;

        body.innerHTML = "";


        iframe = document.createElement("iframe");


        iframe.src = url;

        iframe.title = title;

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
            "280px",
            "important"
        );

        iframe.style.setProperty(
            "min-height",
            "0",
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


        body.appendChild(iframe);


        /* =====================================================
           BACKDROP
           ===================================================== */

        backdrop =
            document.createElement("div");

        backdrop.className =
            "bekuku-modal-backdrop";

        document.body.appendChild(backdrop);


        /* =====================================================
           OPEN STATE
           ===================================================== */

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


        /* =====================================================
           IFRAME LOAD
           ===================================================== */

        iframe.addEventListener(
            "load",
            function () {

                try {

                    const doc =
                        iframe.contentDocument;

                    if (!doc) {
                        return;
                    }


                    const html =
                        doc.documentElement;

                    const iframeBody =
                        doc.body;


                    if (!iframeBody) {
                        return;
                    }


                    /* =================================================
                       RESET IFRAME HEIGHT / OVERFLOW
                       ================================================= */

                    html.style.setProperty(
                        "height",
                        "auto",
                        "important"
                    );

                    iframeBody.style.setProperty(
                        "height",
                        "auto",
                        "important"
                    );

                    iframeBody.style.setProperty(
                        "min-height",
                        "100%",
                        "important"
                    );


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

                    iframeBody.style.setProperty(
                        "overflow-y",
                        "visible",
                        "important"
                    );

                    iframeBody.style.setProperty(
                        "overflow-x",
                        "hidden",
                        "important"
                    );


                    iframeBody.classList.add(
                        "bekuku-product-popup-page"
                    );


                    /* =================================================
                       POPUP ELEMENTS
                       ================================================= */

                    const dialog =
                        modal.querySelector(
                            ".bekuku-modal-dialog"
                        );

                    const modalBody =
                        modal.querySelector(
                            ".bekuku-modal-body"
                        );

                    const modalHeader =
                        modal.querySelector(
                            ".bekuku-modal-header"
                        );

                    const modalTitle =
                        modal.querySelector(
                            ".bekuku-modal-title"
                        );

                    const modalClose =
                        modal.querySelector(
                            ".bekuku-modal-close"
                        );


                    /* =================================================
                       CHECK POPUP TYPE
                       ================================================= */

                    const isEditPopup =
                        /edit\.php/i.test(url);


                    const isCompactPopup =
                        isEditPopup ||
                        iframeBody.classList.contains(
                            "popup-form-page"
                        ) ||
                        iframeBody.classList.contains(
                            "popup-customer-page"
                        ) ||
                        iframeBody.classList.contains(
                            "popup-supplier-page"
                        ) ||
                        iframeBody.classList.contains(
                            "popup-kategori-page"
                        );


                    /* =================================================
                       COMPACT POPUP
                       ================================================= */

                    if (
                        isCompactPopup &&
                        dialog &&
                        modalBody
                    ) {

                        iframeBody.style.setProperty(
                            "min-height",
                            "0",
                            "important"
                        );


                        dialog.style.setProperty(
                            "width",
                            "min(690px, calc(100vw - 40px))",
                            "important"
                        );

                        dialog.style.setProperty(
                            "max-width",
                            "690px",
                            "important"
                        );

                        dialog.style.setProperty(
                            "height",
                            "auto",
                            "important"
                        );

                        dialog.style.setProperty(
                            "max-height",
                            "calc(100vh - 40px)",
                            "important"
                        );


                        modalBody.style.setProperty(
                            "flex",
                            "0 1 auto",
                            "important"
                        );


                        iframe.style.setProperty(
                            "height",
                            "280px",
                            "important"
                        );


                        if (modalHeader) {

                            modalHeader.style.setProperty(
                                "height",
                                "65px",
                                "important"
                            );

                        }


                        if (modalTitle) {

                            modalTitle.style.setProperty(
                                "font-size",
                                "23px",
                                "important"
                            );

                        }


                        if (modalClose) {

                            modalClose.style.setProperty(
                                "width",
                                "48px",
                                "important"
                            );

                            modalClose.style.setProperty(
                                "height",
                                "48px",
                                "important"
                            );

                            modalClose.style.setProperty(
                                "border-radius",
                                "12px",
                                "important"
                            );

                        }


                        function resizePopup() {

                            iframe.style.setProperty(
                                "height",
                                "280px",
                                "important"
                            );

                        }


                        resizePopup();

                        window.setTimeout(
                            resizePopup,
                            100
                        );

                    }


                    /* =================================================
                       EDIT PRODUCT POPUP STYLE
                       ================================================= */

                    if (isEditPopup) {

                        const popupStyle =
                            doc.createElement("style");


                        popupStyle.textContent = `
                            html,
                            body {
                                margin: 0 !important;
                                padding: 0 !important;
                                min-height: 0 !important;
                                background: #0d1b4f !important;
                                overflow-x: hidden !important;
                            }

                            body.bekuku-product-popup-page .app-wrapper,
                            body.bekuku-product-popup-page .app-main,
                            body.bekuku-product-popup-page .app-content,
                            body.bekuku-product-popup-page .container-fluid {
                                min-height: 0 !important;
                                margin: 0 !important;
                                padding: 0 !important;
                            }

                            body.bekuku-product-popup-page .app-wrapper {
                                display: block !important;
                            }

                            body.bekuku-product-popup-page .app-content-header,
                            body.bekuku-product-popup-page .breadcrumb,
                            body.bekuku-product-popup-page .card-header,
                            body.bekuku-product-popup-page .app-header,
                            body.bekuku-product-popup-page .app-sidebar,
                            body.bekuku-product-popup-page .app-footer,
                            body.bekuku-product-popup-page footer,
                            body.bekuku-product-popup-page .main-footer {
                                display: none !important;
                            }

                            body.bekuku-product-popup-page .card {
                                margin: 0 !important;
                                border: 0 !important;
                                border-radius: 0 !important;
                                background: #0d1b4f !important;
                                box-shadow: none !important;
                            }

                            body.bekuku-product-popup-page .card-body {
                                padding: 22px !important;
                                background: #0d1b4f !important;
                            }

                            body.bekuku-product-popup-page .card-footer {
                                display: flex !important;
                                justify-content: flex-end !important;
                                gap: 10px !important;
                                margin: 0 !important;
                                padding: 10px 22px !important;
                                border-top: 1px solid rgba(126, 157, 231, .28) !important;
                                background: #12245c !important;
                            }

                            body.bekuku-product-popup-page.popup-form-page {
                                padding: 0 !important;
                                background: #0d1b4f !important;
                            }

                            body.bekuku-product-popup-page .popup-form-card {
                                width: 100% !important;
                                margin: 0 !important;
                                padding: 0 !important;
                                border: 0 !important;
                                border-radius: 0 !important;
                                background: #0d1b4f !important;
                                box-shadow: none !important;
                            }

                            body.bekuku-product-popup-page .popup-form-body {
                                margin: 0 !important;
                                padding: 22px !important;
                                background: #0d1b4f !important;
                                color: #f5f8ff !important;
                            }

                            body.bekuku-product-popup-page .popup-form-body label {
                                display: block !important;
                                margin: 0 0 8px !important;
                                color: #f5f8ff !important;
                                font: 600 14px/1.3 Arial, Helvetica, sans-serif !important;
                            }

                            body.bekuku-product-popup-page .popup-form-body input,
                            body.bekuku-product-popup-page .popup-form-body select {
                                display: block !important;
                                width: 100% !important;
                                height: 44px !important;
                                padding: 9px 12px !important;
                                border: 1px solid #3b5798 !important;
                                border-radius: 9px !important;
                                background: #132760 !important;
                                color: #fff !important;
                                font: 14px Arial, Helvetica, sans-serif !important;
                                box-sizing: border-box !important;
                            }

                            body.bekuku-product-popup-page .popup-form-actions {
                                display: flex !important;
                                justify-content: flex-end !important;
                                align-items: center !important;
                                gap: 10px !important;
                                margin: 0 !important;
                                padding: 10px 22px !important;
                                border-top: 1px solid rgba(126, 157, 231, .28) !important;
                                background: #12245c !important;
                            }

                            body.bekuku-product-popup-page .popup-form-actions button {
                                min-height: 42px !important;
                                padding: 9px 18px !important;
                                border-radius: 9px !important;
                                font: 600 14px Arial, Helvetica, sans-serif !important;
                            }

                            body.bekuku-product-popup-page form {
                                margin: 0 !important;
                            }

                            body.bekuku-product-popup-page .form-label,
                            body.bekuku-product-popup-page label {
                                color: #f5f8ff !important;
                                font: 600 14px/1.3 Arial, Helvetica, sans-serif !important;
                            }

                            body.bekuku-product-popup-page .form-control,
                            body.bekuku-product-popup-page .form-select,
                            body.bekuku-product-popup-page input,
                            body.bekuku-product-popup-page select {
                                min-height: 44px !important;
                                height: 44px !important;
                                border: 1px solid #3b5798 !important;
                                border-radius: 9px !important;
                                background: #132760 !important;
                                color: #fff !important;
                            }

                            body.bekuku-product-popup-page .btn-primary,
                            body.bekuku-product-popup-page .popup-kategori-button-save {
                                min-height: 42px !important;
                                border: 1px solid #527dff !important;
                                border-radius: 9px !important;
                                background: #315cff !important;
                                color: #fff !important;
                            }

                            body.bekuku-product-popup-page .btn-secondary,
                            body.bekuku-product-popup-page .popup-kategori-button-cancel {
                                min-height: 42px !important;
                                border: 1px solid #526796 !important;
                                border-radius: 9px !important;
                                background: #162b68 !important;
                                color: #dce6ff !important;
                            }
                        `;


                        doc.head.appendChild(
                            popupStyle
                        );

                    }


                    /* =================================================
                       CANCEL / CLOSE BUTTON INSIDE IFRAME
                       ================================================= */

                    const cancel =
                        doc.querySelector(
                            "[data-popup-close], .popup-close"
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
       CLOSE MODAL
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


        if (iframe) {

            iframe.src = "about:blank";

        }


        iframe = null;

    }


    /* =========================================================
       OPEN PRODUCT POPUP
       ========================================================= */

    function openProductPopup(element) {

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


        /* =====================================================
           FORCE POPUP MODE
           ===================================================== */

        popupUrl.searchParams.set(
            "popup",
            "1"
        );


        let title =
            element.dataset.modalTitle;


        if (!title) {

            const path =
                popupUrl.pathname.toLowerCase();


            if (path.includes("/categories/")) {

                title = "Edit Kategori";

            } else if (
                path.includes("/customers/")
            ) {

                title = "Edit Customer";

            } else if (
                path.includes("/suppliers/")
            ) {

                title = "Edit Supplier";

            } else if (
                /create\.php/i.test(
                    popupUrl.pathname
                )
            ) {

                title = "Tambah Produk";

            } else if (
                /edit\.php/i.test(
                    popupUrl.pathname
                )
            ) {

                title = "Edit Produk";

            } else {

                title = "Detail";

            }

        }


        openModal(
            title,
            popupUrl.href
        );

    }


    /* =========================================================
       CLICK HANDLER
       ========================================================= */

    document.addEventListener(
        "click",
        function (event) {

            if (event.defaultPrevented) {
                return;
            }


            const target =
                event.target;


            if (
                !(target instanceof Element)
            ) {
                return;
            }


            const element =
                target.closest(
                    "a[href], [data-modal-url]"
                );


            if (!element) {
                return;
            }


            /* =================================================
               NO MODAL
               ================================================= */

            if (
                element.dataset.noModal !== undefined
            ) {

                return;

            }


            const url =
                element.dataset.modalUrl ||
                element.getAttribute("href") ||
                "";


            /* =================================================
               ONLY CREATE / EDIT / DETAIL
               ================================================= */

            if (
                /(?:create|edit|detail)\.php/i.test(
                    url
                )
            ) {

                event.preventDefault();

                openProductPopup(
                    element
                );

            }

        }
    );


    /* =========================================================
       ESC CLOSE
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


/* =========================================================
   GLOBAL NUMBER INPUTS
   ========================================================= */

(function () {

    "use strict";


    function selectDefaultNumber(input) {

        if (
            input.value === "0" ||
            input.value === "1"
        ) {

            input.select();

        }

    }


    document.addEventListener(
        "focusin",
        function (event) {

            const input =
                event.target;


            if (
                input instanceof HTMLInputElement &&
                input.type === "number"
            ) {

                selectDefaultNumber(
                    input
                );

            }

        }
    );


    document.addEventListener(
        "click",
        function (event) {

            const input =
                event.target;


            if (
                input instanceof HTMLInputElement &&
                input.type === "number"
            ) {

                selectDefaultNumber(
                    input
                );

            }

        }
    );


})();


/* =========================================================
   SELECT SEARCH
   ========================================================= */

(function () {

    "use strict";


    function getSearchLabel(select) {

        if (
            select.name === "supplier_id"
        ) {

            return "Cari nama supplier...";

        }


        if (
            select.name === "customer_id"
        ) {

            return "Cari nama customer...";

        }


        return "Cari kategori...";

    }


    function positionMenu(toggle, menu) {

        const rect =
            toggle.getBoundingClientRect();


        const height =
            Math.min(
                300,
                window.innerHeight - 24
            );


        const spaceBelow =
            window.innerHeight -
            rect.bottom;


        const top =
            spaceBelow >= height + 4
                ? rect.bottom + 4
                : Math.max(
                    12,
                    rect.top - height - 4
                );


        menu.style.top =
            top + "px";


        menu.style.left =
            rect.left + "px";


        menu.style.width =
            rect.width + "px";


        menu.style.maxHeight =
            height + "px";

    }


    function enhanceSelect(select) {

        if (
            select.closest(".purchase-section") ||
            select.closest(".supplier-dropdown") ||
            select.classList.contains(
                "searchable-filter-select"
            ) ||
            select.closest(
                ".global-searchable-select"
            )
        ) {

            return;

        }


        const parent =
            select.parentNode;


        if (!parent) {
            return;
        }


        const wrapper =
            document.createElement("div");


        wrapper.className =
            "global-searchable-select";


        wrapper.innerHTML = `
            <button
                type="button"
                class="form-control global-searchable-toggle"
            ></button>

            <div class="global-searchable-menu">

                <input
                    type="search"
                    class="form-control global-searchable-search"
                    autocomplete="off"
                >

                <div class="global-searchable-options"></div>

            </div>
        `;


        parent.insertBefore(
            wrapper,
            select
        );


        wrapper.appendChild(
            select
        );


        select.style.setProperty(
            "display",
            "none",
            "important"
        );


        const toggle =
            wrapper.querySelector(
                ".global-searchable-toggle"
            );


        const menu =
            wrapper.querySelector(
                ".global-searchable-menu"
            );


        const search =
            wrapper.querySelector(
                ".global-searchable-search"
            );


        const options =
            wrapper.querySelector(
                ".global-searchable-options"
            );


        if (
            !toggle ||
            !menu ||
            !search ||
            !options
        ) {

            return;

        }


        search.placeholder =
            getSearchLabel(select);


        function render() {

            const keyword =
                search.value
                    .toLowerCase()
                    .trim();


            options.innerHTML = "";


            Array.prototype.forEach.call(
                select.options,
                function (option) {

                    if (
                        !option.textContent
                            .toLowerCase()
                            .includes(keyword)
                    ) {

                        return;

                    }


                    const button =
                        document.createElement(
                            "button"
                        );


                    button.type =
                        "button";


                    button.className =
                        "global-searchable-option";


                    button.textContent =
                        option.textContent.trim();


                    button.dataset.value =
                        option.value;


                    options.appendChild(
                        button
                    );

                }
            );


            if (
                !options.children.length
            ) {

                options.innerHTML =
                    '<span class="global-searchable-empty">Data tidak ditemukan</span>';

            }

        }


        function syncLabel() {

            const selected =
                select.options[
                    select.selectedIndex
                ];


            toggle.textContent =
                selected
                    ? selected.textContent.trim()
                    : "Pilih data";

        }


        toggle.addEventListener(
            "click",
            function () {

                document
                    .querySelectorAll(
                        ".global-searchable-select.is-open"
                    )
                    .forEach(
                        function (item) {

                            if (
                                item !== wrapper
                            ) {

                                item.classList.remove(
                                    "is-open"
                                );

                            }

                        }
                    );


                const isOpen =
                    wrapper.classList.toggle(
                        "is-open"
                    );


                if (isOpen) {

                    render();

                    positionMenu(
                        toggle,
                        menu
                    );

                    search.focus();

                }

            }
        );


        search.addEventListener(
            "input",
            render
        );


        options.addEventListener(
            "click",
            function (event) {

                const option =
                    event.target.closest(
                        ".global-searchable-option"
                    );


                if (!option) {
                    return;
                }


                select.value =
                    option.dataset.value;


                select.dispatchEvent(
                    new Event(
                        "change",
                        {
                            bubbles: true
                        }
                    )
                );


                syncLabel();


                wrapper.classList.remove(
                    "is-open"
                );

            }
        );


        select.addEventListener(
            "change",
            syncLabel
        );


        document.addEventListener(
            "click",
            function (event) {

                if (
                    !wrapper.contains(
                        event.target
                    )
                ) {

                    wrapper.classList.remove(
                        "is-open"
                    );

                }

            }
        );


        window.addEventListener(
            "resize",
            function () {

                if (
                    wrapper.classList.contains(
                        "is-open"
                    )
                ) {

                    positionMenu(
                        toggle,
                        menu
                    );

                }

            }
        );


        syncLabel();

    }


    function initializeAll() {

        document
            .querySelectorAll(
                'select[name="category_id"], select[name="supplier_id"], select[name="customer_id"], select[data-searchable-select]'
            )
            .forEach(
                enhanceSelect
            );

    }


    if (
        document.readyState === "loading"
    ) {

        document.addEventListener(
            "DOMContentLoaded",
            initializeAll
        );

    } else {

        initializeAll();

    }


})();

(function () {
    const printButtons = document.querySelectorAll("[data-print]");

    printButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            window.setTimeout(function () {
                window.print();
            }, 50);
        });
    });
}());


/* =========================================================
   DATA TABLE FILTER
   ========================================================= */

(function () {

    "use strict";


    function initializeDataTableFilter() {

        document
            .querySelectorAll(
                ".data-table-card"
            )
            .forEach(
                function (card) {

                    const toggle =
                        card.querySelector(
                            ".data-table-filter-toggle"
                        );


                    const body =
                        card.querySelector(
                            ".card-body"
                        );


                    const table =
                        card.querySelector(
                            "table"
                        );


                    if (
                        !toggle ||
                        !body ||
                        !table
                    ) {

                        return;

                    }


                    /* Prevent duplicate filter */

                    if (
                        body.querySelector(
                            ".data-table-filter"
                        )
                    ) {

                        return;

                    }


                    const filter =
                        document.createElement(
                            "div"
                        );


                    filter.className =
                        "data-table-filter";


                    filter.hidden = true;


                    filter.innerHTML = `
                        <label class="form-label">
                            Cari data
                        </label>

                        <input
                            type="search"
                            class="form-control"
                            placeholder="Ketik nama untuk mencari..."
                            autocomplete="off"
                        >
                    `;


                    body.prepend(
                        filter
                    );


                    const input =
                        filter.querySelector(
                            "input"
                        );


                    toggle.addEventListener(
                        "click",
                        function () {

                            filter.hidden =
                                !filter.hidden;


                            toggle.setAttribute(
                                "aria-expanded",
                                String(
                                    !filter.hidden
                                )
                            );


                            if (
                                !filter.hidden
                            ) {

                                input.focus();

                            } else {

                                input.value = "";


                                table
                                    .querySelectorAll(
                                        "tbody tr"
                                    )
                                    .forEach(
                                        function (row) {

                                            row.hidden =
                                                false;

                                        }
                                    );

                            }

                        }
                    );


                    input.addEventListener(
                        "input",
                        function () {

                            const keyword =
                                input.value
                                    .toLowerCase()
                                    .trim();


                            table
                                .querySelectorAll(
                                    "tbody tr"
                                )
                                .forEach(
                                    function (row) {

                                        row.hidden =
                                            keyword !== "" &&
                                            !row.textContent
                                                .toLowerCase()
                                                .includes(
                                                    keyword
                                                );

                                    }
                                );

                        }
                    );

                }
            );

    }


    if (
        document.readyState === "loading"
    ) {

        document.addEventListener(
            "DOMContentLoaded",
            initializeDataTableFilter
        );

    } else {

        initializeDataTableFilter();

    }


})();