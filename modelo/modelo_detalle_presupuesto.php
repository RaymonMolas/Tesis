<?php

require_once "conexion.php";

class ModeloDetallePresupuesto
{
    // Obtener detalles de un presupuesto
    static public function mdlObtenerDetalles($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM detalle_presupuesto 
                WHERE id_presupuesto = :id_presupuesto
                ORDER BY id_detalle
            ");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetalles: " . $e->getMessage());
            return array();
        }
    }

    // Registrar detalle de presupuesto
    static public function mdlRegistrarDetalle($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO detalle_presupuesto (id_presupuesto, tipo, id_producto, descripcion, cantidad, precio_unitario, subtotal)
                VALUES (:id_presupuesto, :tipo, :id_producto, :descripcion, :cantidad, :precio_unitario, :subtotal)
            ");

            $stmt->bindParam(":id_presupuesto", $datos["id_presupuesto"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
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
                UPDATE detalle_presupuesto 
                SET tipo = :tipo,
                    id_producto = :id_producto,
                    descripcion = :descripcion,
                    cantidad = :cantidad,
                    precio_unitario = :precio_unitario,
                    subtotal = :subtotal
                WHERE id_detalle = :id_detalle
            ");

            $stmt->bindParam(":id_detalle", $datos["id_detalle"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
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
            $stmt = Conexion::conectar()->prepare("DELETE FROM detalle_presupuesto WHERE id_detalle = :id_detalle");
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

    // Eliminar todos los detalles de un presupuesto
    static public function mdlEliminarDetallesPresupuesto($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM detalle_presupuesto WHERE id_presupuesto = :id_presupuesto");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetallesPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    // Calcular total de un presupuesto
    static public function mdlCalcularTotal($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT SUM(subtotal) as total 
                FROM detalle_presupuesto 
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

    // Obtener productos más cotizados
    static public function mdlObtenerProductosMasCotizados($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT dp.descripcion, COUNT(*) as cantidad_cotizaciones,
                       SUM(dp.subtotal) as total_cotizado,
                       AVG(dp.precio_unitario) as precio_promedio
                FROM detalle_presupuesto dp
                INNER JOIN presupuesto p ON dp.id_presupuesto = p.id_presupuesto
                WHERE p.estado != 'cancelado'
                GROUP BY dp.descripcion
                ORDER BY cantidad_cotizaciones DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProductosMasCotizados: " . $e->getMessage());
            return array();
        }
    }

    // Obtener detalles con información del presupuesto
    static public function mdlObtenerDetallesConPresupuesto($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT dp.*, p.fecha_emision, p.estado as estado_presupuesto,
                       v.matricula, v.marca, v.modelo,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM detalle_presupuesto dp
                INNER JOIN presupuesto p ON dp.id_presupuesto = p.id_presupuesto
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE dp.id_presupuesto = :id_presupuesto
                ORDER BY dp.id_detalle
            ");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetallesConPresupuesto: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de detalles
    static public function mdlObtenerEstadisticasDetalles()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(DISTINCT dp.id_presupuesto) as total_presupuestos_con_detalles,
                    COUNT(*) as total_items_cotizados,
                    AVG(dp.subtotal) as promedio_precio_item,
                    SUM(dp.subtotal) as total_cotizado,
                    COUNT(CASE WHEN dp.tipo = 'producto' THEN 1 END) as total_productos,
                    COUNT(CASE WHEN dp.tipo = 'servicio' THEN 1 END) as total_servicios
                FROM detalle_presupuesto dp
                INNER JOIN presupuesto p ON dp.id_presupuesto = p.id_presupuesto
                WHERE p.estado != 'cancelado'
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticasDetalles: " . $e->getMessage());
            return array();
        }
    }

    // Buscar detalles por descripción
    static public function mdlBuscarPorDescripcion($termino_busqueda)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT dp.*, p.id_presupuesto, p.fecha_emision,
                       v.matricula, v.marca, v.modelo,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM detalle_presupuesto dp
                INNER JOIN presupuesto p ON dp.id_presupuesto = p.id_presupuesto
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE dp.descripcion LIKE :termino
                ORDER BY p.fecha_emision DESC
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

    // Verificar si un presupuesto tiene detalles
    static public function mdlTieneDetalles($id_presupuesto)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM detalle_presupuesto 
                WHERE id_presupuesto = :id_presupuesto
            ");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlTieneDetalles: " . $e->getMessage());
            return false;
        }
    }

    // Obtener detalles por tipo
    static public function mdlObtenerDetallesPorTipo($id_presupuesto, $tipo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM detalle_presupuesto 
                WHERE id_presupuesto = :id_presupuesto AND tipo = :tipo
                ORDER BY id_detalle
            ");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetallesPorTipo: " . $e->getMessage());
            return array();
        }
    }
}
?>