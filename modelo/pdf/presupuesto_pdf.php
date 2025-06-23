<?php
require_once(__DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once(__DIR__ . '/../conexion.php');
require_once(__DIR__ . '/../../controlador/presupuesto_controlador.php');

class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'PRESUPUESTO', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Verificar si se recibió el ID del presupuesto
if (!isset($_GET['id'])) {
    die('ID de presupuesto no especificado');
}

$id_presupuesto = $_GET['id'];

// Obtener datos del presupuesto
$presupuesto = PresupuestoControlador::ctrObtenerPresupuesto($id_presupuesto);
$detalles = PresupuestoControlador::ctrObtenerDetallesPresupuesto($id_presupuesto);

if (!$presupuesto) {
    die('Presupuesto no encontrado');
}

// Crear nuevo documento PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Establecer información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Taller Mecánico');
$pdf->SetTitle('Presupuesto #' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT));

// Establecer márgenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Establecer saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Agregar una página
$pdf->AddPage();

// Establecer fuente
$pdf->SetFont('helvetica', '', 12);

// Información del vehículo y cliente
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Información del Vehículo:', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 7, 'Marca: ' . $presupuesto['marca'], 0, 1);
$pdf->Cell(0, 7, 'Modelo: ' . $presupuesto['modelo'], 0, 1);
$pdf->Cell(0, 7, 'Matrícula: ' . $presupuesto['matricula'], 0, 1);
$pdf->Cell(0, 7, 'Cliente: ' . $presupuesto['nombre_cliente'], 0, 1);

$pdf->Ln(5);

// Información del presupuesto
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Información del Presupuesto:', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 7, 'Fecha de Emisión: ' . date('d/m/Y H:i', strtotime($presupuesto['fecha_emision'])), 0, 1);
$pdf->Cell(0, 7, 'Fecha de Validez: ' . date('d/m/Y', strtotime($presupuesto['fecha_validez'])), 0, 1);
$pdf->Cell(0, 7, 'Estado: ' . ucfirst($presupuesto['estado']), 0, 1);
$pdf->Cell(0, 7, 'Elaborado por: ' . $presupuesto['nombre_personal'], 0, 1);

$pdf->Ln(5);

// Tabla de detalles
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Detalles del Presupuesto:', 0, 1);

// Cabecera de la tabla
$pdf->SetFillColor(200, 200, 200);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 7, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(70, 7, 'Descripción', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Precio Unit.', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Subtotal', 1, 1, 'C', true);

// Detalles
$pdf->SetFont('helvetica', '', 10);
foreach ($detalles as $detalle) {
    $pdf->Cell(30, 7, ucfirst($detalle['tipo']), 1, 0, 'C');
    $pdf->Cell(70, 7, $detalle['descripcion'], 1, 0, 'L');
    $pdf->Cell(25, 7, $detalle['cantidad'], 1, 0, 'C');
    $pdf->Cell(30, 7, '₲ ' . number_format($detalle['precio_unitario'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(35, 7, '₲ ' . number_format($detalle['subtotal'], 0, ',', '.'), 1, 1, 'R');
}

// Total
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(155, 10, 'TOTAL:', 0, 0, 'R');
$pdf->Cell(35, 10, '₲ ' . number_format($presupuesto['total'], 0, ',', '.'), 0, 1, 'R');

// Observaciones
if (!empty($presupuesto['observaciones'])) {
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Observaciones:', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 7, $presupuesto['observaciones'], 0, 'L');
}

// Nota de validez
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 7, 'Este presupuesto tiene validez hasta el ' . date('d/m/Y', strtotime($presupuesto['fecha_validez'])), 0, 1, 'C');

// Generar el PDF
$pdf->Output('Presupuesto_' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
