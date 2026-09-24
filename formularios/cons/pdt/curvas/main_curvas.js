// main_curvas.js
$(document).ready(function() {
    // Incluir este script en tu formulario principal
    
    // Agregar tab a los tabs existentes
    const tabHTML = `
        <li class="nav-item">
            <button class="nav-link" id="tabCurvasOMS-tab" data-bs-toggle="tab" data-bs-target="#tabCurvasOMS" type="button" role="tab">
                <i class="bi bi-graph-up-arrow"></i> Curvas OMS
            </button>
        </li>
    `;
    
    // Insertar antes del último tab
    $('#tabHistorial-tab').closest('li').before(tabHTML);
    
    // Incluir los archivos necesarios
    const styles = `
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.min.css">
        <style>
            ${/* Incluir aquí el contenido de curvas_oms.css */ ''}
        </style>
    `;
    
    $('head').append(styles);
    
    // Cargar Chart.js si no está cargado
    if (typeof Chart === 'undefined') {
        $.getScript('https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js', function() {
            initCurvasOMS();
        });
    } else {
        initCurvasOMS();
    }
    
    function initCurvasOMS() {
        // Inicializar sistema de curvas
        window.curvasOMS = new CurvasCrecimientoOMS();
        
        // Recargar datos cuando se muestre el modal
        $('#modalAtencion').on('shown.bs.modal', function() {
            setTimeout(() => {
                if (window.curvasOMS && $('#tabCurvasOMS').hasClass('active')) {
                    window.curvasOMS.cargarDatosPaciente();
                }
            }, 300);
        });
    }
});