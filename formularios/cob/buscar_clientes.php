<?php
require_once '../../assets/dbc.php';

header('Content-Type: application/json');

// Obtener término de búsqueda
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

// Query básica
$query = "SELECT ta.id_cliente, 
        CONCAT_WS(' ',
            TRIM(tp.nombre_1),
            TRIM(tp.nombre_2),
            TRIM(tp.apellido_1),
            TRIM(tp.apellido_2),
            IF(TRIM(tp.apellido_casada) IS NULL OR TRIM(tp.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(tp.apellido_casada)))
        ) AS cliente
        FROM tbl_atencion ta 
        JOIN tbl_persona tp ON tp.id = ta.id_cliente";

// Filtrar si hay búsqueda
if (!empty($searchTerm)) {
    $searchTerm = $conn->real_escape_string($searchTerm);
    $query .= " WHERE CONCAT_WS(' ', 
                tp.nombre_1, 
                tp.nombre_2, 
                tp.apellido_1, 
                tp.apellido_2, 
                tp.apellido_casada
              ) LIKE '%$searchTerm%'";
}

$query .= " GROUP BY ta.id_cliente 
           ORDER BY cliente ASC
           LIMIT 50"; // Límite para no sobrecargar

$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id_cliente'],
        'text' => $row['cliente']
    ];
}

echo json_encode($data);
$conn->close();
?>