<?php
require_once '../../assets/dbc.php';
$id = intval($_GET['id']);
$res = mysqli_query($conn, "SELECT id, nombre, precio FROM cat_paquete WHERE id = $id");

echo json_encode(mysqli_fetch_assoc($res));
?>
