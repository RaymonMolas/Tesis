<?php
require_once "conexion.php";

class ModeloDetalleFactura {
    
    // Registrar nuevo detalle de factura
    static public function mdlRegistrarDetalle($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO detalle_factura 
                (id_factura, tipo, id_producto, descripcion, cantidad, precio_unitario, descuento, subtotal)
                VALUES 
                (:id_factura, :tipo, :id_producto, :descripcion, :cantidad, :precio_unitario, :descuento, :subtotal)
            ");

            $stmt->bindParam(":id_factura", $datos["id_factura"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":descuento", $datos["descuento"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener detalles de una factura
    static public function mdlObtenerDetalles($id_factura) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT df.*, p.nombre as nombre_producto, p.codigo as codigo_producto
                FROM detalle_factura df
                LEFT JOIN producto p ON df.id_producto = p.id_producto
                WHERE df.id_factura = :id_factura
                ORDER BY df.tipo, df.id_detalle
            ");

            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetalles: " . $e->getMessage());
            return array();
        }
    }

    // Eliminar detalles de una factura
    static public function mdlEliminarDetallesFactura($id_factura) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM detalle_factura WHERE id_factura = :id_factura");
            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetallesFactura: " . $e->getMessage());
            return "error";
        }
    }

    // Calcular total de una factura
    static public function mdlCalcularTotal($id_factura) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT SUM(subtotal) as total 
                FROM detalle_factura 
                WHERE id_factura = :id_factura
            ");

            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en mdlCalcularTotal: " . $e->getMessage());
            return 0;
        }
    }
}
?>