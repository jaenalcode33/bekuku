"use strict";

(function () {
    let modal;
    let backdrop;
    let frame;
    let frameLoaded = false;

    function resolvePopupUrl(target) {
        const rawUrl = target.dataset.modalUrl || target.getAttribute("href");

        if (!rawUrl) {
            return "";
        }

        return new URL(rawUrl, window.location.href).href;
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("modal-open");
        if (backdrop) {
            backdrop.remove();
            backdrop = null;
        }
    }

    function openModal(title, content) {
        if (!modal) {
            modal = document.createElement("div");
            modal.className = "bekuku-modal";
            modal.setAttribute("aria-hidden", "true");
            modal.innerHTML =
                '<div class="bekuku-modal-dialog" role="dialog" aria-modal="true">' +
                '<div class="bekuku-modal-header">' +
                '<h5 class="bekuku-modal-title"></h5>' +
                '<button type="button" class="bekuku-modal-close" aria-label="Tutup">&times;</button>' +
                "</div>" +
                '<div class="bekuku-modal-body"></div>' +
                "</div>";
            document.body.appendChild(modal);
            modal.addEventListener("click", function (event) {
                if (
                    event.target === modal ||
                    event.target.closest(".bekuku-modal-close")
                ) {
                    closeModal();
                }
            });
        }

        modal.querySelector(".bekuku-modal-title").textContent = title;
        modal.querySelector(".bekuku-modal-body").replaceChildren(content);
        backdrop = document.createElement("div");
        backdrop.className = "bekuku-modal-backdrop";
        backdrop.addEventListener("click", closeModal);
        document.body.appendChild(backdrop);
        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("modal-open");
    }

    function openCreateModal(target) {
        const wrapper = document.createElement("div");
        wrapper.className = "bekuku-modal-frame";
        frame = document.createElement("iframe");
        const popupUrl = resolvePopupUrl(target);
        const popupRequestUrl = new URL(popupUrl, window.location.href);
        popupRequestUrl.searchParams.set("popup", "1");
        const isFormPopup = /(?:^|\/)(?:create|edit)\.php(?:[?#]|$)/i.test(
            target.getAttribute("href") || target.dataset.modalUrl || ""
        );
        const isCompactPopup =
            isFormPopup || target.dataset.modalCompact === "true";
        const isTransactionPopup = /\/transactions\/create\.php(?:[?#]|$)/i.test(
            popupUrl
        );
        const isTransactionDetailPopup =
            /\/transactions\/detail\.php(?:[?#]|$)/i.test(popupUrl);

        frame.src = popupRequestUrl.href;
        frame.classList.add("bekuku-frame-loading");
        frame.title =
            target.dataset.modalTitle ||
            (/(?:^|\/)edit\.php(?:[?#]|$)/i.test(
                target.getAttribute("href") || target.dataset.modalUrl || ""
            )
                ? "Edit Data"
                : isTransactionDetailPopup
                    ? "Detail Transaksi"
                    : /detail\.php/i.test(target.getAttribute("href") || "")
                        ? "Detail Data"
                    : "Tambah Data");
        frame.loading = "eager";
        frame.addEventListener("load", function () {
            modal.classList.toggle("bekuku-edit-modal", isCompactPopup);
            modal.classList.toggle(
                "bekuku-transaction-modal",
                isTransactionPopup || isTransactionDetailPopup
            );
            if (frameLoaded) {
                try {
                    if (frame.contentWindow.location.pathname.endsWith("index.php")) {
                        window.location.reload();
                    }
                } catch (error) {
                    // Same-origin pages are expected; leave the popup open otherwise.
                }
            }
            try {
                frame.contentDocument.body.classList.add("bekuku-popup-page");
                const popupCloseButton =
                    frame.contentDocument.querySelector("[data-popup-close], .popup-close");
                if (popupCloseButton) {
                    popupCloseButton.addEventListener("click", function () {
                        closeModal();
                    });
                }
                if (isTransactionDetailPopup) {
                    frame.contentDocument.body.classList.add(
                        "transaction-popup-page"
                    );
                }
                if (isCompactPopup) {
                    frame.contentDocument.documentElement.style.overflow = "hidden";
                    const popupBody = frame.contentDocument.body;
                    const popupContent =
                        frame.contentDocument.querySelector(".app-content");
                    const formCard =
                        frame.contentDocument.querySelector(".app-content .card");
                    const contentHeight = formCard
                        ? formCard.getBoundingClientRect().bottom + 18
                        : popupContent
                            ? popupContent.scrollHeight
                            : popupBody.scrollHeight;
                    const frameHeight = Math.min(
                        Math.max(Math.ceil(contentHeight), 220),
                        window.innerHeight * 0.82
                    );
                    frame.style.height = frameHeight + "px";
                    frame.style.width = "100%";
                }
            } catch (error) {
                // Cross-origin frames cannot be adjusted; the form remains usable.
            }
            frame.classList.remove("bekuku-frame-loading");
            frameLoaded = true;
        });
        wrapper.appendChild(frame);
        openModal(frame.title, wrapper);
        modal.classList.remove("bekuku-delete-modal");
        modal.classList.toggle("bekuku-edit-modal", isCompactPopup);
        modal.classList.toggle(
            "bekuku-transaction-modal",
            isTransactionPopup || isTransactionDetailPopup
        );
        modal.classList.toggle(
            "bekuku-transaction-detail-modal",
            isTransactionDetailPopup
        );
    }

    function openDeleteModal(target) {
        const form = document.createElement("form");
        const deleteUrl = new URL(resolvePopupUrl(target), window.location.href);
        const deleteId = deleteUrl.searchParams.get("id");
        form.method = "get";
        form.action = deleteUrl.pathname;
        form.className = "bekuku-delete-form";
        form.innerHTML =
            '<div class="bekuku-delete-icon"><i class="bi bi-trash3"></i></div>' +
            "<p>" +
            (target.dataset.confirm || "Yakin ingin menghapus data ini?") +
            "</p>" +
            (deleteId
                ? '<input type="hidden" name="id" value="' +
                    deleteId.replace(/"/g, "&quot;") +
                    '">'
                : "") +
            '<div class="bekuku-modal-actions">' +
            '<button type="button" class="btn btn-secondary bekuku-cancel">Batal</button>' +
            '<button type="submit" class="btn btn-danger">Hapus</button>' +
            "</div>";
        form.addEventListener("click", function (event) {
            if (event.target.closest(".bekuku-cancel")) {
                event.preventDefault();
                closeModal();
            }
        });
        openModal("Konfirmasi Hapus", form);
        modal.classList.remove(
            "bekuku-edit-modal",
            "bekuku-transaction-modal",
            "bekuku-transaction-detail-modal"
        );
        modal.classList.add("bekuku-delete-modal");
    }

    document.addEventListener("click", function (event) {
        const target = event.target.closest(
            "[data-modal-url], [data-confirm], [data-print], a[href]"
        );

        if (!target) {
            return;
        }

        const targetUrl = target.matches("a[href]")
            ? new URL(target.getAttribute("href"), window.location.href)
            : null;

        if (
            targetUrl &&
            /\/transactions\/create\.php$/i.test(targetUrl.pathname)
        ) {
            event.stopImmediatePropagation();
            return;
        }

        if (target.dataset.noModal !== undefined) {
            return;
        }

        if (target.dataset.modalUrl) {
            event.preventDefault();
            frameLoaded = false;
            openCreateModal(target);
            return;
        }

        if (target.dataset.confirm) {
            event.preventDefault();
            openDeleteModal(target);
            return;
        }

        if (
            target.matches("a[href]") &&
            /(?:^|\/)(?:create|edit|detail)\.php(?:[?#]|$)/i.test(
                target.getAttribute("href") || ""
            )
        ) {
            event.preventDefault();
            frameLoaded = false;
            openCreateModal(target);
            return;
        }

        if (target.matches("[data-print]")) {
            event.preventDefault();
            window.print();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });
})();
