<?php
require '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    //echo json_encode(['success' => false, 'message' => 'ID inválido']);
    //exit;
}

mysqli_begin_transaction($conn);

try {
    // Eliminar detalle primero
    $stmt = $conn->prepare("DELETE FROM tbl_atencion_consulta WHERE id_atencion = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM tbl_atencion_servicio WHERE id_atencion = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM tbl_atencion_servicio_variable WHERE id_atencion = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM tbl_atencion_servicio_informe WHERE id_atencion = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Luego eliminar encabezado
    $stmt2 = $conn->prepare("DELETE FROM tbl_atencion WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $affected = $stmt2->affected_rows;
    $stmt2->close();

    mysqli_commit($conn);

    echo json_encode(['success' => true, 'message' => 'Orden eliminada correctamente']);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
