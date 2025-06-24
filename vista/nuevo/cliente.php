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
<br>
<title>NUEVO CLIENTE</title>
<style>
	body {
		background-color: white;
	}
	h1 {
		font-family: "Copperplate", Fantasy;
		color: red;
	}
</style>

<center><h1>NUEVO CLIENTE</h1></center>

<div class="btn-group">
	<a href="index.php?pagina=tabla/clientes" class="btn btn-danger">Volver</a>
</div>

<div class="d-flex justify-content-center">
	<form class="p-5 w-50 bg-light" method="post">

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" required>
			<label for="nombre">NOMBRE</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" required>
			<label for="apellido">APELLIDO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cédula" required>
			<label for="cedula">CÉDULA</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección" required>
			<label for="direccion">DIRECCIÓN</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" required>
			<label for="telefono">TELÉFONO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required>
			<label for="email">EMAIL</label>
		</div>

		<?php 
			$registro = ClienteControlador::guardarCliente();
			if($registro == "ok"){
				echo '<script>
					if (window.history.replaceState) {
						window.history.replaceState(null, null, window.location.href);
					}
				</script>';
				echo '<div class="alert alert-success">El cliente ha sido registrado</div>
				<script>
					setTimeout(function(){
						window.location = "index.php?pagina=tabla/clientes";
					},1000);
				</script>';
			}
		?>
		
		<br>
		<center><button type="submit" class="btn btn-danger">GUARDAR</button></center>
	</form>
</div>
