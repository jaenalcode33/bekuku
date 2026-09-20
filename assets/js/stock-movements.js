(function () {
    "use strict";

    function initializeProductFilter() {
        var select = document.querySelector("[data-searchable-products]");
        var search = document.querySelector(".product-filter-search");

        if (!select || !search) {
            return;
        }

        var wrapper = document.createElement("div");
        wrapper.className = "movement-product-dropdown";
        wrapper.innerHTML =
            '<button type="button" class="form-control movement-product-toggle">Semua Produk</button>' +
            '<div class="movement-product-menu">' +
                '<input type="search" class="form-control movement-product-search" placeholder="Cari nama produk..." autocomplete="off">' +
                '<div class="movement-product-options"></div>' +
            '</div>';

        search.parentNode.insertBefore(wrapper, search);
        search.remove();
        wrapper.appendChild(select);
        select.style.display = "none";

        var toggle = wrapper.querySelector(".movement-product-toggle");
        var menu = wrapper.querySelector(".movement-product-menu");
        var menuSearch = wrapper.querySelector(".movement-product-search");
        var options = wrapper.querySelector(".movement-product-options");

        Array.prototype.forEach.call(select.options, function (option) {
            if (option.selected) {
                toggle.textContent = option.textContent.trim();
            }
        });

        function renderOptions() {
            var keyword = menuSearch.value.toLowerCase().trim();
            options.innerHTML = "";

            Array.prototype.forEach.call(select.options, function (option) {
                if (option.textContent.toLowerCase().indexOf(keyword) === -1) {
                    return;
                }

                var button = document.createElement("button");
                button.type = "button";
                button.className = "movement-product-option";
                button.textContent = option.textContent.trim();
                button.dataset.value = option.value;
                options.appendChild(button);
            });

            if (!options.children.length) {
                options.innerHTML = '<span class="movement-product-empty">Produk tidak ditemukan</span>';
            }
        }

        toggle.addEventListener("click", function () {
            document.querySelectorAll(".movement-product-dropdown.is-open").forEach(function (item) {
                if (item !== wrapper) {
                    item.classList.remove("is-open");
                }
            });

            var isOpen = wrapper.classList.toggle("is-open");

            if (isOpen) {
                renderOptions();
                menuSearch.value = "";
                var toggleRect = toggle.getBoundingClientRect();
                var menuHeight = Math.min(300, window.innerHeight - 24);
                var spaceBelow = window.innerHeight - toggleRect.bottom;
                var menuTop = spaceBelow >= menuHeight + 4
                    ? toggleRect.bottom + 4
                    : Math.max(12, toggleRect.top - menuHeight - 4);

                menu.style.top = menuTop + "px";
                menu.style.left = toggleRect.left + "px";
                menu.style.width = toggleRect.width + "px";
                menuSearch.focus();
            }
        });

        menuSearch.addEventListener("input", renderOptions);
        window.addEventListener("resize", function () {
            if (!wrapper.classList.contains("is-open")) {
                return;
            }

            var rect = toggle.getBoundingClientRect();
            menu.style.left = rect.left + "px";
            menu.style.width = rect.width + "px";
            menu.style.top = Math.max(12, rect.top - Math.min(300, window.innerHeight - 24) - 4) + "px";
        });
        options.addEventListener("click", function (event) {
            var option = event.target.closest(".movement-product-option");

            if (!option) {
                return;
            }

            select.value = option.dataset.value;
            toggle.textContent = option.textContent;
            wrapper.classList.remove("is-open");
        });

        document.addEventListener("click", function (event) {
            if (!wrapper.contains(event.target)) {
                wrapper.classList.remove("is-open");
            }
        });
    }

    initializeProductFilter();

    var printButtons = document.querySelectorAll("[data-print]");

    if (!printButtons.length) {
        return;
    }

    function printMovements() {
        window.setTimeout(function () {
            window.print();
        }, 50);
    }

    printButtons.forEach(function (button) {
        button.addEventListener("click", printMovements);
    });

}());
