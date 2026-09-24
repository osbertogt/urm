<?php

session_start();
require_once '../../assets/dbc.php'; 

/*
  actions:
    - list_servicios         (POST) -> devuelve registros de vw_servicios_paciente con id_tipo_atencion = 1
    - list_informes          (GET)  -> requiere id_atencion, id_servicio -> devuelve filas de tbl_atencion_servicio_informe
    - upload_informe         (POST, multipart) -> id_atencion, id_servicio, informe_pdf file
    - delete_informe         (POST) -> id (informe id)
*/


function out($arr){ header('Content-Type: application/json; charset=utf-8'); echo json_encode($arr); exit; }

$action = $_REQUEST['action'] ?? '';

if ($action === 'list_servicios') {
    
    $sql = "SELECT 
	  v.id_atencion,
	  v.id_servicio,
	  v.id_tipo_atencion,
	  v.numero_ticket,
	  v.fecha,
	  v.paciente,
	  v.dpi,
	  v.tiposervicio,
	  v.servicio,
	  (
		SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END
		FROM tbl_atencion_servicio_informe i
		WHERE i.id_atencion = v.id_atencion 
		  AND i.id_servicio = v.id_servicio
	  ) AS existe_informe
		FROM vw_servicios_paciente v
		WHERE v.id_tipo_atencion = 1
					ORDER BY id_atencion DESC";
    $res = $conn->query($sql);
    $rows = [];
    while($r = $res->fetch_assoc()){
        
        $rows[] = [
            'id_atencion' => $r['id_atencion'],
            'id_servicio' => $r['id_servicio'],
            'fecha' => $r['fecha'],
            'numero_ticket' => $r['numero_ticket'],
            'paciente' => $r['paciente'],
            'dpi' => $r['dpi'],
            'servicio' => $r['servicio'],
			'existe_informe' => $r['existe_informe']
        ];
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows);
    exit;
}

if ($action === 'list_informes') {
    $id_at = intval($_GET['id_atencion'] ?? $_POST['id_atencion'] ?? 0);
    $id_srv = intval($_GET['id_servicio'] ?? $_POST['id_servicio'] ?? 0);
    if (!$id_at || !$id_srv) out([]);

    $sql = "SELECT id, id_atencion, id_servicio, fecha, informe, nombre_original
            FROM tbl_atencion_servicio_informe
            WHERE id_atencion = ? AND id_servicio = ?
            ORDER BY fecha DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_at, $id_srv);
    $stmt->execute();
    $res = $stmt->get_result();
    $arr = [];
    while($r = $res->fetch_assoc()){
        $arr[] = [
            'id' => $r['id'],
            'fecha' => $r['fecha'],
            'informe' => $r['informe'],
            'nombre_original' => $r['nombre_original']
        ];
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

if ($action === 'upload_informe') {
    // Requiere POST multipart
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['success'=>false,'message'=>'Método no permitido']);

    $id_at = intval($_POST['id_atencion'] ?? 0);
    $id_srv = intval($_POST['id_servicio'] ?? 0);
    if (!$id_at || !$id_srv) out(['success'=>false,'message'=>'Falta id_atencion o id_servicio']);

    if (!isset($_FILES['informe_pdf'])) out(['success'=>false,'message'=>'Archivo no enviado']);

    $file = $_FILES['informe_pdf'];

    // validaciones básicas
    if ($file['error'] !== UPLOAD_ERR_OK) out(['success'=>false,'message'=>'Error al subir (code '.$file['error'].')']);
    if ($file['size'] > 5 * 1024 * 1024) out(['success'=>false,'message'=>'Archivo demasiado grande (máx 5MB)']);

    // validar MIME con finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if ($mime !== 'application/pdf') out(['success'=>false,'message'=>'Solo PDF permitido']);

    // validar extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') out(['success'=>false,'message'=>'Extensión inválida']);

    // destino seguro
    $uploadDir = __DIR__ . '/../uploads/reports/'; // ajusta según tu estructura
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) out(['success'=>false,'message'=>'No se puede crear carpeta de uploads']);
    }

    // nombre aleatorio
    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.pdf';
    $dest = $uploadDir . $newName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        out(['success'=>false,'message'=>'No se pudo mover el archivo']);
    }

    // insertar registro en BD (guardamos nombre real y nombre en FS)
    $sql = "INSERT INTO tbl_atencion_servicio_informe (id_atencion, id_servicio, fecha, informe, nombre_original)
            VALUES (?, ?, NOW(), ?, ?)";
    $stmt = $conn->prepare($sql);
    $original = $file['name'];
    $stmt->bind_param("iiss", $id_at, $id_srv, $newName, $original);
    $ok = $stmt->execute();
    if (!$ok) {
        // intentar borrar archivo físico si DB falla
        @unlink($dest);
        out(['success'=>false,'message'=>'Error BD: '. $conn->error]);
    }

    out(['success'=>true]);
}

// eliminar informe
if ($action === 'delete_informe') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) out(['success'=>false,'message'=>'ID inválido']);
    // obtener filename
    $sql = "SELECT informe FROM tbl_atencion_servicio_informe WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$row = $res->fetch_assoc()) out(['success'=>false,'message'=>'Informe no encontrado']);
    $file = $row['informe'];

    $sql2 = "DELETE FROM tbl_atencion_servicio_informe WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i",$id);
    $ok = $stmt2->execute();

    // borrar fichero
    if ($ok) {
        $path = __DIR__ . '/../uploads/reports/' . $file;
        if (is_file($path)) @unlink($path);
        out(['success'=>true]);
    } else {
        out(['success'=>false,'message'=>'No se pudo eliminar registro']);
    }
}

out(['success'=>false,'message'=>'Acción no válida']);
