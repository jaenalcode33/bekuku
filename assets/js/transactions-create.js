/*
|--------------------------------------------------------------------------
| BEKUKU POS
| JavaScript Transaksi
|--------------------------------------------------------------------------
| Digunakan oleh:
| - transactions/index.php
| - transactions/create.php
| - transactions/edit.php
| - transactions/detail.php
| - transactions/delete.php
|--------------------------------------------------------------------------
*/

"use strict";

function showTransactionNotice(message) {
    const modal = document.getElementById("transactionNoticeModal");

    if (!modal) {
        return;
    }

    modal.querySelector(".transaction-notice-text").textContent = message;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
}

function closeTransactionNotice() {
    const modal = document.getElementById("transactionNoticeModal");

    if (!modal) {
        return;
    }

    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
}


/*
|--------------------------------------------------------------------------
| FORMAT RUPIAH
|--------------------------------------------------------------------------
*/

function formatRupiah(angka) {

    const nilai = Number(angka) || 0;

    return "Rp " + nilai.toLocaleString("id-ID");
}


/*
|--------------------------------------------------------------------------
| HITUNG TOTAL TRANSAKSI
|--------------------------------------------------------------------------
*/

function hitungTotal() {

    const rows = document.querySelectorAll(".product-row");

    let total = 0;
    let selectedCount = 0;

    rows.forEach(function (row) {

        const checkbox =
            row.querySelector(".product-checkbox");

        const quantity =
            row.querySelector(".quantity");

        const subtotal =
            row.querySelector(".subtotal");


        if (!checkbox || !quantity || !subtotal) {
            return;
        }


        if (checkbox.checked) {

            selectedCount++;

            const price =
                parseFloat(
                    checkbox.dataset.price
                ) || 0;


            let qty =
                parseInt(
                    quantity.value
                ) || 0;


            const maxStock =
                parseInt(
                    quantity.max
                ) || 0;


            /*
            | Quantity minimal 1
            */

            if (qty < 1) {
                qty = 1;
                quantity.value = 1;
            }


            /*
            | Quantity tidak boleh melebihi stok
            */

            if (
                maxStock > 0 &&
                qty > maxStock
            ) {

                qty = maxStock;

                quantity.value = maxStock;
            }


            const hasil =
                price * qty;


            subtotal.textContent =
                formatRupiah(hasil);


            total += hasil;

        } else {

            subtotal.textContent =
                "Rp 0";
        }
    });


    /*
    | Update total
    */

    const totalElement =
        document.getElementById("total");


    if (totalElement) {
        totalElement.textContent =
            formatRupiah(total);
    }

    updateQrisBarcode(total);

    const selectedCountElement =
        document.getElementById("selectedCount");

    if (selectedCountElement) {
        selectedCountElement.innerHTML =
            '<i class="bi bi-check2"></i> ' +
            selectedCount +
            " produk";
    }


    /*
    | Update kembalian
    */

    hitungKembalian(total);


    return total;
}


/*
|--------------------------------------------------------------------------
| HITUNG KEMBALIAN
|--------------------------------------------------------------------------
*/

function hitungKembalian(total) {

    const paymentElement =
        document.getElementById(
            "payment_amount"
        );


    const changeElement =
        document.getElementById(
            "change"
        );


    if (!paymentElement || !changeElement) {
        return;
    }


    const payment =
        parseFloat(
            paymentElement.value
        ) || 0;


    const change =
        payment - total;


    if (change >= 0) {

        changeElement.textContent =
            formatRupiah(change);


        changeElement.classList.remove(
            "text-danger"
        );


        changeElement.classList.add(
            "text-success"
        );

    } else {

        changeElement.textContent =
            "Uang kurang";


        changeElement.classList.remove(
            "text-success"
        );


        changeElement.classList.add(
            "text-danger"
        );
    }
}


/*
|--------------------------------------------------------------------------
| INISIALISASI CHECKBOX PRODUK
|--------------------------------------------------------------------------
*/

function initProductCheckbox() {

    const checkboxes =
        document.querySelectorAll(
            ".product-checkbox"
        );


    checkboxes.forEach(function (checkbox) {

        checkbox.addEventListener(
            "change",
            function () {

                const row =
                    this.closest(".product-row");


                if (!row) {
                    return;
                }


                const quantity =
                    row.querySelector(
                        ".quantity"
                    );


                if (!quantity) {
                    return;
                }


                const maxStock =
                    parseInt(
                        quantity.max
                    ) || 0;


                /*
                | Produk stok 0 tidak boleh dipilih
                */

                if (
                    this.checked &&
                    maxStock <= 0
                ) {

                    this.checked = false;


                    quantity.disabled =
                        true;


                    showTransactionNotice(
                        "Produk ini sedang habis."
                    );


                    hitungTotal();

                    return;
                }


                /*
                | Produk dipilih
                */

                if (this.checked) {

                    quantity.disabled =
                        false;


                    if (
                        parseInt(
                            quantity.value
                        ) < 1
                    ) {

                        quantity.value = 1;
                    }


                    quantity.focus();

                } else {

                    /*
                    | Produk tidak dipilih
                    */

                    quantity.disabled =
                        true;


                    quantity.value = 1;
                }


                hitungTotal();
            }
        );
    });
}


/*
|--------------------------------------------------------------------------
| INISIALISASI QUANTITY
|--------------------------------------------------------------------------
*/

function initQuantity() {

    const quantities =
        document.querySelectorAll(
            ".quantity"
        );


    quantities.forEach(function (quantity) {

        function selectDefaultQuantity() {
            if (quantity.value === "1") {
                quantity.select();
            }
        }

        quantity.addEventListener(
            "focus",
            selectDefaultQuantity
        );

        quantity.addEventListener(
            "click",
            selectDefaultQuantity
        );

        quantity.addEventListener(
            "input",
            function () {

                const max =
                    parseInt(
                        this.max
                    ) || 0;


                let value =
                    parseInt(
                        this.value
                    ) || 0;


                /*
                | Minimal 1
                */

                if (value < 1) {

                    value = 1;

                    this.value = 1;
                }


                /*
                | Maksimal sesuai stok
                */

                if (
                    max > 0 &&
                    value > max
                ) {

                    value = max;

                    this.value = max;
                }


                hitungTotal();
            }
        );


        /*
        | Validasi ketika input kehilangan fokus
        */

        quantity.addEventListener(
            "blur",
            function () {

                const max =
                    parseInt(
                        this.max
                    ) || 0;


                let value =
                    parseInt(
                        this.value
                    ) || 1;


                if (value < 1) {
                    value = 1;
                }


                if (
                    max > 0 &&
                    value > max
                ) {

                    value = max;
                }


                this.value = value;


                hitungTotal();
            }
        );
    });
}


/*
|--------------------------------------------------------------------------
| PEMBAYARAN
|--------------------------------------------------------------------------
*/

function initPayment() {

    const paymentElement =
        document.getElementById(
            "payment_amount"
        );

    const paymentMethod =
        document.getElementById(
            "payment_method"
        );


    if (!paymentElement) {
        return;
    }


    paymentElement.addEventListener(
        "input",
        function () {

            hitungTotal();
        }
    );

    if (paymentMethod) {
        paymentMethod.addEventListener(
            "change",
            hitungTotal
        );
    }
}


function updateQrisBarcode(totalValue) {

    const paymentMethod =
        document.getElementById(
            "payment_method"
        );

    const panel =
        document.getElementById(
            "qrisBarcodePanel"
        );

    const barcode =
        document.getElementById(
            "qrisBarcode"
        );

    const amount =
        document.getElementById(
            "qrisBarcodeAmount"
        );

    const note =
        document.getElementById(
            "qrisBarcodeNote"
        );

    if (!paymentMethod || !panel || !barcode || !amount) {
        return;
    }

    const paymentType =
        paymentMethod.value.toLowerCase();

    const isMidtransQris =
        paymentType === "qris";

    const isImageQris =
        paymentType === "qris_image";

    const isQris =
        isMidtransQris || isImageQris;

    panel.classList.toggle("is-visible", isQris);
    panel.setAttribute("aria-hidden", isQris ? "false" : "true");

    if (!isQris) {
        return;
    }

    const totalText =
        document.getElementById("total")?.textContent || "Rp 0";

    const total =
        Math.round(Number(totalValue) || 0);

    amount.textContent =
        "Total: " + totalText.trim();

    barcode.replaceChildren();

    if (isImageQris) {
        const imageUrl =
            paymentMethod.dataset.qrisImageUrl || "";

        if (imageUrl === "") {
            if (note) {
                note.textContent =
                    "Atur BEKUKU_QRIS_IMAGE_URL untuk menampilkan gambar QRIS.";
            }
            return;
        }

        const image = document.createElement("img");
        image.src = imageUrl;
        image.alt = "QRIS merchant";
        image.width = 240;
        image.height = 240;
        image.addEventListener("error", function () {
            barcode.textContent = "Gambar QRIS tidak dapat dimuat.";
            if (note) {
                note.textContent =
                    "Periksa path atau URL BEKUKU_QRIS_IMAGE_URL.";
            }
        });
        barcode.appendChild(image);
        if (note) {
            note.textContent =
                "Scan QRIS merchant menggunakan aplikasi pembayaran.";
        }
        return;
    }

    if (total <= 0) {
        if (note) {
            note.textContent = "Pilih produk terlebih dahulu untuk membuat QRIS.";
        }
        return;
    }

    const csrfToken =
        document.querySelector('input[name="csrf_token"]')?.value || "";
    const body = new URLSearchParams({
        amount: String(total),
        csrf_token: csrfToken
    });

    if (note) {
        note.textContent = "Menghubungkan ke Midtrans...";
    }

    fetch("midtrans-qris.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "Accept": "application/json"
        },
        body: body.toString()
    })
        .then(function (response) {
            return response.json().then(function (result) {
                if (!response.ok) {
                    throw new Error(result.error || "QRIS Midtrans gagal dibuat.");
                }
                return result;
            });
        })
        .then(function (result) {
            const image = document.createElement("img");
            image.src = result.qr_code_url;
            image.alt = "QRIS Midtrans";
            image.width = 180;
            image.height = 180;
            barcode.replaceChildren(image);
            if (note) {
                note.textContent = "Scan QRIS ini menggunakan aplikasi pembayaran.";
            }
        })
        .catch(function (error) {
            barcode.textContent = "QRIS belum tersedia.";
            if (note) {
                note.textContent = error.message;
            }
        });
}


/*
|--------------------------------------------------------------------------
| VALIDASI FORM TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionForm() {

    const form =
        document.querySelector(
            'form[method="POST"]'
        );


    if (!form) {
        return;
    }


    /*
    | Hanya aktif jika halaman memiliki
    | elemen transaksi create.
    */

    const productCheckboxes =
        form.querySelectorAll(
            ".product-checkbox"
        );


    if (
        productCheckboxes.length === 0
    ) {
        return;
    }


    form.addEventListener(
        "submit",
        function (event) {

            let selectedCount = 0;

            let total = 0;


            productCheckboxes.forEach(
                function (checkbox) {

                    if (!checkbox.checked) {
                        return;
                    }


                    selectedCount++;


                    const row =
                        checkbox.closest(
                            ".product-row"
                        );


                    if (!row) {
                        return;
                    }


                    const quantity =
                        row.querySelector(
                            ".quantity"
                        );


                    const qty =
                        parseInt(
                            quantity?.value
                        ) || 0;

                    const maxStock =
                        parseInt(
                            quantity?.max
                        ) || 0;

                    if (
                        maxStock > 0 &&
                        qty > maxStock
                    ) {
                        event.preventDefault();

                        showTransactionNotice(
                            "Transaksi melebihi stok produk. " +
                            "Stok tersedia: " +
                            maxStock +
                            ", jumlah diminta: " +
                            qty +
                            "."
                        );

                        quantity.focus();

                        return;
                    }


                    const price =
                        parseFloat(
                            checkbox.dataset.price
                        ) || 0;


                    total +=
                        price * qty;
                }
            );


            /*
            | Tidak ada produk
            */

            if (selectedCount === 0) {

                event.preventDefault();


                alert(
                    "Silakan pilih minimal satu produk."
                );


                return;
            }


            /*
            | Pembayaran
            */

            const paymentElement =
                document.getElementById(
                    "payment_amount"
                );


            if (paymentElement) {

                const payment =
                    parseFloat(
                        paymentElement.value
                    ) || 0;


                if (payment < total) {

                    event.preventDefault();


                    alert(
                        "Jumlah pembayaran tidak mencukupi."
                    );


                    paymentElement.focus();


                    return;
                }
            }
        }
    );
}


/*
|--------------------------------------------------------------------------
| PENCARIAN PRODUK
|--------------------------------------------------------------------------
*/

function initProductFilter() {

    const searchProduct =
        document.getElementById(
            "searchProduct"
        );


    const filterCategory =
        document.getElementById(
            "filterCategory"
        );


    const productRows =
        document.querySelectorAll(
            ".product-row"
        );


    const noProductFound =
        document.getElementById(
            "noProductFound"
        );

    const productPagination =
        document.getElementById(
            "productPagination"
        );

    const productsPerPage = 5;
    let currentPage = 1;

    /*
    | Tidak berada di halaman create
    */

    initSearchableFilterSelect();

    if (
        !searchProduct ||
        !filterCategory ||
        productRows.length === 0
    ) {
        return;
    }

    function initSearchableFilterSelect() {
        const select = document.querySelector(".searchable-filter-select");

        if (!select || select.parentElement.querySelector(".product-dropdown")) {
            return;
        }

        const wrapper = document.createElement("div");
        wrapper.className = "product-dropdown";
        wrapper.innerHTML = `
            <button type="button" class="form-control product-dropdown-toggle">
                Semua Kategori
            </button>
            <div class="product-dropdown-menu">
                <input type="search" class="form-control product-dropdown-search"
                    placeholder="Cari kategori..." autocomplete="off">
                <div class="product-dropdown-options"></div>
            </div>
        `;

        select.parentNode.insertBefore(wrapper, select);
        select.style.display = "none";

        const toggle = wrapper.querySelector(".product-dropdown-toggle");
        const search = wrapper.querySelector(".product-dropdown-search");
        const options = wrapper.querySelector(".product-dropdown-options");

        function renderOptions() {
            const keyword = search.value.toLowerCase().trim();
            options.innerHTML = "";

            Array.from(select.options).forEach(function (option) {
                if (option.value && !option.textContent.toLowerCase().includes(keyword)) {
                    return;
                }

                const button = document.createElement("button");
                button.type = "button";
                button.className = "product-dropdown-option";
                button.textContent = option.textContent.trim();
                button.dataset.value = option.value;
                options.appendChild(button);
            });

            if (!options.children.length) {
                options.innerHTML = '<span class="product-dropdown-empty">Kategori tidak ditemukan</span>';
            }
        }

        toggle.addEventListener("click", function () {
            document.querySelectorAll(".product-dropdown.is-open").forEach(function (item) {
                if (item !== wrapper) item.classList.remove("is-open");
            });
            wrapper.classList.toggle("is-open");
            renderOptions();
            if (wrapper.classList.contains("is-open")) {
                const rect = toggle.getBoundingClientRect();
                const menu = wrapper.querySelector(".product-dropdown-menu");
                menu.style.left = `${rect.left}px`;
                menu.style.width = `${rect.width}px`;
                menu.style.top = `${rect.bottom + 3}px`;
                search.focus();
            }
        });

        search.addEventListener("input", renderOptions);
        options.addEventListener("click", function (event) {
            const option = event.target.closest(".product-dropdown-option");
            if (!option) return;

            select.value = option.dataset.value;
            toggle.textContent = option.textContent;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            wrapper.classList.remove("is-open");
        });

        document.addEventListener("click", function (event) {
            if (!wrapper.contains(event.target)) {
                wrapper.classList.remove("is-open");
            }
        });
    }


    function filterProducts() {

        const keyword =
            searchProduct.value
                .toLowerCase()
                .trim();


        const category =
            filterCategory.value;


        const matchingRows = [];


        productRows.forEach(
            function (row) {

                const name =
                    row.dataset.name || "";


                const sku =
                    row.dataset.sku || "";


                const barcode =
                    row.dataset.barcode || "";


                const rowCategory =
                    row.dataset.category || "";


                const cocokKeyword =
                    name.includes(keyword) ||
                    sku.includes(keyword) ||
                    barcode.includes(keyword);


                const cocokCategory =
                    category === "" ||
                    rowCategory === category;


                if (
                    cocokKeyword &&
                    cocokCategory
                ) {
                    matchingRows.push(row);

                } else {
                    row.style.display =
                        "none";
                }
            }
        );

        const totalPages = Math.max(
            1,
            Math.ceil(matchingRows.length / productsPerPage)
        );
        currentPage = Math.min(currentPage, totalPages);
        const firstIndex = (currentPage - 1) * productsPerPage;
        const pageRows = matchingRows.slice(
            firstIndex,
            firstIndex + productsPerPage
        );

        matchingRows.forEach(function (row) {
            row.style.display = "none";
        });
        pageRows.forEach(function (row) {
            row.style.display = "";
        });

        if (noProductFound) {
            noProductFound.style.display =
                matchingRows.length === 0
                    ? ""
                    : "none";
        }

        if (productPagination) {
            if (matchingRows.length <= productsPerPage) {
                productPagination.innerHTML = "";
            } else {
                let pagination = '<small class="text-muted">Menampilkan ' +
                    (firstIndex + 1) + "-" +
                    Math.min(firstIndex + productsPerPage, matchingRows.length) +
                    " dari " + matchingRows.length + " produk</small>";
                pagination += '<nav aria-label="Navigasi produk transaksi"><ul class="pagination pagination-sm mb-0">';
                pagination += '<li class="page-item ' +
                    (currentPage === 1 ? "disabled" : "") +
                    '"><button type="button" class="page-link" data-product-page="' +
                    (currentPage - 1) + '">&laquo;</button></li>';
                const pageStart =
                    Math.floor((currentPage - 1) / 10) * 10 + 1;
                const pageEnd = Math.min(totalPages, pageStart + 9);
                for (let page = pageStart; page <= pageEnd; page++) {
                    pagination += '<li class="page-item ' +
                        (page === currentPage ? "active" : "") +
                        '"><button type="button" class="page-link" data-product-page="' +
                        page + '">' + page + "</button></li>";
                }
                pagination += '<li class="page-item ' +
                    (currentPage === totalPages ? "disabled" : "") +
                    '"><button type="button" class="page-link" data-product-page="' +
                    (currentPage + 1) + '">&raquo;</button></li></ul></nav>';
                productPagination.innerHTML = pagination;
            }
        }
    }


    filterCategory.addEventListener(
        "change",
        function () {
            currentPage = 1;
            filterProducts();
        }
    );

    searchProduct.addEventListener("input", function () {
        currentPage = 1;
        filterProducts();
    });

    if (productPagination) {
        productPagination.addEventListener("click", function (event) {
            const button = event.target.closest("[data-product-page]");
            if (!button || button.parentElement.classList.contains("disabled")) {
                return;
            }
            currentPage = Number(button.dataset.productPage) || 1;
            filterProducts();
        });
    }

    filterProducts();
}


/*
|--------------------------------------------------------------------------
| RIWAYAT TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionHistory() {

    const search =
        document.getElementById(
            "transactionSearch"
        );


    const statusFilter =
        document.getElementById(
            "transactionStatusFilter"
        );


    const paymentFilter =
        document.getElementById(
            "transactionPaymentFilter"
        );


    const table =
        document.getElementById(
            "transactionTable"
        );


    const noResult =
        document.getElementById(
            "transactionNoResult"
        );


    const visibleCount =
        document.getElementById(
            "transactionVisibleCount"
        );


    if (
        !search ||
        !statusFilter ||
        !paymentFilter ||
        !table
    ) {
        return;
    }


    const rows =
        table.querySelectorAll(
            "tbody tr.transaction-row"
        );


    function filterTransactions() {

        const keyword =
            search.value
                .toLowerCase()
                .trim();


        const selectedStatus =
            statusFilter.value
                .toLowerCase();


        const selectedPayment =
            paymentFilter.value
                .toLowerCase();


        let count = 0;


        rows.forEach(
            function (row) {

                const id =
                    (
                        row.dataset.id ||
                        ""
                    ).toLowerCase();


                const customer =
                    (
                        row.dataset.customer ||
                        ""
                    ).toLowerCase();


                const date =
                    (
                        row.dataset.date ||
                        ""
                    ).toLowerCase();


                const rowStatus =
                    (
                        row.dataset.status ||
                        ""
                    ).toLowerCase();


                const rowPayment =
                    (
                        row.dataset.payment ||
                        ""
                    ).toLowerCase();


                const cocokKeyword =
                    keyword === "" ||
                    id.includes(keyword) ||
                    customer.includes(keyword) ||
                    date.includes(keyword);


                const cocokStatus =
                    selectedStatus === "all" ||
                    rowStatus === selectedStatus;


                const cocokPayment =
                    selectedPayment === "all" ||
                    rowPayment === selectedPayment;


                if (
                    cocokKeyword &&
                    cocokStatus &&
                    cocokPayment
                ) {

                    row.style.display =
                        "";


                    count++;

                } else {

                    row.style.display =
                        "none";
                }
            }
        );


        /*
        | Update jumlah transaksi tampil
        */

        if (visibleCount) {

            visibleCount.textContent =
                count.toLocaleString(
                    "id-ID"
                );
        }


        /*
        | Tampilkan pesan jika tidak ada
        */

        if (noResult) {

            noResult.style.display =
                count === 0
                    ? ""
                    : "none";
        }
    }


    search.addEventListener(
        "input",
        filterTransactions
    );


    statusFilter.addEventListener(
        "change",
        filterTransactions
    );


    paymentFilter.addEventListener(
        "change",
        filterTransactions
    );
}


/*
|--------------------------------------------------------------------------
| TOMBOL PRINT DETAIL TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionPrint() {

    const printButtons =
        document.querySelectorAll(
            "[data-transaction-print], [data-print]"
        );


    const receiptSize = document.getElementById("receiptSize");
    const receipt = document.getElementById("receiptPrint");

    function printReceipt() {
        if (!receipt) {
            return;
        }

        document.body.classList.add("receipt-print-mode");
        window.setTimeout(function () {
            window.print();
        }, 100);
    }

    window.addEventListener("afterprint", function () {
        document.body.classList.remove("receipt-print-mode");
    });

    if (receiptSize) {
        receiptSize.addEventListener("change", function () {
            document.body.dataset.receiptSize = this.value;
        });
        document.body.dataset.receiptSize = receiptSize.value;
    }

    printButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            printReceipt();
        });
    });
}


/*
|--------------------------------------------------------------------------
| KONFIRMASI DELETE / BATAL TRANSAKSI
|--------------------------------------------------------------------------
*/

function initTransactionDelete() {

    const deleteForms =
        document.querySelectorAll(
            ".transaction-delete-form"
        );


    deleteForms.forEach(
        function (form) {

            form.addEventListener(
                "submit",
                function (event) {

                    const confirmed =
                        window.confirm(
                            "Apakah Anda yakin ingin membatalkan transaksi ini?"
                        );


                    if (!confirmed) {

                        event.preventDefault();
                    }
                }
            );
        }
    );
}


/*
|--------------------------------------------------------------------------
| DISABLE DOUBLE SUBMIT
|--------------------------------------------------------------------------
*/

function initPreventDoubleSubmit() {

    const transactionForms =
        document.querySelectorAll(
            'form[data-transaction-form]'
        );


    transactionForms.forEach(
        function (form) {

            form.addEventListener(
                "submit",
                function () {

                    const submitButton =
                        form.querySelector(
                            'button[type="submit"]'
                        );


                    if (!submitButton) {
                        return;
                    }


                    /*
                    | Jangan langsung disable
                    | jika browser menemukan error
                    | validasi HTML.
                    */

                    setTimeout(
                        function () {

                            submitButton.disabled =
                                true;


                            submitButton.dataset
                                .originalText =
                                submitButton.innerHTML;


                            submitButton.innerHTML =
                                '<i class="bi bi-hourglass-split"></i> Memproses...';

                        },
                        50
                    );
                }
            );
        }
    );
}


/*
|--------------------------------------------------------------------------
| INISIALISASI SEMUA
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {

        /*
        | CREATE
        */

        initProductCheckbox();

        initQuantity();

        initPayment();

        initProductFilter();

        initTransactionForm();


        /*
        | INDEX / RIWAYAT
        */

        initTransactionHistory();


        /*
        | DETAIL
        */

        initTransactionPrint();


        /*
        | DELETE
        */

        initTransactionDelete();


        /*
        | Proteksi double submit
        */

        initPreventDoubleSubmit();


        /*
        | Hitung total awal
        | jika halaman create.
        */

        if (
            document.querySelector(
                ".product-row"
            )
        ) {

            hitungTotal();
        }
    }
);