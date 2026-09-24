<?php

// DEBUG TEMPORAL - colocar al inicio del archivo
error_log("=== INICIO GUARDAR PERSONA ===");
error_log("METODO: " . $_SERVER['REQUEST_METHOD']);
error_log("POST COMPLETO: " . print_r($_POST, true));
error_log("RAW INPUT: " . file_get_contents('php://input'));

require_once '../../assets/dbc.php';

// Verificar si es edición o nuevo
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$es_edicion = ($id > 0);

$campos_requeridos = ['nombre_1', 'apellido_1', 'id_sexo'];
foreach ($campos_requeridos as $campo) {
    if (!isset($_POST[$campo])) {
        echo json_encode([
            "success" => false, 
            "error" => "Campo requerido faltante: $campo"
        ]);
        exit;
    }
}

$nombre_1 = trim($_POST['nombre_1']) ? trim($_POST['nombre_1']) : '';
$nombre_2 = isset($_POST['nombre_2']) ? trim($_POST['nombre_2']) : '';
$apellido_1 = trim($_POST['apellido_1']) ? trim($_POST['apellido_1']) : '';
$apellido_2 = isset($_POST['apellido_2']) ? trim($_POST['apellido_2']) : '';
$apellido_casada = isset($_POST['apellido_casada']) ? trim($_POST['apellido_casada']) : '';
$direccion_calle_avenida = isset($_POST['direccion_calle_avenida']) ? trim($_POST['direccion_calle_avenida']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$id_especialidad = isset($_POST['id_especialidad']) ? intval($_POST['id_especialidad']) : 0;

$id_sexo = intval($_POST['id_sexo']);

$id_departamento = isset($_POST['id_departamento']) ? intval($_POST['id_departamento']) : 0;
$id_municipio = isset($_POST['id_municipio']) ? intval($_POST['id_municipio']) : 0;
$id_tipopersona = 3;

if (empty($nombre_1) || empty($apellido_1)) {
    echo json_encode([
        "success" => false, 
        "error" => "Nombre y apellido son obligatorios"
    ]);
    exit;
}

try {
    if ($es_edicion) {
        // ACTUALIZAR registro existente
        $sql = "UPDATE tbl_persona SET 
                nombre_1 = ?, nombre_2 = ?, apellido_1 = ?, apellido_2 = ?, 
                apellido_casada = ?, id_sexo = ?, id_departamento = ?, id_municipio = ?,
                direccion_calle_avenida = ?, telefono = ?, email = ?, id_especialidad = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param(
            "sssssiiisssii", 
            $nombre_1, $nombre_2, $apellido_1, $apellido_2, $apellido_casada,
            $id_sexo, $id_departamento, $id_municipio,
            $direccion_calle_avenida, $telefono, $email, $id_especialidad,
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Registro actualizado correctamente",
                "updated_id" => $id
            ]);
        } else {
            throw new Exception("Error al actualizar: " . $stmt->error);
        }
        
    } else {
        // INSERTAR nuevo registro
        $sql = "INSERT INTO tbl_persona 
                (id_tipopersona, nombre_1, nombre_2, apellido_1, apellido_2, 
                 apellido_casada, id_sexo, id_departamento, id_municipio,
                 direccion_calle_avenida, telefono, email, id_especialidad)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param(
            "isssssiiisssi", 
            $id_tipopersona, 
            $nombre_1, $nombre_2, $apellido_1, $apellido_2, $apellido_casada,
            $id_sexo, $id_departamento, $id_municipio,
            $direccion_calle_avenida, $telefono, $email, $id_especialidad
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Registro guardado correctamente",
                "insert_id" => $stmt->insert_id
            ]);
        } else {
            throw new Exception("Error al guardar: " . $stmt->error);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>