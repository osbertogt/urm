<?php
session_start();
require_once '../../assets/dbc.php';
?>
<!doctype html>
<html lang="es">
<head>
  <style>
    #sectionInformes { display: none; margin-top: 1rem; }
    #visorPdf { width:100%; height:600px; border:1px solid #ddd; }
    .small-note { font-size: .85rem; color:#666; }
  </style>
</head>
<body>
<div class="container py-4">

  <div class="row">
    <div class="col-md-8"><h3>Analisis de laboratorio</h3></div>
  </div>

  <div class="row">
    <div class="col-12">
      <table id="tblServicios" class="table table-striped table-bordered w-100">
        <thead>
          <tr>
            <th>Id</th>
            <th>Fecha</th>
            <th># Orden</th>
            <th>Paciente</th>
            <th>DPI</th>
            <th>Servicio</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- sección de informes (aparece al hacer clic en "Informes") -->
  <div id="sectionInformes" class="row">
    <div class="col-12">
      <div class="card mt-3">
        <div class="card-header bg-info text-white">
          <i class="bi bi-file-earmark-pdf"></i> Informes para:
          <span id="lblServicioSeleccionado"></span>
        </div>
        <div class="card-body">
          <!-- tabla de informes -->
          <table id="tblInformes" class="table table-sm table-bordered w-100 mb-3">
            <thead>
              <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Archivo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>

          <!-- uploader -->
          <form id="formUploadInforme" enctype="multipart/form-data" class="mb-3">
            <input type="hidden" name="id_atencion" id="u_id_atencion">
            <input type="hidden" name="id_servicio" id="u_id_servicio">

            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label">Seleccionar archivo PDF</label>
                <input type="file" class="form-control" name="informe_pdf" id="informe_pdf" accept="application/pdf" required>
                <div class="small-note">Solo archivos PDF. Max 5 MB.</div>
              </div>
              <div class="col-md-2">
                <label class="form-label">Cargar informe</label>
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-upload"></i> Cargar Informe
                </button>
              </div>
            </div>
            <div class="row g-2">
              <div class="col-md-10">
              </div>
              <div class="col-md-2">
                <button id="btnOcultarInformes" class="btn btn-outline-secondary">Ocultar sección</button>
              </div>
            </div>
          </form>

          <!-- visor PDF -->
          <div id="visorContainer" style="display:none;">
            <h6>Visor</h6>
            <iframe id="visorPdf" src="" frameborder="0"></iframe>
          </div>

        </div>
      </div>
    </div>
  </div>


<script>
$(function(){
  const ajaxUrl = FORM_URL+'lab/ajax_reports.php';
  
  // Variable global para el DataTable principal
  let tblServicios = null;
  
  // Función para recargar tabla principal
  function recargarTablaServicios() {
    if (tblServicios) {
        tblServicios.ajax.reload(null, false);
        console.log('Tabla principal recargada');
    }
  }
  
  // Función para mostrar notificaciones
  function mostrarNotificacion(mensaje, tipo = 'success') {
    // Puedes usar SweetAlert2, Toast de Bootstrap, o un simple alert
    //alert(mensaje);
  }

  // Inicializar DataTable principal
  tblServicios = $('#tblServicios').DataTable({
    ajax: { 
      url: ajaxUrl, 
      type: 'POST', 
      data: { action: 'list_servicios' }, 
      dataSrc: '' 
    },
    columns: [
      { data: 'id_atencion' },
      { data: 'fecha' },
      { data: 'numero_ticket' },
      { data: 'paciente' },
      { data: 'dpi' },
      { data: 'servicio' },
      {
        data: null,
        orderable: false,
        render: function (d) {
          // Determinar color según existe_informe
          const btnClass = (parseInt(d.existe_informe) > 0)
            ? 'btn-success'  // verde si tiene informe
            : 'btn-warning'; // amarillo si no tiene informe

          return `
            <button class="btn btn-sm ${btnClass} btn-informes"
              data-id_atencion="${d.id_atencion}"
              data-id_servicio="${d.id_servicio}"
              data-numero_ticket="${d.numero_ticket}"
              data-servicio="${escapeHtml(d.servicio)}"
              data-existe_informe="${d.existe_informe}">
              <i class="bi bi-folder2-open"></i> Ver / Cargar informes
            </button>
          `;
        }
      }
    ],
    order: [[0,'desc']]
  });

  // helper escape
  function escapeHtml(s){
    return String(s||'').replace(/[&<>"'\/]/g,function (c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'}[c];
    });
  }

  // Abrir sección de informes al pulsar botón
  $(document).on('click', '.btn-informes', function(){
    const id_atencion = $(this).data('id_atencion');
    const numero_ticket = $(this).data('numero_ticket');
    const id_servicio = $(this).data('id_servicio');
    const servicio = $(this).data('servicio');

    // Set hidden inputs
    $('#u_id_atencion').val(id_atencion);
    $('#u_id_servicio').val(id_servicio);
    $('#lblServicioSeleccionado').text(`# Orden ${numero_ticket} — ${servicio}`);

    // Mostrar sección
    $('#sectionInformes').show();
    $('#visorContainer').hide();
    $('#visorPdf').attr('src','');

    // Cargar lista de informes
    loadInformes(id_atencion, id_servicio);
    // Scroll hacia la sección
    $('html,body').animate({scrollTop: $('#sectionInformes').offset().top - 20}, 300);
  });

  $('#btnOcultarInformes').click(function(e){
    e.preventDefault();
    $('#sectionInformes').hide();
  });

  // cargar informes
  function loadInformes(id_atencion, id_servicio){
    $.getJSON(ajaxUrl, { action: 'list_informes', id_atencion, id_servicio }, function(data){
      const tbody = $('#tblInformes tbody').empty();
      if(!Array.isArray(data) || data.length === 0){
        tbody.append('<tr><td colspan="4" class="text-center">No hay informes</td></tr>');
        return;
      }
      data.forEach(function(r){
        const tr = $('<tr>');
        tr.append($('<td>').text(r.id));
        tr.append($('<td>').text(r.fecha));
        tr.append($('<td>').text(r.nombre_original));
        // acciones: ver, descargar, eliminar
        const btns = $('<td>');
        btns.append(`<button class="btn btn-sm btn-outline-primary me-1 btn-ver" data-id="${r.id}"><i class="bi bi-eye"></i></button>`);
        btns.append(`<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${r.id}"><i class="bi bi-trash"></i></button>`);
        tr.append(btns);
        tbody.append(tr);
      });
    }).fail(function(xhr){
      alert('Error al cargar informes');
    });
  }

  // ver PDF en iframe (visor)
  $(document).on('click', '.btn-ver', function(){
    const id = $(this).data('id');
    $('#visorPdf').attr('src',FORM_URL+'lab/serve_report.php?id=' + encodeURIComponent(id));
    $('#visorContainer').show();
    // scroll a visor
    $('html,body').animate({scrollTop: $('#visorContainer').offset().top - 20}, 300);
  });

  // eliminar informe
  $(document).on('click', '.btn-eliminar', function(){
    if(!confirm('Eliminar este informe?')) return;
    const id = $(this).data('id');
    const id_atencion = $('#u_id_atencion').val();
    
    $.post(ajaxUrl, { action: 'delete_informe', id }, function(resp){
        if(resp && resp.success){
            // 1. Recargar tabla de informes
            loadInformes(id_atencion, $('#u_id_servicio').val());
            
            // 2. Recargar tabla principal
            recargarTablaServicios();
            
            // 3. Opcional: mostrar notificación
            mostrarNotificacion('Informe eliminado correctamente', 'success');
        } else {
            alert('Error al eliminar: ' + (resp && resp.message ? resp.message : ''));
        }
    }, 'json').fail(function(){ alert('Error de conexión'); });
  });

  // subir informe
  $('#formUploadInforme').submit(function(e){
    e.preventDefault();
    const id_atencion = $('#u_id_atencion').val();
    const id_servicio = $('#u_id_servicio').val();
    if(!id_atencion || !id_servicio){ alert('Selecciona un servicio primero'); return; }

    const file = $('#informe_pdf')[0].files[0];
    if(!file){ alert('Selecciona un PDF'); return; }
    if(file.type !== 'application/pdf'){ alert('Solo PDF permitido'); return; }
    if(file.size > 5 * 1024 * 1024){ alert('Máx 5 MB'); return; }

    const fd = new FormData(this);
    fd.append('action','upload_informe');

    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: fd,
        processData: false, 
        contentType: false,
        success: function(resp){
            if(resp && resp.success){
                $('#informe_pdf').val('');
                
                // 1. Recargar tabla de informes
                loadInformes(id_atencion, id_servicio);
                
                // 2. Recargar tabla principal
                recargarTablaServicios();
                
                // 3. Opcional: mostrar notificación
                mostrarNotificacion('Informe subido correctamente', 'success');
            } else {
                alert('Error: ' + (resp && resp.message ? resp.message : 'Error desconocido'));
            }
        },
        error: function(){ 
            alert('Error en la subida'); 
        }
    });
  });

});
</script>
</div>
</body>
</html>