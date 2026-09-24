<?php
include_once '../../assets/dbc.php';

if (isset($_GET['departamento_id'])) {
    $departamento_id = intval($_GET['departamento_id']);
    $sql = "SELECT id, nombre FROM cat_municipio WHERE id_departamento = $departamento_id ORDER BY nombre";
    $result = mysqli_query($conn, $sql);
    echo "<option value=''>Seleccione</option>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
    }

} elseif (isset($_GET['municipio_id'])) {
    $municipio_id = intval($_GET['municipio_id']);
    $sql = "SELECT id, nombre FROM cat_aldea WHERE id_municipio = $municipio_id ORDER BY nombre";
    $result = mysqli_query($conn, $sql);
    echo "<option value=''>Seleccione</option>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
    }

} else {
    echo "<option value=''>Seleccione</option>";
}
?>
