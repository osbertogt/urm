<?php
header('Content-Type: application/json');
include_once '../../assets/dbc.php';

$id = $_POST['id_cliente'] ?? '';
$nombre1 = $_POST['nombre'] ?? '';
$nombre2 = $_POST['nombre2'] ?? '';
$nombre3 = $_POST['nrespon'] ?? '';
$nombre4 = $_POST['nrespon2'] ?? '';
$apellido1 = $_POST['apellido1'] ?? '';
$apellido2 = $_POST['apellido2'] ?? '';
$apellido3 = $_POST['apellido3'] ?? '';
$sexo = $_POST['sexo'] ?? 0;
$fnac = $_POST['fnacimiento'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$departamento = $_POST['departamento'] ?? 0;
$municipio = $_POST['municipio'] ?? 0;
$aldea = $_POST['aldea'] ?? '';
$email = $_POST['email'] ?? '';
$dpi = $_POST['dpi'] ?? '';
$nit = $_POST['nit'] ?? '';
$edad_anios = $_POST['edad_anios'] ?? 0;
$edad_meses = $_POST['edad_meses'] ?? 0;
$id_etnia = $_POST['id_etnia'] ?? 0;
$id_motivo_consulta = $_POST['id_motivo_consulta'] ?? 0;
$tipop = 1;

// Validar solo si el campo NO está vacío
if (!empty(trim($dpi))) {
    // El campo tiene valor, validar que sean 12 dígitos
    if (!preg_match('/^\d{13}$/', $dpi)) {
        echo json_encode(['success' => false, 'message' => 'El campo "DPI" debe contener exactamente 13 dígitos numéricos']);
        exit;
    }
}

if ($id == '') {
    // NUEVO
    $sql = "INSERT INTO tbl_persona (nombre_1, nombre_2, nombre_3, nombre_4, apellido_1, apellido_2, apellido_casada, id_sexo, fecha_nacimiento, telefono, direccion_calle_avenida, id_departamento, id_municipio, aldea, email, dpi_cui, nit, id_tipopersona, edad_anios, edad_meses, id_etnia, id_motivo_consulta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssisssiissssiiiii", $nombre1, $nombre2, $nombre3, $nombre4, $apellido1, $apellido2, $apellido3, $sexo, $fnac, $telefono, $direccion, $departamento, $municipio, $aldea, $email, $dpi, $nit, $tipop, $edad_anios, $edad_meses, $id_etnia, $id_motivo_consulta);
} else {
    // EDITAR
    $sql = "UPDATE tbl_persona SET nombre_1=?, nombre_2=?, nombre_3=?, nombre_4=?, apellido_1=?, apellido_2=?, apellido_casada=?, id_sexo=?, fecha_nacimiento=?, telefono=?, direccion_calle_avenida=?, id_departamento=?, id_municipio=?, id_aldea=?, email=?, dpi_cui=?, nit=?, edad_anios=?, edad_meses=?, id_etnia=?, id_motivo_consulta=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssisssiissssiiiii", $nombre1, $nombre2, $nombre3, $nombre4, $apellido1, $apellido2, $apellido3, $sexo, $fnac, $telefono, $direccion, $departamento, $municipio, $aldea, $email, $dpi, $nit, $edad_anios, $edad_meses, $id_etnia, $id_motivo_consulta, $id);
}
if ($stmt->execute()) {
    $nuevo_cliente_id = mysqli_insert_id($conn);
    echo json_encode([
        'status' => 'ok', 
        'message' => 'Cliente guardado correctamente',
        'cliente_id' => $nuevo_cliente_id
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>