<?php
// generar_pdf.php
require_once '../../assets/dbc.php';
require_once '../../assets/tcpdf/tcpdf.php'; // ajusta la ruta a tu instalación TCPDF

// Obtener id_consulta
$id = isset($_GET['id_consulta']) ? (int) $_GET['id_consulta'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "ID inválido";
    exit;
}

// Query que trae todos los datos necesarios (consulta + atencion + cliente + medico + motivo)
$sql = "
SELECT
  c.*,
  a.fecha AS fecha_atencion,
  a.numero_ticket,
  a.id_cliente,
  a.id_medico,
  cli.nombre_1 AS cli_n1, cli.nombre_2 AS cli_n2, cli.apellido_1 AS cli_a1, cli.apellido_2 AS cli_a2,
  cli.edad_anios AS cli_edad, cli.id_sexo AS cli_id_sexo,
  med.nombre_1 AS med_n1, med.nombre_2 AS med_n2, med.apellido_1 AS med_a1, med.apellido_2 AS med_a2,
  ms.nombre AS motivo_nombre,
  s.nombre AS sexo_nombre,
  esp.nombre AS especialidad_nombre
FROM tbl_atencion_consulta c
JOIN tbl_atencion a ON a.id = c.id_atencion
LEFT JOIN tbl_persona cli ON a.id_cliente = cli.id
LEFT JOIN tbl_persona med ON a.id_medico = med.id
LEFT JOIN cat_motivo_admision ms ON c.id_tipo_atencion = ms.id
LEFT JOIN cat_sexo s ON cli.id_sexo = s.id
LEFT JOIN cat_especialidad esp ON med.id_especialidad = esp.id
WHERE c.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

if (!$data) {
    http_response_code(404);
    echo "Registro no encontrado";
    exit;
}

// -- Clase TCPDF personalizada para header con imagen --
class MYPDF extends TCPDF {
    public $header_img = '';
    public function Header() {
        if ($this->header_img && file_exists($this->header_img)) {
            $w = 0; $h = 40; // altura en puntos (ajusta)
            $this->Image($this->header_img, 15, 8, $w, $h, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->Ln(28);
        } else {
            parent::Header();
        }
    }
    // pie opcional
    public function Footer() {
        $this->SetY(-20);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 5, 'Healink-Dataplus', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Crear PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// ** Tamaño media carta **
// Media carta (half-letter) en mm: 140 x 216 mm? (Carta 216 x 279 mm) -> media carta 216 x 140? 
// TCPDF permite tamaños personalizados: array(width, height) en mm.
// Aquí usaremos 140 x 216 (anchura 140mm, altura 216mm) en orientación vertical.
$pageWidthMm = 140;
$pageHeightMm = 216;
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->SetCreator('Healink');
$pdf->SetAuthor('Clinica');
$pdf->SetTitle('Ficha de consulta');
$pdf->SetMargins(15, 50, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setFontSubsetting(false);

// asignar imagen de header (ajusta ruta)
$headerImgPath = __DIR__ . '/../../assets/img/urm_receta_head_uro.png';
$pdf->header_img = $headerImgPath;

// Añadir página (tamaño personalizado)
$pdf->AddPage('P', array($pageWidthMm, $pageHeightMm));
$pdf->SetFont('helvetica', '', 10);

// Helper para imprimir fila
function row($label, $value) {
    $label = htmlspecialchars($label);
    $value = htmlspecialchars((string)$value);
    return "<tr><td style=\"width:35%;\"><b>{$label}</b></td><td>{$value}</td></tr>";
}

// Construir contenido HTML
$clienteNombre = trim(implode(' ', array_filter([$data['cli_n1'],$data['cli_n2'],$data['cli_a1'],$data['cli_a2']])));
$medicoNombre  = trim(implode(' ', array_filter([$data['med_n1'],$data['med_n2'],$data['med_a1'],$data['med_a2']])));
$fechaAtencion = $data['fecha_atencion'];
$numeroTicket  = $data['numero_ticket'];
$motivo        = $data['motivo_nombre'] ?? '';

$html = '<h4>Ficha de consulta</h4>';

// Sección: datos de la consulta
$html .= '<table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Fecha', $fechaAtencion);
$html .= row('Número ticket', $numeroTicket);
$html .= row('Motivo admisión', $motivo);
$html .= row('Médico', $medicoNombre);
$html .= '</table><hr />';

// Sección: datos del cliente
$html .= '<h5>Datos del cliente</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Nombre', $clienteNombre);
$html .= row('Edad (años)', $data['cli_edad']);
$html .= row('Sexo', $data['sexo_nombre']);
$html .= '</table><hr />';

// Sección: datos pediátricos
$html .= '<h5>Datos pediátricos</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Datos parto', $data['datos_parto']);
$html .= row('Datos recién nacido', $data['datos_recien_nacido']);
$html .= row('Alimentación 1er año', $data['alimentacion_1er_anio']);
$html .= row('Desarrollo psicomotor', $data['desarrollo_psicomotor']);
$html .= '</table><hr />';

// motivo_consulta, historia, sintomas
$html .= '<h5>Motivo e historia</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Motivo de consulta', $data['motivo_consulta']);
$html .= row('Historia enfermedad actual', $data['historia_enfermedad_actual']);
$html .= row('Síntomas', $data['sintomas']);
$html .= '</table><hr />';

// signos vitales
$html .= '<h5>Signos vitales</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Presión arterial', $data['sv_presion_arterial']);
$html .= row('Frecuencia cardiaca', $data['sv_frecuencia_cardiaca']);
$html .= row('Frecuencia respiratoria', $data['sv_frecuencia_respiratoria']);
$html .= row('Temperatura', $data['sv_temperatura']);
$html .= row('Auscultación pulmonar', $data['sv_ausc_pulmonar']);
$html .= '</table><hr />';

// antropometría
$html .= '<h5>Antropometría</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Talla (cm)', $data['talla']);
$html .= row('Peso (kg)', $data['peso']);
$html .= row('Circ. cefálica (cm)', $data['circ_cefalica']);
$html .= '</table><hr />';

// examen, diagnostico, tratamiento, medicamentos
$html .= '<h5>Examen y diagnóstico</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Examen físico', $data['examen_fisico']);
$html .= row('Diagnóstico', $data['diagnostico']);
$html .= '</table><hr />';

$html .= '<h5>Tratamiento / Medicamentos</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Tratamiento', $data['tratamiento']);
$html .= row('Medicamentos administrados', $data['medicamentos_administrados']);
$html .= '</table><hr />';

$html .= '<h5>Indicaciones / Receta / Laboratorio</h5><table cellpadding="4" cellspacing="0" border="0">';
$html .= row('Indicaciones médicas', $data['indicaciones_medicas']);
$html .= row('Receta', $data['receta']);
$html .= row('Laboratorios', $data['laboratorios']);
$html .= row('Fecha próxima cita', $data['fecha_proxima_cita']);
$html .= '</table>';

// Escribir HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Salida: forzar descarga / apertura en navegador
$filename = 'Ficha_consulta_' . $numeroTicket . '_' . $id . '.pdf';
$pdf->Output($filename, 'I'); // I = open in browser; use 'D' to force download
exit;
