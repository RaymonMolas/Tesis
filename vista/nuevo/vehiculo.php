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

$clientes = ClienteControlador::buscarCliente();
?>
<br>
<title>NUEVO VEHÍCULO</title>
<style>
	body {
		background-color: white;
	}
	h1 {
		font-family: "Copperplate", Fantasy;
		color: red;
	}
</style>

<center><h1>NUEVO VEHÍCULO</h1></center>

<div class="btn-group">
	<a href="index.php?pagina=tabla/vehiculos" class="btn btn-danger">Volver</a>
</div>

<div class="d-flex justify-content-center">
	<form class="p-5 w-50 bg-light" method="post">
		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="matricula" name="matricula" placeholder="Matrícula" required>
			<label for="matricula">MATRÍCULA</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="marca" name="marca" placeholder="Marca" required>
			<label for="marca">MARCA</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="modelo" name="modelo" placeholder="Modelo" required>
			<label for="modelo">MODELO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="number" class="form-control" id="anho" name="anho" placeholder="Año" min="1900" max="<?php echo date('Y'); ?>" required>
			<label for="anho">AÑO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="color" name="color" placeholder="Color" required>
			<label for="color">COLOR</label>
		</div>

		<div class="form-floating mb-3">
			<select class="form-select" id="id_cliente" name="id_cliente" required>
				<option value="">Seleccione un cliente</option>
				<?php foreach ($clientes as $cliente): ?>
					<option value="<?php echo $cliente['id_cliente']; ?>">
						<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
					</option>
				<?php endforeach; ?>
			</select>
			<label for="id_cliente">CLIENTE</label>
		</div>

		<?php 
			$registro = VehiculoControlador::ctrRegistrarVehiculo();
			if($registro == "ok"){
				echo '<script>
					if (window.history.replaceState) {
						window.history.replaceState(null, null, window.location.href);
					}
				</script>';
				echo '<div class="alert alert-success">El vehículo ha sido registrado</div>
				<script>
					setTimeout(function(){
						window.location = "index.php?pagina=tabla/vehiculos";
					},1000);
				</script>';
			}
		?>
		
		<br>
		<center><button type="submit" class="btn btn-danger">GUARDAR</button></center>
	</form>
</div>
