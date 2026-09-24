<?php
include '../../dbc.php';
$id = intval($_POST['id']);
mysqli_query($conn, "DELETE FROM cat_paquete_servicios WHERE id_paquete = $id");
mysqli_query($conn, "DELETE FROM cat_paquete WHERE id = $id");
?>
