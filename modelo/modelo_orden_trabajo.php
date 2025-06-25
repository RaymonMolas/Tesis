<?php

require_once "conexion.php";

class ModeloOrdenTrabajo
{
    // Listar todas las órdenes de trabajo
    static public function mdlListarOrdenesTrabajo()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT o.*, v.matricula, v.marca, v.modelo, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       p.nombre as nombre_personal
                FROM ordentrabajo o
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal p ON o.id_personal = p.id_personal
                ORDER BY o.fecha_ingreso DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarOrdenesTrabajo: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una orden específica
    static public function mdlObtenerOrdenTrabajo($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT o.*, v.matricula, v.marca, v.modelo, v.id_cliente,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       p.nombre as nombre_personal
                FROM ordentrabajo o
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal p ON o.id_personal = p.id_personal
                WHERE o.id_orden = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenTrabajo: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva orden
    static public function mdlRegistrarOrdenTrabajo($datos) {
        try {
            $pdo = Conexion::conectar();
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO ordentrabajo (id_vehiculo, id_personal, fecha_ingreso, fecha_salida, kilometraje_actual, estado, observaciones)
                VALUES (:id_vehiculo, :id_personal, :fecha_ingreso, :fecha_salida, :kilometraje_actual, :estado, :observaciones)
            ");

            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_ingreso", $datos["fecha_ingreso"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_salida", $datos["fecha_salida"], PDO::PARAM_STR);
            $stmt->bindParam(":kilometraje_actual", $datos["kilometraje_actual"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $id_insertado = $pdo->lastInsertId();
                
                // Verificar que el ID es válido
                if ($id_insertado && $id_insertado > 0) {
                    // Confirmar transacción
                    $pdo->commit();
                    return $id_insertado;
                } else {
                    $pdo->rollBack();
                    return "error";
                }
            } else {
                $pdo->rollBack();
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarOrdenTrabajo: " . $e->getMessage());
            
            // Rollback si hay transacción activa
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            return "error";
        }
    }

    // Actualizar orden
    static public function mdlActualizarOrdenTrabajo($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE ordentrabajo 
                SET id_vehiculo = :id_vehiculo,
                    fecha_salida = :fecha_salida,
                    estado = :estado,
                    observaciones = :observaciones
                WHERE id_orden = :id_orden
            ");

            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_salida", $datos["fecha_salida"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarOrdenTrabajo: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar solo el estado
    static public function mdlActualizarEstado($datos) {
        try {
            $sql = "UPDATE ordentrabajo SET estado = :estado";
            
            if (isset($datos["fecha_salida"]) && $datos["fecha_salida"] !== null) {
                $sql .= ", fecha_salida = :fecha_salida";
            }
            
            $sql .= " WHERE id_orden = :id_orden";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            
            if (isset($datos["fecha_salida"]) && $datos["fecha_salida"] !== null) {
                $stmt->bindParam(":fecha_salida", $datos["fecha_salida"], PDO::PARAM_STR);
            }

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEstado: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar orden
    static public function mdlEliminarOrdenTrabajo($id) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM ordentrabajo WHERE id_orden = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                
                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró la orden con ID: " . $id);
                    return "error";
                }
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarOrdenTrabajo: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener órdenes por cliente
    static public function mdlObtenerOrdenesPorCliente($id_cliente) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT o.*, v.matricula, v.marca, v.modelo, 
                       p.nombre as nombre_personal
                FROM ordentrabajo o
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                INNER JOIN personal p ON o.id_personal = p.id_personal
                WHERE v.id_cliente = :id_cliente
                ORDER BY o.fecha_ingreso DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenesPorCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener órdenes por estado
    static public function mdlObtenerOrdenesPorEstado($estado) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT o.*, v.matricula, v.marca, v.modelo, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       p.nombre as nombre_personal
                FROM ordentrabajo o
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal p ON o.id_personal = p.id_personal
                WHERE o.estado = :estado
                ORDER BY o.fecha_ingreso DESC
            ");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenesPorEstado: " . $e->getMessage());
            return array();
        }
    }

    // Marcar orden como facturada
    static public function mdlMarcarComoFacturada($id_orden) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE ordentrabajo 
                SET facturado = 1 
                WHERE id_orden = :id_orden
            ");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlMarcarComoFacturada: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener estadísticas de órdenes
    static public function mdlObtenerEstadisticas() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_ordenes,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as canceladas,
                    SUM(CASE WHEN facturado = 1 THEN 1 ELSE 0 END) as facturadas
                FROM ordentrabajo
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener órdenes recientes para el dashboard
    static public function mdlObtenerOrdenesRecientes($limite = 5) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT o.id_orden, o.fecha_ingreso, o.estado, 
                       v.matricula, v.marca, v.modelo,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM ordentrabajo o
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                ORDER BY o.fecha_ingreso DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenesRecientes: " . $e->getMessage());
            return array();
        }
    }
}
?>