<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

$presupuestos = PresupuestoControlador::ctrListarPresupuestos();
?>

<style>
    body {
        background-color: #f8f9fa;
    }

    h1 {
        font-family: "Copperplate", Fantasy;
        color: #dc3545;
        margin-top: 20px;
    }

    .table th,
    .table td {
        vertical-align: middle;
        text-align: center;
    }

    .btn i {
        font-size: 1rem;
    }
</style>
<div class="container mt-5">
    <h1 class="text-center mb-4">Presupuestos</h1>

    <div class="d-flex justify-content-between mb-3">
        <a href="index.php?pagina=nuevo/presupuesto" class="btn btn-danger">
            <i class="bi bi-plus-circle"></i> Nuevo Presupuesto
        </a>
        <input type="text" id="buscador" class="form-control w-50" placeholder="Buscar presupuesto...">
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaPresupuestos">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Fecha Emisión</th>
                    <th>Fecha Validez</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presupuestos as $presupuesto): ?>
                    <tr>
                        <td><?php echo $presupuesto["id_presupuesto"]; ?></td>
                        <td><?php echo $presupuesto["nombre_cliente"]; ?></td>
                        <td><?php echo $presupuesto["marca"] . " " . $presupuesto["modelo"] . "<br>(" . $presupuesto["matricula"] . ")"; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($presupuesto["fecha_emision"])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($presupuesto["fecha_validez"])); ?></td>
                        <td>
                            <?php
                            $badgeClass = "";
                            switch ($presupuesto["estado"]) {
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
                        </td>
                        <td>₲ <?php echo number_format($presupuesto["total"], 0, ',', '.'); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?pagina=ver/presupuesto&id=<?php echo $presupuesto["id_presupuesto"]; ?>"
                                    class="btn btn-info btn-sm" title="Ver">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <?php if ($presupuesto["estado"] == "pendiente"): ?>
                                    <a href="index.php?pagina=editar/presupuesto&id=<?php echo $presupuesto["id_presupuesto"]; ?>"
                                        class="btn btn-warning btn-sm" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="eliminarPresupuesto(<?php echo $presupuesto['id_presupuesto']; ?>)"
                                        title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                <?php endif; ?>
<!-- Removed button to create order de trabajo from presupuesto as per user request -->
<!--
<?php if ($presupuesto["estado"] == "aprobado" && !$presupuesto["facturado"]): ?>
    <a href="index.php?pagina=nuevo/orden_trabajo&desde_presupuesto=<?php echo $presupuesto["id_presupuesto"]; ?>"
        class="btn btn-success btn-sm" title="Crear Orden de Trabajo">
        <i class="bi bi-tools"></i>
    </a>
<?php endif; ?>
-->

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Formulario oculto para eliminación -->
<form id="formEliminar" method="post" style="display: none;">
    <input type="hidden" name="eliminarPresupuesto" id="idPresupuestoEliminar">
</form>

<script>
    // Buscador en tiempo real
    document.getElementById('buscador').addEventListener('keyup', function () {
        const texto = this.value.toLowerCase();
        const tabla = document.getElementById('tablaPresupuestos');
        const filas = tabla.getElementsByTagName('tr');

        for (let i = 1; i < filas.length; i++) {
            const fila = filas[i];
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? '' : 'none';
        }
    });

    // Función para eliminar presupuesto
    function eliminarPresupuesto(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¡No podrá revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Usar formulario oculto para enviar POST
                document.getElementById('idPresupuestoEliminar').value = id;
                document.getElementById('formEliminar').submit();
            }
        });
    }
</script>

<?php
// CORRECCIÓN: Manejar eliminación correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["eliminarPresupuesto"])) {
    $eliminar = PresupuestoControlador::ctrEliminarPresupuesto();

    // Si la eliminación fue exitosa, recargar la página
    if ($eliminar == "ok") {
        echo '<script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
            setTimeout(function(){
                window.location.reload();
            }, 1000);
        </script>';
    }
}
?>
