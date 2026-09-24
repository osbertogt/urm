<?php
if (isset($_GET['id'])) {
  require_once '../../assets/dbc.php';
  $id = $_GET['id'];

  $sql = "SELECT id,
                 CASE 
                    WHEN id_especialidad IS NOT NULL THEN 'especialidad'
                    WHEN id_servicio IS NOT NULL THEN 'servicio'
                    WHEN id_paquete IS NOT NULL THEN 'paquete'
                 END AS tipo,
                 COALESCE(id_especialidad, id_servicio, id_paquete) AS id_elemento,
                 id_tipo_precio,
                 precio
          FROM tbl_precio_servicio
          WHERE id = ?";
          
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  echo json_encode($res->fetch_assoc());
  exit;
}
?>
