<?php
if(isset($_GET["id"])){
	$item = "id_cliente";
	$valor = $_GET["id"];
	$usuario = ClienteControlador::buscarcliente($item, $valor);
}
?>

<title>EDITAR CLIENTE</title>
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
<center> <h1>EDITAR CLIENTE</h1> </center>
<div class="btn-group">		
	<a href="index.php?pagina=tabla/clientes" class="btn btn-danger">Volver</a>	
</div>
<div class="d-flex justify-content-center text-center">
	<form class="p-5 bg-light w-50" method="post">
	<div class="form-floating mb-3">
			<input type="text" readonly class="form-control" id="id" name="id" placeholder="id" value="<?php echo $usuario["id_cliente"]; ?>">
			<label for="codigo">CODIGO</label>
		</div>
		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="nombre" name="nombre" placeholder="nombre" value="<?php echo $usuario["nombre"]; ?>">
			<label for="nombre">NOMBRE</label>
		</div>
		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="apellido" name="apellido" placeholder="apellido" value="<?php echo $usuario["apellido"]; ?>">
			<label for="apellido">APELLIDO</label>
		</div>
		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="cedula" name="cedula" placeholder="cedula" value="<?php echo $usuario["cedula"]; ?>">
			<label for="ruc">RUC</label>
		</div>
		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="telefono" name="telefono" placeholder="telefono" value="<?php echo $usuario["telefono"]; ?>">
			<label for="telefono">TELEFONO</label>
		</div>
		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="direccion" name="direccion" placeholder="direccion" value="<?php echo $usuario["direccion"]; ?>">
			<label for="direccion">DIRECCION</label>
		</div>
		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="email" name="email" placeholder="email" value="<?php echo $usuario["email"]; ?>">
			<label for="telefono">TELEFONO</label>
		</div>
    <?php 
		$actualizar = ClienteControlador::actualizarCliente();
		if($actualizar == "ok"){
			echo '<script>
			if ( window.history.replaceState ) {
				window.history.replaceState( null, null, window.location.href );
			}
			</script>';
			echo '<div class="alert alert-success">El cliente ha sido actualizado</div>
			<script>
				setTimeout(function(){
					window.location = "index.php?pagina=tabla/clientes";
				},1000);
			</script>
			';
		}
		?>
		<br>
		<button type="submit" class="btn btn-danger">MODIFICAR</button>
		</form>
</div>