<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

$stmt = $conn->prepare("
    SELECT p.id, 
        CONCAT_WS(' ', COALESCE(p.nombre_1, ''), COALESCE(p.nombre_2, ''),
        TRIM(CONCAT_WS(' ',
            COALESCE(p.apellido_1, ''),
            COALESCE(p.apellido_2, ''),
            CASE 
                WHEN TRIM(COALESCE(p.apellido_casada, '')) != '' THEN CONCAT('de ', apellido_casada)
                ELSE ''
            END
        ))) AS nombre_completo,
        p.direccion_calle_avenida AS direccion,
        p.email,
        p.telefono,
        s.nombre AS sexo,
        d.nombre AS departamento,
        m.nombre AS municipio,
        ce.nombre AS especialidad
    FROM tbl_persona p
    LEFT JOIN cat_sexo s ON p.id_sexo = s.id
    LEFT JOIN cat_departamento d ON p.id_departamento = d.id
    LEFT JOIN cat_municipio m ON p.id_municipio = m.id
    LEFT JOIN cat_especialidad ce ON ce.id = p.id_especialidad
    WHERE p.id_tipopersona = 3
");
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'nombre_completo' => $row['nombre_completo'],
        'sexo' => $row['sexo'],
        'direccion' => $row['direccion'],
        'municipio' => $row['municipio'],
        'departamento' => $row['departamento'],
        'telefono' => $row['telefono'],
        'email' => $row['email'],
        'especialidad' => $row['especialidad'],
        'acciones' => '<button class="btn btn-sm btn-primary">Editar</button>'
    ];
}

echo json_encode(['data' => $data]);
?>