<?php
include_once '../../assets/dbc.php';

$sql = "SELECT id, nombre FROM cat_departamento ORDER BY nombre";
$result = mysqli_query($conn, $sql);

$options = "<option value=''>Seleccione</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $options .= "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
}
echo $options;
?>