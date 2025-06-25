<?php
require_once '../../vendor/autoload.php';
require_once '../conexion.php';
require_once '../modelo_empresa.php';
require_once '../modelo_factura.php';
require_once '../modelo_detalle_factura.php';

use TCPDF;

// Verificar ID de factura
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de factura no especificado o inválido');
}

$id_factura = (int) $_GET['id'];

try {
    // Obtener información de la empresa
    $infoEmpresa = ModeloEmpresa::mdlObtenerInfoEmpresa();
    
    // Obtener datos de la factura
    $factura = ModeloFactura::mdlObtenerFactura($id_factura);
    if (!$factura) {
        die('Factura no encontrada');
    }
    
    // Obtener detalles de la factura
    $detalles = ModeloDetalleFactura::mdlObtenerDetalles($id_factura);
    if (empty($detalles)) {
        die('No se encontraron detalles para esta factura');
    }

    // Crear nuevo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar información del documento
    $pdf->SetCreator('Sistema de Taller');
    $pdf->SetAuthor($infoEmpresa['nombre_empresa'] ?? 'Sistema de Taller');
    $pdf->SetTitle('Factura #' . $factura['numero_factura']);
    $pdf->SetSubject('Factura de Servicios');
    $pdf->SetKeywords('Factura, Taller, Servicios, Reparación');

    // Quitar header y footer por defecto
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Configurar márgenes
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Agregar página
    $pdf->AddPage();

    // =====================================================
    // ENCABEZADO DE LA EMPRESA
    // =====================================================
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(220, 53, 69); // Color rojo del tema
    $pdf->Cell(0, 10, strtoupper($infoEmpresa['nombre_empresa'] ?? 'SISTEMA DE TALLER'), 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    
    if (!empty($infoEmpresa['direccion'])) {
        $pdf->Cell(0, 5, 'Dirección: ' . $infoEmpresa['direccion'], 0, 1, 'L');
    }
    
    $contacto = [];
    if (!empty($infoEmpresa['telefono'])) $contacto[] = 'Tel: ' . $infoEmpresa['telefono'];
    if (!empty($infoEmpresa['email'])) $contacto[] = 'Email: ' . $infoEmpresa['email'];
    if (!empty($contacto)) {
        $pdf->Cell(0, 5, implode(' | ', $contacto), 0, 1, 'L');
    }
    
    if (!empty($infoEmpresa['ruc'])) {
        $pdf->Cell(0, 5, 'RUC: ' . $infoEmpresa['ruc'], 0, 1, 'L');
    }

    // Línea separadora
    $pdf->Ln(5);
    $pdf->SetDrawColor(220, 53, 69);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);

    // =====================================================
    // TÍTULO DE FACTURA
    // =====================================================
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(220, 53, 69);
    $pdf->Cell(0, 15, 'FACTURA', 0, 1, 'C');
    
    // Número de factura
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'N° ' . $factura['numero_factura'], 0, 1, 'C');
    $pdf->Ln(5);

    // =====================================================
    // INFORMACIÓN DE LA FACTURA Y CLIENTE
    // =====================================================
    // Crear tabla de información
    $pdf->SetFont('helvetica', '', 10);
    
    // Columna izquierda - Datos de la factura
    $pdf->SetXY(15, $pdf->GetY());
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(85, 6, 'INFORMACIÓN DE FACTURA', 0, 0, 'L');
    
    // Columna derecha - Datos del cliente
    $pdf->SetXY(105, $pdf->GetY());
    $pdf->Cell(85, 6, 'DATOS DEL CLIENTE', 0, 1, 'L');
    
    // Contenido
    $pdf->SetFont('helvetica', '', 9);
    $y_inicial = $pdf->GetY();
    
    // Información de factura (columna izquierda)
    $pdf->SetXY(15, $y_inicial);
    $info_factura = [
        'Fecha: ' . date('d/m/Y H:i', strtotime($factura['fecha_emision'])),
        'Estado: ' . ucfirst($factura['estado']),
        'Tipo: ' . ucfirst($factura['tipo_factura']),
        'Método de Pago: ' . ucfirst($factura['metodo_pago']),
        'Condición: ' . ($factura['condicion_pago'] ?? 'Contado')
    ];
    
    foreach ($info_factura as $info) {
        $pdf->Cell(85, 5, $info, 0, 1, 'L');
        $pdf->SetX(15);
    }
    
    // Información del cliente (columna derecha)
    $pdf->SetXY(105, $y_inicial);
    $info_cliente = [
        'Cliente: ' . $factura['nombre_cliente'],
        'Cédula: ' . ($factura['cedula'] ?? 'N/A'),
        'Teléfono: ' . ($factura['telefono'] ?? 'N/A'),
        'Email: ' . ($factura['email'] ?? 'N/A'),
        'Dirección: ' . ($factura['direccion'] ?? 'N/A')
    ];
    
    foreach ($info_cliente as $info) {
        $pdf->Cell(85, 5, $info, 0, 1, 'L');
        $pdf->SetX(105);
    }
    
    $pdf->Ln(10);

    // =====================================================
    // TABLA DE DETALLES
    // =====================================================
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 8, 'DETALLES DE LA FACTURA', 0, 1, 'L');
    $pdf->Ln(2);

    // Cabecera de la tabla
    $pdf->SetFillColor(220, 53, 69);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 9);
    
    $pdf->Cell(15, 8, '#', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Tipo', 1, 0, 'C', true);
    $pdf->Cell(70, 8, 'Descripción', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Cant.', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Precio Unit.', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Subtotal', 1, 1, 'C', true);

    // Contenido de la tabla
    $pdf->SetFillColor(245, 245, 245);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    
    $fill = false;
    $subtotal_general = 0;
    $item_num = 1;
    
    foreach ($detalles as $detalle) {
        $pdf->Cell(15, 6, $item_num, 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, ucfirst($detalle['tipo']), 1, 0, 'C', $fill);
        
        // Manejar descripciones largas
        $descripcion = $detalle['descripcion'];
        if (strlen($descripcion) > 45) {
            $descripcion = substr($descripcion, 0, 42) . '...';
        }
        $pdf->Cell(70, 6, $descripcion, 1, 0, 'L', $fill);
        
        $pdf->Cell(20, 6, number_format($detalle['cantidad'], 0), 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, '₲ ' . number_format($detalle['precio_unitario'], 0, ',', '.'), 1, 0, 'R', $fill);
        $pdf->Cell(25, 6, '₲ ' . number_format($detalle['subtotal'], 0, ',', '.'), 1, 1, 'R', $fill);
        
        $subtotal_general += $detalle['subtotal'];
        $fill = !$fill;
        $item_num++;
    }

    // =====================================================
    // TOTALES
    // =====================================================
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    
    // Caja de totales
    $x_totales = 130;
    $pdf->SetX($x_totales);
    
    // Subtotal
    $pdf->Cell(35, 6, 'Subtotal:', 0, 0, 'R');
    $pdf->Cell(25, 6, '₲ ' . number_format($factura['subtotal'], 0, ',', '.'), 1, 1, 'R');
    
    // Descuento (si aplica)
    if ($factura['descuento'] > 0) {
        $pdf->SetX($x_totales);
        $pdf->Cell(35, 6, 'Descuento:', 0, 0, 'R');
        $pdf->Cell(25, 6, '₲ ' . number_format($factura['descuento'], 0, ',', '.'), 1, 1, 'R');
    }
    
    // IVA (si aplica)
    if ($factura['iva'] > 0) {
        $pdf->SetX($x_totales);
        $pdf->Cell(35, 6, 'IVA:', 0, 0, 'R');
        $pdf->Cell(25, 6, '₲ ' . number_format($factura['iva'], 0, ',', '.'), 1, 1, 'R');
    }
    
    // Total final
    $pdf->SetX($x_totales);
    $pdf->SetFillColor(220, 53, 69);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(35, 8, 'TOTAL:', 0, 0, 'R');
    $pdf->Cell(25, 8, '₲ ' . number_format($factura['total'], 0, ',', '.'), 1, 1, 'R', true);
    
    $pdf->SetTextColor(0, 0, 0);

    // =====================================================
    // OBSERVACIONES
    // =====================================================
    if (!empty($factura['observaciones'])) {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 5, $factura['observaciones'], 0, 'L');
    }

    // =====================================================
    // PIE DE PÁGINA
    // =====================================================
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Gracias por confiar en nuestros servicios', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // =====================================================
    // GENERAR EL PDF
    // =====================================================
    $nombre_archivo = 'Factura_' . $factura['numero_factura'] . '.pdf';
    $pdf->Output($nombre_archivo, 'I');

} catch (Exception $e) {
    error_log("Error generando PDF de factura: " . $e->getMessage());
    die('Error al generar el PDF: ' . $e->getMessage());
}
?>