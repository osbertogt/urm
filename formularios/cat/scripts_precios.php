<?php
// listar_precios.php
if (isset($_GET['accion']) && $_GET['accion'] === 'listar') {
  require_once '../../assets/dbc.php';

  $sql = "SELECT ps.id,
                 CONCAT(p.nombre, ' ', pr.nombre, ' ', um.nombre, ' ', p.concentracion, ' - Lote: ', mp.numero_lote) AS producto,
                 tp.nombre AS tipo_precio,
                 ps.precio,
                 ps.fecha_ultimo_precio
          FROM tbl_precio_servicio ps
          JOIN tbl_inv_movimiento_producto mp ON ps.id_producto = mp.id
          JOIN cat_producto p ON mp.id_producto = p.id
          JOIN cat_unidad_medida um ON p.id_unidad_medida = um.id
          JOIN cat_presentacion pr ON p.id_presentacion = pr.id
          LEFT JOIN cat_tipo_precio tp ON ps.id_tipo_precio = tp.id";

  $result = $conn->query($sql);
  $data = [];

  while ($row = $result->fetch_assoc()) {
    $row['precio'] = '$' . number_format($row['precio'], 2);
    $data[] = $row;
  }

  header('Content-Type: application/json');
  echo json_encode(['data' => $data]);
  exit;
}

// listar_productos.php
if (isset($_GET['accion']) && $_GET['accion'] === 'productos') {
  require_once '../../assets/dbc.php';

  $sql = "SELECT mp.id,
                 CONCAT(p.nombre, ' ', pr.nombre, ' ', um.nombre, ' ', p.concentracion, ' - Lote: ', mp.numero_lote) AS nombre
          FROM tbl_inv_movimiento_producto mp
          JOIN cat_producto p ON mp.id_producto = p.id
          JOIN cat_unidad_medida um ON p.id_unidad_medida = um.id
          JOIN cat_presentacion pr ON p.id_presentacion = pr.id
          ORDER BY nombre";

  $res = $conn->query($sql);
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
  echo json_encode($data);
  exit;
}

// listar_tipo_precio.php
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
}

// guardar_precio.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once '../../assets/dbc.php';

  $id = $_POST['id'] ?? null;
  $id_producto = $_POST['id_producto'];
  $id_tipo_precio = $_POST['id_tipo_precio'];
  $precio = $_POST['precio'];
  $fecha = date('Y-m-d');

  if ($id) {
    $stmt = $conn->prepare("UPDATE tbl_precio_servicio SET id_producto=?, id_tipo_precio=?, precio=?, fecha_ultimo_precio=? WHERE id=?");
    $stmt->bind_param("iidsi", $id_producto, $id_tipo_precio, $precio, $fecha, $id);
  } else {
    $stmt = $conn->prepare("INSERT INTO tbl_precio_servicio (id_producto, id_tipo_precio, precio, fecha_ultimo_precio) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $id_producto, $id_tipo_precio, $precio, $fecha);
  }

  $stmt->execute();
  echo "ok";
  exit;
}

// eliminar_precio.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  require_once '../../assets/dbc.php';
  $stmt = $conn->prepare("DELETE FROM tbl_precio_servicio WHERE id = ?");
  $stmt->bind_param("i", $_POST['id']);
  $stmt->execute();
  echo "ok";
  exit;
}
?>
