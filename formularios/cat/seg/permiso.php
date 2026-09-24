<?php
function validarPermiso($ruta, $conn) {
    $sql = "
    SELECT 1
    FROM tbl_rol_permisos rp
    JOIN cat_modulo_opcion o ON rp.id_opcion = o.id
    WHERE rp.id_rol = ?
      AND o.ruta = ?
    LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $_SESSION['id_rol'], $ruta);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) === 0) {
        http_response_code(403);
        die('Acceso no autorizado');
    }
}
