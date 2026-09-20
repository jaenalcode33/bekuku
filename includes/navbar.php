<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a
                    class="nav-link"
                    data-lte-toggle="sidebar"
                    href="#"
                    role="button"
                >
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <?php $currentUser = bekuku_user(); ?>
            <li class="nav-item">
                <span class="nav-link bekuku-user-pill">
                    <i class="bi bi-person-circle"></i>
                    <span><?= htmlspecialchars($currentUser['name'] ?? 'Pengguna', ENT_QUOTES, 'UTF-8') ?></span>
                    <small><?= htmlspecialchars(bekuku_role_label($currentUser['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                </span>
            </li>
            <li class="nav-item">
                <a
                    class="nav-link bekuku-logout-link"
                    href="<?= bekuku_url('logout.php') ?>"
                    title="Keluar"
                    data-logout
                ><i class="bi bi-box-arrow-right"></i></a>
            </li>
        </ul>
    </div>
</nav>

<script>
    (function () {
        const logoutLink = document.querySelector("[data-logout]");

        if (!logoutLink) {
            return;
        }

        logoutLink.addEventListener("click", function (event) {
            event.preventDefault();

            const overlay = document.createElement("div");
            overlay.className = "bekuku-logout-overlay";
            overlay.setAttribute("role", "presentation");
            overlay.innerHTML =
                '<div class="bekuku-logout-dialog" role="dialog" aria-modal="true" aria-labelledby="bekuku-logout-title">' +
                '<div class="bekuku-logout-icon" style="background:linear-gradient(145deg,#315cff,#2649c7) !important;color:#ffffff !important;"><i class="bi bi-box-arrow-right"></i></div>' +
                '<h2 id="bekuku-logout-title">Konfirmasi Logout</h2>' +
                '<p>Apakah Anda yakin ingin keluar dari BEKUKU POS?</p>' +
                '<div class="bekuku-logout-actions">' +
                '<button type="button" class="bekuku-logout-cancel">Batal</button>' +
                '<a class="bekuku-logout-confirm bekuku-logout-danger" style="background:#dc3545 !important;color:#ffffff !important;border:0;" href="' + logoutLink.href + '">Logout</a>' +
                "</div>" +
                "</div>";

            document.body.appendChild(overlay);
            document.body.classList.add("modal-open");

            const close = function () {
                overlay.remove();
                document.body.classList.remove("modal-open");
            };

            overlay.querySelector(".bekuku-logout-cancel").addEventListener("click", close);
            overlay.addEventListener("click", function (modalEvent) {
                if (modalEvent.target === overlay) {
                    close();
                }
            });
        });
    }());

    document.addEventListener("submit", function (event) {
        const form = event.target.closest("[data-delete-confirm]");

        if (!form || form.dataset.deleteConfirmed === "true") {
            return;
        }

        event.preventDefault();

        const label = form.dataset.deleteLabel || "data ini";
        const overlay = document.createElement("div");
        overlay.className = "bekuku-logout-overlay bekuku-delete-overlay";
        overlay.innerHTML =
            '<div class="bekuku-logout-dialog" role="dialog" aria-modal="true" aria-labelledby="bekuku-delete-title">' +
            '<div class="bekuku-logout-icon bekuku-delete-icon" style="background:linear-gradient(145deg,#a65b63,#8e4650) !important;color:#ffffff !important;">' +
            '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 3 1.7 20.5c-.4.7.1 1.5.9 1.5h18.8c.8 0 1.3-.8.9-1.5L12 3Zm0 4.2 7.1 12.1H4.9L12 7.2ZM11 10v5h2v-5h-2Zm0 7v2h2v-2h-2Z"/></svg>' +
            '</div>' +
            '<h2 id="bekuku-delete-title">Hapus ' + label + '?</h2>' +
            '<p>Data ' + label.toLowerCase() + ' ini akan dihapus secara permanen.</p>' +
            '<div class="bekuku-logout-actions">' +
            '<button type="button" class="bekuku-logout-cancel">Batal</button>' +
            '<button type="button" class="bekuku-logout-confirm bekuku-delete-confirm" style="background:#dc3545 !important;color:#ffffff !important;border:0;">Hapus</button>' +
            "</div>" +
            "</div>";

        document.body.appendChild(overlay);
        document.body.classList.add("modal-open");

        const close = function () {
            overlay.remove();
            document.body.classList.remove("modal-open");
        };

        overlay.querySelector(".bekuku-logout-cancel").addEventListener("click", close);
        overlay.querySelector(".bekuku-delete-confirm").addEventListener("click", function () {
            form.dataset.deleteConfirmed = "true";
            close();
            form.submit();
        });
        overlay.addEventListener("click", function (modalEvent) {
            if (modalEvent.target === overlay) {
                close();
            }
        });
    });
</script>
