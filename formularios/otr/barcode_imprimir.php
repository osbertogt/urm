<?php
require_once('../../assets/tcpdf/tcpdf.php');
require_once '../../assets/dbc.php';

$idOrden = $_GET['id_orden'] ?? '0000';

$query_info = "
SELECT 
    ta.id AS id_atencion,
    ta.numero_ticket
FROM tbl_atencion ta
WHERE ta.id = $idOrden
";

$info = mysqli_fetch_assoc(mysqli_query($conn, $query_info));


$pdf = new TCPDF('P', 'mm', [50, 30], true, 'UTF-8', false);
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

// Código de barras tipo CODE 128
$style = [
    'position' => '',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => false,
    'hpadding' => 'auto',
    'vpadding' => 'auto',
    'fgcolor' => [0,0,0],
    'bgcolor' => false, // fondo blanco
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 10,
    'stretchtext' => 4
];

$pdf->write1DBarcode($info['numero_ticket'], 'C128', '', '', '', 18, 0.4, $style, 'N');

$pdf->Output('barcode.pdf', 'I'); // Se muestra en el navegador
?>
