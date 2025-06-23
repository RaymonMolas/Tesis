<?php
require_once '../../vendor/autoload.php'; // Assuming you use Composer and TCPDF or similar library
require_once '../conexion.php';

use TCPDF;

if (!isset($_GET['id'])) {
    die('ID de factura no especificado');
}

$id_factura = intval($_GET['id']);

// Fetch invoice data
$conexion = Conexion::conectar();

require_once '../modelo/modelo_empresa.php';
$infoEmpresa = ModeloEmpresa::mdlObtenerInfoEmpresa();

$stmt = $conexion->prepare("SELECT f.*, c.nombre AS nombre_cliente, c.cedula, c.telefono, c.email, c.direccion, p.nombre AS nombre_personal
                            FROM factura f
                            JOIN cliente c ON f.id_cliente = c.id_cliente
                            JOIN personal p ON f.id_personal = p.id_personal
                            WHERE f.id_factura = :id");
$stmt->bindParam(':id', $id_factura, PDO::PARAM_INT);
$stmt->execute();
$factura = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$factura) {
    die('Factura no encontrada');
}

// Fetch invoice details
$stmt = $conexion->prepare("SELECT d.*, pr.codigo_producto
                            FROM detalle_factura d
                            LEFT JOIN producto pr ON d.id_producto = pr.id_producto
                            WHERE d.id_factura = :id");
$stmt->bindParam(':id', $id_factura, PDO::PARAM_INT);
$stmt->execute();
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Motor Service');
$pdf->SetAuthor('Motor Service');
$pdf->SetTitle('Factura #' . $factura['numero_factura']);
$pdf->SetSubject('Factura');
$pdf->SetKeywords('Factura, Motor Service');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$html = '<h1 style="color:#dc3545;">Factura #' . $factura['numero_factura'] . '</h1>';
$html .= '<p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($factura['fecha_emision'])) . ' ' . date('H:i', strtotime($factura['fecha_emision'])) . '</p>';

$html .= '<h3>Información Legal</h3>';
$html .= '<p><strong>RUC:</strong> ' . htmlspecialchars($infoEmpresa['ruc_empresa']) . '<br>';
$html .= '<strong>Dirección:</strong> ' . htmlspecialchars($infoEmpresa['direccion_empresa']) . '<br>';
$html .= '<strong>Timbrado N°:</strong> ' . htmlspecialchars($infoEmpresa['timbrado_numero']) . '<br>';
$html .= '<strong>Vencimiento:</strong> ' . date('d/m/Y', strtotime($infoEmpresa['timbrado_vencimiento'])) . '</p>';

$html .= '<h3>Cliente</h3>';
$html .= '<p>' . htmlspecialchars($factura['nombre_cliente']) . '<br>';
$html .= 'Cédula: ' . htmlspecialchars($factura['cedula']) . '<br>';
$html .= 'Teléfono: ' . htmlspecialchars($factura['telefono']) . '<br>';
$html .= 'Email: ' . htmlspecialchars($factura['email']) . '<br>';
$html .= 'Dirección: ' . htmlspecialchars($factura['direccion']) . '</p>';

$html .= '<h3>Detalles</h3>';
$html .= '<table border="1" cellpadding="4">';
$html .= '<thead><tr style="background-color:#dc3545;color:#fff;">
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Descuento</th>
            <th>Subtotal</th>
          </tr></thead><tbody>';

foreach ($detalles as $d) {
    $tipo = ucfirst($d['tipo']);
    $descripcion = htmlspecialchars($d['descripcion']);
    if ($d['tipo'] == 'producto' && $d['codigo_producto']) {
        $descripcion .= '<br><small>Código: ' . htmlspecialchars($d['codigo_producto']) . '</small>';
    }
    $cantidad = $d['cantidad'];
    $precio_unitario = number_format($d['precio_unitario'], 0, ',', '.');
    $descuento = number_format($d['descuento'], 0, ',', '.');
    $subtotal = number_format($d['subtotal'], 0, ',', '.');

    $html .= "<tr>
                <td>$tipo</td>
                <td>$descripcion</td>
                <td align=\"center\">$cantidad</td>
                <td align=\"right\">₲ $precio_unitario</td>
                <td align=\"right\">₲ $descuento</td>
                <td align=\"right\"><strong>₲ $subtotal</strong></td>
              </tr>";
}

$html .= '</tbody></table>';

$html .= '<h3>Totales</h3>';
$html .= '<table cellpadding="4">';
$html .= '<tr><td>Subtotal:</td><td>₲ ' . number_format($factura['subtotal'], 0, ',', '.') . '</td></tr>';
if ($factura['descuento'] > 0) {
    $html .= '<tr><td>Descuento:</td><td>-₲ ' . number_format($factura['descuento'], 0, ',', '.') . '</td></tr>';
}
if ($factura['iva'] > 0) {
    $html .= '<tr><td>IVA:</td><td>₲ ' . number_format($factura['iva'], 0, ',', '.') . '</td></tr>';
}
$html .= '<tr><td><strong>TOTAL:</strong></td><td><strong>₲ ' . number_format($factura['total'], 0, ',', '.') . '</strong></td></tr>';
$html .= '</table>';

$html .= '<p>Atendido por: ' . htmlspecialchars($factura['nombre_personal']) . '</p>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('factura_' . $factura['numero_factura'] . '.pdf', 'I');
?>
