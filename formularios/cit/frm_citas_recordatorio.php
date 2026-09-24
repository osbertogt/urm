<!DOCTYPE html>
<html lang="es">
<head>
  <style>
    /* pequeño ajuste para checkboxes centrados */
    table.dataTable td, table.dataTable th { vertical-align: middle; }
  </style>
</head>
<body>
<div class="container mt-4">
  <h4>Enviar recordatorios - Citas</h4>

  <div class="row mb-2 g-2">
    <div class="col-auto">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="optWhatsapp" checked>
        <label class="form-check-label" for="optWhatsapp"><i class="bi bi-whatsapp"></i> WhatsApp</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="optEmail">
        <label class="form-check-label" for="optEmail"><i class="bi bi-envelope"></i> Correo</label>
      </div>
    </div>

    <div class="col text-end">
      <button id="btnEnviarRecordatorios" class="btn btn-primary">
        <i class="bi bi-bell"></i> Enviar recordatorios seleccionados
      </button>
    </div>
  </div>

  <table id="tblCitas" class="table table-striped table-bordered" style="width:100%">
    <thead>
      <tr>
        <th><input type="checkbox" id="chkAll"></th>
        <th>ID</th>
        <th>Médico</th>
        <th>Fecha</th>
        <th>Hora</th>
        <th>Paciente</th>
        <th>Teléfono</th>
        <th>Email</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  // Ajusta esta constante a la ruta base de tu app si usas una

  $(document).ready(function() {
    const table = $('#tblCitas').DataTable({
      ajax: {
        url: FORM_URL + 'cit/listar_vw_citas.php', 
        type: 'GET',
        dataSrc: ''
      },
      columns: [
        {
          data: null,
          orderable: false,
          render: function (d) {
            // checkbox con data-* para reconstruir el mensaje
            return `<input type="checkbox" class="chk-cita" 
                      data-id="${d.id}"
                      data-medico="${escapeHtml(d.medico)}"
                      data-fecha="${d.fecha}"
                      data-hora="${d.hora}"
                      data-paciente="${escapeHtml(d.paciente_nombre)}"
                      data-phone="${escapeHtml(d.paciente_telefono)}"
                      data-email="${escapeHtml(d.paciente_email)}">`;
          }
        },
        { data: 'id' },
        { data: 'medico' },
        { data: 'fecha' },
        { data: 'hora' },
        { data: 'paciente_nombre' },
        { data: 'paciente_telefono' },
        { data: 'paciente_email' },
        { data: 'estado' }
      ],
      order: [[2, 'asc'], [3, 'asc']], 
      pageLength: 25
    });

    // Master checkbox
    $('#chkAll').on('change', function() {
      const checked = $(this).is(':checked');
      $('#tblCitas tbody .chk-cita').prop('checked', checked);
    });

    // Mantener master checkbox sincronizado al cambiar filas
    $('#tblCitas tbody').on('change', '.chk-cita', function() {
      const total = $('#tblCitas tbody .chk-cita').length;
      const checked = $('#tblCitas tbody .chk-cita:checked').length;
      $('#chkAll').prop('checked', total && checked === total);
    });

    // Botón enviar recordatorios
    $('#btnEnviarRecordatorios').on('click', function() {
      const sendWhats = $('#optWhatsapp').is(':checked');
      const sendEmail = $('#optEmail').is(':checked');

      if (!sendWhats && !sendEmail) {
        alert('Seleccione al menos un canal (WhatsApp o Correo).');
        return;
      }

      // recolectar seleccionados
      const items = [];
      $('#tblCitas tbody .chk-cita:checked').each(function() {
        items.push({
          id_cita: $(this).data('id'),
          medico: $(this).data('medico'),
          fecha: $(this).data('fecha'),
          hora: $(this).data('hora'),
          paciente: $(this).data('paciente'),
          phone: $(this).data('phone'),
          email: $(this).data('email')
        });
      });

      if (!items.length) { alert('Seleccione al menos una cita.'); return; }

      // construir mensajes y proceder
      // enviar la petición al servidor para registrar (y opcionalmente enviar email desde servidor)
      // también abrimos las ventanas de WhatsApp desde el cliente si corresponde
      const payload = {
        items: items,
        channels: { whatsapp: sendWhats ? 1 : 0, email: sendEmail ? 1 : 0 },
        client_datetime: new Date().toISOString()
      };

      // Guardar/Enviar (AJAX)
      $.ajax({
        url: FORM_URL + 'cit/reminders.php',
        method: 'POST',
        data: JSON.stringify(payload),
        contentType: 'application/json; charset=utf-8',
        dataType: 'json'
      }).done(function(res) {
        if (!res || !res.success) {
          alert('Error al procesar recordatorios: ' + (res && res.message ? res.message : 'error desconocido'));
          return;
        }

        // respuesta OK: res.results contiene info por item
        // Abrir links de WhatsApp en nuevas pestañas (uno por paciente) — se abre después de guardar
        if (sendWhats) {
          res.results.forEach(r => {
            if (r.whatsapp_url) {
              // abrir en nueva pestaña (evitar bloqueo popup: abrir con delay en loop pequeño; o abrir en respuesta a click)
              window.open(r.whatsapp_url, '_blank');
            }
          });
        }

        alert('Recordatorios procesados. Registros guardados: ' + res.saved_count);
        // reload table si quieres
        table.ajax.reload(null, false);
      }).fail(function(xhr, status, err) {
        console.error(xhr.responseText);
        alert('Error de comunicación con el servidor');
      });
    });

    // función util: escapar HTML para atributos
    function escapeHtml(s) {
      if (s === null || s === undefined) return '';
      return String(s).replace(/&/g, '&amp;')
                      .replace(/"/g, '&quot;')
                      .replace(/</g, '&lt;')
                      .replace(/>/g, '&gt;');
    }
  });
</script>
</body>
</html>
