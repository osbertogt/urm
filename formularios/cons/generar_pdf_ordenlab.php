<?php
require_once('../../assets/tcpdf/tcpdf.php'); 

// Obtener parámetros
$id_cita = $_GET['id_cita'] ?? '';
$paciente = $_GET['paciente'] ?? '';
$edad = $_GET['edad'] ?? '';
$sexo = $_GET['sexo'] ?? '';
$servicios_json = $_GET['servicios'] ?? '[]';
$servicios = json_decode(urldecode($servicios_json), true);

// Crear PDF
class PrescripcionPDF extends TCPDF {
    // Header
    public function Header() {
        $img_file = '../../assets/img/urm_receta_head_uro.png';
        if (file_exists($img_file)) {
 //           $this->Image($img_file, 0, 0, 140, 25, 'PNG'); // ancho del PDF = 140mm

			$w = $this->getPageWidth();
			$h = 0; // alto proporcional

			$this->Image(
				$img_file,
				0,
				0,
				$w,
				$h,
				'PNG',
				'',
				'T',
				false,
				300
			);
        }
        $this->Ln(50); // deja espacio debajo del encabezado
        // Título
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 15, 'PRESCRIPCIÓN DE LABORATORIO', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(35);
    }

   
    // Footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Crear instancia del PDF
//$pdf = new PrescripcionPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf = new PrescripcionPDF('P', 'mm', [140, 216], true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Sistema Médico');
$pdf->SetAuthor('Sistema Médico');
$pdf->SetTitle('Prescripción de Laboratorio');
$pdf->SetSubject('Prescripción Médica');

// Margenes
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Añadir página
$pdf->AddPage();

// Contenido
$pdf->SetFont('helvetica', '', 10);
$pdf->Ln(5);

// Datos del paciente
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 10, 'DATOS DEL PACIENTE', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);

$pdf->Cell(40, 6, 'Paciente:', 0, 0, 'L');
$pdf->Cell(0, 6, htmlspecialchars($paciente), 0, 1, 'L');

$pdf->Cell(40, 6, 'Edad:', 0, 0, 'L');
$pdf->Cell(0, 6, htmlspecialchars($edad), 0, 1, 'L');

$pdf->Cell(40, 6, 'Sexo:', 0, 0, 'L');
$pdf->Cell(0, 6, htmlspecialchars($sexo == 'Masculino' ? 'Masculino' : 'Femenino'), 0, 1, 'L');

$pdf->Cell(40, 6, 'Fecha:', 0, 0, 'L');
$pdf->Cell(0, 6, date('d/m/Y'), 0, 1, 'L');

$pdf->Ln(5);

// Servicios prescritos
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 10, 'EXÁMENES DE LABORATORIO SOLICITADOS', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);

if (!empty($servicios) && is_array($servicios)) {
    $contador = 1;
    foreach ($servicios as $servicio) {
        $pdf->Cell(10, 6, $contador . '.', 0, 0, 'L');
        $pdf->Cell(0, 6, htmlspecialchars($servicio['nombre']), 0, 1, 'L');
        
        if (!empty($servicio['notas'])) {
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->Cell(10, 6, '', 0, 0, 'L');
            $pdf->MultiCell(0, 6, 'Observaciones: ' . htmlspecialchars($servicio['notas']), 0, 'L');
            $pdf->SetFont('helvetica', '', 9);
        }
        
        $pdf->Ln(2);
        $contador++;
    }
} else {
    $pdf->Cell(0, 6, 'No se han especificado exámenes', 0, 1, 'L');
}

$pdf->Ln(15);

// Instrucciones
/* $pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 10, 'INSTRUCCIONES:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
 */
/* $instrucciones = [
    'Presentarse en ayunas de 8-12 horas',
    'Traer esta orden al laboratorio',
    'Los resultados estarán disponibles en 24-48 horas',
    'Para mayores informes, contactar al laboratorio'
];

foreach ($instrucciones as $inst) {
    $pdf->Cell(10, 6, '', 0, 0, 'L');
    $pdf->Cell(5, 6, '•', 0, 0, 'L');
    $pdf->Cell(0, 6, $inst, 0, 1, 'L');
}
 */
$pdf->Ln(20);

// Firma
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 6, '__________________________', 0, 1, 'C');
$pdf->Cell(0, 6, 'Firma y Sello del Médico', 0, 1, 'C');

// Nombre del médico (podrías obtenerlo de la sesión)
/* $pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'Dr. Nombre del Médico', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'Especialidad', 0, 1, 'C');
$pdf->Cell(0, 6, 'Registro Profesional: XXXXXX', 0, 1, 'C');
 */
// Generar PDF
$pdf->Output('orden_lab_' . $id_cita . '.pdf', 'I');
?>