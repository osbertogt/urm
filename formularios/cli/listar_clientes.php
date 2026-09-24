<?php
include_once '../../assets/dbc.php';
header('Content-Type: application/json');

$sql = "SELECT c.id, 
		CONCAT_WS(' ',
			TRIM(c.nombre_1),
			TRIM(c.nombre_2),
			TRIM(c.apellido_1),
			TRIM(c.apellido_2),
			IF(TRIM(c.apellido_casada) IS NULL OR TRIM(c.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(c.apellido_casada)))
		) AS cliente,
		c.direccion_calle_avenida AS direccion, c.telefono, c.dpi_cui,
		d.nombre AS sexo
        FROM tbl_persona c
        JOIN cat_sexo d ON c.id_sexo = d.id
		WHERE c.id_tipopersona=1
		ORDER BY c.id DESC";
$result = mysqli_query($conn, $sql);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        $row['id'],
        $row['cliente'],
        $row['sexo'],
        $row['direccion'],
        $row['telefono'],
        $row['dpi_cui'],
        "<button class='btn btn-sm btn-warning editar' data-id='{$row['id']}' data-toggle='tooltip' data-placement='left' title='Editar' ><i class='bi bi-pencil-square'></i></button>
         <button class='btn btn-sm btn-danger eliminar' data-id='{$row['id']}' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='bi bi-trash'></i></button>
         <button class='btn btn-sm btn-success seleccionar' data-id='{$row['id']}' data-toggle='tooltip' data-placement='left' title='Seleccionar'><i class='bi bi-check2-circle'></i></button>"
    ];
}

echo json_encode(['data' => $data]);
?>