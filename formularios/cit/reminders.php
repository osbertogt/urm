<?php
// reminders.php
// Ruta: FORM_URL + 'cit/reminders.php'
require '../../assets/dbc.php'; // ajustar ruta
header('Content-Type: application/json; charset=utf-8');

// obtener JSON raw
$raw = file_get_contents('php://input');
if (!$raw) {
    echo json_encode(['success'=>false,'message'=>'No payload']); exit;
}
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    echo json_encode(['success'=>false,'message'=>'Payload inválido']); exit;
}

$items = $payload['items'] ?? [];
$channels = $payload['channels'] ?? ['whatsapp'=>0,'email'=>0];
$client_dt = $payload['client_datetime'] ?? date('c');

if (!is_array($items) || empty($items)) {
    echo json_encode(['success'=>false,'message'=>'No hay items']); exit;
}

// preparar insert
$insert_sql = "INSERT INTO tbl_cita_recordatorio (id_cita, fecha_hora, mensaje) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
if (!$stmt) {
    echo json_encode(['success'=>false,'message'=>'Error prepare: '.$conn->error]); exit;
}

$results = [];
$saved_count = 0;

foreach ($items as $it) {
    $id_cita = (int)($it['id_cita'] ?? 0);
    $medico = trim($it['medico'] ?? '');
    $fecha = trim($it['fecha'] ?? '');
    $hora = trim($it['hora'] ?? '');
    $paciente = trim($it['paciente'] ?? '');
    $phone_raw = trim($it['phone'] ?? '');
    $email = trim($it['email'] ?? '');

    // construir mensaje requerido:
    $mensaje = "Estimado(a) {$paciente}, le recordamos su cita agendada con {$medico} el {$fecha} a las {$hora}, por favor presentarse con 10 minutos de anticipación. Le esperamos.";

    // guardar en la tabla (fecha_hora tomada del cliente)
    // validación mínima: id_cita > 0
    if (!$id_cita) {
        $results[] = ['id_cita'=>null,'ok'=>false,'reason'=>'id_cita inválido'];
        continue;
    }

    // guardado
    $fecha_hora_db = $client_dt; // guardamos la fecha/hora enviada por el cliente (ISO)
    $stmt->bind_param('iss', $id_cita, $fecha_hora_db, $mensaje);
    if ($stmt->execute()) {
        $saved_count++;
        $row_id = $stmt->insert_id;
        $resItem = ['id_cita'=>$id_cita, 'saved_id'=>$row_id, 'ok'=>true, 'mensaje'=>$mensaje];
    } else {
        $resItem = ['id_cita'=>$id_cita, 'ok'=>false, 'reason'=>'Error insert: '.$stmt->error];
    }

    // preparar whatsapp url (cliente la abrirá)
    $phone_digits = preg_replace('/\D+/', '', $phone_raw);
    if ($channels['whatsapp']) {
        if ($phone_digits) {
            $msg_enc = urlencode($mensaje);
            $wa_url = "https://wa.me/{$phone_digits}?text={$msg_enc}";
            $resItem['whatsapp_url'] = $wa_url;
        } else {
            $resItem['whatsapp_url'] = null;
            $resItem['whatsapp_error'] = 'Teléfono inválido';
        }
    }

    // enviar correo si se solicitó (simple ejemplo usando mail)
    if ($channels['email'] && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $subject = "Recordatorio de cita";
        $body = $mensaje;
        $headers = "From: no-reply@tu-dominio.com\r\n";
        $mail_sent = @mail($email, $subject, $body, $headers);
        $resItem['email_sent'] = $mail_sent ? 1 : 0;
    } elseif ($channels['email']) {
        $resItem['email_sent'] = 0;
        $resItem['email_error'] = 'Email inválido';
    }

    $results[] = $resItem;
}

$stmt->close();

// devolver resumen
echo json_encode([
    'success' => true,
    'saved_count' => $saved_count,
    'results' => $results
], JSON_UNESCAPED_UNICODE);
exit;
