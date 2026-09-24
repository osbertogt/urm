<?php
$menu = [];

$sql = "
SELECT
    m.id AS modulo_id,
    m.nombre AS modulo,
    o.id AS opcion_id,
    o.nombre AS opcion,
    o.ruta
FROM tbl_rol_permisos rp
JOIN cat_modulo m ON rp.id_modulo = m.id
JOIN cat_modulo_opciones o ON rp.id_opcion = o.id
WHERE rp.id_rol = ?
GROUP BY m.id, o.id
ORDER BY m.id, o.id
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_rol']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $menu[$row['modulo_id']]['nombre'] = $row['modulo'];
    $menu[$row['modulo_id']]['opciones'][] = [
        'nombre' => $row['opcion'],
        'ruta'   => $row['ruta']
    ];
}
