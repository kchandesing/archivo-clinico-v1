document.addEventListener('DOMContentLoaded', function () {
    const sidebarCollapseBtn = document.querySelector('#sidebarCollapse');
    const sidebar = document.querySelector('#sidebar');

    if (sidebarCollapseBtn && sidebar) {
        sidebarCollapseBtn.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }
});
