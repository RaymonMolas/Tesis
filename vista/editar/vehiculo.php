<?php
if(isset($_GET["id"])){
    $vehiculo = VehiculoControlador::ctrObtenerVehiculo($_GET["id"]);
    $clientes = ClienteControlador::buscarCliente();
}
?>

<title>EDITAR VEHÍCULO</title>
<style>
    body{
        background-color: white;
    }
    h1{
        font-family: "Copperplate", Fantasy;
        color: red;
    }
</style>
<br>
<center><h1>EDITAR VEHÍCULO</h1></center>

<div class="btn-group">        
    <a href="index.php?pagina=tabla/vehiculos" class="btn btn-danger">Volver</a>    
</div>

<div class="d-flex justify-content-center text-center">
    <form class="p-5 bg-light w-50" method="post">
        <div class="form-floating mb-3">
            <input type="text" readonly class="form-control" id="id_vehiculo" name="id_vehiculo" placeholder="id" value="<?php echo $vehiculo["id_vehiculo"]; ?>">
            <label for="id_vehiculo">CÓDIGO</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="matricula" name="matricula" placeholder="matricula" value="<?php echo $vehiculo["matricula"]; ?>" required>
            <label for="matricula">MATRÍCULA</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="marca" name="marca" placeholder="marca" value="<?php echo $vehiculo["marca"]; ?>" required>
            <label for="marca">MARCA</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="modelo" name="modelo" placeholder="modelo" value="<?php echo $vehiculo["modelo"]; ?>" required>
            <label for="modelo">MODELO</label>
        </div>

        <div class="form-floating mb-3">
            <input type="number" class="form-control" id="anho" name="anho" placeholder="año" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo $vehiculo["anho"]; ?>" required>
            <label for="anho">AÑO</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="color" name="color" placeholder="color" value="<?php echo $vehiculo["color"]; ?>" required>
            <label for="color">COLOR</label>
        </div>

        <div class="form-floating mb-3">
            <select class="form-select" id="id_cliente" name="id_cliente" required>
                <option value="">Seleccione un cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['id_cliente']; ?>" <?php echo ($cliente['id_cliente'] == $vehiculo['id_cliente']) ? 'selected' : ''; ?>>
                        <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="id_cliente">CLIENTE</label>
        </div>

        <?php 
        $actualizar = VehiculoControlador::ctrActualizarVehiculo();
        if($actualizar == "ok"){
            echo '<script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
            </script>';
            echo '<div class="alert alert-success">El vehículo ha sido actualizado</div>
            <script>
                setTimeout(function(){
                    window.location = "index.php?pagina=tabla/vehiculos";
                },1000);
            </script>
            ';
        }
        ?>
        <br>
        <button type="submit" class="btn btn-danger">MODIFICAR</button>
    </form>
</div>
