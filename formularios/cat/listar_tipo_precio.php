<?php
if (isset($_GET['accion']) && $_GET['accion'] === 'tipos') {
  require_once '../../assets/dbc.php';
  $sql = "SELECT id, nombre FROM cat_tipo_precio";
  $result = $conn->query($sql);
  $data = [];
  while ($row = $result->fetch_assoc()) {
      $data[] = $row;
  }
  echo json_encode($data);
  exit;
}?>