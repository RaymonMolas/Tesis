<?php
if(!isset($_SESSION["validarIngreso"])){
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}else{
    if($_SESSION["validarIngreso"] != "ok"){
        echo '<script>window.location = "index.php?pagina=login";</script>';
        return;
    }
}

if(!isset($_GET["id"])) {
    echo '<script>window.location = "index.php?pagina=tabla/presupuestos";</script>';
    return;
}

$presupuesto = PresupuestoControlador::ctrObtenerPresupuesto($_GET["id"]);
if(!$presupuesto) {
    echo '<script>window.location = "index.php?pagina=tabla/presupuestos";</script>';
    return;
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Presupuesto #<?php echo $presupuesto["id_presupuesto"]; ?></h2>
                <div class="btn-group">
                    <?php if($presupuesto["estado"] == "pendiente"): ?>
                        <button type="button" class="btn btn-success" onclick="actualizarEstado('aprobado')">
                            <i class="bi bi-check-circle"></i> Aprobar
                        </button>
                        <button type="button" class="btn btn-danger" onclick="actualizarEstado('rechazado')">
                            <i class="bi bi-x-circle"></i> Rechazar
                        </button>
                    <?php endif; ?>
                    <a href="index.php?pagina=tabla/presupuestos" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Información General -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> <?php echo $presupuesto["nombre_cliente"]; ?></p>
                            <p><strong>Vehículo:</strong> <?php echo $presupuesto["marca"] . " " . $presupuesto["modelo"] . " (" . $presupuesto["matricula"] . ")"; ?></p>
                            <p><strong>Personal:</strong> <?php echo $presupuesto["nombre_personal"]; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha Emisión:</strong> <?php echo date('d/m/Y H:i', strtotime($presupuesto["fecha_emision"])); ?></p>
                            <p><strong>Fecha Validez:</strong> <?php echo date('d/m/Y', strtotime($presupuesto["fecha_validez"])); ?></p>
                            <p>
                                <strong>Estado:</strong> 
                                <?php
                                $badgeClass = "";
                                switch($presupuesto["estado"]) {
                                    case "pendiente":
                                        $badgeClass = "bg-warning";
                                        break;
                                    case "aprobado":
                                        $badgeClass = "bg-success";
                                        break;
                                    case "rechazado":
                                        $badgeClass = "bg-danger";
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($presupuesto["estado"]); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles del Presupuesto -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Detalles del Presupuesto</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($presupuesto["detalles"] as $detalle): ?>
                                    <tr>
                                        <td><?php echo ucfirst($detalle["tipo"]); ?></td>
                                        <td>
                                            <?php 
                                            echo $detalle["descripcion"];
                                            if ($detalle["tipo"] == "producto" && isset($detalle["codigo_producto"])) {
                                                echo " (Código: " . $detalle["codigo_producto"] . ")";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $detalle["cantidad"]; ?></td>
                                        <td>₲ <?php echo number_format($detalle["precio_unitario"], 0, ',', '.'); ?></td>
                                        <td>₲ <?php echo number_format($detalle["subtotal"], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>₲ <?php echo number_format($presupuesto["total"], 0, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <?php if(!empty($presupuesto["observaciones"])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br($presupuesto["observaciones"]); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function actualizarEstado(estado) {
    const mensaje = estado === 'aprobado' ? 
        '¿Está seguro de aprobar este presupuesto?' : 
        '¿Está seguro de rechazar este presupuesto?';
    
    Swal.fire({
        title: '¿Está seguro?',
        text: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?pagina=ver/presupuesto&id=<?php echo $presupuesto["id_presupuesto"]; ?>';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_presupuesto';
            idInput.value = '<?php echo $presupuesto["id_presupuesto"]; ?>';
            
            const estadoInput = document.createElement('input');
            estadoInput.type = 'hidden';
            estadoInput.name = 'estado';
            estadoInput.value = estado;
            
            form.appendChild(idInput);
            form.appendChild(estadoInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<style>
    body {
        background-color: white;
    }
    h2 {
        font-family: "Copperplate", Fantasy;
        color: red;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header {
        background-color: #f8f9fa;
    }
    .badge {
        font-size: 0.9rem;
    }
</style>

<?php
if(isset($_POST["id_presupuesto"]) && isset($_POST["estado"])) {
    $actualizar = PresupuestoControlador::ctrActualizarEstado();
}
?>
