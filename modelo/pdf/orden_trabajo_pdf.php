<?php
require_once '../../vendor/autoload.php';
require_once '../conexion.php';
require_once '../modelo_empresa.php';
require_once '../modelo_orden_trabajo.php';
require_once '../modelo_orden_detalle.php';

use TCPDF;

// Verificar ID de orden de trabajo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de orden de trabajo no especificado o inválido');
}

$id_orden = (int) $_GET['id'];

try {
    // Obtener información de la empresa
    $infoEmpresa = ModeloEmpresa::mdlObtenerInfoEmpresa();
    
    // Obtener datos de la orden de trabajo
    $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($id_orden);
    if (!$orden) {
        die('Orden de trabajo no encontrada');
    }
    
    // Obtener detalles de la orden de trabajo
    $detalles = ModeloOrdenDetalle::mdlObtenerDetalles($id_orden);

    // Crear nuevo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar información del documento
    $pdf->SetCreator('Sistema de Taller');
    $pdf->SetAuthor($infoEmpresa['nombre_empresa'] ?? 'Sistema de Taller');
    $pdf->SetTitle('Orden de Trabajo #' . str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT));
    $pdf->SetSubject('Orden de Trabajo');
    $pdf->SetKeywords('Orden, Trabajo, Taller, Servicios, Reparación');

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
    $pdf->SetTextColor(255, 193, 7); // Color amarillo/naranja para orden de trabajo
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
    $pdf->SetDrawColor(255, 193, 7);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);

    // =====================================================
    // TÍTULO DE ORDEN DE TRABAJO
    // =====================================================
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(255, 193, 7);
    $pdf->Cell(0, 15, 'ORDEN DE TRABAJO', 0, 1, 'C');
    
    // Número de orden
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'N° ' . str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');
    $pdf->Ln(5);

    // =====================================================
    // INFORMACIÓN DE LA ORDEN Y CLIENTE
    // =====================================================
    // Crear tabla de información
    $pdf->SetFont('helvetica', '', 10);
    
    // Información de la orden
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'INFORMACIÓN DE LA ORDEN', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    
    $info_orden = [
        'Fecha de Ingreso: ' . date('d/m/Y H:i', strtotime($orden['fecha_ingreso'])),
        'Fecha de Salida: ' . ($orden['fecha_salida'] ? date('d/m/Y H:i', strtotime($orden['fecha_salida'])) : 'Pendiente'),
        'Estado: ' . ucfirst($orden['estado']),
        'Mecánico: ' . ($orden['nombre_personal'] ?? 'No asignado'),
        'Tipo de Servicio: ' . ($orden['tipo_servicio'] ?? 'General'),
        'Kilometraje Actual: ' . ($orden['kilometraje_actual'] ? number_format($orden['kilometraje_actual'], 0, ',', '.') . ' km' : 'No especificado')
    ];
    
    foreach ($info_orden as $info) {
        $pdf->Cell(0, 5, $info, 0, 1, 'L');
    }
    
    $pdf->Ln(5);

    // =====================================================
    // INFORMACIÓN DEL VEHÍCULO Y CLIENTE
    // =====================================================
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(95, 6, 'DATOS DEL VEHÍCULO', 0, 0, 'L');
    $pdf->Cell(95, 6, 'DATOS DEL CLIENTE', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 9);
    $y_inicial = $pdf->GetY();
    
    // Información del vehículo (columna izquierda)
    $pdf->SetXY(15, $y_inicial);
    $info_vehiculo = [
        'Marca: ' . ($orden['marca'] ?? 'N/A'),
        'Modelo: ' . ($orden['modelo'] ?? 'N/A'),
        'Matrícula: ' . ($orden['matricula'] ?? 'N/A'),
        'Año: ' . ($orden['año'] ?? 'N/A'),
        'Color: ' . ($orden['color'] ?? 'N/A'),
        'Combustible: ' . ($orden['combustible'] ?? 'N/A')
    ];
    
    foreach ($info_vehiculo as $info) {
        $pdf->Cell(95, 5, $info, 0, 1, 'L');
        $pdf->SetX(15);
    }
    
    // Información del cliente (columna derecha)
    $pdf->SetXY(110, $y_inicial);
    $info_cliente = [
        'Cliente: ' . $orden['nombre_cliente'],
        'Cédula: ' . ($orden['cedula'] ?? 'N/A'),
        'Teléfono: ' . ($orden['telefono'] ?? 'N/A'),
        'Email: ' . ($orden['email'] ?? 'N/A'),
        'Dirección: ' . ($orden['direccion'] ?? 'N/A'),
        ''
    ];
    
    foreach ($info_cliente as $info) {
        if (!empty($info)) {
            $pdf->Cell(85, 5, $info, 0, 1, 'L');
            $pdf->SetX(110);
        }
    }
    
    $pdf->Ln(10);

    // =====================================================
    // DETALLES DE TRABAJO
    // =====================================================
    if (!empty($detalles)) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, 'DETALLES DEL TRABAJO REALIZADO', 0, 1, 'L');
        $pdf->Ln(2);

        // Cabecera de la tabla
        $pdf->SetFillColor(255, 193, 7);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 9);
        
        $pdf->Cell(15, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Servicio', 1, 0, 'C', true);
        $pdf->Cell(80, 8, 'Descripción', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Cant.', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Precio Unit.', 1, 0, 'C', true);
        $pdf->Cell(10, 8, 'Total', 1, 1, 'C', true);

        // Contenido de la tabla
        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 8);
        
        $fill = false;
        $total_general = 0;
        $item_num = 1;
        
        foreach ($detalles as $detalle) {
            $pdf->Cell(15, 6, $item_num, 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, $detalle['tipo_servicio'], 1, 0, 'C', $fill);
            
            // Manejar descripciones largas
            $descripcion = $detalle['descripcion'];
            if (strlen($descripcion) > 50) {
                $descripcion = substr($descripcion, 0, 47) . '...';
            }
            $pdf->Cell(80, 6, $descripcion, 1, 0, 'L', $fill);
            
            $pdf->Cell(20, 6, number_format($detalle['cantidad'], 0), 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, '₲ ' . number_format($detalle['precio_unitario'], 0, ',', '.'), 1, 0, 'R', $fill);
            $pdf->Cell(10, 6, '₲ ' . number_format($detalle['subtotal'], 0, ',', '.'), 1, 1, 'R', $fill);
            
            $total_general += $detalle['subtotal'];
            $fill = !$fill;
            $item_num++;
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(170, 8, 'TOTAL ESTIMADO:', 0, 0, 'R');
        $pdf->Cell(10, 8, '₲ ' . number_format($total_general, 0, ',', '.'), 1, 1, 'R');
    }

    // =====================================================
    // OBSERVACIONES
    // =====================================================
    if (!empty($orden['observaciones'])) {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 5, $orden['observaciones'], 0, 'L');
    }

    // =====================================================
    // FIRMAS
    // =====================================================
    $pdf->Ln(20);
    $pdf->SetFont('helvetica', '', 10);
    
    // Líneas para firmas
    $pdf->Cell(85, 5, str_repeat('_', 30), 0, 0, 'C');
    $pdf->Cell(10, 5, '', 0, 0, 'C');
    $pdf->Cell(85, 5, str_repeat('_', 30), 0, 1, 'C');
    
    $pdf->Cell(85, 5, 'Firma del Cliente', 0, 0, 'C');
    $pdf->Cell(10, 5, '', 0, 0, 'C');
    $pdf->Cell(85, 5, 'Firma del Técnico', 0, 1, 'C');

    // =====================================================
    // PIE DE PÁGINA
    // =====================================================
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Original: Cliente | Duplicado: Taller', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // =====================================================
    // GENERAR EL PDF
    // =====================================================
    $nombre_archivo = 'Orden_Trabajo_' . str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT) . '.pdf';
    $pdf->Output($nombre_archivo, 'I');

} catch (Exception $e) {
    error_log("Error generando PDF de orden de trabajo: " . $e->getMessage());
    die('Error al generar el PDF: ' . $e->getMessage());
}
?>