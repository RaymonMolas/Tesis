<?php
if (!isset($_SESSION["validarIngreso"])) {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
}

// Determinar tipo de usuario
$tipo = isset($_GET["tipo"]) && $_GET["tipo"] == "personal" ? "personal" : "cliente";
$titulo = strtoupper("Nuevo Usuario " . $tipo);

// Obtener lista de clientes o personal
$lista = ($tipo == "cliente") ? ClienteControlador::buscarCliente() : ControladorPersonal::buscarPersonal();

// Normalizar POST para el controlador
if ($_SERVER["REQUEST_METHOD"] === "POST") {
	if ($tipo === "cliente") {
		$_POST["id_cliente"] = $_POST["id_relacionado"];
	} else {
		$_POST["id_personal"] = $_POST["id_relacionado"];
	}
}
?>

<br>
<title><?php echo $titulo; ?></title>
<style>
	body {
		background-color: white;
	}
	h1 {
		font-family: "Copperplate", Fantasy;
		color: red;
	}
</style>

<center><h1><?php echo $titulo; ?></h1></center>

<div class="btn-group">
	<a href="index.php?pagina=tabla/usuarios" class="btn btn-danger">Volver</a>
</div>

<div class="d-flex justify-content-center">
	<form class="p-5 w-50 bg-light" method="post">
		<div class="form-floating mb-3">
			<select class="form-select" name="id_relacionado" id="id_relacionado" required>
				<option value="">Seleccionar <?php echo ($tipo == "cliente") ? "cliente" : "personal"; ?></option>
				<?php foreach ($lista as $item): ?>
					<?php
						$id = ($tipo == "cliente") ? $item["id_cliente"] : $item["id_personal"];
						$nombre = $item["nombre"] . " " . $item["apellido"];
					?>
					<option value="<?php echo $id; ?>"><?php echo $nombre; ?></option>
				<?php endforeach ?>
			</select>
			<label for="id_relacionado"><?php echo ucfirst($tipo); ?></label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required>
			<label for="usuario">USUARIO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña" required>
			<label for="contrasena">CONTRASEÑA</label>
		</div>
		<?php
			$registro = ($tipo === "cliente") 
				? ControladorUsuario::guardarUsuarioCliente() 
				: ControladorUsuario::guardarUsuarioPersonal();

			if ($registro == "ok") {
				echo '<script>
					if (window.history.replaceState) {
						window.history.replaceState(null, null, window.location.href);
					}
				</script>';
				echo '<div class="alert alert-success">El usuario ha sido registrado</div>
				<script>
					setTimeout(function(){
						window.location = "index.php?pagina=tabla/usuarios";
					}, 1000);
				</script>';
			}
		?>
		<br>
		<center><button type="submit" class="btn btn-danger">GUARDAR</button></center>
	</form>
</div>
