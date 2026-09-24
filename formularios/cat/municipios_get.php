<?php
include '../../dbc.php';

$id = intval($_GET['id']);
$sql = "SELECT id, nombre, id_departamento FROM cat_municipio WHERE id = $id";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Municipio no encontrado']);
}
?>
