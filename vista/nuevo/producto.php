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
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Nuevo Producto</h2>
            <form method="post">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="precio" class="form-label">Precio (₲)</label>
                                    <input type="number" class="form-control" id="precio" name="precio" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    <a href="index.php?pagina=tabla/productos" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>

            <?php
            $crearProducto = ProductoControlador::ctrRegistrarProducto();
            ?>
        </div>
    </div>
</div>

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
</style>

<script>
// Formatear el campo de precio mientras se escribe
document.getElementById('precio').addEventListener('input', function(e) {
    // Remover cualquier caracter que no sea número
    let value = this.value.replace(/\D/g, '');
    // Formatear con separadores de miles
    this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
});

// Antes de enviar el formulario, remover los puntos del precio
document.querySelector('form').addEventListener('submit', function(e) {
    const precioInput = document.getElementById('precio');
    precioInput.value = precioInput.value.replace(/\./g, '');
});
</script>
