<?php
require_once "conexion.php";

class ModeloUsuario
{

    /**
     * Crear usuario de personal
     */
    static public function mdlCrearUsuarioPersonal($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO usuariopersonal (id_personal, usuario, contrasena, rol, estado)
                VALUES (:id_personal, :usuario, :contrasena, :rol, :estado)
            ");

            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":contrasena", $datos["contrasena"], PDO::PARAM_STR);
            $stmt->bindParam(":rol", $datos["rol"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCrearUsuarioPersonal: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'usuario') !== false) {
                    return "usuario_duplicado";
                }
            }
            return "error";
        }
    }

    /**
     * Crear usuario de cliente
     */
    static public function mdlCrearUsuarioCliente($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO usuariocliente (id_cliente, usuario, contrasena, estado)
                VALUES (:id_cliente, :usuario, :contrasena, :estado)
            ");

            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":contrasena", $datos["contrasena"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCrearUsuarioCliente: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'usuario') !== false) {
                    return "usuario_duplicado";
                }
            }
            return "error";
        }
    }

    /**
     * Listar usuarios de personal
     */
    static public function mdlListarUsuariosPersonal()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    up.*,
                    CONCAT(p.nombre, ' ', p.apellido) AS nombre_completo,
                    p.cargo,
                    p.cedula,
                    p.telefono,
                    p.email
                FROM usuariopersonal up
                INNER JOIN personal p ON up.id_personal = p.id_personal
                ORDER BY p.nombre, p.apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlListarUsuariosPersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Listar usuarios de clientes
     */
    static public function mdlListarUsuariosCliente()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    uc.*,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_completo,
                    c.cedula,
                    c.telefono,
                    c.email
                FROM usuariocliente uc
                INNER JOIN cliente c ON uc.id_cliente = c.id_cliente
                ORDER BY c.nombre, c.apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlListarUsuariosCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener usuario de personal por ID
     */
    static public function mdlObtenerUsuarioPersonal($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    up.*,
                    CONCAT(p.nombre, ' ', p.apellido) AS nombre_completo,
                    p.cargo
                FROM usuariopersonal up
                INNER JOIN personal p ON up.id_personal = p.id_personal
                WHERE up.id_usuario_personal = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerUsuarioPersonal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario de cliente por ID
     */
    static public function mdlObtenerUsuarioCliente($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    uc.*,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_completo
                FROM usuariocliente uc
                INNER JOIN cliente c ON uc.id_cliente = c.id_cliente
                WHERE uc.id_usuario_cliente = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerUsuarioCliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario de personal
     */
    static public function mdlActualizarUsuarioPersonal($id, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE usuariopersonal SET
                    usuario = :usuario,
                    rol = :rol,
                    estado = :estado
                WHERE id_usuario_personal = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":rol", $datos["rol"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarUsuarioPersonal: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'usuario') !== false) {
                    return "usuario_duplicado";
                }
            }
            return "error";
        }
    }

    /**
     * Actualizar usuario de cliente
     */
    static public function mdlActualizarUsuarioCliente($id, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE usuariocliente SET
                    usuario = :usuario,
                    estado = :estado
                WHERE id_usuario_cliente = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarUsuarioCliente: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'usuario') !== false) {
                    return "usuario_duplicado";
                }
            }
            return "error";
        }
    }

    /**
     * Cambiar contraseña de usuario personal
     */
    static public function mdlCambiarContrasenaPersonal($id, $nueva_contrasena)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE usuariopersonal SET
                    contrasena = :contrasena
                WHERE id_usuario_personal = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":contrasena", $nueva_contrasena, PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCambiarContrasenaPersonal: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Cambiar contraseña de usuario cliente
     */
    static public function mdlCambiarContrasenaCliente($id, $nueva_contrasena)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE usuariocliente SET
                    contrasena = :contrasena
                WHERE id_usuario_cliente = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":contrasena", $nueva_contrasena, PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCambiarContrasenaCliente: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar usuario de personal
     */
    static public function mdlEliminarUsuarioPersonal($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM usuariopersonal WHERE id_usuario_personal = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarUsuarioPersonal: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar usuario de cliente
     */
    static public function mdlEliminarUsuarioCliente($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM usuariocliente WHERE id_usuario_cliente = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarUsuarioCliente: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Verificar si el nombre de usuario existe
     */
    static public function mdlVerificarUsuario($usuario, $tipo = 'personal', $id_excluir = null)
    {
        try {
            if ($tipo === 'personal') {
                $sql = "SELECT COUNT(*) FROM usuariopersonal WHERE usuario = :usuario";
                if ($id_excluir) {
                    $sql .= " AND id_usuario_personal != :id_excluir";
                }
            } else {
                $sql = "SELECT COUNT(*) FROM usuariocliente WHERE usuario = :usuario";
                if ($id_excluir) {
                    $sql .= " AND id_usuario_cliente != :id_excluir";
                }
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
            if ($id_excluir) {
                $stmt->bindParam(":id_excluir", $id_excluir, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en mdlVerificarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado de usuario personal
     */
    static public function mdlCambiarEstadoPersonal($id, $estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE usuariopersonal SET 
                    estado = :estado 
                WHERE id_usuario_personal = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCambiarEstadoPersonal: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Cambiar estado de usuario cliente
     */
    static public function mdlCambiarEstadoCliente($id, $estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE usuariocliente SET 
                    estado = :estado 
                WHERE id_usuario_cliente = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCambiarEstadoCliente: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener estadísticas de usuarios
     */
    static public function mdlEstadisticasUsuarios()
    {
        try {
            // Usuarios de personal
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_usuarios_personal,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as personal_activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as personal_inactivos,
                    SUM(CASE WHEN estado = 'bloqueado' THEN 1 ELSE 0 END) as personal_bloqueados,
                    SUM(CASE WHEN rol = 'administrador' THEN 1 ELSE 0 END) as administradores,
                    SUM(CASE WHEN rol = 'gerente' THEN 1 ELSE 0 END) as gerentes,
                    SUM(CASE WHEN rol = 'empleado' THEN 1 ELSE 0 END) as empleados
                FROM usuariopersonal
            ");
            $stmt->execute();
            $stats_personal = $stmt->fetch(PDO::FETCH_ASSOC);

            // Usuarios de clientes
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_usuarios_cliente,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as clientes_activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as clientes_inactivos,
                    SUM(CASE WHEN estado = 'bloqueado' THEN 1 ELSE 0 END) as clientes_bloqueados
                FROM usuariocliente
            ");
            $stmt->execute();
            $stats_cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            return array_merge($stats_personal, $stats_cliente);
        } catch (Exception $e) {
            error_log("Error en mdlEstadisticasUsuarios: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener personal sin usuario asignado
     */
    static public function mdlPersonalSinUsuario()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.id_personal,
                    CONCAT(p.nombre, ' ', p.apellido) AS nombre_completo,
                    p.cargo,
                    p.cedula
                FROM personal p
                LEFT JOIN usuariopersonal up ON p.id_personal = up.id_personal
                WHERE up.id_personal IS NULL AND p.estado = 'activo'
                ORDER BY p.nombre, p.apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlPersonalSinUsuario: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener clientes sin usuario asignado
     */
    static public function mdlClientesSinUsuario()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    c.id_cliente,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_completo,
                    c.cedula,
                    c.email
                FROM cliente c
                LEFT JOIN usuariocliente uc ON c.id_cliente = uc.id_cliente
                WHERE uc.id_cliente IS NULL AND c.estado = 'activo'
                ORDER BY c.nombre, c.apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlClientesSinUsuario: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar usuarios
     */
    static public function mdlBuscarUsuarios($termino, $tipo = 'ambos')
    {
        try {
            $resultados = array();

            if ($tipo === 'personal' || $tipo === 'ambos') {
                $stmt = Conexion::conectar()->prepare("
                    SELECT 
                        'personal' as tipo_usuario,
                        up.id_usuario_personal as id_usuario,
                        up.usuario,
                        up.rol,
                        up.estado,
                        CONCAT(p.nombre, ' ', p.apellido) AS nombre_completo,
                        p.cargo
                    FROM usuariopersonal up
                    INNER JOIN personal p ON up.id_personal = p.id_personal
                    WHERE (up.usuario LIKE :termino OR p.nombre LIKE :termino2 OR p.apellido LIKE :termino3)
                    ORDER BY p.nombre, p.apellido
                ");
                $termino_like = '%' . $termino . '%';
                $stmt->bindParam(":termino", $termino_like, PDO::PARAM_STR);
                $stmt->bindParam(":termino2", $termino_like, PDO::PARAM_STR);
                $stmt->bindParam(":termino3", $termino_like, PDO::PARAM_STR);
                $stmt->execute();
                $resultados = array_merge($resultados, $stmt->fetchAll(PDO::FETCH_ASSOC));
            }

            if ($tipo === 'cliente' || $tipo === 'ambos') {
                $stmt = Conexion::conectar()->prepare("
                    SELECT 
                        'cliente' as tipo_usuario,
                        uc.id_usuario_cliente as id_usuario,
                        uc.usuario,
                        '' as rol,
                        uc.estado,
                        CONCAT(c.nombre, ' ', c.apellido) AS nombre_completo,
                        'cliente' as cargo
                    FROM usuariocliente uc
                    INNER JOIN cliente c ON uc.id_cliente = c.id_cliente
                    WHERE (uc.usuario LIKE :termino OR c.nombre LIKE :termino2 OR c.apellido LIKE :termino3)
                    ORDER BY c.nombre, c.apellido
                ");
                $termino_like = '%' . $termino . '%';
                $stmt->bindParam(":termino", $termino_like, PDO::PARAM_STR);
                $stmt->bindParam(":termino2", $termino_like, PDO::PARAM_STR);
                $stmt->bindParam(":termino3", $termino_like, PDO::PARAM_STR);
                $stmt->execute();
                $resultados = array_merge($resultados, $stmt->fetchAll(PDO::FETCH_ASSOC));
            }

            return $resultados;
        } catch (Exception $e) {
            error_log("Error en mdlBuscarUsuarios: " . $e->getMessage());
            return array();
        }
    }
}
?>