<?php
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

if (!isset($_GET["id"])) {
    echo '<script>window.location = "index.php?pagina=tabla/productos";</script>';
    return;
}
$producto = ProductoControlador::ctrObtenerProducto($_GET["id"]);
if (!$producto) {
    echo '<script>window.location = "index.php?pagina=tabla/productos";</script>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    ProductoControlador::ctrActualizarProducto();
}
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Editar Producto</h2>
    <div class="d-flex justify-content-center">
        <form method="post" class="p-5 w-75 bg-light shadow-sm rounded">
            <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto["id_producto"]); ?>">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="codigo" class="form-label">Código</label><input type="text"
                        class="form-control" id="codigo" name="codigo"
                        value="<?php echo htmlspecialchars($producto["codigo"]); ?>" required></div>
                <div class="col-md-6 mb-3"><label for="nombre" class="form-label">Nombre del Producto</label><input
                        type="text" class="form-control" id="nombre" name="nombre"
                        value="<?php echo htmlspecialchars($producto["nombre"]); ?>" required></div>
            </div>
            <div class="mb-3"><label for="descripcion" class="form-label">Descripción</label><textarea
                    class="form-control" id="descripcion" name="descripcion" rows="3"
                    required><?php echo htmlspecialchars($producto["descripcion"]); ?></textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="precio" class="form-label">Precio (₲)</label><input type="number"
                        class="form-control" id="precio" name="precio"
                        value="<?php echo htmlspecialchars($producto["precio"]); ?>" min="0" required></div>
                <div class="col-md-4 mb-3"><label for="stock" class="form-label">Stock</label><input type="number"
                        class="form-control" id="stock" name="stock"
                        value="<?php echo htmlspecialchars($producto["stock"]); ?>" min="0" required></div>
                <div class="col-md-4 mb-3"><label for="estado" class="form-label">Estado</label><select
                        class="form-select" id="estado" name="estado" required>
                        <option value="activo" <?php if ($producto["estado"] == "activo")
                            echo "selected"; ?>>Activo
                        </option>
                        <option value="inactivo" <?php if ($producto["estado"] == "inactivo")
                            echo "selected"; ?>>Inactivo
                        </option>
                    </select></div>
            </div>
            <div class="mt-3 d-flex gap-2 justify-content-center">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="index.php?pagina=tabla/productos" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>