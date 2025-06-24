<?php
/**
 * Controlador de Plantilla - Motor Service
 * Sistema de Gestión Automotriz - Versión 2.0
 * 
 * Maneja el enrutamiento y la carga de vistas del sistema
 */

class ControladorPlantilla
{

	/**
	 * Traer la plantilla principal del sistema
	 */
	public function ctrTraerPlantilla()
	{
		include "plantilla.php";
	}

	/**
	 * Validar y cargar página solicitada
	 */
	public static function ctrCargarPagina()
	{
		if (isset($_GET["pagina"])) {
			$pagina = $_GET["pagina"];

			// Validar que la página esté en la lista de páginas permitidas
			if (self::validarPagina($pagina)) {
				// Verificar permisos de acceso
				if (self::verificarPermisos($pagina)) {
					// Cargar la vista correspondiente
					self::incluirVista($pagina);
				} else {
					self::mostrarErrorPermisos();
				}
			} else {
				self::mostrarError404();
			}
		} else {
			// Página por defecto según el tipo de usuario
			self::cargarPaginaDefecto();
		}
	}

	/**
	 * Validar que la página solicitada existe y está permitida
	 */
	private static function validarPagina($pagina)
	{
		$paginas_permitidas = [
			// Páginas principales
			"inicio",
			"login",
			"salir",

			// Páginas de tabla/listado
			"tabla/clientes",
			"tabla/usuarios",
			"tabla/personales",
			"tabla/productos",
			"tabla/vehiculos",
			"tabla/presupuestos",
			"tabla/facturas",
			"tabla/orden_trabajo",
			"tabla/historicocitas",
			"tabla/historial",

			// Páginas de creación
			"nuevo/cliente",
			"nuevo/usuario",
			"nuevo/personal",
			"nuevo/producto",
			"nuevo/vehiculo",
			"nuevo/presupuesto",
			"nuevo/factura",
			"nuevo/orden_trabajo",

			// Páginas de edición
			"editar/cliente",
			"editar/usuario",
			"editar/personal",
			"editar/producto",
			"editar/vehiculo",
			"editar/presupuesto",
			"editar/orden_trabajo",

			// Páginas de visualización
			"ver/presupuesto",
			"ver/factura",
			"ver/orden_trabajo",
			"ver/cliente",
			"ver/vehiculo",

			// Páginas especiales
			"agendamiento",
			"obtener_vehiculo",
			"marcar_leidas",

			// Páginas de reportes
			"reportes/general",
			"reportes/clientes",
			"reportes/vehiculos",
			"reportes/ordenes",
			"reportes/facturas",
			"reportes/productos",

			// Páginas de configuración
			"configuracion/empresa",
			"configuracion/sistema",
			"configuracion/usuarios",

			// Módulo de caja (si se implementa)
			"caja/apertura",
			"caja/actual",
			"caja/cierre",
			"caja/historial",
			"caja/reporte",

			// Páginas de ayuda
			"ayuda/manual",
			"ayuda/contacto",
			"ayuda/acerca"
		];

		return in_array($pagina, $paginas_permitidas);
	}

	/**
	 * Verificar permisos de acceso según el tipo de usuario
	 */
	private static function verificarPermisos($pagina)
	{
		// Si no hay sesión válida, solo permitir login
		if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
			return $pagina === "login";
		}

		$tipo_usuario = $_SESSION["tipo_usuario"];

		// Definir páginas restringidas por tipo de usuario
		$paginas_solo_personal = [
			"tabla/personales",
			"nuevo/personal",
			"editar/personal",
			"tabla/usuarios",
			"nuevo/usuario",
			"editar/usuario",
			"tabla/productos",
			"nuevo/producto",
			"editar/producto",
			"tabla/facturas",
			"nuevo/factura",
			"editar/factura",
			"ver/factura",
			"tabla/orden_trabajo",
			"nuevo/orden_trabajo",
			"editar/orden_trabajo",
			"ver/orden_trabajo",
			"tabla/presupuestos",
			"nuevo/presupuesto",
			"editar/presupuesto",
			"ver/presupuesto",
			"tabla/historicocitas",
			"reportes/general",
			"reportes/clientes",
			"reportes/vehiculos",
			"reportes/ordenes",
			"reportes/facturas",
			"reportes/productos",
			"configuracion/empresa",
			"configuracion/sistema",
			"configuracion/usuarios",
			"caja/apertura",
			"caja/actual",
			"caja/cierre",
			"caja/historial",
			"caja/reporte"
		];

		$paginas_solo_cliente = [
			"agendamiento",
			"tabla/historial",
			"marcar_leidas"
		];

		$paginas_compartidas = [
			"inicio",
			"salir",
			"obtener_vehiculo",
			"tabla/clientes",
			"nuevo/cliente",
			"editar/cliente",
			"ver/cliente",
			"tabla/vehiculos",
			"nuevo/vehiculo",
			"editar/vehiculo",
			"ver/vehiculo",
			"ayuda/manual",
			"ayuda/contacto",
			"ayuda/acerca"
		];

		// Verificar permisos
		if ($tipo_usuario == "cliente") {
			return in_array($pagina, $paginas_solo_cliente) || in_array($pagina, $paginas_compartidas);
		} elseif ($tipo_usuario == "personal") {
			// El personal puede acceder a todo excepto restricciones específicas
			return true;
		}

		return false;
	}

	/**
	 * Incluir la vista solicitada
	 */
	private static function incluirVista($pagina)
	{
		$archivo_vista = $pagina . ".php";
		$ruta_completa = __DIR__ . "/../vista/" . $archivo_vista;

		if (file_exists($ruta_completa)) {
			include $ruta_completa;
		} else {
			// Si el archivo no existe, mostrar error 404
			self::mostrarError404();
		}
	}

	/**
	 * Cargar página por defecto según el tipo de usuario
	 */
	private static function cargarPaginaDefecto()
	{
		if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
			include "vista/login.php";
		} else {
			// Redirigir a la página de inicio apropiada
			if ($_SESSION["tipo_usuario"] == "cliente") {
				include "vista/agendamiento.php";
			} else {
				include "vista/inicio.php";
			}
		}
	}

	/**
	 * Mostrar error 404 - Página no encontrada
	 */
	private static function mostrarError404()
	{
		http_response_code(404);
		?>
		<div class="container mt-5">
			<div class="row justify-content-center">
				<div class="col-md-6">
					<div class="text-center">
						<div class="error-container"
							style="background: white; border-radius: 15px; padding: 50px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-left: 5px solid #dc2626;">
							<div style="font-size: 72px; color: #dc2626; margin-bottom: 20px;">
								<i class="bi bi-exclamation-triangle"></i>
							</div>
							<h1 style="color: #dc2626; margin-bottom: 20px;">Página No Encontrada</h1>
							<p style="color: #666; margin-bottom: 30px;">
								La página que solicitas no existe o no está disponible.
							</p>
							<div>
								<a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
									<i class="bi bi-arrow-left"></i> Volver
								</a>
								<a href="index.php?pagina=inicio" class="btn btn-primary">
									<i class="bi bi-house"></i> Ir al Inicio
								</a>
							</div>
							<div class="mt-4">
								<small class="text-muted">
									Error 404 - Motor Service v<?php echo SISTEMA_VERSION ?? '2.0'; ?>
								</small>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Mostrar error de permisos
	 */
	private static function mostrarErrorPermisos()
	{
		http_response_code(403);
		?>
		<div class="container mt-5">
			<div class="row justify-content-center">
				<div class="col-md-6">
					<div class="text-center">
						<div class="error-container"
							style="background: white; border-radius: 15px; padding: 50px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-left: 5px solid #ffc107;">
							<div style="font-size: 72px; color: #ffc107; margin-bottom: 20px;">
								<i class="bi bi-shield-exclamation"></i>
							</div>
							<h1 style="color: #ffc107; margin-bottom: 20px;">Acceso Restringido</h1>
							<p style="color: #666; margin-bottom: 30px;">
								No tienes permisos para acceder a esta página.
							</p>
							<div>
								<a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
									<i class="bi bi-arrow-left"></i> Volver
								</a>
								<?php if ($_SESSION["tipo_usuario"] == "cliente"): ?>
									<a href="index.php?pagina=agendamiento" class="btn btn-primary">
										<i class="bi bi-calendar"></i> Mis Citas
									</a>
								<?php else: ?>
									<a href="index.php?pagina=inicio" class="btn btn-primary">
										<i class="bi bi-house"></i> Dashboard
									</a>
								<?php endif; ?>
							</div>
							<div class="mt-4">
								<small class="text-muted">
									Error 403 - Motor Service v<?php echo SISTEMA_VERSION ?? '2.0'; ?>
								</small>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Obtener breadcrumb para la página actual
	 */
	public static function obtenerBreadcrumb($pagina)
	{
		$breadcrumbs = [
			"inicio" => ["Inicio"],
			"tabla/clientes" => ["Inicio", "Clientes"],
			"nuevo/cliente" => ["Inicio", "Clientes", "Nuevo Cliente"],
			"editar/cliente" => ["Inicio", "Clientes", "Editar Cliente"],
			"tabla/vehiculos" => ["Inicio", "Vehículos"],
			"nuevo/vehiculo" => ["Inicio", "Vehículos", "Nuevo Vehículo"],
			"editar/vehiculo" => ["Inicio", "Vehículos", "Editar Vehículo"],
			"tabla/personales" => ["Inicio", "Personal"],
			"nuevo/personal" => ["Inicio", "Personal", "Nuevo Personal"],
			"editar/personal" => ["Inicio", "Personal", "Editar Personal"],
			"tabla/productos" => ["Inicio", "Productos"],
			"nuevo/producto" => ["Inicio", "Productos", "Nuevo Producto"],
			"editar/producto" => ["Inicio", "Productos", "Editar Producto"],
			"tabla/orden_trabajo" => ["Inicio", "Órdenes de Trabajo"],
			"nuevo/orden_trabajo" => ["Inicio", "Órdenes", "Nueva Orden"],
			"editar/orden_trabajo" => ["Inicio", "Órdenes", "Editar Orden"],
			"tabla/presupuestos" => ["Inicio", "Presupuestos"],
			"nuevo/presupuesto" => ["Inicio", "Presupuestos", "Nuevo Presupuesto"],
			"editar/presupuesto" => ["Inicio", "Presupuestos", "Editar Presupuesto"],
			"tabla/facturas" => ["Inicio", "Facturas"],
			"nuevo/factura" => ["Inicio", "Facturas", "Nueva Factura"],
			"agendamiento" => ["Inicio", "Agendamiento"],
			"tabla/historial" => ["Inicio", "Mi Historial"],
			"tabla/historicocitas" => ["Inicio", "Historial de Citas"],
			"tabla/usuarios" => ["Inicio", "Usuarios"],
			"nuevo/usuario" => ["Inicio", "Usuarios", "Nuevo Usuario"],
			"editar/usuario" => ["Inicio", "Usuarios", "Editar Usuario"]
		];

		return $breadcrumbs[$pagina] ?? ["Inicio"];
	}

	/**
	 * Obtener título de la página
	 */
	public static function obtenerTituloPagina($pagina)
	{
		$titulos = [
			"inicio" => "Dashboard - Motor Service",
			"login" => "Iniciar Sesión - Motor Service",
			"tabla/clientes" => "Gestión de Clientes",
			"nuevo/cliente" => "Nuevo Cliente",
			"editar/cliente" => "Editar Cliente",
			"tabla/vehiculos" => "Gestión de Vehículos",
			"nuevo/vehiculo" => "Nuevo Vehículo",
			"editar/vehiculo" => "Editar Vehículo",
			"tabla/personales" => "Gestión de Personal",
			"nuevo/personal" => "Nuevo Personal",
			"editar/personal" => "Editar Personal",
			"tabla/productos" => "Gestión de Productos",
			"nuevo/producto" => "Nuevo Producto",
			"editar/producto" => "Editar Producto",
			"tabla/orden_trabajo" => "Órdenes de Trabajo",
			"nuevo/orden_trabajo" => "Nueva Orden de Trabajo",
			"editar/orden_trabajo" => "Editar Orden de Trabajo",
			"tabla/presupuestos" => "Gestión de Presupuestos",
			"nuevo/presupuesto" => "Nuevo Presupuesto",
			"editar/presupuesto" => "Editar Presupuesto",
			"tabla/facturas" => "Gestión de Facturas",
			"nuevo/factura" => "Nueva Factura",
			"agendamiento" => "Agendamiento de Citas",
			"tabla/historial" => "Mi Historial de Servicios",
			"tabla/historicocitas" => "Historial de Citas",
			"tabla/usuarios" => "Gestión de Usuarios",
			"nuevo/usuario" => "Nuevo Usuario",
			"editar/usuario" => "Editar Usuario"
		];

		return $titulos[$pagina] ?? "Motor Service";
	}

	/**
	 * Verificar si se requiere incluir scripts específicos para la página
	 */
	public static function incluirScriptsPagina($pagina)
	{
		$scripts_especiales = [
			"agendamiento" => ["fullcalendar", "sweetalert2"],
			"nuevo/orden_trabajo" => ["select2", "datepicker"],
			"nuevo/presupuesto" => ["select2", "calculator"],
			"nuevo/factura" => ["select2", "print"],
			"tabla/clientes" => ["datatables"],
			"tabla/vehiculos" => ["datatables"],
			"tabla/productos" => ["datatables"],
			"tabla/orden_trabajo" => ["datatables"],
			"tabla/presupuestos" => ["datatables"],
			"tabla/facturas" => ["datatables"],
			"reportes/general" => ["charts", "export"]
		];

		if (isset($scripts_especiales[$pagina])) {
			foreach ($scripts_especiales[$pagina] as $script) {
				switch ($script) {
					case "datatables":
						echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">';
						echo '<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>';
						echo '<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>';
						break;
					case "select2":
						echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">';
						echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';
						break;
					case "sweetalert2":
						echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
						break;
					case "fullcalendar":
						echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">';
						echo '<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>';
						break;
					case "charts":
						echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
						break;
				}
			}
		}
	}

	/**
	 * Obtener configuración de meta tags para SEO
	 */
	public static function obtenerMetaTags($pagina)
	{
		$meta_tags = [
			"inicio" => [
				"description" => "Sistema de gestión automotriz Motor Service - Dashboard principal",
				"keywords" => "motor service, gestión automotriz, dashboard, taller mecánico"
			],
			"agendamiento" => [
				"description" => "Agenda tu cita en Motor Service - Servicio integral automotriz",
				"keywords" => "agendar cita, motor service, servicio automotriz, cita taller"
			],
			"tabla/clientes" => [
				"description" => "Gestión de clientes - Motor Service",
				"keywords" => "clientes, gestión, motor service, base de datos"
			]
		];

		$default_meta = [
			"description" => "Motor Service - Sistema de gestión integral automotriz",
			"keywords" => "motor service, gestión automotriz, taller mecánico, servicios"
		];

		return $meta_tags[$pagina] ?? $default_meta;
	}

	/**
	 * Verificar si la página requiere autenticación
	 */
	public static function requiereAutenticacion($pagina)
	{
		$paginas_publicas = ["login"];
		return !in_array($pagina, $paginas_publicas);
	}

	/**
	 * Generar menú dinámico según permisos del usuario
	 */
	public static function generarMenuNavegacion()
	{
		if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
			return [];
		}

		$tipo_usuario = $_SESSION["tipo_usuario"];

		if ($tipo_usuario == "personal") {
			return [
				"Dashboard" => "inicio",
				"Clientes" => [
					"Ver Clientes" => "tabla/clientes",
					"Nuevo Cliente" => "nuevo/cliente"
				],
				"Vehículos" => [
					"Ver Vehículos" => "tabla/vehiculos",
					"Nuevo Vehículo" => "nuevo/vehiculo"
				],
				"Servicios" => [
					"Órdenes de Trabajo" => "tabla/orden_trabajo",
					"Nueva Orden" => "nuevo/orden_trabajo",
					"Presupuestos" => "tabla/presupuestos",
					"Nuevo Presupuesto" => "nuevo/presupuesto"
				],
				"Facturación" => [
					"Ver Facturas" => "tabla/facturas",
					"Nueva Factura" => "nuevo/factura"
				],
				"Agendamiento" => "agendamiento",
				"Productos" => [
					"Ver Productos" => "tabla/productos",
					"Nuevo Producto" => "nuevo/producto"
				],
				"Personal" => [
					"Ver Personal" => "tabla/personales",
					"Nuevo Personal" => "nuevo/personal"
				],
				"Usuarios" => [
					"Ver Usuarios" => "tabla/usuarios",
					"Nuevo Usuario" => "nuevo/usuario"
				]
			];
		} else {
			return [
				"Mis Citas" => "agendamiento",
				"Mi Historial" => "tabla/historial",
				"Mis Vehículos" => [
					"Ver Vehículos" => "tabla/vehiculos",
					"Nuevo Vehículo" => "nuevo/vehiculo"
				]
			];
		}
	}
}
?>