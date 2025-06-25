<?php
require_once '../../vendor/autoload.php';
require_once '../conexion.php';
require_once '../modelo_empresa.php';
require_once '../modelo_presupuesto.php';
require_once '../modelo_detalle_presupuesto.php';

use TCPDF;

// Verificar ID de presupuesto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de presupuesto no especificado o inválido');
}

$id_presupuesto = (int) $_GET['id'];

try {
    // Obtener información de la empresa
    $infoEmpresa = ModeloEmpresa::mdlObtenerInfoEmpresa();
    
    // Obtener datos del presupuesto
    $presupuesto = ModeloPresupuesto::mdlObtenerPresupuesto($id_presupuesto);
    if (!$presupuesto) {
        die('Presupuesto no encontrado');
    }
    
    // Obtener detalles del presupuesto
    $detalles = ModeloDetallePresupuesto::mdlObtenerDetalles($id_presupuesto);
    if (empty($detalles)) {
        die('No se encontraron detalles para este presupuesto');
    }

    // Crear nuevo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar información del documento
    $pdf->SetCreator('Sistema de Taller');
    $pdf->SetAuthor($infoEmpresa['nombre_empresa'] ?? 'Sistema de Taller');
    $pdf->SetTitle('Presupuesto #' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT));
    $pdf->SetSubject('Presupuesto de Servicios');
    $pdf->SetKeywords('Presupuesto, Cotización, Taller, Servicios, Reparación');

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
    $pdf->SetTextColor(40, 167, 69); // Color verde para presupuesto
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
    $pdf->SetDrawColor(40, 167, 69);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);

    // =====================================================
    // TÍTULO DE PRESUPUESTO
    // =====================================================
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(40, 167, 69);
    $pdf->Cell(0, 15, 'PRESUPUESTO', 0, 1, 'C');
    
    // Número de presupuesto
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'N° ' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');
    $pdf->Ln(5);

    // =====================================================
    // INFORMACIÓN DEL PRESUPUESTO Y CLIENTE
    // =====================================================
    // Crear tabla de información
    $pdf->SetFont('helvetica', '', 10);
    
    // Columna izquierda - Datos del presupuesto
    $pdf->SetXY(15, $pdf->GetY());
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(85, 6, 'INFORMACIÓN DEL PRESUPUESTO', 0, 0, 'L');
    
    // Columna derecha - Datos del cliente
    $pdf->SetXY(105, $pdf->GetY());
    $pdf->Cell(85, 6, 'DATOS DEL CLIENTE', 0, 1, 'L');
    
    // Contenido
    $pdf->SetFont('helvetica', '', 9);
    $y_inicial = $pdf->GetY();
    
    // Información del presupuesto (columna izquierda)
    $pdf->SetXY(15, $y_inicial);
    
    // Determinar color del estado
    $estado_color = '';
    switch($presupuesto['estado']) {
        case 'pendiente': $estado_color = 'Pendiente'; break;
        case 'aprobado': $estado_color = 'Aprobado'; break;
        case 'rechazado': $estado_color = 'Rechazado'; break;
        case 'vencido': $estado_color = 'Vencido'; break;
        default: $estado_color = ucfirst($presupuesto['estado']);
    }
    
    $info_presupuesto = [
        'Fecha de Emisión: ' . date('d/m/Y H:i', strtotime($presupuesto['fecha_emision'])),
        'Válido hasta: ' . date('d/m/Y', strtotime($presupuesto['fecha_validez'])),
        'Estado: ' . $estado_color,
        'Elaborado por: ' . ($presupuesto['nombre_personal'] ?? 'N/A'),
        'Vehículo: ' . ($presupuesto['marca'] ?? '') . ' ' . ($presupuesto['modelo'] ?? '') . ' (' . ($presupuesto['matricula'] ?? '') . ')'
    ];
    
    foreach ($info_presupuesto as $info) {
        $pdf->Cell(85, 5, $info, 0, 1, 'L');
        $pdf->SetX(15);
    }
    
    // Información del cliente (columna derecha)
    $pdf->SetXY(105, $y_inicial);
    $info_cliente = [
        'Cliente: ' . $presupuesto['nombre_cliente'],
        'Cédula: ' . ($presupuesto['cedula'] ?? 'N/A'),
        'Teléfono: ' . ($presupuesto['telefono'] ?? 'N/A'),
        'Email: ' . ($presupuesto['email'] ?? 'N/A'),
        'Dirección: ' . ($presupuesto['direccion'] ?? 'N/A')
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
    $pdf->Cell(0, 8, 'DETALLES DEL PRESUPUESTO', 0, 1, 'L');
    $pdf->Ln(2);

    // Cabecera de la tabla
    $pdf->SetFillColor(40, 167, 69);
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
    // TOTAL
    // =====================================================
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    
    // Caja de total
    $x_total = 130;
    $pdf->SetX($x_total);
    $pdf->SetFillColor(40, 167, 69);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(35, 10, 'TOTAL:', 0, 0, 'R');
    $pdf->Cell(25, 10, '₲ ' . number_format($presupuesto['total'], 0, ',', '.'), 1, 1, 'R', true);
    
    $pdf->SetTextColor(0, 0, 0);

    // =====================================================
    // OBSERVACIONES
    // =====================================================
    if (!empty($presupuesto['observaciones'])) {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 5, $presupuesto['observaciones'], 0, 'L');
    }

    // =====================================================
    // CONDICIONES Y VALIDEZ
    // =====================================================
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'CONDICIONES GENERALES:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    $condiciones = [
        '• Este presupuesto tiene validez hasta el ' . date('d/m/Y', strtotime($presupuesto['fecha_validez'])),
        '• Los precios están expresados en Guaraníes (₲)',
        '• Los trabajos se realizarán según disponibilidad de turnos',
        '• Se requiere aprobación del cliente para proceder con los trabajos',
        '• Los repuestos utilizados tendrán garantía según fabricante'
    ];
    
    foreach ($condiciones as $condicion) {
        $pdf->Cell(0, 5, $condicion, 0, 1, 'L');
    }

    // =====================================================
    // PIE DE PÁGINA
    // =====================================================
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Presupuesto sin compromiso - Gracias por su confianza', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // =====================================================
    // GENERAR EL PDF
    // =====================================================
    $nombre_archivo = 'Presupuesto_' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT) . '.pdf';
    $pdf->Output($nombre_archivo, 'I');

} catch (Exception $e) {
    error_log("Error generando PDF de presupuesto: " . $e->getMessage());
    die('Error al generar el PDF: ' . $e->getMessage());
}
?>