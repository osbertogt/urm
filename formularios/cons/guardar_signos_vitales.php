<?php
session_start();
require_once '../../assets/dbc.php';

// Verificar que la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    //exit;
}

// Validar datos requeridos
$required_fields = ['fecha', 'temperatura_corporal', 'presion_arterial', 
                   'frecuencia_respiratoria', 'frecuencia_cardiaca', 'saturacion_oxigeno'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        //echo json_encode(['success' => false, 'message' => "El campo $field es requerido"]);
        //exit;
    }
}

// Recoger y sanitizar datos
$id = isset($_POST['id_signos_vitales']) ? intval($_POST['id_signos_vitales']) : 0;
$id_atencion = isset($_POST['id_atencion']) ? intval($_POST['id_atencion']) : 0;
$id_cliente = $_SESSION['cliente_activo'];
$fecha = $conn->real_escape_string($_POST['fecha_signos']);
$temperatura = floatval($_POST['temperatura_corporal']);
$presion = $conn->real_escape_string($_POST['presion_arterial']);
$frec_respiratoria = intval($_POST['frecuencia_respiratoria']);
$frec_cardiaca = intval($_POST['frecuencia_cardiaca']);
$saturacion = intval($_POST['saturacion_oxigeno']);

// Validaciones adicionales
if ($temperatura < 34 || $temperatura > 42) {
    //echo json_encode(['success' => false, 'message' => 'La temperatura debe estar entre 34°C y 42°C']);
    //exit;
}

if ($saturacion < 0 || $saturacion > 100) {
    //echo json_encode(['success' => false, 'message' => 'La saturación de oxígeno debe estar entre 0% y 100%']);
    exit;
}

// Preparar la consulta según si es insertar o actualizar
if ($id === 0) {
    // Insertar nuevo registro
    $query = "INSERT INTO tbl_atencion_consulta_siv 
              (id_cliente, fecha, temperatura_corporal, presion_arterial, 
               frecuencia_respiratoria, frecuencia_cardiaca, saturacion_oxigeno) 
              VALUES ($id_cliente, '$fecha', $temperatura, '$presion', 
                      $frec_respiratoria, $frec_cardiaca, $saturacion)";
} else {
    // Actualizar registro existente
    $query = "UPDATE tbl_atencion_consulta_siv SET 
              fecha = '$fecha', 
              temperatura_corporal = $temperatura, 
              presion_arterial = '$presion', 
              frecuencia_respiratoria = $frec_respiratoria, 
              frecuencia_cardiaca = $frec_cardiaca, 
              saturacion_oxigeno = $saturacion 
              WHERE id = $id ";
}

// Ejecutar la consulta
if ($conn->query($query)) {
    echo json_encode(['success' => true, 'message' => 'Datos guardados correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $conn->error]);
}

$conn->close();
?>