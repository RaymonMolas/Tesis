<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {

	// MARCAR COMO COMPLETADO
	if (isset($_POST["id_completar"])) {
		$id = $_POST["id_completar"];
		$resultado = ControladorAgendamiento::actualizarEstado($id, "completado");

		if ($resultado === "ok") {
			// Notificar clientes sin cita activa
			$clientesSinCita = ModeloAgendamiento::obtenerClientesSinCitaActiva();
			foreach ($clientesSinCita as $cliente) {
				ModeloAgendamiento::insertarNotificacion($cliente["id_cliente"], "¡Hay fechas disponibles para agendar tu cita!");
			}

			$_SESSION["mensajeJS"] = "Swal.fire({
                icon: 'success',
                title: 'Cita completada',
                text: '✅ La cita fue marcada como completada correctamente.',
                confirmButtonText: 'Aceptar'
            });";
		} else {
			$_SESSION["mensajeJS"] = "Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '❌ No se pudo marcar la cita como completada.',
                confirmButtonText: 'Cerrar'
            });";
		}

		header("Location: index.php?pagina=agendamiento");
		exit;
	}

	// APROBAR o RECHAZAR
	foreach ($_POST as $clave => $valor) {
		// Aprobar
		if (strpos($clave, 'accion_aprobar_') === 0) {
			$id = str_replace('accion_aprobar_', '', $clave);
			$resultado = ControladorAgendamiento::actualizarEstado($id, "aprobado");

			if ($resultado === "ok") {
				// Notificar clientes sin cita activa
				$clientesSinCita = ModeloAgendamiento::obtenerClientesSinCitaActiva();
				foreach ($clientesSinCita as $cliente) {
					ModeloAgendamiento::insertarNotificacion($cliente["id_cliente"], "¡Hay fechas disponibles para agendar tu cita!");
				}

				$_SESSION["mensajeJS"] = "Swal.fire({
                    icon: 'success',
                    title: 'Cita aprobada',
                    text: '✅ La cita fue aprobada correctamente.',
                    confirmButtonText: 'Aceptar'
                });";
			} elseif ($resultado === "limite_excedido") {
				$_SESSION["mensajeJS"] = "Swal.fire({
                    icon: 'error',
                    title: '❌ No se puede aprobar',
                    text: 'Ya hay 6 citas activas en esa fecha y ninguna fue completada.',
                    confirmButtonText: 'Entendido'
                });";
			} else {
				$_SESSION["mensajeJS"] = "Swal.fire({
                    icon: 'warning',
                    title: 'Error al aprobar',
                    text: '⚠️ Ocurrió un error al aprobar la cita.',
                    confirmButtonText: 'Cerrar'
                });";
			}

			header("Location: " . $_SERVER["REQUEST_URI"]);
			exit;
		}

		// Rechazar
		if (strpos($clave, 'accion_rechazar_') === 0) {
			$id = str_replace('accion_rechazar_', '', $clave);
			$resultado = ControladorAgendamiento::actualizarEstado($id, "rechazado");

			if ($resultado === "ok") {
				// Notificar clientes sin cita activa
				$clientesSinCita = ModeloAgendamiento::obtenerClientesSinCitaActiva();
				foreach ($clientesSinCita as $cliente) {
					ModeloAgendamiento::insertarNotificacion($cliente["id_cliente"], "¡Hay fechas disponibles para agendar tu cita!");
				}

				$_SESSION["mensajeJS"] = "Swal.fire({
                    icon: 'success',
                    title: 'Cita rechazada',
                    text: '✅ La cita fue rechazada correctamente.',
                    confirmButtonText: 'Aceptar'
                });";
			} else {
				$_SESSION["mensajeJS"] = "Swal.fire({
                    icon: 'warning',
                    title: 'Error al rechazar',
                    text: '⚠️ Ocurrió un error al rechazar la cita.',
                    confirmButtonText: 'Cerrar'
                });";
			}

			header("Location: " . $_SERVER["REQUEST_URI"]);
			exit;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<style>
		h1 {
			color: white;
		}

		.bd-placeholder-img {
			font-size: 1.125rem;
			text-anchor: middle;
			-webkit-user-select: none;
			-moz-user-select: none;
			user-select: none;
		}

		@media (min-width: 768px) {
			.bd-placeholder-img-lg {
				font-size: 3.5rem;
			}
		}

		.bi {
			vertical-align: -.125em;
			fill: white;
		}

		.offcanvas-title {
			color: white;
		}

		.navbar-brand {
			padding-bottom: 10px
		}

		a img {
			width: 15%;
			border-radius: 50%;
			max-width: 100%;
		}
	</style>
</head>
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol id="bootstrap" viewBox="0 0 118 94">
		<title>Bootstrap</title>
		<path fill-rule="evenodd" clip-rule="evenodd"
			d="M24.509 0c-6.733 0-11.715 5.893-11.492 12.284.214 6.14-.064 14.092-2.066 20.577C8.943 39.365 5.547 43.485 0 44.014v5.972c5.547.529 8.943 4.649 10.951 11.153 2.002 6.485 2.28 14.437 2.066 20.577C12.794 88.106 17.776 94 24.51 94H93.5c6.733 0 11.714-5.893 11.491-12.284-.214-6.14.064-14.092 2.066-20.577 2.009-6.504 5.396-10.624 10.943-11.153v-5.972c-5.547-.529-8.934-4.649-10.943-11.153-2.002-6.484-2.28-14.437-2.066-20.577C105.214 5.894 100.233 0 93.5 0H24.508zM80 57.863C80 66.663 73.436 72 62.543 72H44a2 2 0 01-2-2V24a2 2 0 012-2h18.437c9.083 0 15.044 4.92 15.044 12.474 0 5.302-4.01 10.049-9.119 10.88v.277C75.317 46.394 80 51.21 80 57.863zM60.521 28.34H49.948v14.934h8.905c6.884 0 10.68-2.772 10.68-7.727 0-4.643-3.264-7.207-9.012-7.207zM49.948 49.2v16.458H60.91c7.167 0 10.964-2.876 10.964-8.281 0-5.406-3.903-8.178-11.425-8.178H49.948z">
		</path>
	</symbol>
	<symbol id="home" viewBox="0 0 16 16">
		<path
			d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z" />
	</symbol>
	<symbol id="speedometer2" viewBox="0 0 16 16">
		<path
			d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z" />
		<path fill-rule="evenodd"
			d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z" />
	</symbol>
	<symbol id="table" viewBox="0 0 16 16">
		<path
			d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z" />
	</symbol>
	<symbol id="grid" viewBox="0 0 16 16">
		<path
			d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z" />
	</symbol>
	<symbol id="calendar" viewBox="0 0 16 16">
		<path
			d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
	</symbol>
	<symbol id="gear" viewBox="0 0 16 16">
		<path
			d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z" />
		<path
			d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z" />
	</symbol>
</svg>

<body>
	<?php if (isset($_GET["pagina"]) && $_GET["pagina"] != "login"): ?>
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
			<div class="container-fluid">
				<a href="index.php?pagina=inicio"
					class="d-flex align-items-center col-md-3 mb-2 mb-md-0 text-white text-decoration-none">
					<img src="../img/img-01.jpg" alt="IMG">
				</a>
				<div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
					<?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
						<ul class="nav col-12 col-lg-auto my-2 justify-content-center my-md-0 text-small">
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button"
									data-bs-toggle="dropdown">
									<svg class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#calendar" />
									</svg>Agendamiento
								</a>
								<ul class="dropdown-menu bg-dark">
									<li><a href="index.php?pagina=tabla/historicocitas" class="nav-link text-white"><i
												class="bi bi-clock-history"></i> Historial</a></li>
									<li><a href="index.php?pagina=agendamiento" class="nav-link text-white"><i
												class="bi bi-journals"></i> Citas</a></li>
								</ul>
							</li>
							<li><a href="index.php?pagina=tabla/historial" class="nav-link text-white">
									<svg class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#speedometer2" />
									</svg> Historial
								</a></li>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button"
									data-bs-toggle="dropdown">
									<svg class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#gear" />
									</svg>Herramientas
								</a>
								<ul class="dropdown-menu bg-dark">
									<li><a href="index.php?pagina=tabla/clientes" class="nav-link text-white"><i
												class="bi bi-person-circle"></i> Clientes</a></li>
									<li><a href="index.php?pagina=tabla/vehiculos" class="nav-link text-white"><i
												class="bi bi-car-front"></i> Vehículos</a></li>
									<li><a href="index.php?pagina=tabla/personales" class="nav-link text-white"><i
												class="bi bi-person-badge"></i> Personales</a></li>
									<li><a href="index.php?pagina=tabla/productos" class="nav-link text-white"><i
												class="bi bi-box2"></i> Productos</a></li>
									<li><a href="index.php?pagina=tabla/usuarios" class="nav-link text-white"><i
												class="bi bi-person-badge"></i> Usuarios</a></li>
								</ul>
							</li>
							<li><a href="index.php?pagina=tabla/presupuestos" class="nav-link text-white">
									<svg class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#grid" />
									</svg> Presupuestos
								</a></li>
							<li><a href="index.php?pagina=tabla/orden_trabajo" class="nav-link text-white">
									<svg class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#table" />
									</svg> Ordenes
								</a></li>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownFactura" role="button"
									data-bs-toggle="dropdown">
									<svg class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#grid" />
									</svg>Facturación
								</a>
								<ul class="dropdown-menu bg-dark">
									<li><a href="index.php?pagina=tabla/facturas" class="nav-link text-white"><i
												class="bi bi-receipt"></i> Facturas</a></li>
									<li><a href="index.php?pagina=nuevo/factura" class="nav-link text-white"><i
												class="bi bi-plus-circle"></i> Nueva Factura</a></li>
								</ul>
							</li>
							<!-- Caja menu removed as per request
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownCaja" role="button"
									data-bs-toggle="dropdown">
									<svg class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#speedometer2" />
									</svg>Caja
								</a>
								<ul class="dropdown-menu bg-dark">
									<li><a href="index.php?pagina=caja/actual" class="nav-link text-white"><i
												class="bi bi-cash-stack"></i> Caja Actual</a></li>
									<li><a href="index.php?pagina=caja/apertura" class="nav-link text-white"><i
												class="bi bi-unlock"></i> Abrir Caja</a></li>
									<li><a href="index.php?pagina=caja/historial" class="nav-link text-white"><i
												class="bi bi-clock-history"></i> Historial</a></li>
								</ul>
							</li>
							-->
						</ul>
					<?php elseif ($_SESSION["tipo_usuario"] == "cliente"): ?>
						<ul class="nav col-12 col-lg-auto my-2 justify-content-center my-md-0 text-small">
							<li><a href="index.php?pagina=agendamiento" class="nav-link text-white"><svg
										class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#calendar" />
									</svg> Citas</a></li>
							<li><a href="index.php?pagina=tabla/historial" class="nav-link text-white"><svg
										class="bi d-block mx-auto mb-1" width="24" height="24">
										<use xlink:href="#speedometer2" />
									</svg> Historial</a></li>
						</ul>
					<?php endif; ?>
				</div>
				<div class="col-md-3 text-end d-flex align-items-center justify-content-end gap-3">
					<?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
						<!-- Campana de notificaciones -->
						<div class="dropdown">
							<a href="#" class="text-white text-decoration-none position-relative" data-bs-toggle="modal"
								data-bs-target="#modalTodasCitas">
								<i class="bi bi-bell-fill fs-4"></i>
								<?php
								$solicitudes = ControladorAgendamiento::listarSolicitudesPendientes();
								$pendientes = count($solicitudes);
								echo '<script>const totalPendientes = ' . $pendientes . ';</script>';
								if ($pendientes > 0): ?>
									<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
										<?php echo $pendientes; ?>
									</span>
								<?php endif; ?>
							</a>
						</div>

						<!-- Usuario -->
						<div class="dropdown text-end">
							<a href="#" class="d-block text-white text-decoration-none dropdown-toggle" id="userDropdown"
								data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-person-circle"></i>
								<?php
								$idPersonal = $_SESSION["id_personal"] ?? "??";
								echo "PersonalID: $idPersonal - " . $_SESSION["usuario"];
								?>
							</a>
							<ul class="dropdown-menu dropdown-menu-end text-small" aria-labelledby="userDropdown">
								<li><a class="dropdown-item" href="index.php?pagina=editar/usuario">Modificar Usuario</a></li>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li><a class="dropdown-item" href="index.php?pagina=salir">Cerrar Sesión</a></li>
							</ul>
						</div>
					<?php else: ?>
						<?php if ($_SESSION["tipo_usuario"] == "cliente"): ?>
							<!-- Notificaciones del Cliente -->
							<div class="dropdown">
								<a href="#" class="text-white text-decoration-none position-relative" data-bs-toggle="modal"
									data-bs-target="#modalNotificacionesCliente">
									<i class="bi bi-bell-fill fs-4"></i>
									<?php
									$notificaciones = ModeloAgendamiento::obtenerNotificacionesCliente($_SESSION["id_cliente"]);
									$noLeidas = array_filter($notificaciones, fn($n) => $n["leida"] == 0);
									$cantidadNoLeidas = count($noLeidas);
									echo '<script>const notificacionesCliente = ' . $cantidadNoLeidas . ';</script>';
									if ($cantidadNoLeidas > 0): ?>
										<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
											<?php echo $cantidadNoLeidas; ?>
										</span>
									<?php endif; ?>
								</a>
							</div>
							<script>
								document.addEventListener('DOMContentLoaded', function () {
									const modalNoti = document.getElementById('modalNotificacionesCliente');
									modalNoti.addEventListener('shown.bs.modal', function () {
										fetch('marcar_leidas.php', { method: 'POST' });
										localStorage.setItem("notificacionesCliente", "0");
									});
								});
							</script>
							<!-- Cliente -->
							<span class="me-3 text-white">
								<?php
								$id_cliente = $_SESSION["id_cliente"] ?? "??";
								echo "ClientID: $id_cliente - " . $_SESSION["usuario"];
								?>
							</span>
							<a class="btn btn-primary" href="index.php?pagina=salir">Cerrar Sesión</a>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</nav>
		<!-- Sonido y alerta -->
		<?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
			<audio id="notificacionSonido" src="https://www.myinstants.com/media/sounds/mario-coin.mp3" preload="auto"></audio>
			<script>
				document.addEventListener("DOMContentLoaded", () => {
					const sonido = document.getElementById("notificacionSonido");

					if (typeof totalPendientes !== "undefined" && totalPendientes > 0) {
						const prev = localStorage.getItem("prevSolicitudes") || 0;

						if (parseInt(prev) < totalPendientes) {
							sonido.play();
							Swal.fire({
								icon: 'info',
								title: 'Nueva solicitud de cita',
								text: 'Hay nuevas citas pendientes de aprobación.',
								toast: true,
								position: 'bottom-end',
								showConfirmButton: false,
								timer: 5000
							});
						}
						localStorage.setItem("prevSolicitudes", totalPendientes);
					}
				});
			</script>
			<script>
				document.addEventListener('DOMContentLoaded', function () {
					const modal = document.getElementById('modalCita');
					if (!modal) return; // ← Esto evita errores en páginas como agendamiento.php

					modal.addEventListener('show.bs.modal', function (event) {
						const button = event.relatedTarget;
						document.getElementById('modal_id_cita').value = button.getAttribute('data-id');
						document.getElementById('modal_cliente').innerText = button.getAttribute('data-cliente');
						document.getElementById('modal_fecha').innerText = button.getAttribute('data-fecha');
						document.getElementById('modal_hora').innerText = button.getAttribute('data-hora');
						document.getElementById('modal_motivo').innerText = button.getAttribute('data-motivo');
					});
				});
			</script>
		<?php endif; ?>
	<?php endif; ?>

	<main class="container">
		<div class="p-5 rounded">
			<div class="container py-5">
				<?php
				if (isset($_GET["pagina"])) {
					if (
						in_array($_GET["pagina"], [
							"inicio",
							"login",
							"obtener_vehiculo",
							"agendamiento",
							"marcar_leidas",
							"tabla/clientes",
							"tabla/usuarios",
							"tabla/personales",
							"tabla/productos",
							"tabla/vehiculos",
							"tabla/presupuestos",
							"tabla/facturas",
							"nuevo/cliente",
							"nuevo/usuario",
							"nuevo/personal",
							"nuevo/producto",
							"nuevo/vehiculo",
							"nuevo/presupuesto",
							"nuevo/factura",
							"editar/cliente",
							"editar/usuario",
							"editar/personal",
							"editar/producto",
							"editar/vehiculo",
							"editar/presupuesto",
							"ver/presupuesto",
							"ver/factura",
							"tabla/orden_trabajo",
							"nuevo/orden_trabajo",
							"editar/orden_trabajo",
							"ver/orden_trabajo",
							"tabla/historicocitas",
							"tabla/historial",
							"caja/apertura",
							"caja/actual",
							"caja/cierre",
							"caja/historial",
							"caja/reporte",
							"salir"
						])
					) {
						include $_GET["pagina"] . ".php";
					} else {
						include "error404.php";
					}
				} else {
					include "login.php";
				}
				?>
			</div>
		</div>
		<!-- Modal que muestra todas las citas pendientes -->
		<div class="modal fade" id="modalTodasCitas" tabindex="-1" aria-labelledby="modalTodasCitasLabel"
			aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<form method="post">
						<div class="modal-header">
							<h5 class="modal-title" id="modalTodasCitasLabel">Solicitudes de Citas Pendientes</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"
								aria-label="Cerrar"></button>
						</div>

						<?php if (!empty($mensaje)): ?>
							<div class="alert alert-<?php echo $tipoAlerta; ?> alert-dismissible fade show m-3"
								role="alert">
								<?php echo $mensaje; ?>
								<button type="button" class="btn-close" data-bs-dismiss="alert"
									aria-label="Cerrar"></button>
							</div>
						<?php endif; ?>

						<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
							<?php if ($pendientes > 0): ?>
								<?php foreach ($solicitudes as $solicitud): ?>
									<div class="border rounded p-3 mb-3">
										<input type="hidden" name="id_cita" value="<?php echo $solicitud['id_cita']; ?>">
										<p><strong>Cliente:</strong> <?php echo $solicitud["cliente"]; ?></p>
										<p><strong>Fecha:</strong> <?php echo $solicitud["fecha"]; ?></p>
										<p><strong>Hora:</strong> <?php echo $solicitud["hora"]; ?></p>
										<p><strong>Motivo:</strong> <?php echo $solicitud["motivo"]; ?></p>
										<div class="d-flex gap-2">
											<button type="submit" name="accion_aprobar_<?php echo $solicitud['id_cita']; ?>"
												class="btn btn-success btn-sm">Aprobar</button>
											<button type="submit" name="accion_rechazar_<?php echo $solicitud['id_cita']; ?>"
												class="btn btn-danger btn-sm">Rechazar</button>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else: ?>
								<p class="text-muted">No hay solicitudes pendientes.</p>
							<?php endif; ?>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- Modal de Notificaciones para el Cliente -->
		<div class="modal fade" id="modalNotificacionesCliente" tabindex="-1"
			aria-labelledby="modalNotificacionesClienteLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form method="post">
						<div class="modal-header">
							<h5 class="modal-title" id="modalNotificacionesClienteLabel">Tus Notificaciones</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal"
								aria-label="Cerrar"></button>
						</div>
						<div class="modal-body" style="max-height: 400px; overflow-y: auto;">
							<?php if (!empty($notificaciones)): ?>
								<?php foreach ($notificaciones as $noti): ?>
									<div class="border-bottom pb-2 mb-2">
										<p class="mb-1"><?php echo $noti["mensaje"]; ?></p>
										<small class="text-muted"><?php echo $noti["fecha_creacion"]; ?></small>
									</div>
								<?php endforeach; ?>
							<?php else: ?>
								<p class="text-muted text-center">No tienes notificaciones.</p>
							<?php endif; ?>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php if (!empty($_SESSION["mensajeJS"])): ?>
			<script>
				document.addEventListener("DOMContentLoaded", function () {
					<?php
					echo $_SESSION["mensajeJS"];
					unset($_SESSION["mensajeJS"]);
					?>
				});
			</script>
		<?php endif; ?>
	</main>
</body>

</html>