<?php
require_once "conexion.php";

class ModeloPresupuesto
{
    // Listar todos los presupuestos
    static public function mdlListarPresupuestos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*, 
                       v.marca, v.modelo, v.matricula,
                       c.nombre as nombre_cliente,
                       pe.nombre as nombre_personal
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal pe ON p.id_personal = pe.id_personal
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
SELECT p.*, 
       v.marca, v.modelo, v.matricula,
       c.id_cliente,
       c.nombre as nombre_cliente,
       pe.nombre as nombre_personal
FROM presupuesto p
INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
INNER JOIN cliente c ON v.id_cliente = c.id_cliente
INNER JOIN personal pe ON p.id_personal = pe.id_personal
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

            // Debug: Log de los datos que se van a insertar
            error_log("Insertando presupuesto con datos: " . print_r($datos, true));

            if ($stmt->execute()) {
                $id_presupuesto = $pdo->lastInsertId();
                error_log("Presupuesto insertado con ID: " . $id_presupuesto);

                // Verificar que el ID es válido
                if ($id_presupuesto && $id_presupuesto > 0) {
                    // Confirmar transacción
                    $pdo->commit();
                    return $id_presupuesto;
                } else {
                    error_log("Error: lastInsertId devolvió: " . var_export($id_presupuesto, true));
                    $pdo->rollBack();
                    return "error";
                }
            } else {
                error_log("Error en execute() del presupuesto");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                $pdo->rollBack();
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarPresupuesto: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());

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
            error_log("Eliminando presupuesto con ID: " . $id);

            $stmt = Conexion::conectar()->prepare("DELETE FROM presupuesto WHERE id_presupuesto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                error_log("Filas afectadas en eliminación: " . $filasAfectadas);

                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró el presupuesto con ID: " . $id);
                    return "error";
                }
            } else {
                error_log("Error en execute() de eliminación");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
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
}
