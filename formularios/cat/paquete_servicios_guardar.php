<?php
require_once '../../assets/dbc.php';
$id_paquete = intval($_POST['id_paquete']);
$servicios = $_POST['servicios']; // array

foreach ($servicios as $id_servicio) {
  $id_servicio = intval($id_servicio);
  // evita duplicados
  $check = mysqli_query($conn, "SELECT 1 FROM cat_paquete_servicio WHERE id_paquete=$id_paquete AND id_servicio=$id_servicio");
  if (!mysqli_num_rows($check)) {
    mysqli_query($conn, "INSERT INTO cat_paquete_servicio (id_paquete, id_servicio) VALUES ($id_paquete, $id_servicio)");
  }
}
?>
