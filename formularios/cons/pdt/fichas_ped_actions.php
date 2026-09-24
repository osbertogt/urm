<?php
require_once '../../../assets/dbc.php';

$action = $_POST['action'] ?? '';

if ($action === 'listar') {
    $sql = "
        SELECT 
            c.id,
            c.fecha,
            c.hora,
			c.id_cliente,
			c.id_medico,
            CONCAT_WS(' ', p1.nombre_1, p1.nombre_2, p1.apellido_1, p1.apellido_2) AS cliente,
			p1.edad_anios edad, p1.direccion_calle_avenida direccion,cs.nombre sexo,
            CONCAT_WS(' ', p2.nombre_1, p2.nombre_2, p2.apellido_1, p2.apellido_2) AS medico
        FROM tbl_cita c
        INNER JOIN tbl_persona p1 ON c.id_cliente = p1.id
        INNER JOIN tbl_persona p2 ON c.id_medico = p2.id
		JOIN cat_sexo cs ON cs.id=p1.id_sexo
        WHERE c.id_medico=3
        ORDER BY p2.nombre_1, c.fecha DESC
    ";

    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}

if ($action === 'detalle') {
    $id = intval($_POST['id'] ?? 0);

    $sql = "
        SELECT 
            c.id,
            c.fecha,
            c.hora,
            CONCAT_WS(' ', p1.nombre_1, p1.nombre_2, p1.apellido_1, p1.apellido_2) AS cliente,
            CONCAT_WS(' ', p2.nombre_1, p2.nombre_2, p2.apellido_1, p2.apellido_2) AS medico
        FROM tbl_cita c
        INNER JOIN tbl_persona p1 ON c.id_cliente = p1.id
        INNER JOIN tbl_persona p2 ON c.id_medico = p2.id
        WHERE c.id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        echo "
        <div class='text-start'>
          <p><strong>ID Cita:</strong> {$res['id']}</p>
          <p><strong>Fecha:</strong> {$res['fecha']}</p>
          <p><strong>Hora:</strong> {$res['hora']}</p>
          <p><strong>Cliente:</strong> {$res['cliente']}</p>
          <p><strong>Médico:</strong> {$res['medico']}</p>
        </div>";
    } else {
        echo "<p class='text-danger'>No se encontró información de la cita.</p>";
    }
    exit;
}
?>
