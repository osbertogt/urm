<?php
// obtener_cita.php
require_once '../../assets/dbc.php';
$id = $_GET['id'] ?? null;
if ($id) {
    $sql = "SELECT a.*,b.id_especialidad 
	FROM tbl_cita a
	JOIN tbl_persona b ON b.id=a.id_medico
	WHERE a.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
}
?>