<?php
// curvas_ajax.php
session_start();
require_once '../../../assets/dbc.php'; 
header('Content-Type: application/json; charset=utf-8');
error_log("Cliente en sesión: " . ($_SESSION['cliente_activo'] ?? 'NO SET'));

$cliente = isset($_SESSION['cliente_activo']) ? (int) $_SESSION['cliente_activo'] : 0;
if (!$cliente) { echo json_encode(['success'=>false,'message'=>'No hay cliente en sesión']); exit; }

/**
 * Estrategia:
 * - Obtener edad actual (años, meses) desde tbl_persona => calcular fecha aproximada de nacimiento:
 *     fecha_nac_aprox = today - (edad_anios años + edad_meses meses)
 * - Obtener registros de antropometría: join tbl_atencion_consulta (c) con tbl_atencion (a)
 *   para el cliente. Para cada registro calcular age_months = meses entre fecha_nac_aprox y a.fecha
 * - Devolver array ordenado asc por fecha
 */

// obtener edad / sexo del paciente
$stmt = $conn->prepare("SELECT edad_anios, edad_meses, id_sexo, fecha_nacimiento FROM tbl_persona WHERE id = ?");
$stmt->bind_param("i", $cliente);
$stmt->execute();
$res = $stmt->get_result();
$pac = $res->fetch_assoc();
if (!$pac) { echo json_encode(['success'=>false,'message'=>'Paciente no encontrado']); exit; }

// calcular fecha de nacimiento aproximada (usar DateTime en PHP)
$edad_anios = (int)($pac['edad_anios'] ?? 0);
$edad_meses = (int)($pac['edad_meses'] ?? 0);
$now = new DateTime(); // ahora
// restar años y meses
$fecha_nac = $pac['fecha_nacimiento']
    ? new DateTime($pac['fecha_nacimiento'])
    : new DateTime();

if ($edad_anios > 0) $fecha_nac->modify('-' . $edad_anios . ' years');
if ($edad_meses > 0) $fecha_nac->modify('-' . $edad_meses . ' months');
// normalizar al primer día del mes para menor ruido (opcional)
// $fecha_nac->modify('first day of this month');

$fecha_nac_str = $fecha_nac->format('Y-m-d');

// obtener registros antropometría (talla/peso/circ_cefalica) de todas las atenciones del cliente
$sql = "SELECT a.id AS id_atencion, a.fecha AS fecha_atencion,
               c.talla, c.peso, c.circ_cefalica
        FROM tbl_atencion_consulta c
        JOIN tbl_atencion a ON a.id = c.id_atencion
        WHERE a.id_cliente = ?
          AND (c.talla IS NOT NULL OR c.peso IS NOT NULL OR c.circ_cefalica IS NOT NULL)
        ORDER BY a.fecha ASC";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $cliente);
$stmt2->execute();
$res2 = $stmt2->get_result();

$rows = [];
while ($r = $res2->fetch_assoc()) {
    $fecha = $r['fecha_atencion'];
    // calcular edad en meses: diferencia meses entre fecha_nac y fecha_actual_registro
    try {
        $d_reg = new DateTime($fecha);
        $diff = $fecha_nac->diff($d_reg);
        $months = ($diff->y * 12) + $diff->m;
        // si día del mes del registro es menor al nacimiento, ajustar (opcional)
        if ((int)$d_reg->format('d') < (int)$fecha_nac->format('d')) {
            $months = max(0, $months - 1);
        }
    } catch (Exception $e) {
        $months = null;
    }
    $rows[] = [
        'id_atencion' => (int)$r['id_atencion'],
        'fecha' => $fecha,
        'talla' => $r['talla'] === null ? null : floatval($r['talla']),
        'peso' => $r['peso'] === null ? null : floatval($r['peso']),
        'circ_cefalica' => $r['circ_cefalica'] === null ? null : floatval($r['circ_cefalica']),
        'age_months' => $months
    ];
}

echo json_encode([
    'success' => true,
    'fecha_nacimiento_aprox' => $fecha_nac_str,
    'edad_actual_anios' => $edad_anios,
    'edad_actual_meses' => $edad_meses,
    'sexo' => (int)$pac['id_sexo'],
    'data' => $rows
]);
exit;
