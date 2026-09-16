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
                '<div class="bekuku-logout-icon"><i class="bi bi-box-arrow-right"></i></div>' +
                '<h2 id="bekuku-logout-title">Konfirmasi Logout</h2>' +
                '<p>Apakah Anda yakin ingin keluar dari BEKUKU POS?</p>' +
                '<div class="bekuku-logout-actions">' +
                '<button type="button" class="bekuku-logout-cancel">Batal</button>' +
                '<a class="bekuku-logout-confirm" href="' + logoutLink.href + '">Logout</a>' +
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
</script>
