<?php
session_start();
require_once '../../assets/dbc.php';

// Verificar que la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar datos requeridos
$required_fields = ['fecha', 'id_servicio', 'resultado'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "El campo $field es requerido"]);
        exit;
    }
}

// Recoger y sanitizar datos
$id = isset($_POST['id_laboratorio']) ? intval($_POST['id_laboratorio']) : 0;
$id_atencion = isset($_POST['id_atencion']) ? intval($_POST['id_atencion']) : 0;
$id_cliente = $_SESSION['cliente_activo'];
$fecha = $mysqli->real_escape_string($_POST['fecha']);
$id_servicio = intval($_POST['id_servicio']);
$resultado = $mysqli->real_escape_string($_POST['resultado']);

// Manejar la subida de archivos
$informe = '';
if (isset($_FILES['informe_pdf']) && $_FILES['informe_pdf']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['informe_pdf'];
    
    // Validar tipo de archivo
    $allowed_types = ['application/pdf'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos PDF']);
        exit;
    }
    
    // Validar tamaño (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'El archivo no puede ser mayor a 5MB']);
        exit;
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $informe = 'informe_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_path = '../../informes/' . $informe;
    
    // Crear directorio si no existe
    if (!file_exists('../../informes')) {
        mkdir('../../informes', 0777, true);
    }
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
        exit;
    }
}

// Preparar la consulta según si es insertar o actualizar
if ($id === 0) {
    // Insertar nuevo registro
    if ($informe) {
        $query = "INSERT INTO tbl_laboratorios 
                  (id_cliente, id_atencion, fecha, id_servicio, resultado, informe) 
                  VALUES ($id_cliente, $id_atencion, '$fecha', $id_servicio, '$resultado', '$informe')";
    } else {
        $query = "INSERT INTO tbl_laboratorios 
                  (id_cliente, id_atencion, fecha, id_servicio, resultado) 
                  VALUES ($id_cliente, $id_atencion, '$fecha', $id_servicio, '$resultado')";
    }
} else {
    // Actualizar registro existente - primero obtener el informe actual si existe
    $current_informe = '';
    if ($id > 0) {
        $check_query = "SELECT informe FROM tbl_laboratorios WHERE id = $id";
        $check_result = $mysqli->query($check_query);
        if ($check_result && $check_result->num_rows > 0) {
            $current_data = $check_result->fetch_assoc();
            $current_informe = $current_data['informe'];
        }
    }
    
    // Si se subió un nuevo archivo, eliminar el anterior
    if ($informe && $current_informe) {
        $old_file_path = '../../informes/' . $current_informe;
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }
    }
    
    // Construir la consulta de actualización
    if ($informe) {
        $query = "UPDATE tbl_laboratorios SET 
                  fecha = '$fecha', 
                  id_servicio = $id_servicio, 
                  resultado = '$resultado', 
                  informe = '$informe',
                  id_atencion = $id_atencion
                  WHERE id = $id AND id_cliente = $id_cliente";
    } else {
        $query = "UPDATE tbl_laboratorios SET 
                  fecha = '$fecha', 
                  id_servicio = $id_servicio, 
                  resultado = '$resultado',
                  id_atencion = $id_atencion
                  WHERE id = $id AND id_cliente = $id_cliente";
    }
}

// Ejecutar la consulta
if ($mysqli->query($query)) {
    echo json_encode(['success' => true, 'message' => 'Datos guardados correctamente']);
} else {
    // Si hubo error y se subió un archivo, eliminarlo
    if ($informe) {
        $file_path = '../../informes/' . $informe;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $mysqli->error]);
}

$mysqli->close();
?>