<?php
require_once('../../../assets/tcpdf/tcpdf.php');

// === Sanitizar variables ===
$laboratorios   = isset($_GET['laboratorios']) ? htmlspecialchars(trim($_GET['laboratorios']), ENT_QUOTES, 'UTF-8') : '';
$paciente = isset($_GET['paciente']) ? htmlspecialchars(trim($_GET['paciente']), ENT_QUOTES, 'UTF-8') : 'Paciente';
$fecha    = isset($_GET['fecha']) ? htmlspecialchars(trim($_GET['fecha']), ENT_QUOTES, 'UTF-8') : date('d/m/Y');

if (empty($laboratorios)) {
    die('No se recibió texto para generar la orden.');
}

// === Clase personalizada con header/footer ===
class MYPDF extends TCPDF {
    public function Header() {
        $img_file = '../../../assets/img/urm_receta_head_uro.png';
        if (file_exists($img_file)) {
            $this->Image($img_file, 0, 0, 140, 25, 'PNG'); 
        }
        $this->Ln(25);
    }

    public function Footer() {
        $img_file = '';
        if (file_exists($img_file)) {
            $this->Image($img_file, 0, 200, 140, 16, 'PNG');
        }
    }
}

// === Crear PDF ===
$pdf = new MYPDF('P', 'mm', array(140, 216), true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Healink-Dataplus');
$pdf->SetTitle('Orden de Laboratorio');
$pdf->SetMargins(15, 30, 15); 
$pdf->SetAutoPageBreak(true, 1);
$pdf->AddPage();

// === Contenido ===
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Orden de laboratorio', 0, 1, 'C');
$pdf->Ln(3);

$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 8, 'Paciente: ' . $paciente, 0, 1);
$pdf->Cell(0, 8, 'Fecha: ' . $fecha, 0, 1);
$pdf->Ln(5);

// === Receta ===
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, $laboratorios, 0, 'L', false);
$pdf->Ln(15);

// === Firma ===
$pdf->Cell(0, 10, '_____________________________', 0, 1, 'C');
$pdf->Cell(0, 6, 'Dr. Mario Miranda', 0, 1, 'C');

// === Salida ===
$pdf->Output('orden_labs.pdf', 'I');
?>
