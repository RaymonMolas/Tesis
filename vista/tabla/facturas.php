<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

$facturas = FacturaControlador::ctrListarFacturas();
?>

<title>FACTURAS</title>
<style>
    body {
        background-color: #f8f9fa;
    }
    h1 {
        font-family: "Copperplate", Fantasy;
        color: #dc3545;
        margin-top: 20px;
    }
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }
    .btn i {
        font-size: 1rem;
    }
    .estado-badge {
        font-size: 0.85rem;
    }
</style>

<div class="container mt-5">
    <h1 class="text-center mb-4">Facturas</h1>

    <div class="d-flex justify-content-between mb-3">
        <div class="d-flex gap-2">
            <a href="index.php?pagina=nuevo/factura" class="btn btn-success">
                <i class="bi bi-receipt"></i> Nueva Factura
            </a>
            <!-- Caja button removed as per request -->
            <!-- <a href="index.php?pagina=caja/actual" class="btn btn-primary">
                <i class="bi bi-cash-stack"></i> Ver Caja
            </a> -->
        </div>
        <input type="text" id="buscador" class="form-control w-50" placeholder="Buscar factura...">
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <?php 
                        $hoy = array_filter($facturas, function($f) { 
                            return date('Y-m-d', strtotime($f['fecha_emision'])) == date('Y-m-d'); 
                        });
                        echo count($hoy);
                        ?>
                    </h5>
                    <p class="card-text">Facturas Hoy</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success">
                        ₲ <?php 
                        $totalHoy = array_reduce($hoy, function($sum, $f) { 
                            return $sum + ($f['estado'] == 'pagada' ? $f['total'] : 0); 
                        }, 0);
                        echo number_format($totalHoy, 0, ',', '.');
                        ?>
                    </h5>
                    <p class="card-text">Cobrado Hoy</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning">
                        <?php 
                        $pendientes = array_filter($facturas, function($f) { 
                            return $f['estado'] == 'pendiente'; 
                        });
                        echo count($pendientes);
                        ?>
                    </h5>
                    <p class="card-text">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-danger">
                        ₲ <?php 
                        $totalPendiente = array_reduce($pendientes, function($sum, $f) { 
                            return $sum + $f['total']; 
                        }, 0);
                        echo number_format($totalPendiente, 0, ',', '.');
                        ?>
                    </h5>
                    <p class="card-text">Por Cobrar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaFacturas">
            <thead class="table-dark">
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Método Pago</th>
                        <!-- Caja column removed as per request -->
                        <!-- <th>Caja</th> -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturas as $factura): ?>
                    <tr>
                        <td><strong><?php echo $factura["numero_factura"]; ?></strong></td>
                        <td><?php echo $factura["nombre_cliente"]; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($factura["fecha_emision"])); ?></td>
                        <td>
                            <span class="badge <?php echo $factura['tipo_factura'] == 'contado' ? 'bg-info' : 'bg-secondary'; ?>">
                                <?php echo ucfirst($factura["tipo_factura"]); ?>
                            </span>
                        </td>
                        <td><strong>₲ <?php echo number_format($factura["total"], 0, ',', '.'); ?></strong></td>
                        <td>
                            <?php
                            $badgeClass = "";
                            switch($factura["estado"]) {
                                case "pendiente":
                                    $badgeClass = "bg-warning";
                                    break;
                                case "pagada":
                                    $badgeClass = "bg-success";
                                    break;
                                case "anulada":
                                    $badgeClass = "bg-danger";
                                    break;
                            }
                            ?>
                            <span class="badge estado-badge <?php echo $badgeClass; ?>">
                                <?php echo ucfirst($factura["estado"]); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo ucfirst($factura["metodo_pago"]); ?>
                            </span>
                        </td>
                        <!-- Caja data removed as per request -->
                        <!-- <td>
                            <span class="badge <?php echo $factura['estado_caja'] == 'abierta' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $factura['estado_caja'] == 'abierta' ? 'Abierta' : 'Cerrada'; ?>
                            </span>
                        </td> -->
                        <td>
                            <div class="btn-group">
                                <a href="index.php?pagina=ver/factura&id=<?php echo $factura["id_factura"]; ?>" 
                                   class="btn btn-info btn-sm" title="Ver Factura">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                
                                <?php if ($factura["estado"] == "pendiente"): ?>
                                    <button class="btn btn-success btn-sm" 
                                            onclick="marcarPagada(<?php echo $factura['id_factura']; ?>)"
                                            title="Marcar como Pagada">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($factura["estado"] != "anulada"): ?>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="anularFactura(<?php echo $factura['id_factura']; ?>)"
                                            title="Anular Factura">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <a href="../modelo/pdf/factura_pdf.php?id=<?php echo $factura["id_factura"]; ?>" 
                                   target="_blank" class="btn btn-secondary btn-sm" title="Imprimir PDF">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Formularios ocultos para acciones -->
<form id="formMarcarPagada" method="post" style="display: none;">
    <input type="hidden" name="id_factura" id="idFacturaPagada">
    <input type="hidden" name="estado" value="pagada">
</form>

<form id="formAnular" method="post" style="display: none;">
    <input type="hidden" name="anular_factura" id="idFacturaAnular">
    <input type="hidden" name="motivo_anulacion" id="motivoAnulacion">
</form>

<script>
// Buscador en tiempo real
document.getElementById('buscador').addEventListener('keyup', function() {
    const texto = this.value.toLowerCase();
    const tabla = document.getElementById('tablaFacturas');
    const filas = tabla.getElementsByTagName('tr');

    for (let i = 1; i < filas.length; i++) {
        const fila = filas[i];
        const contenido = fila.textContent.toLowerCase();
        fila.style.display = contenido.includes(texto) ? '' : 'none';
    }
});

// Marcar como pagada
function marcarPagada(id) {
    Swal.fire({
        title: '¿Marcar como pagada?',
        text: "Se registrará el pago en la caja actual",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, marcar como pagada',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('idFacturaPagada').value = id;
            document.getElementById('formMarcarPagada').submit();
        }
    });
}

// Anular factura
function anularFactura(id) {
    Swal.fire({
        title: 'Anular Factura',
        text: "Ingrese el motivo de anulación:",
        input: 'textarea',
        inputPlaceholder: 'Motivo de la anulación...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Anular Factura',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo de anulación';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('idFacturaAnular').value = id;
            document.getElementById('motivoAnulacion').value = result.value;
            document.getElementById('formAnular').submit();
        }
    });
}
</script>

<?php
// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["id_factura"]) && isset($_POST["estado"])) {
        $actualizar = FacturaControlador::ctrActualizarEstadoFactura();
    } elseif (isset($_POST["anular_factura"])) {
        $anular = FacturaControlador::ctrAnularFactura();
    }
}
?>