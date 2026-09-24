<?php
require_once('../../assets/tcpdf/tcpdf.php');
require_once '../../assets/dbc.php';

// ==== ID de atención recibido por GET ====
$id_atencion = intval($_GET['id_atencion']); // Sanitiza

// ==== Consultar datos de organización (para logo/header/footer) ====
$query_org = "SELECT logo, header_c FROM cat_organizacion LIMIT 1";
$org = mysqli_fetch_assoc(mysqli_query($conn, $query_org));

// Rutas absolutas para TCPDF
$logo_path   = !empty($org['logo'])   ? '../../assets/img/' . $org['logo']   : '';
$header_path = !empty($org['header_c']) ? '../../assets/img/' . $org['header_c'] : '';

// ==== Clase personalizada ====
class MYPDF extends TCPDF {
    public $logo;
    public $header_img;

	public function Header() {
		$img = $this->header_img ?: $this->logo;
		if (!empty($img) && file_exists($img)) {
			$ancho_util = $this->getPageWidth() - $this->lMargin - $this->rMargin;
			$this->Image(
				$img,
				$this->lMargin,
				0,
				$ancho_util,
				20, // alto automático
				'', '', '', false, 300, '', false, false, 0
			);
			$this->Ln(10); // espacio debajo del header
		}
	}

    // Footer deshabilitado
    public function Footer() {}
}

// ==== Consultar datos de encabezado de cuenta ====
$query_info = "
SELECT ta.id_cliente,ta.fecha,ta.codigo,ta.id_tipo_precio,ta.id_tipo_pago1,ta.total,ta.numero_pagos,ta.abono,ta.saldo,
CONCAT_WS(' ',
			TRIM(tp.nombre_1),
			TRIM(tp.nombre_2),
			TRIM(tp.apellido_1),
			TRIM(tp.apellido_2),
			IF(TRIM(tp.apellido_casada) IS NULL OR TRIM(tp.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(tp.apellido_casada)))
		) AS nombre_completo,
        ctp.nombre tipoprecio
FROM tbl_cuenta_cliente ta
JOIN tbl_persona tp ON tp.id=ta.id_cliente
JOIN cat_tipo_precio ctp ON ctp.id=ta.id_tipo_precio
WHERE ta.id = $id_atencion
LIMIT 1";

$info = mysqli_fetch_assoc(mysqli_query($conn, $query_info));
$clientecta = $info['id_cliente'];
$fechacta = $info['fecha'];
$idtipopago=$info['id_tipo_pago1'];
$recargo=0;
$idtipopago=(int)$idtipopago;
if ( $idtipopago === 1) {
   $recargo = 0.05;
}
$idprecio = $info['id_tipo_precio'];



// ==== Consultar variables validadas ====
$query_vars = "
SELECT 'Servicios prestados' tipo,cs.nombre descripcion,tas.id_atencion,tas.id_servicio,ta.id_cliente,ta.fecha fecha_atencion, 
 tps.id_tipo_precio,tps.precio, 1 as cantidad, tps.precio subtotal
FROM tbl_atencion_servicio tas
JOIN tbl_atencion ta ON ta.id=tas.id_atencion
JOIN tbl_precio_servicio tps ON tps.id_servicio=tas.id_servicio
JOIN cat_servicio cs ON cs.id=tas.id_servicio
  WHERE ta.id_cliente = $clientecta
    AND DATE(ta.fecha) = DATE('$fechacta')
    AND tps.id_tipo_precio = $idprecio
  ORDER BY cs.nombre;";

$result = mysqli_query($conn, $query_vars);

// Agrupar por tipo
$variables = [];
while ($row = mysqli_fetch_assoc($result)) {
    $variables[$row['tipo']][] = $row;
}

// ==== Configurar PDF ====
// 8.5 pulgadas ancho x 5 pulgadas alto (215.9 mm x 127 mm)
$pdf = new MYPDF('P', 'mm', array(127, 215.9), true, 'UTF-8', false);
$pdf->logo       = $logo_path;
$pdf->header_img = $header_path;
$pdf->setPrintFooter(false); // Desactivar pie de página

$pdf->SetTitle("Comprobante_" . $info['codigo']);
// Márgenes: izq, sup (después del header), der
$pdf->SetMargins(10, 15, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage('P', array(127, 215.9));

// ==== Título ====
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Ln(5);
$pdf->Cell(0, 8, 'Cuenta No. '.$info['codigo'], 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Fecha: ' . $info['fecha'], 0, 1);
$pdf->Cell(0, 5, 'Paciente: ' . $info['nombre_completo'] . '  | ID: ' . $info['id_cliente'], 0, 1);
$pdf->Ln(3);

// ==== Cuerpo ====
foreach ($variables as $tipo => $items) {
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 6, $tipo, 0, 1, 'L');

    // Cabecera tabla
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(50, 6, 'Servicio', 1, 0, 'L', true);
    $pdf->Cell(30, 6, 'Precio', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 9);
    foreach ($items as $v) {
		$precio=$v['precio']+($v['precio']*$recargo);
		//$precio=$recargo;
        $pdf->Cell(50, 5, $v['descripcion'], 1, 0, 'L');
        $pdf->Cell(30, 5, number_format($precio, 2, '.', ','), 1, 1, 'C');
    }
    $pdf->Ln(2);
}

//==== Totales ====
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, 
    'Total: Q.' . number_format($info['total'], 2) . 
    '   | Abono: Q.' . number_format($info['abono'], 2) . 
    '   | Saldo: Q.' . number_format($info['saldo'], 2), 
1, 1);
$pdf->Cell(0, 6, 
    'Tipo precio: ' . $info['tipoprecio'], 
1, 1);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Ln(5);

// ==== Salida ====
$pdf->Output("comprobante_$id_atencion.pdf", 'I');
?>
