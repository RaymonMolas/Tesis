<?php
/**
 * Generador de PDF para Facturas - Motor Service
 * Sistema de Gestión Automotriz - Versión 2.0
 */

// Incluir archivos necesarios
require_once(__DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once(__DIR__ . '/../conexion.php');
require_once(__DIR__ . '/../modelo_factura.php');
require_once(__DIR__ . '/../modelo_detalle_factura.php');
require_once(__DIR__ . '/../modelo_empresa.php');
require_once(__DIR__ . '/../../controlador/factura_controlador.php');

class FacturaPDF extends TCPDF
{
    private $empresa_info;

    public function __construct($empresa_info)
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->empresa_info = $empresa_info;
    }

    public function Header()
    {
        // Fondo del header en rojo corporativo
        $this->SetFillColor(220, 38, 38);
        $this->Rect(0, 0, 210, 55, 'F');

        // Logo Motor Service
        $logo_path = __DIR__ . '/../../img/img-01.jpg';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 8, 35, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            // Fallback: texto del logo
            $this->SetXY(15, 8);
            $this->SetFont('helvetica', '', 9);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(40, 4, 'Servicio Integral', 0, 1, 'C');
            $this->SetX(15);
            $this->Cell(40, 4, 'Automotriz', 0, 1, 'C');

            $this->SetXY(15, 18);
            $this->SetFont('helvetica', 'B', 16);
            $this->Cell(40, 8, 'Motor', 0, 0, 'C');
            $this->SetXY(15, 26);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(40, 6, 'Service', 0, 1, 'C');
        }

        // Título principal
        $this->SetXY(65, 12);
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(80, 15, 'FACTURA', 0, 0, 'C');

        // Información legal de la empresa
        $this->SetXY(150, 8);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(45, 4, 'RUC: ' . $this->empresa_info['ruc_empresa'], 0, 1, 'R');
        $this->SetX(150);
        $this->Cell(45, 4, 'Tel: ' . $this->empresa_info['telefono_empresa'], 0, 1, 'R');
        $this->SetX(150);
        $this->Cell(45, 4, $this->empresa_info['website_empresa'], 0, 1, 'R');
        $this->SetX(150);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(45, 4, 'Timbrado N°: ' . $this->empresa_info['timbrado_numero'], 0, 1, 'R');
        $this->SetX(150);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(45, 4, 'Vto: ' . date('d/m/Y', strtotime($this->empresa_info['timbrado_vencimiento'])), 0, 1, 'R');

        // Línea decorativa
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(1);
        $this->Line(15, 47, 195, 47);

        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->Ln(20);
    }

    public function Footer()
    {
        $this->SetY(-30);

        // Línea decorativa
        $this->SetDrawColor(220, 38, 38);
        $this->SetLineWidth(1);
        $this->Line(15, $this->GetY(), 195, $this->GetY());

        // Fondo del footer
        $this->SetFillColor(248, 249, 250);
        $this->Rect(15, $this->GetY() + 2, 180, 20, 'F');

        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(220, 38, 38);
        $this->Cell(0, 5, $this->empresa_info['nombre_empresa'], 0, 1, 'C');

        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, $this->empresa_info['direccion_empresa'], 0, 1, 'C');
        $this->Cell(0, 4, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages() . ' - Documento generado el ' . date('d/m/Y H:i'), 0, 0, 'C');

        $this->SetTextColor(0, 0, 0);
    }
}

// Verificar parámetros
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<div style="text-align:center; margin-top:50px; font-family:Arial; background:#fff5f5; padding:30px; border-radius:10px; max-width:600px; margin:50px auto; box-shadow:0 4px 6px rgba(0,0,0,0.1); border-left:5px solid #dc2626;">
        <h2 style="color:#dc2626; margin-bottom:20px;">⚠️ Error - Motor Service</h2>
        <p>ID de factura no especificado o inválido</p>
        <button onclick="window.history.back()" style="background:#dc2626; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">← Volver</button>
    </div>');
}

$id_factura = (int) $_GET['id'];

try {
    // Obtener información de la empresa
    $empresa = ModeloEmpresa::mdlObtenerInfoEmpresa();
    if (!$empresa) {
        throw new Exception('No se pudo obtener la información de la empresa');
    }

    // Obtener datos de la factura
    $factura = FacturaControlador::ctrObtenerFactura($id_factura);
    if (!$factura) {
        throw new Exception('Factura no encontrada');
    }

    // Obtener detalles de la factura
    $detalles = FacturaControlador::ctrObtenerDetallesFactura($id_factura);

    // Crear PDF
    $pdf = new FacturaPDF($empresa);

    // Configurar documento
    $pdf->SetCreator('Motor Service - Sistema de Gestión');
    $pdf->SetAuthor($empresa['nombre_empresa']);
    $pdf->SetTitle('Factura N° ' . $factura['numero_factura']);
    $pdf->SetSubject('Factura de Venta - Motor Service');
    $pdf->SetKeywords('factura, motor service, venta, servicios, automotriz');

    // Configurar márgenes
    $pdf->SetMargins(15, 60, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(25);
    $pdf->SetAutoPageBreak(TRUE, 35);

    $pdf->AddPage();

    // Número de factura destacado
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(220, 38, 38);
    $pdf->RoundedRect(15, $pdf->GetY(), 180, 15, 3, '1111', 'DF');
    $pdf->Cell(0, 15, 'FACTURA N° ' . $factura['numero_factura'], 0, 1, 'C');

    $pdf->Ln(5);

    // Fecha y estado
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 8, 'Fecha: ' . date('d/m/Y H:i', strtotime($factura['fecha_emision'])), 0, 1, 'C');

    // Estado de la factura
    $estado_color = ['pendiente' => [255, 193, 7], 'pagado' => [40, 167, 69], 'vencido' => [220, 53, 69], 'anulado' => [108, 117, 125]];
    $color = $estado_color[$factura['estado']] ?? [108, 117, 125];

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($color[0], $color[1], $color[2]);
    $pdf->Cell(0, 10, 'ESTADO: ' . strtoupper($factura['estado']), 0, 1, 'C');

    $pdf->Ln(10);

    // Información del cliente
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 8, 'DATOS DEL CLIENTE', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(255, 255, 255);

    $cliente_info = [
        ['Cliente:', $factura['nombre_cliente']],
        ['Documento:', $factura['cedula_ruc']],
        ['Teléfono:', $factura['telefono_cliente'] ?: 'No especificado'],
        ['Email:', $factura['email_cliente'] ?: 'No especificado'],
        ['Dirección:', $factura['direccion_cliente'] ?: 'No especificada']
    ];

    foreach ($cliente_info as $info) {
        $pdf->Cell(40, 8, $info[0], 1, 0, 'L', true);
        $pdf->Cell(140, 8, $info[1], 1, 1, 'L');
    }

    $pdf->Ln(10);

    // Información del vehículo (si existe)
    if ($factura['id_orden'] || $factura['id_presupuesto']) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(220, 38, 38);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'INFORMACIÓN DEL VEHÍCULO', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(248, 249, 250);

        // Obtener información del vehículo desde la orden o presupuesto
        $vehiculo_info = [];
        if ($factura['id_orden']) {
            require_once(__DIR__ . '/../modelo_orden_trabajo.php');
            $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($factura['id_orden']);
            if ($orden) {
                $vehiculo_info = [
                    ['Matrícula:', $orden['matricula']],
                    ['Marca:', $orden['marca']],
                    ['Modelo:', $orden['modelo']],
                    ['Año:', $orden['anho']]
                ];
            }
        } elseif ($factura['id_presupuesto']) {
            require_once(__DIR__ . '/../modelo_presupuesto.php');
            $presupuesto = ModeloPresupuesto::mdlObtenerPresupuesto($factura['id_presupuesto']);
            if ($presupuesto) {
                $vehiculo_info = [
                    ['Matrícula:', $presupuesto['matricula']],
                    ['Marca:', $presupuesto['marca']],
                    ['Modelo:', $presupuesto['modelo']],
                    ['Año:', $presupuesto['anho']]
                ];
            }
        }

        foreach ($vehiculo_info as $info) {
            $pdf->Cell(40, 8, $info[0], 1, 0, 'L', true);
            $pdf->Cell(140, 8, $info[1], 1, 1, 'L');
        }

        $pdf->Ln(10);
    }

    // Tabla de productos/servicios
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'DETALLE DE LA FACTURA', 1, 1, 'C', true);

    // Encabezados de la tabla
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(15, 8, 'ITEM', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'TIPO', 1, 0, 'C', true);
    $pdf->Cell(65, 8, 'DESCRIPCIÓN', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'CANT.', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'PRECIO UNIT.', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'DESC.', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'SUBTOTAL', 1, 1, 'C', true);

    // Contenido de la tabla
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetFillColor(255, 255, 255);

    $item_numero = 1;

    foreach ($detalles as $detalle) {
        $tipo_texto = ($detalle['tipo'] == 'producto') ? 'PROD.' : 'SERV.';

        $pdf->Cell(15, 8, str_pad($item_numero, 2, '0', STR_PAD_LEFT), 1, 0, 'C');
        $pdf->Cell(20, 8, $tipo_texto, 1, 0, 'C');

        // Descripción con ajuste de texto
        $descripcion = $detalle['descripcion'];
        if (strlen($descripcion) > 40) {
            $descripcion = substr($descripcion, 0, 37) . '...';
        }
        $pdf->Cell(65, 8, $descripcion, 1, 0, 'L');

        $pdf->Cell(20, 8, number_format($detalle['cantidad'], 0), 1, 0, 'C');
        $pdf->Cell(25, 8, 'Gs. ' . number_format($detalle['precio_unitario'], 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(20, 8, 'Gs. ' . number_format($detalle['descuento'], 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(25, 8, 'Gs. ' . number_format($detalle['subtotal'], 0, ',', '.'), 1, 1, 'R');

        $item_numero++;
    }

    // Totales
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 11);

    // Subtotal
    $pdf->Cell(125, 8, '', 0, 0);
    $pdf->Cell(40, 8, 'SUBTOTAL:', 1, 0, 'R');
    $pdf->Cell(25, 8, 'Gs. ' . number_format($factura['subtotal'], 0, ',', '.'), 1, 1, 'R');

    // Descuento (si existe)
    if ($factura['descuento'] > 0) {
        $pdf->Cell(125, 8, '', 0, 0);
        $pdf->Cell(40, 8, 'DESCUENTO:', 1, 0, 'R');
        $pdf->Cell(25, 8, 'Gs. ' . number_format($factura['descuento'], 0, ',', '.'), 1, 1, 'R');
    }

    // IVA (si existe)
    if ($factura['iva'] > 0) {
        $pdf->Cell(125, 8, '', 0, 0);
        $pdf->Cell(40, 8, 'IVA (10%):', 1, 0, 'R');
        $pdf->Cell(25, 8, 'Gs. ' . number_format($factura['iva'], 0, ',', '.'), 1, 1, 'R');
    }

    // Total
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(125, 12, '', 0, 0);
    $pdf->Cell(40, 12, 'TOTAL:', 1, 0, 'R', true);
    $pdf->Cell(25, 12, 'Gs. ' . number_format($factura['total'], 0, ',', '.'), 1, 1, 'R', true);

    $pdf->Ln(10);

    // Observaciones
    if (!empty($factura['observaciones'])) {
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 8, 'OBSERVACIONES', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(0, 8, $factura['observaciones'], 1, 'L', true);

        $pdf->Ln(5);
    }

    // Información adicional
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Personal responsable: ' . $factura['nombre_personal'], 0, 1, 'L');
    $pdf->Cell(0, 5, 'Tipo de factura: ' . ucfirst($factura['tipo_factura']), 0, 1, 'L');

    if ($factura['fecha_vencimiento']) {
        $pdf->Cell(0, 5, 'Fecha de vencimiento: ' . date('d/m/Y', strtotime($factura['fecha_vencimiento'])), 0, 1, 'L');
    }

    if ($factura['fecha_pago']) {
        $pdf->SetTextColor(40, 167, 69);
        $pdf->Cell(0, 5, 'Fecha de pago: ' . date('d/m/Y H:i', strtotime($factura['fecha_pago'])), 0, 1, 'L');
    }

    $pdf->Ln(15);

    // Información legal
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 8, 'INFORMACIÓN LEGAL', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $info_legal = [
        '• Esta factura cumple con los requisitos establecidos por la SET (Subsecretaría de Estado de Tributación).',
        '• El timbrado habilitante vence el ' . date('d/m/Y', strtotime($empresa['timbrado_vencimiento'])) . '.',
        '• Los servicios están exentos de IVA según Art. 83 de la Ley 125/91.',
        '• Esta factura es válida como comprobante de pago y garantía de los servicios prestados.',
        '• Para consultas o reclamos comunicarse al ' . $empresa['telefono_empresa'] . '.'
    ];

    foreach ($info_legal as $info) {
        $pdf->Cell(0, 5, $info, 0, 1, 'L');
    }

    // Mensaje de agradecimiento
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(0, 10, '¡GRACIAS POR CONFIAR EN MOTOR SERVICE!', 0, 1, 'C');

    // Salida del PDF
    $nombre_archivo = 'Factura_' . $factura['numero_factura'] . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $factura['nombre_cliente']);
    $pdf->Output($nombre_archivo . '.pdf', 'I');

} catch (Exception $e) {
    die('<div style="text-align:center; margin-top:50px; font-family:Arial; background:#fff5f5; padding:30px; border-radius:10px; max-width:600px; margin:50px auto; box-shadow:0 4px 6px rgba(0,0,0,0.1); border-left:5px solid #dc2626;">
        <h2 style="color:#dc2626; margin-bottom:20px;">⚠️ Error - Motor Service</h2>
        <p>Error al generar la factura: ' . htmlspecialchars($e->getMessage()) . '</p>
        <button onclick="window.history.back()" style="background:#dc2626; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">← Volver</button>
    </div>');
}
?>