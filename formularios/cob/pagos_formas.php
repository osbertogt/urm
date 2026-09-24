<?php
require '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT id, nombre FROM cat_formapago ORDER BY nombre";
$res = mysqli_query($conn, $sql);

$out = [];
while ($r = mysqli_fetch_assoc($res)) $out[] = $r;

echo json_encode($out, JSON_UNESCAPED_UNICODE);
mysqli_close($conn);
