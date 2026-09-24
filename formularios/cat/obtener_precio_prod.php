<?php
if (isset($_GET['id'])) {
  require_once '../../assets/dbc.php';

  $id = intval($_GET['id']);

  $sql = "SELECT ps.id,
                 ps.id_producto,
                 ps.id_tipo_precio,
                 ps.precio,
                 ps.fecha_ultimo_precio
          FROM tbl_precio_servicio ps
          WHERE ps.id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    header('Content-Type: application/json');
    echo json_encode($row);
  } else {
    http_response_code(404);
    echo json_encode(['error' => 'Registro no encontrado']);
  }

  $stmt->close();
  $conn->close();
  exit;
}
?>
