<?php

require_once '../../../assets/dbc.php';
require_once '../../../assets/tcpdf/tcpdf.php';
require_once __DIR__ . '/formato_fecha.php'; 
$id = isset($_GET['id_consulta']) ? (int) $_GET['id_consulta'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "ID inválido";
    exit;
}
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
LEFT JOIN cat_motivo_admision ms ON a.id_motivo_admision = ms.id
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

function normalizarTexto($texto) {
    if ($texto === null || $texto === '') {
        return '';
    }

    // Normalizar saltos de línea
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);

    // Convertir saltos a <br>
    return nl2br($texto, false); // <br>
}



class MYPDF extends TCPDF {
    public $header_img = '';
    // Sección que se está escribiendo; si hay salto de página a media sección se repite su nombre
    public $seccionActual = null;
    public $margenSuperiorBase = 50;
    public function Header() {
        $this->pintarEncabezado();

        if ($this->seccionActual !== null) {
            $this->SetFont('helvetica', 'B', 9);
            $this->SetXY($this->original_lMargin, $this->margenSuperiorBase);
            $this->Cell(0, 5, $this->seccionActual . ' (continuación)', 0, 1, 'L');
            $this->tMargin = $this->margenSuperiorBase + 7;
        } else {
            $this->tMargin = $this->margenSuperiorBase;
        }
    }
    private function pintarEncabezado() {
        if ($this->header_img && file_exists($this->header_img)) {
			$w = $this->getPageWidth();
			$h = 0; // alto proporcional

			$this->Image(
				$this->header_img,
				0,
				0,
				$w,
				$h,
				'',
				'',
				'T',
				false,
				300
			);
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

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


$pageWidthMm = 140;
$pageHeightMm = 216;
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->SetCreator('Healink');
$pdf->SetAuthor('Clinica');
$pdf->SetTitle('Ficha de consulta');
$pdf->SetMargins(15, 50, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setFontSubsetting(false);


$headerImgPath = __DIR__ . '/../../../assets/img/urm_receta_head_uro.png';
$pdf->header_img = $headerImgPath;

$pdf->AddPage('P', array($pageWidthMm, $pageHeightMm));
$pdf->SetFont('helvetica', '', 9);

function row($label, $value) {
    return '
        <tr>
            <td width="30%" style="font-weight:semi-bold;">'.$label.':</td>
            <td width="70%">'.$value.'</td>
        </tr>
    ';
}

// Si no queda espacio para al menos $mm, pasar a la siguiente página antes de escribir
function asegurarEspacio($pdf, $mm) {
    if ($pdf->GetY() + $mm > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
        $pdf->AddPage();
    }
}

function escribirHtml($pdf, $html) {
    $pdf->writeHTML($html, false, false, true, false, '');
}

// Un encabezado de grupo (h4) no debe quedar solo al pie de la página
function escribirTitulo($pdf, $titulo) {
    asegurarEspacio($pdf, 15);
    escribirHtml($pdf, '<h4>' . $titulo . '</h4>');
}

// Cada campo va en su propia tabla para saber qué sección se parte en el salto de página
function escribirCampo($pdf, $label, $value) {
    asegurarEspacio($pdf, 8);
    $pdf->seccionActual = $label;
    escribirHtml($pdf, '<table cellpadding="0" cellspacing="0" border="0">' . row($label, $value) . '</table>');
    $pdf->seccionActual = null;
}


$clienteNombre = trim(implode(' ', array_filter([$data['cli_n1'],$data['cli_n2'],$data['cli_a1'],$data['cli_a2']])));
$medicoNombre  = trim(implode(' ', array_filter([$data['med_n1'],$data['med_n2'],$data['med_a1'],$data['med_a2']])));
$fechaAtencion = formatoFecha($data['fecha_atencion']);
$numeroTicket  = $data['numero_ticket'];
$motivo        = $data['motivo_nombre'] ?? '';

$receta = normalizarTexto($data['receta']);
$laboratorios = normalizarTexto($data['laboratorios']);
$indicaciones_medicas = normalizarTexto($data['indicaciones_medicas']);
$diagnostico = normalizarTexto($data['diagnostico']);
$tratamiento = normalizarTexto($data['tratamiento']);
$medicamentos_administrados = normalizarTexto($data['medicamentos_administrados']);
$motivo_consulta = normalizarTexto($data['motivo_consulta']);
$sintomas = normalizarTexto($data['sintomas']);
$historia_enfermedad_actual = normalizarTexto($data['historia_enfermedad_actual']);

$html = '<h3 style="text-align:center;">Ficha de consulta</h3>';

$html .= '<table cellpadding="0" cellspacing="0" border="0">';
$html .= row('Fecha', $fechaAtencion);
$html .= row('Número consulta', $numeroTicket);
$html .= row('Tipo de consulta', $motivo);
$html .= row('Médico', $medicoNombre);
$html .= '</table><hr/>';

$html .= '<h4>Datos del paciente</h4><table cellpadding="0" cellspacing="0" border="0">';
$html .= row('Nombre', $clienteNombre);
$html .= row('Edad (años)', $data['cli_edad']);
$html .= row('Sexo', $data['sexo_nombre']);
$html .= '</table><hr/>';
escribirHtml($pdf, $html);

escribirTitulo($pdf, 'Motivo e historia');
escribirCampo($pdf, 'Motivo de consulta', $motivo_consulta);
escribirCampo($pdf, 'Historia enfermedad actual', $historia_enfermedad_actual);
escribirHtml($pdf, '<hr/>');

$signos = [
    'PA' => $data['sv_presion_arterial'],
    'FC' => $data['sv_frecuencia_cardiaca'],
    'FR' => $data['sv_frecuencia_respiratoria'],
    'T'  => $data['sv_temperatura'],
];
$signosTxt = [];
foreach ($signos as $etq => $val) {
    $signosTxt[] = '<b>' . $etq . ':</b> ' . htmlspecialchars(trim((string)$val));
}
asegurarEspacio($pdf, 8);
escribirHtml($pdf, '<p><b>Signos vitales:</b>&nbsp;&nbsp;&nbsp;&nbsp;' . implode('&nbsp;&nbsp;&nbsp;&nbsp;', $signosTxt) . '</p><hr/>');

escribirTitulo($pdf, 'Examen e impresión clínica');
escribirCampo($pdf, 'Examen físico', normalizarTexto($data['examen_fisico']));
escribirCampo($pdf, 'Impresión clínica', $diagnostico);
escribirHtml($pdf, '<hr/>');

escribirTitulo($pdf, 'Medicamentos');
escribirCampo($pdf, 'Medicamentos administrados', $medicamentos_administrados);
escribirHtml($pdf, '<hr/>');

escribirTitulo($pdf, 'Indicaciones / Receta / Laboratorio');
escribirCampo($pdf, 'Indicaciones médicas', $indicaciones_medicas);
escribirCampo($pdf, 'Receta', $receta);
escribirCampo($pdf, 'Laboratorios', $laboratorios);
escribirCampo($pdf, 'Fecha próxima cita', formatoFecha($data['fecha_proxima_cita']));

$filename = 'Ficha_consulta_' . $numeroTicket . '_' . $id . '.pdf';
$pdf->Output($filename, 'I'); 
exit;
