(function () {
    "use strict";

    function initializeSnapCheckout() {
        const form = document.getElementById("transactionForm");
        const paymentMethod = document.getElementById("payment_method");
        const paymentAmount = document.getElementById("payment_amount");
        const orderIdInput = document.getElementById("midtransOrderId");
        const submitButton = document.getElementById("saveTransaction");
        const message = document.getElementById("snapCheckoutMessage");
        let checkoutControlStates = [];

        if (!form || !paymentMethod || !paymentAmount || !orderIdInput || !submitButton || !message) {
            return;
        }

        function showMessage(text, type) {
            message.className = "alert alert-" + type + " mt-2";
            message.textContent = text;
        }

        function resetSubmitButton() {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Transaksi';
        }

        function lockCheckout(locked) {
            if (locked) {
                checkoutControlStates = Array.from(
                    form.querySelectorAll(".product-checkbox, .quantity, [name='customer_id'], #payment_method")
                ).map(function (control) {
                    const state = { control: control, disabled: control.disabled };
                    control.disabled = true;
                    return state;
                });
                return;
            }

            checkoutControlStates.forEach(function (state) {
                state.control.disabled = state.disabled;
            });
            checkoutControlStates = [];
        }

        form.addEventListener("submit", function (event) {
            if (paymentMethod.value !== "midtrans_snap" || form.dataset.snapSubmitting === "true") {
                return;
            }

            event.preventDefault();

            if (form.dataset.snapProcessing === "true") {
                return;
            }

            if (form.dataset.snapEnabled !== "true") {
                showMessage(
                    "Snap Checkout belum dikonfigurasi. Periksa MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY di .env.",
                    "danger"
                );
                return;
            }

            if (!window.snap || typeof window.snap.pay !== "function") {
                showMessage(
                    "Snap Checkout tidak dapat dimuat. Periksa koneksi internet lalu muat ulang halaman.",
                    "danger"
                );
                return;
            }

            form.dataset.snapProcessing = "true";
            submitButton.disabled = true;
            submitButton.textContent = "Menghubungkan ke Midtrans...";
            message.className = "alert alert-info mt-2";
            message.textContent = "Menyiapkan pembayaran aman melalui Midtrans Snap...";

            const formData = new FormData(form);
            formData.delete("product_id[]");
            formData.delete("quantity[]");
            document.querySelectorAll(".product-row").forEach(function (row) {
                const checkbox = row.querySelector(".product-checkbox");
                const quantity = row.querySelector(".quantity");
                if (checkbox && checkbox.checked && quantity) {
                    formData.append("product_id[]", checkbox.value);
                    formData.append("quantity[]", quantity.value);
                }
            });
            lockCheckout(true);

            fetch("midtrans-snap-token.php", {
                method: "POST",
                headers: {
                    "Accept": "application/json"
                },
                body: formData
            })
                .then(function (response) {
                    return response.text().then(function (text) {
                        let result;
                        try {
                            result = JSON.parse(text);
                        } catch {
                            throw new Error("Respons server Snap tidak valid. Muat ulang halaman dan coba kembali.");
                        }

                        if (!response.ok) {
                            throw new Error(result.error || "Gagal menyiapkan pembayaran Snap.");
                        }
                        return result;
                    });
                })
                .then(function (result) {
                    if (!result.token || !result.order_id || !Number.isFinite(Number(result.amount))) {
                        throw new Error("Midtrans tidak mengembalikan detail checkout yang valid.");
                    }

                    window.snap.pay(result.token, {
                        onSuccess: function (paymentResult) {
                            if (paymentResult.order_id && paymentResult.order_id !== result.order_id) {
                                showMessage("ID pembayaran tidak cocok. Silakan hubungi administrator.", "danger");
                                lockCheckout(false);
                                resetSubmitButton();
                                form.dataset.snapProcessing = "false";
                                return;
                            }

                            lockCheckout(false);
                            paymentMethod.value = "midtrans_snap";
                            orderIdInput.value = result.order_id;
                            paymentAmount.value = String(result.amount);
                            document.querySelectorAll(".product-row").forEach(function (row) {
                                const checkbox = row.querySelector(".product-checkbox");
                                const quantity = row.querySelector(".quantity");
                                if (checkbox && quantity && !checkbox.checked) {
                                    quantity.disabled = true;
                                }
                            });
                            form.dataset.snapSubmitting = "true";
                            form.requestSubmit(submitButton);
                        },
                        onPending: function () {
                            showMessage(
                                "Pembayaran masih menunggu dan transaksi BEKUKU belum dicatat. Cek statusnya di dashboard Midtrans sebelum mencoba lagi agar tidak membayar dua kali.",
                                "warning"
                            );
                            lockCheckout(false);
                            resetSubmitButton();
                            form.dataset.snapProcessing = "false";
                        },
                        onError: function () {
                            showMessage("Pembayaran gagal diproses oleh Midtrans. Silakan coba kembali.", "danger");
                            lockCheckout(false);
                            resetSubmitButton();
                            form.dataset.snapProcessing = "false";
                        },
                        onClose: function () {
                            showMessage("Checkout ditutup sebelum pembayaran selesai.", "secondary");
                            lockCheckout(false);
                            resetSubmitButton();
                            form.dataset.snapProcessing = "false";
                        }
                    });
                })
                .catch(function (error) {
                    showMessage(error.message, "danger");
                    lockCheckout(false);
                    resetSubmitButton();
                    form.dataset.snapProcessing = "false";
                });
        });

        function updatePaymentAmount() {
            const isSnap = paymentMethod.value === "midtrans_snap";
            paymentAmount.required = !isSnap;
            paymentAmount.readOnly = isSnap;
            if (isSnap) {
                paymentAmount.value = "";
                orderIdInput.value = "";
            }
            message.classList.add("d-none");
        }

        paymentMethod.addEventListener("change", updatePaymentAmount);
        updatePaymentAmount();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializeSnapCheckout, { once: true });
    } else {
        initializeSnapCheckout();
    }
}());
