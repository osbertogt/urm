<?php
// dashboard_stats.php
require_once '../../assets/dbc.php'; // ajustar ruta
header('Content-Type: application/json; charset=utf-8');

// Obtener ym (YYYY-MM) del POST, validar
$ym = isset($_POST['ym']) ? trim($_POST['ym']) : date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
    $ym = date('Y-m');
}
list($year, $month) = explode('-', $ym);
$year = (int)$year;
$month = (int)$month;
if ($month < 1 || $month > 12) { $month = (int)date('n'); }

// Rango de fechas para el mes
$start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
$endDate = new DateTime("$year-$month-01");
$endDate->modify('last day of this month')->setTime(23,59,59);
$end = $endDate->format('Y-m-d H:i:s');

$conn->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);

try {
    // 1) Obtener lista de médicos (id, nombre completo, especialidad)
    $sqlMed = "SELECT p.id,
                CONCAT_WS(' ',
                  TRIM(p.nombre_1),
                  TRIM(p.nombre_2),
                  TRIM(p.apellido_1),
                  TRIM(p.apellido_2)
                ) AS medico,
                COALESCE(e.nombre,'') AS especialidad
              FROM tbl_persona p
              LEFT JOIN cat_especialidad e ON p.id_especialidad = e.id
              WHERE p.id_tipopersona = 3
              ORDER BY medico";
    $resMed = $conn->query($sqlMed);
    $medicos = [];
    while ($r = $resMed->fetch_assoc()) {
        $medicos[(int)$r['id']] = [
            'id' => (int)$r['id'],
            'medico' => $r['medico'],
            'especialidad' => $r['especialidad'],
            'citas_programadas' => 0,
            'citas_confirmadas' => 0,
            'consultas_atendidas' => 0,
            'mes' => sprintf('%04d-%02d', $year, $month)
        ];
    }

    // 2) Contar citas programadas por medico en el mes
    $sql1 = "SELECT c.id_medico, COUNT(*) AS cnt
             FROM tbl_cita c
             WHERE c.fecha >= ? AND c.fecha <= ?
             GROUP BY c.id_medico";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("ss", $start, $end);
    $stmt1->execute();
    $r1 = $stmt1->get_result();
    while ($row = $r1->fetch_assoc()) {
        $mid = (int)$row['id_medico'];
        if (isset($medicos[$mid])) $medicos[$mid]['citas_programadas'] = (int)$row['cnt'];
    }
    $stmt1->close();

    // 3) Contar citas confirmadas por medico (estado nombre contiene 'confirm')
    $sql2 = "SELECT c.id_medico, COUNT(*) AS cnt
             FROM tbl_cita c
             JOIN cat_estado ce ON c.id_estado = ce.id
             WHERE c.fecha >= ? AND c.fecha <= ?
               AND LOWER(ce.nombre) LIKE '%confirm%'
             GROUP BY c.id_medico";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ss", $start, $end);
    $stmt2->execute();
    $r2 = $stmt2->get_result();
    while ($row = $r2->fetch_assoc()) {
        $mid = (int)$row['id_medico'];
        if (isset($medicos[$mid])) $medicos[$mid]['citas_confirmadas'] = (int)$row['cnt'];
    }
    $stmt2->close();

    // 4) Contar consultas atendidas (tbl_atencion) por medico en el mes
    $sql3 = "SELECT a.id_medico, COUNT(*) AS cnt
             FROM tbl_atencion a
             WHERE a.fecha >= ? AND a.fecha <= ?
             GROUP BY a.id_medico";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("ss", $start, $end);
    $stmt3->execute();
    $r3 = $stmt3->get_result();
    while ($row = $r3->fetch_assoc()) {
        $mid = (int)$row['id_medico'];
        if (isset($medicos[$mid])) $medicos[$mid]['consultas_atendidas'] = (int)$row['cnt'];
    }
    $stmt3->close();

    // 5) Consultas atendidas por día de la semana (global, para el mes)
    // usamos DAYNAME(a.fecha) que devuelve inglés (Monday...). Lo normalizamos en PHP.
    $sql4 = "SELECT DAYNAME(a.fecha) AS dayname, COUNT(*) AS cnt
             FROM tbl_atencion a
             WHERE DAYOFWEEK(a.fecha) <> 1 and a.fecha >= ? AND a.fecha <= ? 
             GROUP BY DAYNAME(a.fecha)";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->bind_param("ss", $start, $end);
    $stmt4->execute();
    $r4 = $stmt4->get_result();
    $week_counts = [];
    while ($row = $r4->fetch_assoc()) {
        $week_counts[$row['dayname']] = (int)$row['cnt'];
    }
    $stmt4->close();

    $conn->commit();

    // Preparar salida: array ordenado de medicos
    $doctors_out = array_values($medicos);

    echo json_encode([
        'success' => true,
        'ym' => sprintf('%04d-%02d', $year, $month),
        'doctors' => $doctors_out,
        'week_counts' => $week_counts
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
