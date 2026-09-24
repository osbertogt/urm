<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
<div class="container mt-4">
  <div class="d-flex mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPersona"><i class="bi bi-plus-circle"></i> Agregar médico</button>
  </div>
  <h5>Médicos registrados</h5>
  <table id="tablaPersonas" class="table table-sm table-bordered table-striped" style="width:100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre Completo</th>
        <th>Sexo</th>
        <th>Dirección</th>
        <th>Municipio</th>
        <th>Departamento</th>
        <th>Teléfono</th>
        <th>Email</th>
        <th>Especialidad</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalPersona" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="formPersona" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo médico</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <input class="form-control" name="nombre_1" placeholder="Primer Nombre" required>
        </div>
        <div class="col-md-6">
          <input class="form-control" name="nombre_2" placeholder="Segundo Nombre">
        </div>
        <div class="col-md-6">
          <input class="form-control" name="apellido_1" placeholder="Primer Apellido" required>
        </div>
        <div class="col-md-6">
          <input class="form-control" name="apellido_2" placeholder="Segundo Apellido">
        </div>
        <div class="col-md-6">
          <input class="form-control" name="apellido_casada" placeholder="Apellido de Casada">
        </div>
        <div class="col-md-6">
          <input class="form-control" name="direccion_calle_avenida" placeholder="Dirección">
        </div>
        <div class="col-md-6">
          <select class="form-select" name="id_sexo" id="selectSexo" required></select>
        </div>
        <div class="col-md-6">
          <select class="form-select" name="id_especialidad" id="selectEspecialidad" required></select>
        </div>
        <div class="col-md-6">
          <select class="form-select select2" name="id_departamento" id="selectDepartamento" ></select>
        </div>
        <div class="col-md-6">
          <select class="form-select select2" name="id_municipio" id="selectMunicipio" ></select>
        </div>
        <div class="col-md-6">
          <input class="form-control" name="telefono" placeholder="Teléfono">
        </div>
        <div class="col-md-6">
          <input class="form-control" name="email" placeholder="Correo Electrónico">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>


<script>
	$(document).ready(function () {
	  const tabla = $('#tablaPersonas').DataTable({
		ajax: FORM_URL + 'per/obtener_personas.php',
		columns: [
		  { data: 'id' },
		  { data: 'nombre_completo' },
		  { data: 'sexo' },
		  { data: 'direccion' },
		  { data: 'municipio' },
		  { data: 'departamento' },
		  { data: 'telefono' },
		  { data: 'email' },
		  { data: 'especialidad' },
		{
			data: null,
			render: function (data) {
				return `
				<button class="btn btn-sm btn-warning btnEditar" data-id="${data.id}" data-toggle='tooltip' title='Editar'><i class="bi bi-pencil-square"></i></button>
				<button class="btn btn-sm btn-danger btnEliminar" data-id="${data.id}" data-toggle='tooltip' title='Eliminar'><i class="bi bi-trash"></i></button>
				`;
			}
		}
		]
	  });

	// Cargar selects
	function cargarSelect(url, selector, placeholder) {
	$.getJSON(url, function (data) {
	  $(selector).empty().append(`<option value="">${placeholder}</option>`);
	  $.each(data.results, function (i, item) {
		$(selector).append(`<option value="${item.id}">${item.text}</option>`);
	  });
	});
	}

	cargarSelect(FORM_URL+'per/get_sexos.php', '#selectSexo', 'Seleccione Sexo');
	cargarSelect(FORM_URL+'per/get_especialidades.php', '#selectEspecialidad', 'Seleccione Especialidad');

	$.fn.select2.defaults.set('dropdownParent', $('.modal'));
	$('#selectDepartamento').select2({
	  ajax: {
		url: FORM_URL+'per/get_departamentos.php',
		dataType: 'json',
		processResults: function (data) {
		  return data; 
		}
	  },
	  placeholder: 'Seleccione Departamento',
	  allowClear: true
	});

	$('#selectMunicipio').select2();

	$('#selectDepartamento').on('change', function() {
		const idDepto = $(this).val();
		
		if (idDepto) {
			$('#selectMunicipio').prop('disabled', false);
			
			$('#selectMunicipio').html('<option value="">Cargando municipios...</option>');
			
			$.getJSON(FORM_URL + `per/get_municipios.php?id_departamento=${idDepto}`, function(data) {
				if (data.error) {
					$('#selectMunicipio').html(`<option value="">Error: ${data.message}</option>`);
					return;
				}
				
				let options = '<option value="">Seleccione Municipio</option>';
				
				$.each(data, function(i, item) {
					options += `<option value="${item.id}">${item.nombre}</option>`;
				});
				
				$('#selectMunicipio').html(options);
			}).fail(function() {
				$('#selectMunicipio').html('<option value="">Error al cargar municipios</option>');
			});
		} else {
			$('#selectMunicipio').html('<option value="">Seleccione un departamento primero</option>')
								.prop('disabled', true);
		}
	});
	// Guardar persona
$('#formPersona').on('submit', function (e) {
    e.preventDefault();
    
    const data = $(this).serialize();
    
    $.ajax({
        url: FORM_URL + 'per/guardar_persona.php',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                $('#modalPersona').modal('hide');
                tabla.ajax.reload();
                $('#formPersona')[0].reset();
                $('#selectDepartamento, #selectMunicipio').val(null).trigger('change');
                
                if ($('#id_persona').length) {
                    $('#id_persona').remove();
                }
                
                //alert(res.message);
            } else {
                alert('Error: ' + res.error);
            }
        },
        error: function() {
            alert('Error de conexión');
        }
    });
});
	
	// Eliminar persona
	$('#tablaPersonas').on('click', '.btnEliminar', function () {
	if (confirm('¿Seguro que desea eliminar este registro?')) {
	  const id = $(this).data('id');
  		$.post(FORM_URL +'per/eliminar_persona.php', { id: id }, function (res) {
			tabla.ajax.reload();
  		});
	}
	});

// Editar persona 
$('#tablaPersonas').on('click', '.btnEditar', function () {
    const id = $(this).data('id');
    
    // Cambiar título del modal
    $('.modal-title').text('Editar médico');
    
    // Resetear formulario
    $('#formPersona')[0].reset();
    $('#selectDepartamento, #selectMunicipio').val(null).trigger('change');
    
    // Obtener datos de la persona
    $.get(FORM_URL + 'per/get_persona.php', { id: id }, function (data) {
        const persona = JSON.parse(data);
        
        // Llenar el formulario con los datos
        $('input[name="nombre_1"]').val(persona.nombre_1 || '');
        $('input[name="nombre_2"]').val(persona.nombre_2 || '');
        $('input[name="apellido_1"]').val(persona.apellido_1 || '');
        $('input[name="apellido_2"]').val(persona.apellido_2 || '');
        $('input[name="apellido_casada"]').val(persona.apellido_casada || '');
        $('input[name="direccion_calle_avenida"]').val(persona.direccion_calle_avenida || '');
        $('input[name="telefono"]').val(persona.telefono || '');
        $('input[name="email"]').val(persona.email || '');
        
        // Selects simples
        $('#selectSexo').val(persona.id_sexo || '');
        $('#selectEspecialidad').val(persona.id_especialidad || '');
        
        // Cargar departamento y municipio (Select2)
        if (persona.id_departamento) {
            // Primero cargar el departamento
            $.ajax({
                url: FORM_URL + 'per/get_departamentos.php',
                dataType: 'json'
            }).done(function(deptos) {
                // Buscar el departamento en los resultados
                const depto = deptos.results.find(d => d.id == persona.id_departamento);
                if (depto) {
                    // Crear nueva opción y seleccionar
                    const newOption = new Option(depto.text, persona.id_departamento, true, true);
                    $('#selectDepartamento').append(newOption).trigger('change');
                    
                    // Luego cargar municipios
                    setTimeout(function() {
                        $.getJSON(FORM_URL + `per/get_municipios.php?id_departamento=${persona.id_departamento}`, function(municipios) {
                            const municipio = municipios.find(m => m.id == persona.id_municipio);
                            if (municipio) {
                                const newOptionMun = new Option(municipio.nombre, persona.id_municipio, true, true);
                                $('#selectMunicipio').append(newOptionMun).trigger('change');
                            }
                        });
                    }, 500);
                }
            });
        }
        
        // Agregar campo hidden para el ID (importante para la actualización)
        if (!$('#id_persona').length) {
            $('#formPersona').prepend('<input type="hidden" name="id" id="id_persona">');
        }
        $('#id_persona').val(persona.id);
        
        // Mostrar el modal
        $('#modalPersona').modal('show');
        
    }).fail(function() {
        alert('Error al cargar los datos de la persona');
    });
});	
	
});
</script>
</body>
</html>
