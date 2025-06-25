<?php

require_once "conexion.php";

class ModeloDetalleFactura
{
    // Obtener detalles de una factura
    static public function mdlObtenerDetalles($id_factura)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM detalle_factura 
                WHERE id_factura = :id_factura
                ORDER BY id_detalle
            ");
            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetalles: " . $e->getMessage());
            return array();
        }
    }

    // Registrar detalle de factura
    static public function mdlRegistrarDetalle($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO detalle_factura (id_factura, tipo, id_producto, descripcion, cantidad, precio_unitario, descuento, subtotal)
                VALUES (:id_factura, :tipo, :id_producto, :descripcion, :cantidad, :precio_unitario, :descuento, :subtotal)
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

    // Actualizar detalle
    static public function mdlActualizarDetalle($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE detalle_factura 
                SET tipo = :tipo,
                    id_producto = :id_producto,
                    descripcion = :descripcion,
                    cantidad = :cantidad,
                    precio_unitario = :precio_unitario,
                    descuento = :descuento,
                    subtotal = :subtotal
                WHERE id_detalle = :id_detalle
            ");

            $stmt->bindParam(":id_detalle", $datos["id_detalle"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":descuento", $datos["descuento"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar un detalle específico
    static public function mdlEliminarDetalle($id_detalle)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM detalle_factura WHERE id_detalle = :id_detalle");
            $stmt->bindParam(":id_detalle", $id_detalle, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                
                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró el detalle con ID: " . $id_detalle);
                    return "error";
                }
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar todos los detalles de una factura
    static public function mdlEliminarDetallesFactura($id_factura)
    {
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
    static public function mdlCalcularTotal($id_factura)
    {
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

    // Obtener productos/servicios más vendidos
    static public function mdlObtenerMasVendidos($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT df.descripcion, SUM(df.cantidad) as cantidad_vendida,
                       SUM(df.subtotal) as total_vendido,
                       AVG(df.precio_unitario) as precio_promedio,
                       COUNT(DISTINCT df.id_factura) as facturas_incluido
                FROM detalle_factura df
                INNER JOIN factura f ON df.id_factura = f.id_factura
                WHERE f.estado = 'pagada'
                GROUP BY df.descripcion
                ORDER BY cantidad_vendida DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerMasVendidos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener detalles con información de la factura
    static public function mdlObtenerDetallesConFactura($id_factura)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT df.*, f.fecha_emision, f.estado as estado_factura, f.numero_factura,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM detalle_factura df
                INNER JOIN factura f ON df.id_factura = f.id_factura
                INNER JOIN cliente c ON f.id_cliente = c.id_cliente
                WHERE df.id_factura = :id_factura
                ORDER BY df.id_detalle
            ");
            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetallesConFactura: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de ventas
    static public function mdlObtenerEstadisticasVentas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(DISTINCT df.id_factura) as total_facturas_con_detalles,
                    COUNT(*) as total_items_vendidos,
                    SUM(df.cantidad) as cantidad_total_vendida,
                    AVG(df.subtotal) as promedio_precio_item,
                    SUM(df.subtotal) as total_facturado_items,
                    COUNT(CASE WHEN df.tipo = 'producto' THEN 1 END) as total_productos_vendidos,
                    COUNT(CASE WHEN df.tipo = 'servicio' THEN 1 END) as total_servicios_vendidos
                FROM detalle_factura df
                INNER JOIN factura f ON df.id_factura = f.id_factura
                WHERE f.estado = 'pagada'
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticasVentas: " . $e->getMessage());
            return array();
        }
    }

    // Buscar detalles por descripción
    static public function mdlBuscarPorDescripcion($termino_busqueda)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT df.*, f.id_factura, f.fecha_emision, f.numero_factura,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM detalle_factura df
                INNER JOIN factura f ON df.id_factura = f.id_factura
                INNER JOIN cliente c ON f.id_cliente = c.id_cliente
                WHERE df.descripcion LIKE :termino
                ORDER BY f.fecha_emision DESC
            ");
            $termino = "%" . $termino_busqueda . "%";
            $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarPorDescripcion: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si una factura tiene detalles
    static public function mdlTieneDetalles($id_factura)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM detalle_factura 
                WHERE id_factura = :id_factura
            ");
            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlTieneDetalles: " . $e->getMessage());
            return false;
        }
    }

    // Obtener detalles por tipo
    static public function mdlObtenerDetallesPorTipo($id_factura, $tipo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM detalle_factura 
                WHERE id_factura = :id_factura AND tipo = :tipo
                ORDER BY id_detalle
            ");
            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetallesPorTipo: " . $e->getMessage());
            return array();
        }
    }

    // Obtener resumen de ventas por período
    static public function mdlResumenVentasPeriodo($fecha_inicio, $fecha_fin)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    DATE(f.fecha_emision) as fecha,
                    COUNT(DISTINCT df.id_factura) as facturas_del_dia,
                    COUNT(*) as items_vendidos,
                    SUM(df.cantidad) as cantidad_total,
                    SUM(df.subtotal) as total_del_dia
                FROM detalle_factura df
                INNER JOIN factura f ON df.id_factura = f.id_factura
                WHERE f.estado = 'pagada'
                AND DATE(f.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY DATE(f.fecha_emision)
                ORDER BY fecha DESC
            ");
            $stmt->bindParam(":fecha_inicio", $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlResumenVentasPeriodo: " . $e->getMessage());
            return array();
        }
    }
}
?>