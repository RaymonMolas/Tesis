<?php

require_once "conexion.php";

class ModeloPresupuesto
{
    // Listar todos los presupuestos
    static public function mdlListarPresupuestos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*, v.matricula, v.marca, v.modelo, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       per.nombre as nombre_personal
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal per ON p.id_personal = per.id_personal
                ORDER BY p.fecha_emision DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un presupuesto específico
    static public function mdlObtenerPresupuesto($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*, v.matricula, v.marca, v.modelo, v.id_cliente,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       per.nombre as nombre_personal
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal per ON p.id_personal = per.id_personal
                WHERE p.id_presupuesto = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPresupuesto: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo presupuesto
    static public function mdlRegistrarPresupuesto($datos)
    {
        try {
            $pdo = Conexion::conectar();

            // Iniciar transacción
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
            INSERT INTO presupuesto (id_vehiculo, id_personal, fecha_emision, fecha_validez, estado, total, observaciones)
            VALUES (:id_vehiculo, :id_personal, :fecha_emision, :fecha_validez, :estado, :total, :observaciones)
        ");

            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_emision", $datos["fecha_emision"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_validez", $datos["fecha_validez"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $id_presupuesto = $pdo->lastInsertId();

                // Verificar que el ID es válido
                if ($id_presupuesto && $id_presupuesto > 0) {
                    // Confirmar transacción
                    $pdo->commit();
                    return $id_presupuesto;
                } else {
                    $pdo->rollBack();
                    return "error";
                }
            } else {
                $pdo->rollBack();
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarPresupuesto: " . $e->getMessage());

            // Rollback si hay transacción activa
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return "error";
        }
    }

    // Actualizar presupuesto
    static public function mdlActualizarPresupuesto($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE presupuesto 
                SET id_vehiculo = :id_vehiculo,
                    fecha_validez = :fecha_validez,
                    estado = :estado,
                    total = :total,
                    observaciones = :observaciones
                WHERE id_presupuesto = :id_presupuesto
            ");

            $stmt->bindParam(":id_presupuesto", $datos["id_presupuesto"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_validez", $datos["fecha_validez"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar presupuesto
    static public function mdlEliminarPresupuesto($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM presupuesto WHERE id_presupuesto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();

                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró el presupuesto con ID: " . $id);
                    return "error";
                }
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar estado del presupuesto
    static public function mdlActualizarEstado($id, $estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE presupuesto SET estado = :estado WHERE id_presupuesto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEstado: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener presupuestos por cliente
    static public function mdlObtenerPresupuestosPorCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*, v.matricula, v.marca, v.modelo,
                       per.nombre as nombre_personal
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN personal per ON p.id_personal = per.id_personal
                WHERE v.id_cliente = :id_cliente
                ORDER BY p.fecha_emision DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPresupuestosPorCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener presupuestos por estado
    static public function mdlObtenerPresupuestosPorEstado($estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*, v.matricula, v.marca, v.modelo,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       per.nombre as nombre_personal
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal per ON p.id_personal = per.id_personal
                WHERE p.estado = :estado
                ORDER BY p.fecha_emision DESC
            ");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPresupuestosPorEstado: " . $e->getMessage());
            return array();
        }
    }

    // Marcar presupuesto como facturado
    static public function mdlMarcarComoFacturado($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE presupuesto 
                SET facturado = 1 
                WHERE id_presupuesto = :id_presupuesto
            ");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlMarcarComoFacturado: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener estadísticas de presupuestos
    static public function mdlObtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_presupuestos,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
                    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
                    SUM(CASE WHEN facturado = 1 THEN 1 ELSE 0 END) as facturados,
                    AVG(total) as promedio_valor
                FROM presupuesto
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener presupuestos recientes para el dashboard
    static public function mdlObtenerPresupuestosRecientes($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.id_presupuesto, p.fecha_emision, p.estado, p.total,
                       v.matricula, v.marca, v.modelo,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                ORDER BY p.fecha_emision DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPresupuestosRecientes: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si un presupuesto puede ser eliminado
    static public function mdlPuedeEliminar($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT estado, facturado 
                FROM presupuesto 
                WHERE id_presupuesto = :id
            ");
            $stmt->bindParam(":id", $id_presupuesto, PDO::PARAM_INT);
            $stmt->execute();
            $presupuesto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($presupuesto) {
                // Solo se puede eliminar si está pendiente y no facturado
                return ($presupuesto['estado'] == 'pendiente' && $presupuesto['facturado'] == 0);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en mdlPuedeEliminar: " . $e->getMessage());
            return false;
        }
    }
}
?>