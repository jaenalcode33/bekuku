"use strict";

(function () {
    let modal = null;
    let trigger = null;

    function addModalStyles() {
        if (document.getElementById("bekuku-transaction-modal-styles")) {
            return;
        }

        const style = document.createElement("style");
        style.id = "bekuku-transaction-modal-styles";
        style.textContent = `
            .bekuku-transaction-modal .bekuku-modal-dialog {
                display: flex !important;
                flex-direction: column !important;
                width: min(1440px, calc(100vw - 48px)) !important;
                height: min(920px, calc(100vh - 48px)) !important;
                max-width: none !important;
                max-height: calc(100vh - 48px) !important;
                overflow: hidden !important;
            }

            .bekuku-transaction-modal .bekuku-modal-header {
                flex: 0 0 62px !important;
            }

            .bekuku-transaction-modal .bekuku-modal-body {
                display: flex !important;
                flex: 1 1 auto !important;
                min-height: 0 !important;
                overflow: hidden !important;
            }

            .bekuku-transaction-modal iframe {
                display: block !important;
                flex: 1 1 auto !important;
                width: 100% !important;
                height: 100% !important;
                min-height: 0 !important;
                border: 0 !important;
            }

            @media (max-width: 767.98px) {
                .bekuku-transaction-modal {
                    padding: 0 !important;
                }

                .bekuku-transaction-modal .bekuku-modal-dialog {
                    width: 100vw !important;
                    height: 100dvh !important;
                    max-height: 100dvh !important;
                    border-radius: 0 !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.remove();
        modal = null;
        document.body.classList.remove("modal-open");
        document.removeEventListener("keydown", onKeyDown);
        trigger?.focus();
        trigger = null;
    }

    function onKeyDown(event) {
        if (event.key === "Escape") {
            closeModal();
        }
    }

    function openModal(link) {
        addModalStyles();
        trigger = link;

        const url = new URL(link.href, window.location.href);
        url.searchParams.set("popup", "1");

        modal = document.createElement("div");
        modal.className = "bekuku-modal bekuku-form-modal bekuku-transaction-modal is-open";
        modal.setAttribute("aria-hidden", "false");
        modal.innerHTML = `
            <div class="bekuku-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="bekuku-transaction-modal-title">
                <div class="bekuku-modal-header">
                    <h2 class="bekuku-modal-title" id="bekuku-transaction-modal-title">Transaksi Baru</h2>
                    <button type="button" class="bekuku-modal-close" aria-label="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="bekuku-modal-body">
                    <iframe title="Transaksi Baru"></iframe>
                </div>
            </div>
        `;

        const iframe = modal.querySelector("iframe");
        iframe.src = url.href;
        iframe.addEventListener("load", function () {
            const currentUrl = new URL(iframe.contentWindow.location.href);
            if (/\/transactions\/detail\.php$/i.test(currentUrl.pathname)) {
                currentUrl.searchParams.delete("popup");
                window.location.assign(currentUrl.href);
                return;
            }

            const cancelLink = iframe.contentDocument.querySelector(".cancel-button");
            cancelLink?.addEventListener("click", function (event) {
                event.preventDefault();
                closeModal();
            }, { once: true });
        });

        modal.querySelector(".bekuku-modal-close").addEventListener("click", closeModal);
        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.body.appendChild(modal);
        document.body.classList.add("modal-open");
        document.addEventListener("keydown", onKeyDown);
        modal.querySelector(".bekuku-modal-close").focus();
    }

    document.addEventListener("click", function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const link = target.closest("a[href]");
        if (!link || link.target === "_blank" || link.hasAttribute("data-no-modal")) {
            return;
        }

        const url = new URL(link.href, window.location.href);
        if (!/\/transactions\/create\.php$/i.test(url.pathname)) {
            return;
        }

        event.preventDefault();
        openModal(link);
    }, true);
}());
