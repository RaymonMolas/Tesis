<?php
require_once "conexion.php";

class ModeloDetallePresupuesto
{
    // Registrar nuevo detalle
    static public function mdlRegistrarDetalle($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO detalles_presupuesto 
                (id_presupuesto, descripcion, cantidad, precio_unitario, subtotal, tipo, id_producto)
                VALUES 
                (:id_presupuesto, :descripcion, :cantidad, :precio_unitario, :subtotal, :tipo, :id_producto)
            ");

            $stmt->bindParam(":id_presupuesto", $datos["id_presupuesto"], PDO::PARAM_INT);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener detalles de un presupuesto
    static public function mdlObtenerDetalles($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT d.*, p.nombre as nombre_producto, p.codigo as codigo_producto
                FROM detalles_presupuesto d
                LEFT JOIN producto p ON d.id_producto = p.id_producto
                WHERE d.id_presupuesto = :id_presupuesto
                ORDER BY d.tipo, d.id_detalle
            ");

            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetalles: " . $e->getMessage());
            return array();
        }
    }

    // Actualizar detalle
    static public function mdlActualizarDetalle($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE detalles_presupuesto 
                SET descripcion = :descripcion,
                    cantidad = :cantidad,
                    precio_unitario = :precio_unitario,
                    subtotal = :subtotal,
                    tipo = :tipo,
                    id_producto = :id_producto
                WHERE id_detalle = :id_detalle
            ");

            $stmt->bindParam(":id_detalle", $datos["id_detalle"], PDO::PARAM_INT);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar detalle
    static public function mdlEliminarDetalle($id_detalle)
    {
        try {
            error_log("Eliminando detalle con ID: " . $id_detalle);

            $stmt = Conexion::conectar()->prepare("DELETE FROM detalles_presupuesto WHERE id_detalle = :id_detalle");
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

    // Eliminar todos los detalles de un presupuesto
    static public function mdlEliminarDetallesPresupuesto($id_presupuesto)
    {
        try {
            error_log("Eliminando detalles del presupuesto ID: " . $id_presupuesto);

            // Primero verificar cuántos detalles hay
            $stmtCount = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM detalles_presupuesto WHERE id_presupuesto = :id_presupuesto");
            $stmtCount->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmtCount->execute();
            $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
            error_log("Detalles encontrados para eliminar: " . $count['total']);

            // Eliminar detalles
            $stmt = Conexion::conectar()->prepare("DELETE FROM detalles_presupuesto WHERE id_presupuesto = :id_presupuesto");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);

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
            error_log("Error en mdlEliminarDetallesPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    // Calcular total del presupuesto
    static public function mdlCalcularTotal($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT SUM(subtotal) as total 
                FROM detalles_presupuesto 
                WHERE id_presupuesto = :id_presupuesto
            ");

            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en mdlCalcularTotal: " . $e->getMessage());
            return 0;
        }
    }
}
