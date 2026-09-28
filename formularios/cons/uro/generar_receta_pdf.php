<?php
require_once('../../../assets/tcpdf/tcpdf.php');
require_once __DIR__ . '/formato_fecha.php';

// === Sanitizar variables ===
$receta   = isset($_GET['receta']) ? htmlspecialchars(trim($_GET['receta']), ENT_QUOTES, 'UTF-8') : '';
$tratamiento   = isset($_GET['tratamiento']) ? htmlspecialchars(trim($_GET['tratamiento']), ENT_QUOTES, 'UTF-8') : '';
$paciente = isset($_GET['paciente']) ? htmlspecialchars(trim($_GET['paciente']), ENT_QUOTES, 'UTF-8') : 'Paciente';
$fecha    = isset($_GET['fecha']) ? htmlspecialchars(formatoFecha($_GET['fecha']), ENT_QUOTES, 'UTF-8') : date('d-m-Y');
$proxima_cita    = isset($_GET['proxima_cita']) ? htmlspecialchars(formatoFecha($_GET['proxima_cita']), ENT_QUOTES, 'UTF-8') : date('d-m-Y');

if (empty($receta)) {
    die('No se recibió texto para generar la receta.');
}

// === Clase personalizada con header/footer ===
class MYPDF extends TCPDF {
    public function Header() {
        $img_file = '../../../assets/img/urm_receta_head_uro.png';
        if (file_exists($img_file)) {
            //$this->Image($img_file, 0, 0, 140, 25, 'PNG'); // ancho del PDF = 140mm
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
        $this->Ln(38); // deja espacio debajo del encabezado
    }

    public function Footer() {
        $img_file = '';
        if (file_exists($img_file)) {
            $this->Image($img_file, 0, 200, 140, 16, 'PNG');
        }
    }
}

// === Crear PDF ===
$pdf = new MYPDF('P', 'mm', [140, 216], true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Médico');
$pdf->SetTitle('Receta Médica');
$pdf->SetMargins(15, 45, 15); // margen superior aumentado por el header
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// === Contenido ===
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Receta Médica', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 4, 'Paciente: ' . $paciente, 0, 1);
$pdf->Cell(0, 4, 'Fecha: ' . $fecha, 0, 1);
$pdf->Ln(3);

// === Receta ===
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 4, $receta, 0, 'L', false);
$pdf->MultiCell(0, 4, $tratamiento, 0, 'L', false);
$pdf->Cell(0, 5, 'Próxima cita: ' . $proxima_cita, 0, 1);
$pdf->Ln(15);

// === Firma ===
$pdf->Cell(0, 10, '_____________________________', 0, 1, 'C');
$pdf->Cell(0, 6, 'Dr. Mario Miranda', 0, 1, 'C');

// === Salida ===
$pdf->Output('receta_medica.pdf', 'I');
?>
