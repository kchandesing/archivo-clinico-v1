document.addEventListener('DOMContentLoaded', function () {
    const sidebarCollapseBtn = document.querySelector('#sidebarCollapse');
    const sidebar = document.querySelector('#sidebar');

    if (sidebarCollapseBtn && sidebar) {
        sidebarCollapseBtn.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }
});


document.addEventListener('DOMContentLoaded', function (){
    const checkPinp = document.querySelector('#flexCheckDefault'); 
    const inpPn = document.getElementById("paisn");

    // Es mejor usar el evento 'change' para checkboxes
    checkPinp.addEventListener('change', function () {
        
        // 2. CORRECCIÓN: Usa triple igual '===' para comparar. 
        // Además, puedes simplificarlo dejando solo (checkPinp.checked)
        if (checkPinp.checked) {
            inpPn.removeAttribute('readonly');
            console.log("Se ha removido el atributo de lectura");
        }
        else {
            // 3. MEJORA: Para elementos booleanos como readonly, basta con pasar el string 'readonly'
            inpPn.setAttribute('readonly', 'readonly');
            console.log("Se mantiene el atributo de lectura");
        }
    });
})