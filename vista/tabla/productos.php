<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} else {
    if ($_SESSION["validarIngreso"] != "ok") {
        echo '<script>window.location = "index.php?pagina=login";</script>';
        return;
    }
}

$productos = ProductoControlador::ctrListarProductos();
?>

<title>PRODUCTOS</title>
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
</style>
<div class="container mt-5">
    <h1 class="text-center">PRODUCTOS</h1>

    <div class="d-flex justify-content-between my-3">
        <a href="index.php?pagina=nuevo/producto" class="btn btn-danger">
            <i class="bi bi-plus-circle"></i> Nuevo Producto
        </a>
        <input type="text" id="buscadorProductos" class="form-control w-50" placeholder="Buscar producto...">
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaProductos">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="table-light">
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo $producto["codigo"]; ?></td>
                        <td><?php echo $producto["nombre"]; ?></td>
                        <td><?php echo $producto["descripcion"]; ?></td>
                        <td>₲ <?php echo number_format($producto["precio"], 0, ',', '.'); ?></td>
                        <td><?php echo $producto["stock"]; ?></td>
                        <td>
                            <span class="badge <?php echo $producto["estado"] == 'activo' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo ucfirst($producto["estado"]); ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="index.php?pagina=editar/producto&id=<?php echo $producto["id_producto"]; ?>" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="eliminarProducto" value="<?php echo $producto["id_producto"]; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este producto?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById("buscadorProductos").addEventListener("keyup", function() {
    const filtro = this.value.toLowerCase();
    document.querySelectorAll("#tablaProductos tbody tr").forEach(row => {
        const texto = row.textContent.toLowerCase();
        row.style.display = texto.includes(filtro) ? "" : "none";
    });
});
</script>

<?php
if(isset($_POST["eliminarProducto"])) {
    ProductoControlador::ctrEliminarProducto();
}
?>
