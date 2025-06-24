<?php
require_once "conexion.php";

class LoginModelo
{

  /**
   * Validar login de personal
   */
  static public function mdlValidarLoginPersonal($usuario, $contrasena)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                SELECT 
                    up.id_usuario_personal,
                    up.usuario,
                    up.rol,
                    up.estado as estado_usuario,
                    p.id_personal,
                    p.nombre,
                    p.apellido,
                    p.cargo,
                    p.email,
                    p.estado as estado_personal
                FROM usuariopersonal up
                INNER JOIN personal p ON up.id_personal = p.id_personal
                WHERE up.usuario = :usuario 
                AND up.contrasena = :contrasena 
                AND up.estado = 'activo'
                AND p.estado = 'activo'
            ");

      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      $stmt->bindParam(":contrasena", $contrasena, PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      error_log("Error en mdlValidarLoginPersonal: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Validar login de cliente
   */
  static public function mdlValidarLoginCliente($usuario, $contrasena)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                SELECT 
                    uc.id_usuario_cliente,
                    uc.usuario,
                    uc.estado as estado_usuario,
                    c.id_cliente,
                    c.nombre,
                    c.apellido,
                    c.cedula,
                    c.ruc,
                    c.telefono,
                    c.email,
                    c.direccion,
                    c.estado as estado_cliente
                FROM usuariocliente uc
                INNER JOIN cliente c ON uc.id_cliente = c.id_cliente
                WHERE uc.usuario = :usuario 
                AND uc.contrasena = :contrasena 
                AND uc.estado = 'activo'
                AND c.estado = 'activo'
            ");

      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      $stmt->bindParam(":contrasena", $contrasena, PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      error_log("Error en mdlValidarLoginCliente: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Verificar si el usuario existe (cualquier tipo)
   */
  static public function mdlVerificarUsuarioExiste($usuario)
  {
    try {
      // Verificar en personal
      $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM usuariopersonal WHERE usuario = :usuario
            ");
      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      $stmt->execute();
      $personal = $stmt->fetch(PDO::FETCH_ASSOC);

      // Verificar en clientes
      $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM usuariocliente WHERE usuario = :usuario
            ");
      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      $stmt->execute();
      $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

      return ($personal['total'] > 0 || $cliente['total'] > 0);
    } catch (Exception $e) {
      error_log("Error en mdlVerificarUsuarioExiste: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Registrar intento de login
   */
  static public function mdlRegistrarIntentoLogin($datos)
  {
    try {
      // Crear tabla de logs si no existe
      $conexion = Conexion::conectar();
      $conexion->exec("
                CREATE TABLE IF NOT EXISTS log_login (
                    id_log INT AUTO_INCREMENT PRIMARY KEY,
                    usuario VARCHAR(50) NOT NULL,
                    tipo_usuario ENUM('personal', 'cliente') NOT NULL,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    exitoso TINYINT(1) DEFAULT 0,
                    motivo_fallo VARCHAR(100),
                    fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_usuario (usuario),
                    INDEX idx_fecha (fecha_intento)
                )
            ");

      $stmt = $conexion->prepare("
                INSERT INTO log_login 
                (usuario, tipo_usuario, ip_address, user_agent, exitoso, motivo_fallo)
                VALUES (:usuario, :tipo_usuario, :ip_address, :user_agent, :exitoso, :motivo_fallo)
            ");

      $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
      $stmt->bindParam(":tipo_usuario", $datos["tipo_usuario"], PDO::PARAM_STR);
      $stmt->bindParam(":ip_address", $datos["ip_address"], PDO::PARAM_STR);
      $stmt->bindParam(":user_agent", $datos["user_agent"], PDO::PARAM_STR);
      $stmt->bindParam(":exitoso", $datos["exitoso"], PDO::PARAM_INT);
      $stmt->bindParam(":motivo_fallo", $datos["motivo_fallo"], PDO::PARAM_STR);

      return $stmt->execute();
    } catch (Exception $e) {
      error_log("Error en mdlRegistrarIntentoLogin: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtener intentos de login recientes
   */
  static public function mdlObtenerIntentosRecientes($usuario, $minutos = 30)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as intentos
                FROM log_login 
                WHERE usuario = :usuario 
                AND exitoso = 0 
                AND fecha_intento >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)
            ");
      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      $stmt->bindParam(":minutos", $minutos, PDO::PARAM_INT);
      $stmt->execute();

      $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
      return $resultado ? intval($resultado['intentos']) : 0;
    } catch (Exception $e) {
      error_log("Error en mdlObtenerIntentosRecientes: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Verificar si usuario está bloqueado por intentos
   */
  static public function mdlUsuarioBloqueado($usuario, $max_intentos = 5)
  {
    try {
      $intentos = self::mdlObtenerIntentosRecientes($usuario, 30);
      return $intentos >= $max_intentos;
    } catch (Exception $e) {
      error_log("Error en mdlUsuarioBloqueado: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Limpiar intentos exitosos (resetear contador)
   */
  static public function mdlLimpiarIntentosFallidos($usuario)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                DELETE FROM log_login 
                WHERE usuario = :usuario 
                AND exitoso = 0 
                AND fecha_intento >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ");
      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (Exception $e) {
      error_log("Error en mdlLimpiarIntentosFallidos: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualizar último acceso del usuario personal
   */
  static public function mdlActualizarUltimoAccesoPersonal($id_usuario)
  {
    try {
      // Crear tabla de últimos accesos si no existe
      $conexion = Conexion::conectar();
      $conexion->exec("
                CREATE TABLE IF NOT EXISTS ultimo_acceso (
                    id_acceso INT AUTO_INCREMENT PRIMARY KEY,
                    id_usuario_personal INT,
                    id_usuario_cliente INT,
                    ultimo_acceso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    FOREIGN KEY (id_usuario_personal) REFERENCES usuariopersonal(id_usuario_personal) ON DELETE CASCADE,
                    FOREIGN KEY (id_usuario_cliente) REFERENCES usuariocliente(id_usuario_cliente) ON DELETE CASCADE,
                    INDEX idx_personal (id_usuario_personal),
                    INDEX idx_cliente (id_usuario_cliente)
                )
            ");

      // Actualizar o insertar último acceso
      $stmt = $conexion->prepare("
                INSERT INTO ultimo_acceso 
                (id_usuario_personal, ultimo_acceso, ip_address, user_agent)
                VALUES (:id_usuario, NOW(), :ip, :user_agent)
                ON DUPLICATE KEY UPDATE 
                ultimo_acceso = NOW(),
                ip_address = :ip2,
                user_agent = :user_agent2
            ");

      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

      $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
      $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
      $stmt->bindParam(":user_agent", $user_agent, PDO::PARAM_STR);
      $stmt->bindParam(":ip2", $ip, PDO::PARAM_STR);
      $stmt->bindParam(":user_agent2", $user_agent, PDO::PARAM_STR);

      return $stmt->execute();
    } catch (Exception $e) {
      error_log("Error en mdlActualizarUltimoAccesoPersonal: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualizar último acceso del usuario cliente
   */
  static public function mdlActualizarUltimoAccesoCliente($id_usuario)
  {
    try {
      // Similar al método anterior pero para clientes
      $conexion = Conexion::conectar();

      $stmt = $conexion->prepare("
                INSERT INTO ultimo_acceso 
                (id_usuario_cliente, ultimo_acceso, ip_address, user_agent)
                VALUES (:id_usuario, NOW(), :ip, :user_agent)
                ON DUPLICATE KEY UPDATE 
                ultimo_acceso = NOW(),
                ip_address = :ip2,
                user_agent = :user_agent2
            ");

      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

      $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
      $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
      $stmt->bindParam(":user_agent", $user_agent, PDO::PARAM_STR);
      $stmt->bindParam(":ip2", $ip, PDO::PARAM_STR);
      $stmt->bindParam(":user_agent2", $user_agent, PDO::PARAM_STR);

      return $stmt->execute();
    } catch (Exception $e) {
      error_log("Error en mdlActualizarUltimoAccesoCliente: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtener estadísticas de login
   */
  static public function mdlEstadisticasLogin($periodo_dias = 30)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_intentos,
                    SUM(CASE WHEN exitoso = 1 THEN 1 ELSE 0 END) as exitosos,
                    SUM(CASE WHEN exitoso = 0 THEN 1 ELSE 0 END) as fallidos,
                    COUNT(DISTINCT usuario) as usuarios_unicos,
                    COUNT(DISTINCT ip_address) as ips_unicas
                FROM log_login 
                WHERE fecha_intento >= DATE_SUB(NOW(), INTERVAL :dias DAY)
            ");
      $stmt->bindParam(":dias", $periodo_dias, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      error_log("Error en mdlEstadisticasLogin: " . $e->getMessage());
      return array();
    }
  }

  /**
   * Obtener historial de accesos de un usuario
   */
  static public function mdlHistorialAccesos($usuario, $limite = 10)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                SELECT 
                    fecha_intento,
                    ip_address,
                    exitoso,
                    motivo_fallo
                FROM log_login 
                WHERE usuario = :usuario 
                ORDER BY fecha_intento DESC 
                LIMIT :limite
            ");
      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      error_log("Error en mdlHistorialAccesos: " . $e->getMessage());
      return array();
    }
  }

  /**
   * Limpiar logs antiguos
   */
  static public function mdlLimpiarLogsAntiguos($dias = 90)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                DELETE FROM log_login 
                WHERE fecha_intento < DATE_SUB(NOW(), INTERVAL :dias DAY)
            ");
      $stmt->bindParam(":dias", $dias, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->rowCount();
    } catch (Exception $e) {
      error_log("Error en mdlLimpiarLogsAntiguos: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Verificar fortaleza de contraseña
   */
  static public function mdlVerificarFortalezaContrasena($contrasena)
  {
    $puntuacion = 0;
    $requisitos = array();

    // Longitud mínima
    if (strlen($contrasena) >= 8) {
      $puntuacion += 2;
      $requisitos['longitud'] = true;
    } else {
      $requisitos['longitud'] = false;
    }

    // Contiene mayúsculas
    if (preg_match('/[A-Z]/', $contrasena)) {
      $puntuacion += 1;
      $requisitos['mayusculas'] = true;
    } else {
      $requisitos['mayusculas'] = false;
    }

    // Contiene minúsculas
    if (preg_match('/[a-z]/', $contrasena)) {
      $puntuacion += 1;
      $requisitos['minusculas'] = true;
    } else {
      $requisitos['minusculas'] = false;
    }

    // Contiene números
    if (preg_match('/[0-9]/', $contrasena)) {
      $puntuacion += 1;
      $requisitos['numeros'] = true;
    } else {
      $requisitos['numeros'] = false;
    }

    // Contiene caracteres especiales
    if (preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
      $puntuacion += 1;
      $requisitos['especiales'] = true;
    } else {
      $requisitos['especiales'] = false;
    }

    // Determinar nivel
    if ($puntuacion >= 5) {
      $nivel = 'muy_fuerte';
    } elseif ($puntuacion >= 4) {
      $nivel = 'fuerte';
    } elseif ($puntuacion >= 3) {
      $nivel = 'moderada';
    } else {
      $nivel = 'debil';
    }

    return array(
      'puntuacion' => $puntuacion,
      'nivel' => $nivel,
      'requisitos' => $requisitos
    );
  }

  /**
   * Generar token de sesión seguro
   */
  static public function mdlGenerarTokenSesion($usuario, $tipo_usuario)
  {
    try {
      $token = bin2hex(random_bytes(32));
      $expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));

      // Crear tabla de tokens si no existe
      $conexion = Conexion::conectar();
      $conexion->exec("
                CREATE TABLE IF NOT EXISTS tokens_sesion (
                    id_token INT AUTO_INCREMENT PRIMARY KEY,
                    token VARCHAR(64) UNIQUE NOT NULL,
                    usuario VARCHAR(50) NOT NULL,
                    tipo_usuario ENUM('personal', 'cliente') NOT NULL,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    fecha_expiracion TIMESTAMP NOT NULL,
                    activo TINYINT(1) DEFAULT 1,
                    INDEX idx_token (token),
                    INDEX idx_usuario (usuario)
                )
            ");

      // Insertar nuevo token
      $stmt = $conexion->prepare("
                INSERT INTO tokens_sesion (token, usuario, tipo_usuario, fecha_expiracion)
                VALUES (:token, :usuario, :tipo_usuario, :expiracion)
            ");

      $stmt->bindParam(":token", $token, PDO::PARAM_STR);
      $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
      $stmt->bindParam(":tipo_usuario", $tipo_usuario, PDO::PARAM_STR);
      $stmt->bindParam(":expiracion", $expiracion, PDO::PARAM_STR);

      if ($stmt->execute()) {
        return $token;
      }

      return false;
    } catch (Exception $e) {
      error_log("Error en mdlGenerarTokenSesion: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Validar token de sesión
   */
  static public function mdlValidarTokenSesion($token)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                SELECT usuario, tipo_usuario, fecha_expiracion
                FROM tokens_sesion 
                WHERE token = :token 
                AND activo = 1 
                AND fecha_expiracion > NOW()
            ");
      $stmt->bindParam(":token", $token, PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      error_log("Error en mdlValidarTokenSesion: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Invalidar token de sesión
   */
  static public function mdlInvalidarToken($token)
  {
    try {
      $stmt = Conexion::conectar()->prepare("
                UPDATE tokens_sesion 
                SET activo = 0 
                WHERE token = :token
            ");
      $stmt->bindParam(":token", $token, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (Exception $e) {
      error_log("Error en mdlInvalidarToken: " . $e->getMessage());
      return false;
    }
  }
}
?>