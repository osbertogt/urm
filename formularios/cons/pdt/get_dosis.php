<?php
require_once "../../../assets/dbc.php";

$sql = "SELECT id, nombre FROM cat_dosis ORDER BY nombre";
$res = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}
echo json_encode($data);
