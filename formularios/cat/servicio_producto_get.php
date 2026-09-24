<?php
include '../../dbc.php';

$id = intval($_GET['id']);
$sql = "SELECT id, id_producto, cantidad FROM tbl_productos_servicios WHERE id = $id";
$res = mysqli_query($conn, $sql);
echo json_encode(mysqli_fetch_assoc($res));
