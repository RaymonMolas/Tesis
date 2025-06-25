<?php

require_once "conexion.php";

class ModeloUsuario
{
    // Buscar usuarios por tipo (cliente o personal)
    static public function buscarUsuarios($tipo)
    {
        try {
            if ($tipo == "cliente") {
                $stmt = Conexion::conectar()->prepare("
                    SELECT uc.*, 
                           CONCAT(c.nombre, ' ', c.apellido) as nombre,
                           c.telefono,
                           c.email
                    FROM usuariocliente uc
                    INNER JOIN cliente c ON uc.id_cliente = c.id_cliente
                    ORDER BY uc.fecha_creacion DESC
                ");
            } else {
                $stmt = Conexion::conectar()->prepare("
                    SELECT up.*, 
                           CONCAT(p.nombre, ' ', p.apellido) as nombre,
                           p.telefono,
                           p.email
                    FROM usuariopersonal up
                    INNER JOIN personal p ON up.id_personal = p.id_personal
                    ORDER BY up.fecha_creacion DESC
                ");
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarUsuarios: " . $e->getMessage());
            return array();
        }
    }

    // Guardar usuario en la tabla correspondiente
    static public function guardarUsuario($tabla, $datos)
    {
        try {
            if ($tabla == "usuariocliente") {
                $stmt = Conexion::conectar()->prepare("
                    INSERT INTO usuariocliente (id_cliente, usuario, contrasena, estado, fecha_creacion)
                    VALUES (:id_cliente, :usuario, :contrasena, 'activo', NOW())
                ");
                $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            } else {
                $stmt = Conexion::conectar()->prepare("
                    INSERT INTO usuariopersonal (id_personal, usuario, contrasena, estado, fecha_creacion)
                    VALUES (:id_personal, :usuario, :contrasena, 'activo', NOW())
                ");
                $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            }

            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":contrasena", $datos["contrasena"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en guardarUsuario: " . $e->getMessage());
            return "error";
        }
    }

    // Buscar usuario por ID
    static public function buscarUsuarioPorId($tabla, $campo, $id)
    {
        try {
            if ($tabla == "usuariocliente") {
                $stmt = Conexion::conectar()->prepare("
                    SELECT uc.*, 
                           CONCAT(c.nombre, ' ', c.apellido) as nombre_completo
                    FROM usuariocliente uc
                    INNER JOIN cliente c ON uc.id_cliente = c.id_cliente
                    WHERE uc.$campo = :id
                ");
            } else {
                $stmt = Conexion::conectar()->prepare("
                    SELECT up.*, 
                           CONCAT(p.nombre, ' ', p.apellido) as nombre_completo
                    FROM usuariopersonal up
                    INNER JOIN personal p ON up.id_personal = p.id_personal
                    WHERE up.$campo = :id
                ");
            }
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarUsuarioPorId: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar usuario
    static public function actualizarUsuario($tabla, $campo, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE $tabla 
                SET usuario = :usuario,
                    contrasena = :contrasena,
                    fecha_actualizacion = NOW()
                WHERE $campo = :id
            ");

            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":contrasena", $datos["contrasena"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en actualizarUsuario: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar usuario
    static public function eliminarUsuario($tabla, $campo, $valor)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE $campo = :valor");
            $stmt->bindParam(":valor", $valor, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en eliminarUsuario: " . $e->getMessage());
            return "error";
        }
    }

    // Verificar si ya existe un usuario con ese nombre
    static public function verificarUsuarioExiste($usuario, $excluir_id = null, $tabla = null)
    {
        try {
            // Si no se especifica tabla, buscar en ambas
            if ($tabla) {
                $campo_id = ($tabla == "usuariocliente") ? "id_usuario_cliente" : "id_usuario_personal";
                $sql = "SELECT COUNT(*) as total FROM $tabla WHERE usuario = :usuario";
                if ($excluir_id) {
                    $sql .= " AND $campo_id != :excluir_id";
                }
                
                $stmt = Conexion::conectar()->prepare($sql);
                $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
                if ($excluir_id) {
                    $stmt->bindParam(":excluir_id", $excluir_id, PDO::PARAM_INT);
                }
                $stmt->execute();
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                return $resultado['total'] > 0;
            } else {
                // Buscar en ambas tablas
                $stmt1 = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM usuariocliente WHERE usuario = :usuario");
                $stmt1->bindParam(":usuario", $usuario, PDO::PARAM_STR);
                $stmt1->execute();
                $resultado1 = $stmt1->fetch(PDO::FETCH_ASSOC);

                $stmt2 = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM usuariopersonal WHERE usuario = :usuario");
                $stmt2->bindParam(":usuario", $usuario, PDO::PARAM_STR);
                $stmt2->execute();
                $resultado2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                return ($resultado1['total'] > 0) || ($resultado2['total'] > 0);
            }
        } catch (PDOException $e) {
            error_log("Error en verificarUsuarioExiste: " . $e->getMessage());
            return false;
        }
    }

    // Buscar usuario para login
    static public function buscarUsuario($tabla, $campo, $valor)
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $campo = :valor");
            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarUsuario: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar último acceso
    static public function actualizarUltimoAcceso($tabla, $campo, $id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE $tabla 
                SET ultimo_acceso = NOW()
                WHERE $campo = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en actualizarUltimoAcceso: " . $e->getMessage());
            return "error";
        }
    }

    // Cambiar contraseña
    static public function cambiarContrasena($tabla, $campo, $id, $nueva_contrasena)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE $tabla 
                SET contrasena = :contrasena,
                    fecha_actualizacion = NOW()
                WHERE $campo = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":contrasena", $nueva_contrasena, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en cambiarContrasena: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener estadísticas de usuarios
    static public function obtenerEstadisticas()
    {
        try {
            // Estadísticas de usuarios cliente
            $stmt1 = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_usuarios_cliente,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as usuarios_cliente_activos,
                    COUNT(CASE WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as accesos_cliente_semana
                FROM usuariocliente
            ");
            $stmt1->execute();
            $estadisticas_cliente = $stmt1->fetch(PDO::FETCH_ASSOC);

            // Estadísticas de usuarios personal
            $stmt2 = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_usuarios_personal,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as usuarios_personal_activos,
                    COUNT(CASE WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as accesos_personal_semana
                FROM usuariopersonal
            ");
            $stmt2->execute();
            $estadisticas_personal = $stmt2->fetch(PDO::FETCH_ASSOC);

            // Combinar estadísticas
            return array(
                'total_usuarios' => $estadisticas_cliente['total_usuarios_cliente'] + $estadisticas_personal['total_usuarios_personal'],
                'usuarios_activos' => $estadisticas_cliente['usuarios_cliente_activos'] + $estadisticas_personal['usuarios_personal_activos'],
                'accesos_semana' => $estadisticas_cliente['accesos_cliente_semana'] + $estadisticas_personal['accesos_personal_semana'],
                'usuarios_cliente' => $estadisticas_cliente['total_usuarios_cliente'],
                'usuarios_personal' => $estadisticas_personal['total_usuarios_personal']
            );
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si cliente ya tiene usuario
    static public function clienteTieneUsuario($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM usuariocliente WHERE id_cliente = :id_cliente
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en clienteTieneUsuario: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si personal ya tiene usuario
    static public function personalTieneUsuario($id_personal)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM usuariopersonal WHERE id_personal = :id_personal
            ");
            $stmt->bindParam(":id_personal", $id_personal, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en personalTieneUsuario: " . $e->getMessage());
            return false;
        }
    }

    // Bloquear usuario por intentos fallidos
    static public function bloquearUsuario($tabla, $usuario)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE $tabla 
                SET estado = 'bloqueado',
                    fecha_bloqueo = NOW(),
                    intentos_fallidos = intentos_fallidos + 1
                WHERE usuario = :usuario
            ");
            $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en bloquearUsuario: " . $e->getMessage());
            return "error";
        }
    }

    // Desbloquear usuario
    static public function desbloquearUsuario($tabla, $campo, $id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE $tabla 
                SET estado = 'activo',
                    intentos_fallidos = 0,
                    fecha_bloqueo = NULL
                WHERE $campo = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en desbloquearUsuario: " . $e->getMessage());
            return "error";
        }
    }
}
?>