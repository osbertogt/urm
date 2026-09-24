<?php
include '../../dbc.php';

$id = intval($_POST['id']);
$sql = "DELETE FROM cat_municipio WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    echo "OK";
} else {
    http_response_code(500);
    echo "Error al eliminar: " . mysqli_error($conn);
}
?>
