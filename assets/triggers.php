<?php
/**
 * SCRIPT PARA GENERAR TRIGGERS DE AUDITORÍA
 * Versión con usuario de sesión PHP
 */

// Iniciar sesión para obtener el usuario actual
session_start();

require_once '../assets/dbc.php';

class TriggerGenerator {
    private $conn;
    private $usuario_actual;
    private $tablas_procesadas = 0;
    private $errores = [];
    private $triggers_creados = [
        'INSERT' => 0,
        'UPDATE' => 0,
        'DELETE' => 0
    ];
    
    public function __construct($conn) {
        $this->conn = $conn;
        
        // Obtener usuario de sesión o usar 'SISTEMA' por defecto
        $this->usuario_actual = isset($_SESSION['usuario']) 
            ? $_SESSION['usuario'] 
            : (isset($_SESSION['username']) ? $_SESSION['username'] : 'SISTEMA');
    }
    
    /**
     * Obtener todas las tablas de la base de datos
     */
    private function obtenerTablas() {
        $sql = "SELECT 
                    TABLE_NAME,
                    (SELECT COLUMN_NAME 
                     FROM INFORMATION_SCHEMA.COLUMNS c 
                     WHERE c.TABLE_NAME = t.TABLE_NAME 
                     AND c.TABLE_SCHEMA = DATABASE()
                     AND (c.COLUMN_KEY = 'PRI' OR c.COLUMN_NAME LIKE '%id%')
                     LIMIT 1) as pk_column
                FROM INFORMATION_SCHEMA.TABLES t
                WHERE t.TABLE_SCHEMA = DATABASE() 
                AND t.TABLE_TYPE = 'BASE TABLE'
                AND t.TABLE_NAME != 'auditoria_general'
                ORDER BY t.TABLE_NAME";
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            throw new Exception("Error obteniendo tablas: " . $this->conn->error);
        }
        
        $tablas = [];
        while ($row = $result->fetch_assoc()) {
            if (empty($row['pk_column'])) {
                $row['pk_column'] = 'id';
            }
            $tablas[] = $row;
        }
        
        return $tablas;
    }
    
    /**
     * Obtener todas las columnas de una tabla
     */
    private function obtenerColumnas($tabla) {
        $sql = "SELECT COLUMN_NAME, DATA_TYPE 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = '$tabla'
                ORDER BY ORDINAL_POSITION";
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            throw new Exception("Error obteniendo columnas de $tabla: " . $this->conn->error);
        }
        
        $columnas = [];
        while ($row = $result->fetch_assoc()) {
            $columnas[] = $row['COLUMN_NAME'];
        }
        
        return $columnas;
    }
    
    /**
     * Generar el string JSON_OBJECT para las columnas
     */
    private function generarJsonColumns($columnas, $prefix) {
        if (empty($columnas)) {
            return "'sin_columnas', ''";
        }
        
        $parts = [];
        foreach ($columnas as $col) {
            $col_escaped = str_replace("'", "''", $col);
            $parts[] = "'$col_escaped', $prefix.$col";
        }
        
        return implode(", ", $parts);
    }
    
    /**
     * Eliminar triggers existentes
     */
    private function eliminarTrigger($tabla, $tipo) {
        $trigger_name = "tr_{$tabla}_{$tipo}";
        $sql = "DROP TRIGGER IF EXISTS $trigger_name";
        
        if (!$this->conn->query($sql)) {
            $this->errores[] = "Error eliminando trigger $trigger_name: " . $this->conn->error;
            return false;
        }
        
        return true;
    }
    
    /**
     * Crear trigger INSERT con usuario de sesión
     */
    private function crearTriggerInsert($tabla, $pk, $columnas) {
        $trigger_name = "tr_{$tabla}_insert";
        $json_columns = $this->generarJsonColumns($columnas, 'NEW');
        
        // Escapar el usuario para evitar problemas con comillas
        $usuario_escapado = $this->conn->real_escape_string($this->usuario_actual);
        
        $sql = "
            CREATE TRIGGER $trigger_name
            AFTER INSERT ON `$tabla`
            FOR EACH ROW
            BEGIN
                INSERT INTO auditoria_general (
                    tabla_afectada,
                    operacion,
                    usuario_operacion,
                    id_registro,
                    datos_nuevos
                ) VALUES (
                    '$tabla',
                    'INSERT',
                    '$usuario_escapado',
                    NEW.{$pk},
                    JSON_OBJECT($json_columns)
                );
            END
        ";
        
        if (!$this->conn->query($sql)) {
            $this->errores[] = "Error creando trigger INSERT para $tabla: " . $this->conn->error;
            return false;
        }
        
        $this->triggers_creados['INSERT']++;
        return true;
    }
    
    /**
     * Crear trigger UPDATE con usuario de sesión
     */
    private function crearTriggerUpdate($tabla, $pk, $columnas) {
        $trigger_name = "tr_{$tabla}_update";
        $json_old = $this->generarJsonColumns($columnas, 'OLD');
        $json_new = $this->generarJsonColumns($columnas, 'NEW');
        $usuario_escapado = $this->conn->real_escape_string($this->usuario_actual);
        
        $sql = "
            CREATE TRIGGER $trigger_name
            AFTER UPDATE ON `$tabla`
            FOR EACH ROW
            BEGIN
                INSERT INTO auditoria_general (
                    tabla_afectada,
                    operacion,
                    usuario_operacion,
                    id_registro,
                    datos_antiguos,
                    datos_nuevos
                ) VALUES (
                    '$tabla',
                    'UPDATE',
                    '$usuario_escapado',
                    NEW.{$pk},
                    JSON_OBJECT($json_old),
                    JSON_OBJECT($json_new)
                );
            END
        ";
        
        if (!$this->conn->query($sql)) {
            $this->errores[] = "Error creando trigger UPDATE para $tabla: " . $this->conn->error;
            return false;
        }
        
        $this->triggers_creados['UPDATE']++;
        return true;
    }
    
    /**
     * Crear trigger DELETE con usuario de sesión
     */
    private function crearTriggerDelete($tabla, $pk, $columnas) {
        $trigger_name = "tr_{$tabla}_delete";
        $json_old = $this->generarJsonColumns($columnas, 'OLD');
        $usuario_escapado = $this->conn->real_escape_string($this->usuario_actual);
        
        $sql = "
            CREATE TRIGGER $trigger_name
            BEFORE DELETE ON `$tabla`
            FOR EACH ROW
            BEGIN
                INSERT INTO auditoria_general (
                    tabla_afectada,
                    operacion,
                    usuario_operacion,
                    id_registro,
                    datos_antiguos
                ) VALUES (
                    '$tabla',
                    'DELETE',
                    '$usuario_escapado',
                    OLD.{$pk},
                    JSON_OBJECT($json_old)
                );
            END
        ";
        
        if (!$this->conn->query($sql)) {
            $this->errores[] = "Error creando trigger DELETE para $tabla: " . $this->conn->error;
            return false;
        }
        
        $this->triggers_creados['DELETE']++;
        return true;
    }
    
    /**
     * Mostrar información del usuario actual
     */
    private function mostrarInfoUsuario() {
        echo "<div style='background: #e8f4fd; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
        echo "<strong>👤 Usuario detectado en sesión:</strong> " . htmlspecialchars($this->usuario_actual);
        
        // Mostrar todas las variables de sesión disponibles (debug)
        echo "<br><small>Variables de sesión disponibles:</small>";
        echo "<pre style='background: #fff; padding: 5px; font-size: 11px;'>";
        foreach ($_SESSION as $key => $value) {
            if (!is_array($value)) {
                echo "$key: " . htmlspecialchars($value) . "\n";
            }
        }
        echo "</pre>";
        echo "</div>";
    }
    
    /**
     * Ejecutar la generación de todos los triggers
     */
    public function generarTodos() {
        echo "<h2>🔧 GENERANDO TRIGGERS DE AUDITORÍA</h2>";
        echo "<p>Inicio: " . date('Y-m-d H:i:s') . "</p>";
        
        // Mostrar información del usuario
        $this->mostrarInfoUsuario();
        
        try {
            // Verificar que la tabla auditoria_general existe
            $check = $this->conn->query("SHOW TABLES LIKE 'auditoria_general'");
            if ($check->num_rows == 0) {
                throw new Exception("La tabla 'auditoria_general' no existe. Créala primero.");
            }
            
            // Verificar que la columna usuario_operacion existe y es suficientemente larga
            $this->verificarColumnaUsuario();
            
            // Obtener tablas
            $tablas = $this->obtenerTablas();
            
            if (empty($tablas)) {
                echo "<p style='color: orange;'>⚠️ No se encontraron tablas para procesar</p>";
                return;
            }
            
            echo "<p>📊 Tablas encontradas: " . count($tablas) . "</p>";
            echo "<ul>";
            
            foreach ($tablas as $tabla_info) {
                $tabla = $tabla_info['TABLE_NAME'];
                $pk = $tabla_info['pk_column'];
                
                echo "<li><strong>$tabla</strong> (PK: $pk)... ";
                
                $columnas = $this->obtenerColumnas($tabla);
                
                $this->eliminarTrigger($tabla, 'insert');
                $this->eliminarTrigger($tabla, 'update');
                $this->eliminarTrigger($tabla, 'delete');
                
                $ok_insert = $this->crearTriggerInsert($tabla, $pk, $columnas);
                $ok_update = $this->crearTriggerUpdate($tabla, $pk, $columnas);
                $ok_delete = $this->crearTriggerDelete($tabla, $pk, $columnas);
                
                if ($ok_insert && $ok_update && $ok_delete) {
                    echo "<span style='color: green;'>✓ OK</span>";
                    $this->tablas_procesadas++;
                } else {
                    echo "<span style='color: red;'>✗ ERROR</span>";
                }
                
                echo "</li>";
                flush();
            }
            
            echo "</ul>";
            
            $this->mostrarResumen();
            
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
        }
    }
    
    /**
     * Verificar que la columna usuario_operacion existe y es adecuada
     */
    private function verificarColumnaUsuario() {
        $check = $this->conn->query("SHOW COLUMNS FROM auditoria_general LIKE 'usuario_operacion'");
        if ($check->num_rows == 0) {
            // Si no existe, agregarla
            $this->conn->query("ALTER TABLE auditoria_general 
                                ADD COLUMN usuario_operacion VARCHAR(100) AFTER operacion");
            echo "<p style='color: blue;'>📝 Columna 'usuario_operacion' agregada a auditoria_general</p>";
        } else {
            // Verificar longitud
            $col = $check->fetch_assoc();
            if (strpos($col['Type'], 'varchar') !== false) {
                // Si existe pero es muy corta, modificarla
                $this->conn->query("ALTER TABLE auditoria_general 
                                    MODIFY COLUMN usuario_operacion VARCHAR(100)");
            }
        }
    }
    
    private function mostrarResumen() {
        echo "<hr>";
        echo "<h3>📈 RESUMEN</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Concepto</th><th>Cantidad</th></tr>";
        echo "<tr><td>Tablas procesadas</td><td>" . $this->tablas_procesadas . "</td></tr>";
        echo "<tr><td>Triggers INSERT</td><td>" . $this->triggers_creados['INSERT'] . "</td></tr>";
        echo "<tr><td>Triggers UPDATE</td><td>" . $this->triggers_creados['UPDATE'] . "</td></tr>";
        echo "<tr><td>Triggers DELETE</td><td>" . $this->triggers_creados['DELETE'] . "</td></tr>";
        echo "<tr><td><strong>TOTAL TRIGGERS</strong></td><td><strong>" . array_sum($this->triggers_creados) . "</strong></td></tr>";
        echo "</table>";
        
        if (!empty($this->errores)) {
            echo "<h3 style='color: red;'>❌ ERRORES</h3>";
            echo "<ul>";
            foreach ($this->errores as $error) {
                echo "<li style='color: red;'>$error</li>";
            }
            echo "</ul>";
        }
        
        echo "<p>Fin: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p style='color: green;'><strong>✅ Proceso completado</strong></p>";
    }
}

// ============================================================
// EJECUCIÓN
// ============================================================

echo "<!DOCTYPE html>
<html>
<head>
    <title>Generador de Triggers de Auditoría</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h2, h3 { color: #333; }
        ul { list-style-type: none; padding: 0; }
        li { padding: 5px; border-bottom: 1px solid #eee; }
        li:hover { background: #f9f9f9; }
        table { margin: 20px 0; }
        th { background: #f0f0f0; }
        .warning { background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
";

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que hay un usuario en sesión
if (!isset($_SESSION['usuario']) && !isset($_SESSION['username'])) {
    echo "<div class='warning'>";
    echo "<strong>⚠️ ADVERTENCIA:</strong> No se detectó usuario en sesión. Se usará 'SISTEMA' por defecto.<br>";
    echo "Para registrar el usuario correctamente, asegúrate de haber iniciado sesión antes de ejecutar este script.";
    echo "</div>";
}

// Verificar conexión
if (!$conn) {
    die("<p style='color: red;'>❌ Error de conexión a la base de datos</p>");
}

// Crear instancia y ejecutar
$generator = new TriggerGenerator($conn);
$generator->generarTodos();

$conn->close();

echo "</body></html>";
?>