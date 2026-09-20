"use strict";

(function () {
    function getSearchLabel(select) {
        if (select.name === "supplier_id") {
            return "Cari nama supplier...";
        }

        if (select.name === "customer_id") {
            return "Cari nama customer...";
        }

        return "Cari kategori...";
    }

    function positionMenu(toggle, menu) {
        var rect = toggle.getBoundingClientRect();
        var height = Math.min(300, window.innerHeight - 24);
        var spaceBelow = window.innerHeight - rect.bottom;
        var top = spaceBelow >= height + 4
            ? rect.bottom + 4
            : Math.max(12, rect.top - height - 4);

        menu.style.top = top + "px";
        menu.style.left = rect.left + "px";
        menu.style.width = rect.width + "px";
        menu.style.maxHeight = height + "px";
    }

    function enhanceSelect(select) {
        if (
            select.closest(".purchase-section") ||
            select.closest(".supplier-dropdown") ||
            select.classList.contains("searchable-filter-select") ||
            select.closest(".global-searchable-select")
        ) {
            return;
        }

        var wrapper = document.createElement("div");
        wrapper.className = "global-searchable-select";
        wrapper.innerHTML =
            '<button type="button" class="form-control global-searchable-toggle"></button>' +
            '<div class="global-searchable-menu">' +
                '<input type="search" class="form-control global-searchable-search" autocomplete="off">' +
                '<div class="global-searchable-options"></div>' +
            "</div>";

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.style.setProperty("display", "none", "important");

        var toggle = wrapper.querySelector(".global-searchable-toggle");
        var menu = wrapper.querySelector(".global-searchable-menu");
        var search = wrapper.querySelector(".global-searchable-search");
        var options = wrapper.querySelector(".global-searchable-options");
        search.placeholder = getSearchLabel(select);

        function render() {
            var keyword = search.value.toLowerCase().trim();
            options.innerHTML = "";

            Array.prototype.forEach.call(select.options, function (option) {
                if (!option.textContent.toLowerCase().includes(keyword)) {
                    return;
                }

                var button = document.createElement("button");
                button.type = "button";
                button.className = "global-searchable-option";
                button.textContent = option.textContent.trim();
                button.dataset.value = option.value;
                options.appendChild(button);
            });

            if (!options.children.length) {
                options.innerHTML = '<span class="global-searchable-empty">Data tidak ditemukan</span>';
            }
        }

        function syncLabel() {
            var selected = select.options[select.selectedIndex];
            toggle.textContent = selected
                ? selected.textContent.trim()
                : "Pilih data";
        }

        toggle.addEventListener("click", function () {
            document.querySelectorAll(".global-searchable-select.is-open").forEach(function (item) {
                if (item !== wrapper) {
                    item.classList.remove("is-open");
                }
            });

            var isOpen = wrapper.classList.toggle("is-open");
            if (isOpen) {
                render();
                positionMenu(toggle, menu);
                search.focus();
            }
        });

        search.addEventListener("input", render);
        options.addEventListener("click", function (event) {
            var option = event.target.closest(".global-searchable-option");
            if (!option) {
                return;
            }

            select.value = option.dataset.value;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            syncLabel();
            wrapper.classList.remove("is-open");
        });

        select.addEventListener("change", syncLabel);
        document.addEventListener("click", function (event) {
            if (!wrapper.contains(event.target)) {
                wrapper.classList.remove("is-open");
            }
        });
        window.addEventListener("resize", function () {
            if (wrapper.classList.contains("is-open")) {
                positionMenu(toggle, menu);
            }
        });

        syncLabel();
    }

    function initializeAll() {
        document.querySelectorAll(
            'select[name="category_id"], select[name="supplier_id"], select[name="customer_id"], select[data-searchable-select]'
        ).forEach(enhanceSelect);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeAll);
    } else {
        initializeAll();
    }
}());
