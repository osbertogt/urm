<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  require_once '../../assets/dbc.php';
  $stmt = $conn->prepare("DELETE FROM tbl_precio_servicio WHERE id = ?");
  $stmt->bind_param("i", $_POST['id']);
  $stmt->execute();
  echo "ok";
  exit;
}
?>