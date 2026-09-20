(function () {
    "use strict";

    document.querySelectorAll(".data-table-card").forEach(function (card) {
        const toggle = card.querySelector(".data-table-filter-toggle");
        const body = card.querySelector(".card-body");
        const table = card.querySelector("table");

        if (!toggle || !body || !table) {
            return;
        }

        const filter = document.createElement("div");
        filter.className = "data-table-filter";
        filter.hidden = true;
        filter.innerHTML = `
            <label class="form-label">Cari data</label>
            <input type="search" class="form-control" placeholder="Ketik nama untuk mencari..." autocomplete="off">
        `;
        body.prepend(filter);

        const input = filter.querySelector("input");
        toggle.addEventListener("click", function () {
            filter.hidden = !filter.hidden;
            toggle.setAttribute("aria-expanded", String(!filter.hidden));
            if (!filter.hidden) {
                input.focus();
            } else {
                input.value = "";
                table.querySelectorAll("tbody tr").forEach(function (row) {
                    row.hidden = false;
                });
            }
        });

        input.addEventListener("input", function () {
            const keyword = input.value.toLowerCase().trim();
            table.querySelectorAll("tbody tr").forEach(function (row) {
                row.hidden = keyword !== ""
                    && !row.textContent.toLowerCase().includes(keyword);
            });
        });
    });
})();
