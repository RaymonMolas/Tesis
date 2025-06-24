<?php
require_once "conexion.php";

class ModeloOrdenTrabajo {
    // Listar todas las órdenes de trabajo
    static public function mdlListarOrdenesTrabajo() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT ot.*, 
                       v.marca, v.modelo, v.matricula,
                       c.nombre as nombre_cliente,
                       p.nombre as nombre_personal
                FROM ordentrabajo ot
                INNER JOIN vehiculo v ON ot.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal p ON ot.id_personal = p.id_personal
                ORDER BY ot.fecha_ingreso DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarOrdenesTrabajo: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una orden específica
    static public function mdlObtenerOrdenTrabajo($id) {
        try {
            $stmt = Conexion::conectar()->prepare("
SELECT ot.*, 
       v.marca, v.modelo, v.matricula,
       c.id_cliente,
       c.nombre as nombre_cliente,
       p.nombre as nombre_personal
FROM ordentrabajo ot
INNER JOIN vehiculo v ON ot.id_vehiculo = v.id_vehiculo
INNER JOIN cliente c ON v.id_cliente = c.id_cliente
INNER JOIN personal p ON ot.id_personal = p.id_personal
WHERE ot.id_orden = :id
            ");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenTrabajo: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva orden - CORRECCIÓN PRINCIPAL
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

            // Debug: Log de los datos que se van a insertar
            error_log("Insertando orden con datos: " . print_r($datos, true));

            if ($stmt->execute()) {
                $id_insertado = $pdo->lastInsertId();
                error_log("Orden insertada con ID: " . $id_insertado);
                
                // Verificar que el ID es válido
                if ($id_insertado && $id_insertado > 0) {
                    // Confirmar transacción
                    $pdo->commit();
                    // IMPORTANTE: Devolver el ID directamente
                    return $id_insertado;
                } else {
                    error_log("Error: lastInsertId devolvió: " . var_export($id_insertado, true));
                    $pdo->rollBack();
                    return "error";
                }
            } else {
                error_log("Error en execute() de la orden");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                $pdo->rollBack();
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarOrdenTrabajo: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            
            // Rollback si hay transacción activa
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            return "error";
        }
    }

    // ELIMINAR ESTA FUNCIÓN - Ya no es necesaria
    // static public function mdlObtenerUltimoId() { ... }

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

    // Eliminar orden
    static public function mdlEliminarOrdenTrabajo($id) {
        try {
            error_log("Eliminando orden con ID: " . $id);
            
            $stmt = Conexion::conectar()->prepare("DELETE FROM ordentrabajo WHERE id_orden = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                error_log("Filas afectadas en eliminación de orden: " . $filasAfectadas);
                
                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró la orden con ID: " . $id);
                    return "error";
                }
            } else {
                error_log("Error en execute() de eliminación de orden");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarOrdenTrabajo: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar estado de orden
    static public function mdlActualizarEstado($datos) {
        try {
            $sql = "UPDATE ordentrabajo SET estado = :estado";
            if (isset($datos["fecha_salida"]) && $datos["fecha_salida"]) {
                $sql .= ", fecha_salida = :fecha_salida";
            }
            $sql .= " WHERE id_orden = :id_orden";

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            
            if (isset($datos["fecha_salida"]) && $datos["fecha_salida"]) {
                $stmt->bindParam(":fecha_salida", $datos["fecha_salida"], PDO::PARAM_STR);
            }

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEstado: " . $e->getMessage());
            return "error";
        }
    }
}
?>