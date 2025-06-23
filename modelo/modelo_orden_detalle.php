<?php
require_once "conexion.php";

class ModeloOrdenDetalle {
    // Registrar nuevo detalle
    static public function mdlRegistrarDetalle($datos) {
        try {
            error_log("=== REGISTRANDO DETALLE ORDEN ===");
            error_log("Datos recibidos: " . print_r($datos, true));

            $stmt = Conexion::conectar()->prepare("
                INSERT INTO orden_detalle 
                (id_orden, tipo_servicio, descripcion, cantidad, precio_unitario, subtotal)
                VALUES 
                (:id_orden, :tipo_servicio, :descripcion, :cantidad, :precio_unitario, :subtotal)
            ");

            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $datos["tipo_servicio"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                error_log("Detalle insertado correctamente");
                return "ok";
            } else {
                error_log("Error en execute() del detalle");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error PDO en mdlRegistrarDetalle: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return "error";
        } catch (Exception $e) {
            error_log("Error general en mdlRegistrarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener detalles de una orden
    static public function mdlObtenerDetalles($id_orden) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM orden_detalle 
                WHERE id_orden = :id_orden 
                ORDER BY id_detalle
            ");
            
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetalles: " . $e->getMessage());
            return array();
        }
    }

    // Calcular total de una orden
    static public function mdlCalcularTotal($id_orden) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT SUM(subtotal) as total 
                FROM orden_detalle 
                WHERE id_orden = :id_orden
            ");
            
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en mdlCalcularTotal: " . $e->getMessage());
            return 0;
        }
    }

    // Eliminar detalles de una orden
    static public function mdlEliminarDetalles($id_orden) {
        try {
            error_log("Eliminando detalles de la orden ID: " . $id_orden);
            
            // Primero verificar cuántos detalles hay
            $stmtCount = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM orden_detalle WHERE id_orden = :id_orden");
            $stmtCount->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmtCount->execute();
            $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
            error_log("Detalles encontrados para eliminar: " . $count['total']);
            
            // Eliminar detalles
            $stmt = Conexion::conectar()->prepare("DELETE FROM orden_detalle WHERE id_orden = :id_orden");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                error_log("Detalles eliminados: " . $filasAfectadas);
                return "ok";
            } else {
                error_log("Error en execute() de eliminación de detalles");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetalles: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar un detalle específico
    static public function mdlEliminarDetalle($id_detalle) {
        try {
            error_log("Eliminando detalle con ID: " . $id_detalle);
            
            $stmt = Conexion::conectar()->prepare("DELETE FROM orden_detalle WHERE id_detalle = :id_detalle");
            $stmt->bindParam(":id_detalle", $id_detalle, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                error_log("Filas afectadas en eliminación de detalle: " . $filasAfectadas);
                
                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró el detalle con ID: " . $id_detalle);
                    return "error";
                }
            } else {
                error_log("Error en execute() de eliminación de detalle");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetalle: " . $e->getMessage());
            return "error";
        }
    }
}
?>