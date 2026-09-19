let productOptions = '';


function formatNumber(number) {

    return new Intl.NumberFormat(
        'id-ID',
        {
            maximumFractionDigits: 0
        }
    ).format(
        Number(number) || 0
    );

}


/*
 * HITUNG TOTAL
 */

function calculateTotal() {

    let total = 0;


    document
        .querySelectorAll('.product-row')
        .forEach(function (row) {


            const quantity =
                Number(
                    row.querySelector(
                        '.quantity'
                    )?.value || 0
                );


            const price =
                Number(
                    row.querySelector(
                        '.purchase-price'
                    )?.value || 0
                );


            const subtotal =
                quantity * price;


            total += subtotal;


            const subtotalInput =
                row.querySelector(
                    '.subtotal'
                );


            if (subtotalInput) {

                subtotalInput.value =
                    'Rp ' +
                    formatNumber(subtotal);

            }

        });


    const totalDisplay =
        document.getElementById(
            'totalDisplay'
        );


    if (totalDisplay) {

        totalDisplay.value =
            'Rp ' +
            formatNumber(total);

    }


    calculatePayment(total);


    return total;

}


/*
 * HITUNG PEMBAYARAN
 */

function calculatePayment(total = null) {


    if (total === null) {

        total = 0;


        document
            .querySelectorAll('.product-row')
            .forEach(function (row) {

                const quantity =
                    Number(
                        row.querySelector(
                            '.quantity'
                        )?.value || 0
                    );


                const price =
                    Number(
                        row.querySelector(
                            '.purchase-price'
                        )?.value || 0
                    );


                total +=
                    quantity * price;

            });

    }


    const payment =
        Number(
            document.getElementById(
                'paymentAmount'
            )?.value || 0
        );


    const remaining =
        Math.max(
            0,
            total - payment
        );


    const remainingDisplay =
        document.getElementById(
            'remainingDisplay'
        );


    if (remainingDisplay) {

        remainingDisplay.value =
            'Rp ' +
            formatNumber(remaining);

    }

}


/*
 * PRODUK BERUBAH
 */

function productChanged(select) {


    const row =
        select.closest(
            '.product-row'
        );


    if (!row) {
        return;
    }


    const option =
        select.options[
            select.selectedIndex
        ];


    const price =
        option?.dataset.price || '0';


    const priceInput =
        row.querySelector(
            '.purchase-price'
        );


    if (priceInput) {

        priceInput.value = price;

    }


    calculateTotal();

}


/*
 * TAMBAH BARIS PRODUK
 */

function addProductRow() {


    const tbody =
        document.getElementById(
            'productBody'
        );


    if (!tbody || !productOptions) {
        return;
    }


    const row =
        document.createElement('tr');


    row.className =
        'product-row';


    row.innerHTML = `

        <td>

            <select
                name="product_id[]"
                class="form-select product-select"
                required
            >

                ${productOptions}

            </select>

        </td>


        <td>

            <input
                type="number"
                name="quantity[]"
                class="form-control quantity"
                min="1"
                value="1"
                required
            >

        </td>


        <td>

            <input
                type="number"
                name="purchase_price[]"
                class="form-control purchase-price"
                min="0"
                step="0.01"
                value="0"
                required
            >

        </td>


        <td>

            <input
                type="text"
                class="form-control subtotal"
                value="Rp 0"
                readonly
            >

        </td>


        <td>

            <input
                type="text"
                name="batch_number[]"
                class="form-control"
                maxlength="100"
                placeholder="BATCH-001"
            >

        </td>


        <td>

            <input
                type="date"
                name="expiry_date[]"
                class="form-control"
            >

        </td>


        <td class="text-center">

            <button
                type="button"
                class="btn btn-outline-danger btn-sm"
                data-action="remove-product-row"
                title="Hapus"
            >

                <i class="bi bi-trash"></i>

            </button>

        </td>

    `;


    tbody.appendChild(row);


    calculateTotal();

}


/*
 * HAPUS BARIS PRODUK
 */

function removeProductRow(button) {


    const rows =
        document.querySelectorAll(
            '.product-row'
        );


    if (rows.length <= 1) {

        alert(
            'Minimal harus ada satu produk.'
        );

        return;

    }


    const row =
        button.closest(
            '.product-row'
        );


    if (row) {

        row.remove();

    }


    calculateTotal();

}


/*
 * INIT
 */

document.addEventListener(
    'DOMContentLoaded',
    function () {


        const firstProduct =
            document.querySelector(
                '.product-select'
            );


        if (firstProduct) {

            productOptions =
                firstProduct.innerHTML;

        }


        calculateTotal();

    }
);


/*
 * BUTTON ACTION
 */

document.addEventListener(
    'click',
    function (event) {


        const button =
            event.target.closest(
                '[data-action]'
            );


        if (!button) {
            return;
        }


        const action =
            button.dataset.action;


        if (
            action ===
            'add-product-row'
        ) {

            addProductRow();

        }


        if (
            action ===
            'remove-product-row'
        ) {

            removeProductRow(button);

        }

    }
);


/*
 * SELECT PRODUK
 */

document.addEventListener(
    'change',
    function (event) {


        if (
            event.target.matches(
                '.product-select'
            )
        ) {

            productChanged(
                event.target
            );

        }

    }
);


/*
 * INPUT
 */

document.addEventListener(
    'input',
    function (event) {


        if (
            event.target.matches(
                '.quantity, .purchase-price'
            )
        ) {

            calculateTotal();

        }


        if (
            event.target.matches(
                '#paymentAmount'
            )
        ) {

            calculatePayment();

        }

    }
);


/*
 * SUBMIT
 */

document.addEventListener(
    'submit',
    function (event) {


        const form =
            event.target.closest(
                '#purchaseForm'
            );


        if (!form) {
            return;
        }


        const total =
            calculateTotal();


        const payment =
            Number(
                document.getElementById(
                    'paymentAmount'
                )?.value || 0
            );


        if (total <= 0) {

            event.preventDefault();


            alert(
                'Total pembelian harus lebih dari Rp 0.'
            );


            return;

        }


        if (payment > total) {

            event.preventDefault();


            alert(
                'Pembayaran tidak boleh lebih besar dari total pembelian.'
            );


            return;

        }


        const button =
            document.getElementById(
                'savePurchaseButton'
            );


        if (button) {

            button.disabled = true;


            button.innerHTML = `
                <i class="bi bi-hourglass-split me-1"></i>
                Menyimpan...
            `;

        }

    }
);