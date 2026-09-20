let productOptions = '';

function positionProductDropdown(toggle, menu) {
    const rect = toggle.getBoundingClientRect();
    const viewportPadding = 12;
    const gap = 4;
    const availableBelow = window.innerHeight - rect.bottom - viewportPadding - gap;
    const availableAbove = rect.top - viewportPadding - gap;
    const openUp = availableBelow < 240 && availableAbove > availableBelow;
    const height = Math.max(
        120,
        Math.min(300, openUp ? availableAbove : availableBelow)
    );

    menu.style.left = `${rect.left}px`;
    menu.style.width = `${rect.width}px`;
    menu.style.height = `${height}px`;
    menu.style.maxHeight = `${height}px`;
    menu.style.top = openUp ? 'auto' : `${rect.bottom + gap}px`;
    menu.style.bottom = openUp
        ? `${window.innerHeight - rect.top + gap}px`
        : 'auto';
}

function initializeSupplierDropdown() {
    const select = document.querySelector('select[name="supplier_id"]');

    if (!select || select.parentElement.querySelector('.supplier-dropdown')) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'supplier-dropdown product-dropdown';
    wrapper.innerHTML = `
        <button type="button" class="form-control product-dropdown-toggle">
            Pilih supplier
        </button>
        <div class="product-dropdown-menu">
            <input type="search" class="form-control product-dropdown-search"
                placeholder="Cari nama supplier..." autocomplete="off">
            <div class="product-dropdown-options"></div>
        </div>
    `;

    select.parentNode.insertBefore(wrapper, select);
    select.style.display = 'none';

    const toggle = wrapper.querySelector('.product-dropdown-toggle');
    const search = wrapper.querySelector('.product-dropdown-search');
    const options = wrapper.querySelector('.product-dropdown-options');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.value) {
        toggle.textContent = selectedOption.textContent.trim();
    }

    function renderOptions() {
        const keyword = search.value.toLowerCase().trim();
        options.innerHTML = '';

        Array.from(select.options).forEach(function (option) {
            if (!option.value || !option.textContent.toLowerCase().includes(keyword)) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'product-dropdown-option';
            button.textContent = option.textContent.trim();
            button.dataset.value = option.value;
            options.appendChild(button);
        });

        if (!options.children.length) {
            options.innerHTML = '<span class="product-dropdown-empty">Supplier tidak ditemukan</span>';
        }
    }

    toggle.addEventListener('click', function () {
        document.querySelectorAll('.product-dropdown.is-open').forEach(function (item) {
            if (item !== wrapper) item.classList.remove('is-open');
        });
        wrapper.classList.toggle('is-open');
        renderOptions();
        if (wrapper.classList.contains('is-open')) {
            wrapper.style.zIndex = '2000';
            const menu = wrapper.querySelector('.product-dropdown-menu');
            positionProductDropdown(toggle, menu);
            search.focus();
        } else {
            wrapper.style.zIndex = '';
        }
    });

    search.addEventListener('input', renderOptions);
    options.addEventListener('click', function (event) {
        const option = event.target.closest('.product-dropdown-option');
        if (!option) return;

        select.value = option.dataset.value;
        toggle.textContent = option.textContent;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        wrapper.classList.remove('is-open');
    });

    document.addEventListener('click', function (event) {
        if (!wrapper.contains(event.target)) wrapper.classList.remove('is-open');
    });
}

function initializeProductDropdown(row) {
    const select = row.querySelector('.product-select');

    if (!select || row.querySelector('.product-dropdown')) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'product-dropdown';
    wrapper.innerHTML = `
        <button type="button" class="form-control product-dropdown-toggle">
            Pilih produk
        </button>
        <div class="product-dropdown-menu">
            <input type="search" class="form-control product-dropdown-search"
                placeholder="Cari nama produk atau SKU..." autocomplete="off">
            <div class="product-dropdown-options"></div>
        </div>
    `;

    select.parentNode.insertBefore(wrapper, select);
    select.style.display = 'none';

    const toggle = wrapper.querySelector('.product-dropdown-toggle');
    const search = wrapper.querySelector('.product-dropdown-search');
    const options = wrapper.querySelector('.product-dropdown-options');

    function renderOptions() {
        const keyword = search.value.toLowerCase().trim();
        options.innerHTML = '';

        Array.from(select.options).forEach(function (option) {
            if (!option.value || !option.textContent.toLowerCase().includes(keyword)) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'product-dropdown-option';
            button.textContent = option.textContent.trim();
            button.dataset.value = option.value;
            options.appendChild(button);
        });

        if (!options.children.length) {
            options.innerHTML = '<span class="product-dropdown-empty">Produk tidak ditemukan</span>';
        }
    }

    function close() {
        wrapper.classList.remove('is-open');
    }

    toggle.addEventListener('click', function () {
        document.querySelectorAll('.product-dropdown.is-open').forEach(function (item) {
            if (item !== wrapper) item.classList.remove('is-open');
        });
        wrapper.classList.toggle('is-open');
        if (wrapper.classList.contains('is-open')) {
            wrapper.style.zIndex = '2000';
            const menu = search.closest('.product-dropdown-menu');
            positionProductDropdown(toggle, menu);
        } else {
            wrapper.style.zIndex = '';
        }
        renderOptions();
        if (wrapper.classList.contains('is-open')) search.focus();
    });

    search.addEventListener('input', renderOptions);
    options.addEventListener('click', function (event) {
        const option = event.target.closest('.product-dropdown-option');
        if (!option) return;

        select.value = option.dataset.value;
        toggle.textContent = option.textContent;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        close();
    });

    document.addEventListener('click', function (event) {
        if (!wrapper.contains(event.target)) close();
    });

    renderOptions();
}

function filterProductOptions(searchInput) {
    const row = searchInput.closest('.product-row');
    const select = row?.querySelector('.product-select');

    if (!select) {
        return;
    }

    const keyword = searchInput.value.toLowerCase().trim();

    Array.from(select.options).forEach(function (option) {
        if (!option.value) {
            option.hidden = false;
            return;
        }

        option.hidden = keyword !== '' &&
            !option.textContent.toLowerCase().includes(keyword);
    });
}


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
    initializeProductDropdown(row);

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

            initializeProductDropdown(firstProduct.closest('.product-row'));
        }

        initializeSupplierDropdown();

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

        if (event.target.matches('.product-search')) {
            filterProductOptions(event.target);
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