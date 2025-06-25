<?php

require_once "conexion.php";

class ModeloLogin
{
    // Buscar usuario para login
    static public function buscarUsuario($tabla, $item, $valor)
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :valor");
            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarUsuario: " . $e->getMessage());
            return false;
        }
    }

    // Obtener usuario por ID
    static public function obtenerUsuarioPorId($id, $tabla, $campo_id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $campo_id = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerUsuarioPorId: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar última fecha de acceso
    static public function actualizarUltimoAcceso($id_usuario, $tabla)
    {
        try {
            $campo_id = ($tabla == "usuariocliente") ? "id_usuario_cliente" : "id_usuario_personal";
            
            $stmt = Conexion::conectar()->prepare("
                UPDATE $tabla 
                SET ultimo_acceso = NOW() 
                WHERE $campo_id = :id
            ");
            $stmt->bindParam(":id", $id_usuario, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en actualizarUltimoAcceso: " . $e->getMessage());
            return "error";
        }
    }

    // Cambiar contraseña
    static public function cambiarContrasena($id_usuario, $nueva_contrasena, $tabla, $campo_id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE $tabla 
                SET contrasena = :contrasena 
                WHERE $campo_id = :id
            ");
            $stmt->bindParam(":contrasena", $nueva_contrasena, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id_usuario, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en cambiarContrasena: " . $e->getMessage());
            return "error";
        }
    }

    // Verificar si usuario existe
    static public function verificarUsuarioExiste($usuario, $tabla)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM $tabla WHERE usuario = :usuario
            ");
            $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en verificarUsuarioExiste: " . $e->getMessage());
            return false;
        }
    }

    // Obtener usuarios activos (último acceso en los últimos 30 días)
    static public function obtenerUsuariosActivos($tabla)
    {
        try {
            $campo_id = ($tabla == "usuariocliente") ? "id_usuario_cliente" : "id_usuario_personal";
            $tabla_relacion = ($tabla == "usuariocliente") ? "cliente" : "personal";
            $campo_fk = ($tabla == "usuariocliente") ? "id_cliente" : "id_personal";
            
            $stmt = Conexion::conectar()->prepare("
                SELECT u.*, 
                       CONCAT(r.nombre, ' ', r.apellido) as nombre_completo,
                       u.ultimo_acceso
                FROM $tabla u
                INNER JOIN $tabla_relacion r ON u.$campo_fk = r.$campo_fk
                WHERE u.ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY u.ultimo_acceso DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerUsuariosActivos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de login
    static public function obtenerEstadisticasLogin()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM usuariocliente) as total_clientes,
                    (SELECT COUNT(*) FROM usuariopersonal) as total_personal,
                    (SELECT COUNT(*) FROM usuariocliente WHERE ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as clientes_activos_semana,
                    (SELECT COUNT(*) FROM usuariopersonal WHERE ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as personal_activo_semana,
                    (SELECT COUNT(*) FROM usuariocliente WHERE DATE(ultimo_acceso) = CURDATE()) as clientes_hoy,
                    (SELECT COUNT(*) FROM usuariopersonal WHERE DATE(ultimo_acceso) = CURDATE()) as personal_hoy
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasLogin: " . $e->getMessage());
            return array();
        }
    }

    // Registrar intento de login fallido
    static public function registrarIntentoFallido($usuario, $ip, $user_agent)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO intentos_login_fallidos (usuario, ip, user_agent, fecha_intento)
                VALUES (:usuario, :ip, :user_agent, NOW())
            ");
            $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
            $stmt->bindParam(":user_agent", $user_agent, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en registrarIntentoFallido: " . $e->getMessage());
            return "error";
        }
    }

    // Verificar si IP está bloqueada por múltiples intentos fallidos
    static public function verificarIPBloqueada($ip, $limite_intentos = 5, $tiempo_bloqueo = 15)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as intentos
                FROM intentos_login_fallidos 
                WHERE ip = :ip 
                AND fecha_intento >= DATE_SUB(NOW(), INTERVAL :tiempo MINUTE)
            ");
            $stmt->bindParam(":ip", $ip, PDO::PARAM_STR);
            $stmt->bindParam(":tiempo", $tiempo_bloqueo, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['intentos'] >= $limite_intentos;
        } catch (PDOException $e) {
            error_log("Error en verificarIPBloqueada: " . $e->getMessage());
            return false;
        }
    }

    // Limpiar intentos fallidos antiguos
    static public function limpiarIntentosFallidos($dias_antiguedad = 7)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                DELETE FROM intentos_login_fallidos 
                WHERE fecha_intento < DATE_SUB(NOW(), INTERVAL :dias DAY)
            ");
            $stmt->bindParam(":dias", $dias_antiguedad, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en limpiarIntentosFallidos: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener información completa del usuario logueado
    static public function obtenerPerfilCompleto($id_usuario, $tipo)
    {
        try {
            if ($tipo == "cliente") {
                $stmt = Conexion::conectar()->prepare("
                    SELECT uc.*, c.nombre, c.apellido, c.telefono, c.email, c.direccion,
                           COUNT(v.id_vehiculo) as total_vehiculos
                    FROM usuariocliente uc
                    INNER JOIN cliente c ON uc.id_cliente = c.id_cliente
                    LEFT JOIN vehiculo v ON c.id_cliente = v.id_cliente
                    WHERE uc.id_usuario_cliente = :id
                    GROUP BY uc.id_usuario_cliente
                ");
            } else {
                $stmt = Conexion::conectar()->prepare("
                    SELECT up.*, p.nombre, p.apellido, p.telefono, p.email, p.cargo
                    FROM usuariopersonal up
                    INNER JOIN personal p ON up.id_personal = p.id_personal
                    WHERE up.id_usuario_personal = :id
                ");
            }
            
            $stmt->bindParam(":id", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPerfilCompleto: " . $e->getMessage());
            return false;
        }
    }
}
?>