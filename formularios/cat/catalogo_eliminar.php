<?php
require_once '../../assets/dbc.php';
$tabla = $_POST['tabla'];
$id = intval($_POST['id']);
mysqli_query($conn, "DELETE FROM $tabla WHERE id = $id");
echo "OK";
