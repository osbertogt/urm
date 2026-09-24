<?php
// guardar_vacuna.php
require_once 'config.php';
require_once "../../../assets/dbc.php";

establecerHeadersJSON();

try {
    // Verificar método POST
    /* if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }
   */  
    // Validar datos requeridos
    $errores = [];
    
 /*    if (!isset($_POST['id_cliente']) || !validarEntero($_POST['id_cliente'])) {
        $errores[] = 'ID de cliente no válido';
    }
 */	$id_cliente=116;
    if (!isset($_POST['fecha_vacuna']) || !validarFecha($_POST['fecha_vacuna'])) {
        $errores[] = 'Fecha de vacuna no válida';
    }
    
    if (!isset($_POST['id_vacuna']) || !validarEntero($_POST['id_vacuna'])) {
        $errores[] = 'Vacuna no válida';
    }
    
    if (!isset($_POST['accion']) || !in_array($_POST['accion'], ['crear', 'editar'])) {
        $errores[] = 'Acción no válida';
    }
    
    if ($_POST['accion'] === 'editar' && (!isset($_POST['id_registro']) || !validarEntero($_POST['id_registro']))) {
        $errores[] = 'ID de registro no válido para edición';
    }
    
    if (!empty($errores)) {
        echo json_encode([
            'success' => false, 
            'message' => implode(', ', $errores)
        ]);
        exit;
    }
    
    // Limpiar datos
    //$conn = conectarDB();
    
    $id_cliente = (int)$_POST['id_cliente'];
    $fecha_vacuna = $_POST['fecha_vacuna'];
    $id_vacuna = (int)$_POST['id_vacuna'];
    $observaciones = isset($_POST['observaciones']) ? limpiarDato($_POST['observaciones'], $conn) : '';
    $accion = $_POST['accion'];
    
    // Verificar que la vacuna existe
    $stmt_check_vacuna = mysqli_prepare($conn, "SELECT id FROM cat_vacuna WHERE id = ?");
    mysqli_stmt_bind_param($stmt_check_vacuna, 'i', $id_vacuna);
    mysqli_stmt_execute($stmt_check_vacuna);
    
    if (mysqli_stmt_get_result($stmt_check_vacuna)->num_rows === 0) {
        mysqli_stmt_close($stmt_check_vacuna);
        mysqli_close($conn);
        echo json_encode(['success' => false, 'message' => 'La vacuna seleccionada no existe']);
        exit;
    }
    mysqli_stmt_close($stmt_check_vacuna);
    
    // Iniciar transacción
    mysqli_autocommit($conn, false);
    
    try {
        $id_atencion = null;
        
        if ($accion === 'crear') {
            // Verificar si ya existe una atención para este cliente en esta fecha
            $stmt_atencion = mysqli_prepare($conn, 
                "SELECT id FROM tbl_atencion WHERE id_cliente = ? AND fecha = ?"
            );
            mysqli_stmt_bind_param($stmt_atencion, 'is', $id_cliente, $fecha_vacuna);
            mysqli_stmt_execute($stmt_atencion);
            $resultado_atencion = mysqli_stmt_get_result($stmt_atencion);
            
            if ($fila_atencion = mysqli_fetch_assoc($resultado_atencion)) {
                $id_atencion = $fila_atencion['id'];
            } else {
                // Crear nueva atención
                $stmt_nueva_atencion = mysqli_prepare($conn,
                    "INSERT INTO tbl_atencion (fecha, id_cliente) VALUES (?, ?)"
                );
                mysqli_stmt_bind_param($stmt_nueva_atencion, 'si', $fecha_vacuna, $id_cliente);
                
                if (!mysqli_stmt_execute($stmt_nueva_atencion)) {
                    throw new Exception('Error al crear la atención: ' . mysqli_error($conn));
                }
                
                $id_atencion = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt_nueva_atencion);
            }
            mysqli_stmt_close($stmt_atencion);
            
            // Verificar que no se registre la misma vacuna en la misma atención
            $stmt_check_duplicado = mysqli_prepare($conn,
                "SELECT id FROM tbl_atencion_vacunas WHERE id_atencion = ? AND id_vacuna = ?"
            );
            mysqli_stmt_bind_param($stmt_check_duplicado, 'ii', $id_atencion, $id_vacuna);
            mysqli_stmt_execute($stmt_check_duplicado);
            
            if (mysqli_stmt_get_result($stmt_check_duplicado)->num_rows > 0) {
                mysqli_stmt_close($stmt_check_duplicado);
                throw new Exception('Esta vacuna ya está registrada para esta fecha');
            }
            mysqli_stmt_close($stmt_check_duplicado);
            
            // Insertar nueva vacuna
            $stmt_vacuna = mysqli_prepare($conn,
                "INSERT INTO tbl_atencion_vacunas (id_atencion, fecha, id_vacuna, observaciones) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt_vacuna, 'isis', $id_atencion, $fecha_vacuna, $id_vacuna, $observaciones);
            
            if (!mysqli_stmt_execute($stmt_vacuna)) {
                throw new Exception('Error al registrar la vacuna: ' . mysqli_error($conn));
            }
            
            $mensaje = 'Vacuna registrada correctamente';
            
        } else { // editar
            $id_registro = (int)$_POST['id_registro'];
            
            // Verificar que el registro existe y pertenece al cliente
            $stmt_check = mysqli_prepare($conn,
                "SELECT av.id_atencion 
                 FROM tbl_atencion_vacunas av
                 INNER JOIN tbl_atencion a ON av.id_atencion = a.id
                 WHERE av.id = ? AND a.id_cliente = ?"
            );
            mysqli_stmt_bind_param($stmt_check, 'ii', $id_registro, $id_cliente);
            mysqli_stmt_execute($stmt_check);
            $resultado_check = mysqli_stmt_get_result($stmt_check);
            
            if ($fila_check = mysqli_fetch_assoc($resultado_check)) {
                $id_atencion = $fila_check['id_atencion'];
                mysqli_stmt_close($stmt_check);
                
                // Verificar que no haya duplicado al editar (excepto el mismo registro)
                $stmt_check_duplicado = mysqli_prepare($conn,
                    "SELECT id FROM tbl_atencion_vacunas 
                     WHERE id_atencion = ? AND id_vacuna = ? AND id != ?"
                );
                mysqli_stmt_bind_param($stmt_check_duplicado, 'iii', $id_atencion, $id_vacuna, $id_registro);
                mysqli_stmt_execute($stmt_check_duplicado);
                
                if (mysqli_stmt_get_result($stmt_check_duplicado)->num_rows > 0) {
                    mysqli_stmt_close($stmt_check_duplicado);
                    throw new Exception('Esta vacuna ya está registrada para esta fecha');
                }
                mysqli_stmt_close($stmt_check_duplicado);
                
                // Actualizar el registro
                $stmt_update = mysqli_prepare($conn,
                    "UPDATE tbl_atencion_vacunas 
                     SET fecha = ?, id_vacuna = ?, observaciones = ?
                     WHERE id = ?"
                );
                mysqli_stmt_bind_param($stmt_update, 'sisi', $fecha_vacuna, $id_vacuna, $observaciones, $id_registro);
                
                if (!mysqli_stmt_execute($stmt_update)) {
                    throw new Exception('Error al actualizar la vacuna: ' . mysqli_error($conn));
                }
                
                mysqli_stmt_close($stmt_update);
                $mensaje = 'Vacuna actualizada correctamente';
                
            } else {
                mysqli_stmt_close($stmt_check);
                throw new Exception('El registro no existe o no pertenece al cliente especificado');
            }
        }
        
        if (isset($stmt_vacuna)) {
            mysqli_stmt_close($stmt_vacuna);
        }
        
        // Confirmar transacción
        mysqli_commit($conn);
        mysqli_autocommit($conn, true);
        mysqli_close($conn);
        
        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ]);
        
    } catch (Exception $e) {
        // Rollback en caso de error
        mysqli_rollback($conn);
        mysqli_autocommit($conn, true);
        mysqli_close($conn);
        
        error_log('Error en transacción de vacuna: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        mysqli_close($conn);
    }
    
    error_log('Error general en guardar_vacuna.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>