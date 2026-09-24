<?php
session_start();
include_once '../../assets/dbc.php';

$response = ['nombre' => 'Ninguno'];

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $_SESSION['cliente_activo'] = $id;

    $stmt = $conn->prepare("SELECT CONCAT_WS(' ',
			TRIM(c.nombre_1),
			TRIM(c.nombre_2),
			TRIM(c.apellido_1),
			TRIM(c.apellido_2),
			IF(TRIM(c.apellido_casada) IS NULL OR TRIM(c.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(c.apellido_casada))),
			' (',c.id,')') AS nombre 
			FROM tbl_persona c WHERE c.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $response['nombre'] = $row['nombre'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);

?>
