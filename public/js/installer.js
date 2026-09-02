document.addEventListener('DOMContentLoaded', function () {
    const installerForm = document.querySelector('#installerForm');
    const submitBtn = document.querySelector('#submitBtn');

    if (installerForm && submitBtn) {
        installerForm.addEventListener('submit', function () {
            // Validar si el formulario nativo es correcto antes de bloquear
            if (installerForm.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Procesando infraestructura y triggers...
                `;
            }
        });
    }
});
