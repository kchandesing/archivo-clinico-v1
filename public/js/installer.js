document.addEventListener('DOMContentLoaded', function () {
    const installerForm = document.querySelector('#installerForm');
    const submitBtn = document.querySelector('#submitBtn');
    
    // Selectores para el botón de ver/ocultar contraseña
    const toggleMasterKeyBtn = document.querySelector('#toggleMasterKeyBtn');
    const masterKeyInput = document.querySelector('#master_key');
    const eyeIcon = document.querySelector('#eyeIcon');

    // 1. Lógica interactiva para Ver / Ocultar la Master Key
    if (toggleMasterKeyBtn && masterKeyInput) {
        toggleMasterKeyBtn.addEventListener('click', function () {
            // Cambiar dinámicamente el tipo de atributo
            const isPassword = masterKeyInput.getAttribute('type') === 'password';
            masterKeyInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Modificar visualmente el icono SVG del ojo (Ojo abierto vs Ojo tachado)
            if (isPassword) {
                eyeIcon.innerHTML = `
                    <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879.548 5.168 1.838A13.141 13.141 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                    <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-4.114 1.815-2.83-2.83-.182.182a3.5 3.5 0 0 0 4.714 4.714l-.183-.183z"/>
                    <path d="M4.653 12.135a6.836 6.836 0 0 1-1.9-1.594C1.178 9.21 0 8 0 8s3-5.5 8-5.5a7.025 7.025 0 0 1 2.81.587l-.49.49A5.977 5.977 0 0 0 8 3.5c-2.12 0-3.879.548-5.168 1.838A13.143 13.143 0 0 0 1.172 8c.058.087.122.183.195.288.338.484.84 1.133 1.481 1.775a6.27 6.27 0 0 0 1.1 1.001l-.52.522zm10.518 2.37A.5.5 0 0 1 15 14.5l-13-13a.5.5 0 0 1 .707-.707l13 13a.5.5 0 0 1-.707.707z"/>
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 4.12 8 4.12c2.12 0 3.879.548 5.168 1.838A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 11.88 8 11.88c-2.12 0-3.879-.548-5.168-1.838A13.133 13.133 0 0 1 1.173 8z"/>
                    <path d="M5.5 8a2.5 2.5 0 1 1 5 0 2.5 2.5 0 0 1-5 0z"/>
                `;
            }
        });
    }

    // 2. Control de envío para evitar peticiones duplicadas
    if (installerForm && submitBtn) {
        installerForm.addEventListener('submit', function () {
            if (installerForm.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Aprovisionando entorno y triggers...
                `;
            }
        });
    }
});
