<?php
require_once "conexion.php";

class ModeloPersonal
{

    /**
     * Crear nuevo personal
     */
    static public function mdlCrearPersonal($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO personal (nombre, apellido, cedula, telefono, email, direccion, cargo, estado)
                VALUES (:nombre, :apellido, :cedula, :telefono, :email, :direccion, :cargo, :estado)
            ");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":cargo", $datos["cargo"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCrearPersonal: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'cedula') !== false) {
                    return "cedula_duplicada";
                }
            }
            return "error";
        }
    }

    /**
     * Obtener lista de personal
     */
    static public function mdlListarPersonal()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.*,
                    u.usuario,
                    u.rol,
                    u.estado as estado_usuario
                FROM personal p
                LEFT JOIN usuariopersonal u ON p.id_personal = u.id_personal
                ORDER BY p.nombre, p.apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlListarPersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener personal por ID
     */
    static public function mdlObtenerPersonal($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM personal WHERE id_personal = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerPersonal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar personal
     */
    static public function mdlActualizarPersonal($id, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE personal SET
                    nombre = :nombre,
                    apellido = :apellido,
                    cedula = :cedula,
                    telefono = :telefono,
                    email = :email,
                    direccion = :direccion,
                    cargo = :cargo,
                    estado = :estado,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_personal = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":cargo", $datos["cargo"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarPersonal: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'cedula') !== false) {
                    return "cedula_duplicada";
                }
            }
            return "error";
        }
    }

    /**
     * Eliminar personal
     */
    static public function mdlEliminarPersonal($id)
    {
        try {
            // Verificar si tiene órdenes asignadas
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM ordentrabajo WHERE id_personal = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $ordenes = $stmt->fetchColumn();

            if ($ordenes > 0) {
                return "tiene_ordenes";
            }

            // Verificar si tiene presupuestos asignados
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM presupuesto WHERE id_personal = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $presupuestos = $stmt->fetchColumn();

            if ($presupuestos > 0) {
                return "tiene_presupuestos";
            }

            // Si no tiene dependencias, eliminar
            $stmt = Conexion::conectar()->prepare("DELETE FROM personal WHERE id_personal = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarPersonal: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener personal por cargo
     */
    static public function mdlObtenerPersonalPorCargo($cargo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                WHERE cargo = :cargo AND estado = 'activo'
                ORDER BY nombre, apellido
            ");
            $stmt->bindParam(":cargo", $cargo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerPersonalPorCargo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Contar personal por cargo
     */
    static public function mdlContarPersonalPorCargo()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    cargo,
                    COUNT(*) as cantidad,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                    SUM(CASE WHEN estado = 'vacaciones' THEN 1 ELSE 0 END) as vacaciones
                FROM personal
                GROUP BY cargo
                ORDER BY cantidad DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlContarPersonalPorCargo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Verificar si existe la cédula
     */
    static public function mdlVerificarCedula($cedula, $id_excluir = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM personal WHERE cedula = :cedula";
            if ($id_excluir) {
                $sql .= " AND id_personal != :id_excluir";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":cedula", $cedula, PDO::PARAM_STR);
            if ($id_excluir) {
                $stmt->bindParam(":id_excluir", $id_excluir, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en mdlVerificarCedula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener personal activo
     */
    static public function mdlObtenerPersonalActivo()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                WHERE estado = 'activo'
                ORDER BY nombre, apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerPersonalActivo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Cambiar estado del personal
     */
    static public function mdlCambiarEstado($id, $estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE personal SET 
                    estado = :estado, 
                    fecha_actualizacion = CURRENT_TIMESTAMP 
                WHERE id_personal = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCambiarEstado: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener estadísticas del personal
     */
    static public function mdlEstadisticasPersonal()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_personal,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                    SUM(CASE WHEN estado = 'vacaciones' THEN 1 ELSE 0 END) as vacaciones,
                    SUM(CASE WHEN cargo = 'mecanico' THEN 1 ELSE 0 END) as mecanicos,
                    SUM(CASE WHEN cargo = 'electricista' THEN 1 ELSE 0 END) as electricistas,
                    SUM(CASE WHEN cargo = 'gerente' THEN 1 ELSE 0 END) as gerentes,
                    SUM(CASE WHEN cargo = 'recepcionista' THEN 1 ELSE 0 END) as recepcionistas,
                    SUM(CASE WHEN cargo = 'administrador' THEN 1 ELSE 0 END) as administradores
                FROM personal
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlEstadisticasPersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar personal con filtros
     */
    static public function mdlBuscarPersonal($filtros)
    {
        try {
            $sql = "SELECT * FROM personal WHERE 1=1";
            $params = array();

            if (!empty($filtros['nombre'])) {
                $sql .= " AND (nombre LIKE :nombre OR apellido LIKE :nombre)";
                $params[':nombre'] = '%' . $filtros['nombre'] . '%';
            }

            if (!empty($filtros['cedula'])) {
                $sql .= " AND cedula LIKE :cedula";
                $params[':cedula'] = '%' . $filtros['cedula'] . '%';
            }

            if (!empty($filtros['cargo'])) {
                $sql .= " AND cargo = :cargo";
                $params[':cargo'] = $filtros['cargo'];
            }

            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            $sql .= " ORDER BY nombre, apellido";

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlBuscarPersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener carga de trabajo del personal
     */
    static public function mdlObtenerCargaTrabajo($id_personal = null, $fecha_inicio = null, $fecha_fin = null)
    {
        try {
            $sql = "
                SELECT 
                    p.id_personal,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_completo,
                    p.cargo,
                    COUNT(o.id_orden) as ordenes_asignadas,
                    SUM(CASE WHEN o.estado = 'en_proceso' THEN 1 ELSE 0 END) as ordenes_en_proceso,
                    SUM(CASE WHEN o.estado = 'completado' THEN 1 ELSE 0 END) as ordenes_completadas,
                    COUNT(pr.id_presupuesto) as presupuestos_asignados
                FROM personal p
                LEFT JOIN ordentrabajo o ON p.id_personal = o.id_personal
                LEFT JOIN presupuesto pr ON p.id_personal = pr.id_personal
                WHERE p.estado = 'activo'
            ";

            $params = array();

            if ($id_personal) {
                $sql .= " AND p.id_personal = :id_personal";
                $params[':id_personal'] = $id_personal;
            }

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND (o.fecha_ingreso BETWEEN :fecha_inicio AND :fecha_fin 
                          OR pr.fecha_emision BETWEEN :fecha_inicio AND :fecha_fin)";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin'] = $fecha_fin;
            }

            $sql .= " GROUP BY p.id_personal ORDER BY ordenes_en_proceso DESC, nombre_completo";

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCargaTrabajo: " . $e->getMessage());
            return array();
        }
    }
}
?>