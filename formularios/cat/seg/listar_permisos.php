<?php
require_once '../../../assets/dbc.php';
require_once 'helpers.php';

$id_rol = isset($_GET['id_rol']) ? intval($_GET['id_rol']) : 0;
if (!$id_rol) json_response([],200);

$sql = "SELECT rp.id AS id_permiso_rol, m.nombre AS modulo, o.nombre AS opcion, p.nombre AS permiso
        FROM tbl_rol_permisos rp
        INNER JOIN cat_modulo m ON rp.id_modulo = m.id
        INNER JOIN cat_modulo_opciones o ON rp.id_opcion = o.id
        INNER JOIN cat_permiso p ON rp.id_permiso = p.id
        WHERE rp.id_rol = ?
        ORDER BY m.nombre, o.nombre, p.nombre";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i',$id_rol);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = [
        'id_permiso_rol' => (int)$r['id_permiso_rol'],
        'modulo' => e($r['modulo']),
        'opcion' => e($r['opcion']),
        'permiso' => e($r['permiso'])
    ];
}
json_response($out);
