<?php
// serve_report.php
session_start();
require_once '../../assets/dbc.php';

// Recibe id (del informe) y entrega el PDF inline
$id = intval($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); echo "Bad request"; exit; }

// Obtiene nombre de archivo desde la BD
$sql = "SELECT informe, nombre_original FROM tbl_atencion_servicio_informe WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
if (!$row = $res->fetch_assoc()) { http_response_code(404); echo "Not found"; exit; }

$filename = $row['informe'];
$original = $row['nombre_original'];
$path = __DIR__ . '/../uploads/reports/' . $filename;

if (!is_file($path)) { http_response_code(404); echo "Archivo no encontrado"; exit; }

// Forzar headers seguros
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($path));
// inline para ver en navegador
header('Content-Disposition: inline; filename="'.basename($original).'"');
header('Cache-Control: private, max-age=86400');

readfile($path);
exit;
