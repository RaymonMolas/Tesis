<?php
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

if (isset($_GET["id"])) {
    $vehiculo = VehiculoControlador::ctrObtenerVehiculo($_GET["id"]);
    $clientes = ClienteControlador::buscarCliente();
    if (!$vehiculo) {
        echo '<script>window.location = "index.php?pagina=tabla/vehiculos";</script>';
        return;
    }
} else {
    echo '<script>window.location = "index.php?pagina=tabla/vehiculos";</script>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_vehiculo'])) {
    VehiculoControlador::ctrActualizarVehiculo();
}
?>

<title>EDITAR VEHÍCULO</title>
<style>
    h1 {
        font-family: "Copperplate", Fantasy;
        color: red;
    }
</style>

<div class="container mt-4">
    <h1 class="text-center">EDITAR VEHÍCULO</h1>
    <div class="d-flex justify-content-center">
        <form class="p-5 w-75 bg-light shadow-sm rounded" method="post">
            <input type="hidden" name="id_vehiculo" value="<?php echo htmlspecialchars($vehiculo["id_vehiculo"]); ?>">
            <div class="form-floating mb-3"><input type="text" class="form-control" name="matricula"
                    value="<?php echo htmlspecialchars($vehiculo["matricula"]); ?>" required><label>MATRÍCULA</label>
            </div>
            <div class="form-floating mb-3"><input type="text" class="form-control" name="marca"
                    value="<?php echo htmlspecialchars($vehiculo["marca"]); ?>" required><label>MARCA</label></div>
            <div class="form-floating mb-3"><input type="text" class="form-control" name="modelo"
                    value="<?php echo htmlspecialchars($vehiculo["modelo"]); ?>" required><label>MODELO</label></div>
            <div class="form-floating mb-3"><input type="number" class="form-control" name="anho" min="1900"
                    max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($vehiculo["anho"]); ?>"
                    required><label>AÑO</label></div>
            <div class="form-floating mb-3"><input type="text" class="form-control" name="color"
                    value="<?php echo htmlspecialchars($vehiculo["color"]); ?>" required><label>COLOR</label></div>
            <div class="form-floating mb-3">
                <select class="form-select" id="id_cliente" name="id_cliente" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo htmlspecialchars($cliente['id_cliente']); ?>" <?php if ($cliente['id_cliente'] == $vehiculo['id_cliente'])
                               echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="id_cliente">CLIENTE PROPIETARIO</label>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <button type="submit" class="btn btn-danger">MODIFICAR VEHÍCULO</button>
                <a href="index.php?pagina=tabla/vehiculos" class="btn btn-secondary">CANCELAR</a>
            </div>
        </form>
    </div>
</div>