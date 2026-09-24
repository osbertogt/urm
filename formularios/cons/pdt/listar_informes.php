<?php
include_once '../../../assets/dbc.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['id_cliente'])) {
    echo json_encode(["data" => []]);
    exit;
}

$id_cliente = $_POST['id_cliente'];
$FORM_URL = $_POST['FORM_URL'];

$sql = "SELECT 
            asi.fecha,
            asi.nombre_original,
            asi.informe
        FROM tbl_atencion_servicio_informe asi
        INNER JOIN tbl_atencion a ON asi.id_atencion = a.id
        WHERE a.id_cliente = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_cliente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {

    $ruta = $FORM_URL."/uploads/reports/" . $row['informe'];

    // Botón de visualización
    $btnVer = '<a href="'.$ruta.'" target="_blank" class="btn btn-sm btn-primary">
                    <i class="bi bi-file-earmark-pdf"></i>
               </a>';

    $data[] = [
        "fecha" => htmlspecialchars($row['fecha']),
        "nombre_original" => htmlspecialchars($row['nombre_original']),
        "ver" => $btnVer
    ];
}

echo json_encode([
    "data" => $data
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
