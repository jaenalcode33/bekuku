let productOptions = '';

document.addEventListener('DOMContentLoaded', function () {

    const firstSelect =
        document.querySelector('.product-select');

    if (firstSelect) {

        productOptions =
            firstSelect.innerHTML;

    }

    calculatePayment();

});

document.addEventListener('click', function (event) {
    const action = event.target.closest('[data-action]');

    if (!action) {
        return;
    }

    if (action.dataset.action === 'add-product-row') {
        addProductRow();
    }

    if (action.dataset.action === 'remove-product-row') {
        removeProductRow(action);
    }
});

document.addEventListener('change', function (event) {
    if (event.target.matches('.product-select')) {
        productChanged(event.target);
    }
});

document.addEventListener('input', function (event) {
    if (event.target.matches('.quantity, .purchase-price')) {
        calculateRow(event.target);
    }

    if (event.target.matches('[name="payment_amount"]')) {
        calculatePayment();
    }
});


/*
|--------------------------------------------------------------------------
| Tambah baris produk
|--------------------------------------------------------------------------
*/

function addProductRow() {

    const tbody =
        document.getElementById('productBody');


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
                value="0"
                readonly
            >

        </td>


        <td>

            <input
                type="text"
                name="batch_number[]"
                class="form-control"
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
                class="btn btn-danger btn-sm"
                data-action="remove-product-row"
            >

                <i class="bi bi-trash"></i>

            </button>

        </td>

    `;


    tbody.appendChild(row);

}


/*
|--------------------------------------------------------------------------
| Hapus baris
|--------------------------------------------------------------------------
*/

function removeProductRow(button) {

    const rows =
        document.querySelectorAll('.product-row');


    if (rows.length <= 1) {

        alert('Minimal harus ada satu produk.');

        return;
    }


    button
        .closest('tr')
        .remove();


    calculateTotal();

}


/*
|--------------------------------------------------------------------------
| Ketika produk dipilih
|--------------------------------------------------------------------------
*/

function productChanged(select) {

    const row =
        select.closest('tr');


    const option =
        select.options[select.selectedIndex];


    const price =
        option.dataset.price || 0;


    const priceInput =
        row.querySelector('.purchase-price');


    priceInput.value =
        price;


    calculateRow(select);

}


/*
|--------------------------------------------------------------------------
| Hitung subtotal baris
|--------------------------------------------------------------------------
*/

function calculateRow(element) {

    const row =
        element.closest('tr');


    const quantity =
        parseFloat(
            row.querySelector('.quantity').value
        ) || 0;


    const price =
        parseFloat(
            row.querySelector('.purchase-price').value
        ) || 0;


    const subtotal =
        quantity * price;


    row.querySelector('.subtotal').value =
        formatRupiah(subtotal);


    calculateTotal();

}


/*
|--------------------------------------------------------------------------
| Hitung total pembelian
|--------------------------------------------------------------------------
*/

function calculateTotal() {

    let total = 0;


    document
        .querySelectorAll('.product-row')
        .forEach(function (row) {


            const quantity =
                parseFloat(
                    row.querySelector('.quantity').value
                ) || 0;


            const price =
                parseFloat(
                    row.querySelector('.purchase-price').value
                ) || 0;


            total +=
                quantity * price;

        });


    document.getElementById('totalDisplay').value =
        'Rp ' + formatNumber(total);


    calculatePayment();

}


/*
|--------------------------------------------------------------------------
| Hitung pembayaran
|--------------------------------------------------------------------------
*/

function calculatePayment() {

    let total = 0;


    document
        .querySelectorAll('.product-row')
        .forEach(function (row) {


            const quantity =
                parseFloat(
                    row.querySelector('.quantity').value
                ) || 0;


            const price =
                parseFloat(
                    row.querySelector('.purchase-price').value
                ) || 0;


            total +=
                quantity * price;

        });


    const payment =
        parseFloat(
            document.getElementById('paymentAmount').value
        ) || 0;


    let remaining =
        total - payment;


    if (remaining < 0) {

        remaining = 0;

    }


    document.getElementById('remainingDisplay').value =
        'Rp ' + formatNumber(remaining);

}


/*
|--------------------------------------------------------------------------
| Format Rupiah
|--------------------------------------------------------------------------
*/

function formatRupiah(number) {

    return 'Rp ' + formatNumber(number);

}


/*
|--------------------------------------------------------------------------
| Format angka
|--------------------------------------------------------------------------
*/

function formatNumber(number) {

    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0
    }).format(number);

}
