<?php
require_once "../modelo/login_modelo.php";

class LoginControlador
{

  /**
   * Procesar login
   */
  static public function ctrLogin()
  {
    if (isset($_POST["usuario_login"]) && isset($_POST["contrasena_login"])) {

      $usuario = strtolower(trim($_POST["usuario_login"]));
      $contrasena = md5($_POST["contrasena_login"]);
      $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

      // Verificar si el usuario está bloqueado por intentos fallidos
      if (LoginModelo::mdlUsuarioBloqueado($usuario)) {
        $datos_log = array(
          "usuario" => $usuario,
          "tipo_usuario" => "unknown",
          "ip_address" => $ip_address,
          "user_agent" => $user_agent,
          "exitoso" => 0,
          "motivo_fallo" => "Usuario bloqueado por intentos fallidos"
        );
        LoginModelo::mdlRegistrarIntentoLogin($datos_log);

        echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Acceso Bloqueado",
                        text: "Tu cuenta ha sido bloqueada temporalmente por múltiples intentos fallidos. Intenta en 30 minutos.",
                        confirmButtonColor: "#dc2626"
                    });
                </script>';
        return;
      }

      // Intentar login como personal primero
      $usuario_personal = LoginModelo::mdlValidarLoginPersonal($usuario, $contrasena);

      if ($usuario_personal) {
        // Login exitoso como personal
        self::ctrEstablecerSesionPersonal($usuario_personal);

        // Registrar login exitoso
        $datos_log = array(
          "usuario" => $usuario,
          "tipo_usuario" => "personal",
          "ip_address" => $ip_address,
          "user_agent" => $user_agent,
          "exitoso" => 1,
          "motivo_fallo" => null
        );
        LoginModelo::mdlRegistrarIntentoLogin($datos_log);
        LoginModelo::mdlLimpiarIntentosFallidos($usuario);
        LoginModelo::mdlActualizarUltimoAccesoPersonal($usuario_personal['id_usuario_personal']);

        echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Bienvenido!",
                        text: "Acceso autorizado - Motor Service",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location = "index.php?pagina=inicio";
                    });
                </script>';
        return;
      }

      // Si no es personal, intentar como cliente
      $usuario_cliente = LoginModelo::mdlValidarLoginCliente($usuario, $contrasena);

      if ($usuario_cliente) {
        // Login exitoso como cliente
        self::ctrEstablecerSesionCliente($usuario_cliente);

        // Registrar login exitoso
        $datos_log = array(
          "usuario" => $usuario,
          "tipo_usuario" => "cliente",
          "ip_address" => $ip_address,
          "user_agent" => $user_agent,
          "exitoso" => 1,
          "motivo_fallo" => null
        );
        LoginModelo::mdlRegistrarIntentoLogin($datos_log);
        LoginModelo::mdlLimpiarIntentosFallidos($usuario);
        LoginModelo::mdlActualizarUltimoAccesoCliente($usuario_cliente['id_usuario_cliente']);

        echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Bienvenido!",
                        text: "Acceso autorizado - Portal Cliente",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location = "index.php?pagina=agendamiento";
                    });
                </script>';
        return;
      }

      // Login fallido
      $motivo = "Credenciales incorrectas";
      if (!LoginModelo::mdlVerificarUsuarioExiste($usuario)) {
        $motivo = "Usuario no existe";
      }

      $datos_log = array(
        "usuario" => $usuario,
        "tipo_usuario" => "unknown",
        "ip_address" => $ip_address,
        "user_agent" => $user_agent,
        "exitoso" => 0,
        "motivo_fallo" => $motivo
      );
      LoginModelo::mdlRegistrarIntentoLogin($datos_log);

      // Calcular intentos restantes
      $intentos_fallidos = LoginModelo::mdlObtenerIntentosRecientes($usuario);
      $intentos_restantes = 5 - $intentos_fallidos;

      $mensaje_adicional = "";
      if ($intentos_restantes <= 2 && $intentos_restantes > 0) {
        $mensaje_adicional = " Te quedan $intentos_restantes intentos antes del bloqueo.";
      }

      echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Acceso Denegado",
                    text: "Usuario o contraseña incorrectos.' . $mensaje_adicional . '",
                    confirmButtonColor: "#dc2626"
                });
            </script>';
    }
  }

  /**
   * Establecer sesión para personal
   */
  static private function ctrEstablecerSesionPersonal($usuario_personal)
  {
    $_SESSION["validarIngreso"] = "ok";
    $_SESSION["tipo_usuario"] = "personal";
    $_SESSION["id_usuario_personal"] = $usuario_personal["id_usuario_personal"];
    $_SESSION["id_personal"] = $usuario_personal["id_personal"];
    $_SESSION["usuario"] = $usuario_personal["usuario"];
    $_SESSION["nombre_completo"] = $usuario_personal["nombre"] . " " . $usuario_personal["apellido"];
    $_SESSION["nombre"] = $usuario_personal["nombre"];
    $_SESSION["apellido"] = $usuario_personal["apellido"];
    $_SESSION["rol"] = $usuario_personal["rol"];
    $_SESSION["cargo"] = $usuario_personal["cargo"];
    $_SESSION["email"] = $usuario_personal["email"];
    $_SESSION["fecha_login"] = date('Y-m-d H:i:s');

    // Generar token de sesión
    $token = LoginModelo::mdlGenerarTokenSesion($usuario_personal["usuario"], "personal");
    if ($token) {
      $_SESSION["token_sesion"] = $token;
    }
  }

  /**
   * Establecer sesión para cliente
   */
  static private function ctrEstablecerSesionCliente($usuario_cliente)
  {
    $_SESSION["validarIngreso"] = "ok";
    $_SESSION["tipo_usuario"] = "cliente";
    $_SESSION["id_usuario_cliente"] = $usuario_cliente["id_usuario_cliente"];
    $_SESSION["id_cliente"] = $usuario_cliente["id_cliente"];
    $_SESSION["usuario"] = $usuario_cliente["usuario"];
    $_SESSION["nombre_completo"] = $usuario_cliente["nombre"] . " " . $usuario_cliente["apellido"];
    $_SESSION["nombre"] = $usuario_cliente["nombre"];
    $_SESSION["apellido"] = $usuario_cliente["apellido"];
    $_SESSION["cedula"] = $usuario_cliente["cedula"];
    $_SESSION["ruc"] = $usuario_cliente["ruc"];
    $_SESSION["telefono"] = $usuario_cliente["telefono"];
    $_SESSION["email"] = $usuario_cliente["email"];
    $_SESSION["direccion"] = $usuario_cliente["direccion"];
    $_SESSION["fecha_login"] = date('Y-m-d H:i:s');

    // Generar token de sesión
    $token = LoginModelo::mdlGenerarTokenSesion($usuario_cliente["usuario"], "cliente");
    if ($token) {
      $_SESSION["token_sesion"] = $token;
    }
  }

  /**
   * Cerrar sesión
   */
  static public function ctrCerrarSesion()
  {
    // Invalidar token si existe
    if (isset($_SESSION["token_sesion"])) {
      LoginModelo::mdlInvalidarToken($_SESSION["token_sesion"]);
    }

    // Limpiar todas las variables de sesión
    session_unset();
    session_destroy();

    // Regenerar ID de sesión por seguridad
    session_start();
    session_regenerate_id(true);

    echo '<script>
            window.location = "index.php?pagina=login";
        </script>';
  }

  /**
   * Verificar sesión activa
   */
  static public function ctrVerificarSesion()
  {
    // Verificar si hay sesión básica
    if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
      return false;
    }

    // Verificar token de sesión si existe
    if (isset($_SESSION["token_sesion"])) {
      $token_valido = LoginModelo::mdlValidarTokenSesion($_SESSION["token_sesion"]);
      if (!$token_valido) {
        // Token inválido o expirado
        self::ctrCerrarSesion();
        return false;
      }
    }

    // Verificar tiempo de inactividad (opcional)
    if (isset($_SESSION["ultima_actividad"])) {
      $tiempo_inactivo = time() - $_SESSION["ultima_actividad"];
      if ($tiempo_inactivo > 3600) { // 1 hora de inactividad
        self::ctrCerrarSesion();
        return false;
      }
    }

    // Actualizar última actividad
    $_SESSION["ultima_actividad"] = time();

    return true;
  }

  /**
   * Validar permisos de rol
   */
  static public function ctrValidarPermisos($permisos_requeridos = array())
  {
    if (!self::ctrVerificarSesion()) {
      return false;
    }

    if ($_SESSION["tipo_usuario"] == "personal") {
      $rol = $_SESSION["rol"];

      // Definir permisos por rol
      $permisos_roles = array(
        "administrador" => ["todas"],
        "gerente" => ["ver_reportes", "gestionar_personal", "gestionar_clientes", "gestionar_vehiculos", "gestionar_ordenes", "gestionar_presupuestos", "gestionar_facturas"],
        "empleado" => ["gestionar_clientes", "gestionar_vehiculos", "gestionar_ordenes", "crear_presupuestos", "ver_facturas"]
      );

      if (!isset($permisos_roles[$rol])) {
        return false;
      }

      $permisos_usuario = $permisos_roles[$rol];

      // Si tiene permiso "todas", puede hacer todo
      if (in_array("todas", $permisos_usuario)) {
        return true;
      }

      // Verificar permisos específicos
      foreach ($permisos_requeridos as $permiso) {
        if (!in_array($permiso, $permisos_usuario)) {
          return false;
        }
      }

      return true;
    }

    // Los clientes solo pueden acceder a sus propias funciones
    if ($_SESSION["tipo_usuario"] == "cliente") {
      $permisos_cliente = ["ver_historial", "agendar_citas", "ver_vehiculos"];

      foreach ($permisos_requeridos as $permiso) {
        if (!in_array($permiso, $permisos_cliente)) {
          return false;
        }
      }

      return true;
    }

    return false;
  }

  /**
   * Cambiar contraseña
   */
  static public function ctrCambiarContrasena()
  {
    if (isset($_POST["contrasena_actual"]) && isset($_POST["contrasena_nueva"])) {

      if (!self::ctrVerificarSesion()) {
        echo json_encode(array("status" => "error", "message" => "Sesión no válida"));
        return;
      }

      $contrasena_actual = md5($_POST["contrasena_actual"]);
      $contrasena_nueva = $_POST["contrasena_nueva"];
      $contrasena_nueva_md5 = md5($contrasena_nueva);

      // Verificar fortaleza de la nueva contraseña
      $fortaleza = LoginModelo::mdlVerificarFortalezaContrasena($contrasena_nueva);
      if ($fortaleza['nivel'] == 'debil') {
        echo json_encode(array(
          "status" => "error",
          "message" => "La nueva contraseña es muy débil. Debe tener al menos 8 caracteres, mayúsculas, minúsculas y números."
        ));
        return;
      }

      $usuario = $_SESSION["usuario"];
      $tipo_usuario = $_SESSION["tipo_usuario"];

      // Verificar contraseña actual
      if ($tipo_usuario == "personal") {
        $usuario_valido = LoginModelo::mdlValidarLoginPersonal($usuario, $contrasena_actual);
        if ($usuario_valido) {
          require_once "../modelo/modelo_usuario.php";
          $resultado = ModeloUsuario::mdlCambiarContrasenaPersonal($_SESSION["id_usuario_personal"], $contrasena_nueva_md5);
        }
      } else {
        $usuario_valido = LoginModelo::mdlValidarLoginCliente($usuario, $contrasena_actual);
        if ($usuario_valido) {
          require_once "../modelo/modelo_usuario.php";
          $resultado = ModeloUsuario::mdlCambiarContrasenaCliente($_SESSION["id_usuario_cliente"], $contrasena_nueva_md5);
        }
      }

      if (!$usuario_valido) {
        echo json_encode(array("status" => "error", "message" => "La contraseña actual es incorrecta"));
        return;
      }

      if ($resultado == "ok") {
        echo json_encode(array("status" => "success", "message" => "Contraseña cambiada correctamente"));
      } else {
        echo json_encode(array("status" => "error", "message" => "Error al cambiar la contraseña"));
      }
    }
  }

  /**
   * Obtener información del usuario logueado
   */
  static public function ctrObtenerInfoUsuario()
  {
    if (!self::ctrVerificarSesion()) {
      return false;
    }

    $info = array(
      "tipo_usuario" => $_SESSION["tipo_usuario"],
      "usuario" => $_SESSION["usuario"],
      "nombre_completo" => $_SESSION["nombre_completo"],
      "email" => $_SESSION["email"] ?? null,
      "fecha_login" => $_SESSION["fecha_login"] ?? null,
      "ultima_actividad" => date('Y-m-d H:i:s', $_SESSION["ultima_actividad"] ?? time())
    );

    if ($_SESSION["tipo_usuario"] == "personal") {
      $info["rol"] = $_SESSION["rol"];
      $info["cargo"] = $_SESSION["cargo"];
      $info["id_personal"] = $_SESSION["id_personal"];
    } else {
      $info["id_cliente"] = $_SESSION["id_cliente"];
      $info["cedula"] = $_SESSION["cedula"];
      $info["telefono"] = $_SESSION["telefono"];
    }

    return $info;
  }

  /**
   * Obtener estadísticas de login para dashboard
   */
  static public function ctrEstadisticasLogin()
  {
    try {
      if (!self::ctrValidarPermisos(["ver_reportes"])) {
        return array();
      }

      return LoginModelo::mdlEstadisticasLogin();
    } catch (Exception $e) {
      error_log("Error en ctrEstadisticasLogin: " . $e->getMessage());
      return array();
    }
  }

  /**
   * Obtener historial de accesos
   */
  static public function ctrHistorialAccesos($usuario = null, $limite = 10)
  {
    try {
      if (!self::ctrValidarPermisos(["ver_reportes"])) {
        return array();
      }

      if (!$usuario) {
        $usuario = $_SESSION["usuario"];
      }

      return LoginModelo::mdlHistorialAccesos($usuario, $limite);
    } catch (Exception $e) {
      error_log("Error en ctrHistorialAccesos: " . $e->getMessage());
      return array();
    }
  }

  /**
   * Forzar cambio de contraseña en primer login
   */
  static public function ctrRequiereCambioContrasena()
  {
    if (!self::ctrVerificarSesion()) {
      return false;
    }

    // Verificar si es la primera vez que accede (esto se podría implementar con un campo en la BD)
    // Por ahora, verificar si la contraseña es muy simple o temporal
    return false; // Implementar lógica según necesidades
  }

  /**
   * Generar código de recuperación de contraseña
   */
  static public function ctrGenerarCodigoRecuperacion()
  {
    if (isset($_POST["email_recuperacion"])) {
      $email = $_POST["email_recuperacion"];

      // Verificar si el email existe en el sistema
      // Generar código temporal
      // Enviar por email (si está configurado)

      echo '<script>
                Swal.fire({
                    icon: "info",
                    title: "Código enviado",
                    text: "Si el email existe en nuestro sistema, recibirás un código de recuperación en breve.",
                    confirmButtonColor: "#dc2626"
                });
            </script>';
    }
  }

  /**
   * Limpiar sesiones y logs antiguos (tarea de mantenimiento)
   */
  static public function ctrLimpiezaMantenimiento()
  {
    try {
      if (!self::ctrValidarPermisos(["todas"])) {
        return array("status" => "error", "message" => "Sin permisos");
      }

      $logs_eliminados = LoginModelo::mdlLimpiarLogsAntiguos(90);

      return array(
        "status" => "success",
        "message" => "Mantenimiento completado",
        "logs_eliminados" => $logs_eliminados
      );
    } catch (Exception $e) {
      error_log("Error en ctrLimpiezaMantenimiento: " . $e->getMessage());
      return array("status" => "error", "message" => "Error en mantenimiento");
    }
  }

  /**
   * Validar acceso a página específica
   */
  static public function ctrValidarAccesoPagina($pagina)
  {
    if (!self::ctrVerificarSesion()) {
      echo '<script>window.location = "index.php?pagina=login";</script>';
      return false;
    }

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
      "tabla/orden_trabajo",
      "nuevo/orden_trabajo",
      "editar/orden_trabajo"
    ];

    $paginas_solo_cliente = [
      "agendamiento",
      "tabla/historial"
    ];

    if ($_SESSION["tipo_usuario"] == "cliente" && in_array($pagina, $paginas_solo_personal)) {
      echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Acceso Denegado",
                    text: "No tienes permisos para acceder a esta página"
                }).then(function() {
                    window.location = "index.php?pagina=agendamiento";
                });
            </script>';
      return false;
    }

    if ($_SESSION["tipo_usuario"] == "personal" && in_array($pagina, $paginas_solo_cliente)) {
      // El personal puede acceder a todas las páginas de cliente
      return true;
    }

    return true;
  }
}
?>