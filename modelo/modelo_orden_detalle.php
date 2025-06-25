<?php

require_once "conexion.php";

class ModeloOrdenDetalle
{
    // Obtener detalles de una orden
    static public function mdlObtenerDetalles($id_orden)
    {
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

    // Registrar detalle de orden
    static public function mdlRegistrarDetalle($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO orden_detalle (id_orden, tipo_servicio, descripcion, cantidad, precio_unitario, subtotal)
                VALUES (:id_orden, :tipo_servicio, :descripcion, :cantidad, :precio_unitario, :subtotal)
            ");

            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $datos["tipo_servicio"], PDO::PARAM_STR);
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
                UPDATE orden_detalle 
                SET tipo_servicio = :tipo_servicio,
                    descripcion = :descripcion,
                    cantidad = :cantidad,
                    precio_unitario = :precio_unitario,
                    subtotal = :subtotal
                WHERE id_detalle = :id_detalle
            ");

            $stmt->bindParam(":id_detalle", $datos["id_detalle"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $datos["tipo_servicio"], PDO::PARAM_STR);
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
            $stmt = Conexion::conectar()->prepare("DELETE FROM orden_detalle WHERE id_detalle = :id_detalle");
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

    // Eliminar todos los detalles de una orden
    static public function mdlEliminarDetalles($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM orden_detalle WHERE id_orden = :id_orden");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetalles: " . $e->getMessage());
            return "error";
        }
    }

    // Calcular total de una orden
    static public function mdlCalcularTotal($id_orden)
    {
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

    // Obtener servicios más utilizados
    static public function mdlObtenerServiciosMasUtilizados($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT tipo_servicio, COUNT(*) as cantidad_usos,
                       SUM(subtotal) as total_facturado
                FROM orden_detalle od
                INNER JOIN ordentrabajo o ON od.id_orden = o.id_orden
                WHERE o.estado = 'completado'
                GROUP BY tipo_servicio
                ORDER BY cantidad_usos DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerServiciosMasUtilizados: " . $e->getMessage());
            return array();
        }
    }

    // Obtener detalles con información de la orden
    static public function mdlObtenerDetallesConOrden($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT od.*, o.fecha_ingreso, o.estado as estado_orden,
                       v.matricula, v.marca, v.modelo,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM orden_detalle od
                INNER JOIN ordentrabajo o ON od.id_orden = o.id_orden
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE od.id_orden = :id_orden
                ORDER BY od.id_detalle
            ");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetallesConOrden: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de servicios
    static public function mdlObtenerEstadisticasServicios()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(DISTINCT od.id_orden) as total_ordenes_con_servicios,
                    COUNT(*) as total_servicios_realizados,
                    AVG(od.subtotal) as promedio_precio_servicio,
                    SUM(od.subtotal) as total_facturado_servicios
                FROM orden_detalle od
                INNER JOIN ordentrabajo o ON od.id_orden = o.id_orden
                WHERE o.estado = 'completado'
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticasServicios: " . $e->getMessage());
            return array();
        }
    }

    // Buscar detalles por descripción
    static public function mdlBuscarPorDescripcion($termino_busqueda)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT od.*, o.id_orden, o.fecha_ingreso,
                       v.matricula, v.marca, v.modelo,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM orden_detalle od
                INNER JOIN ordentrabajo o ON od.id_orden = o.id_orden
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE od.descripcion LIKE :termino
                ORDER BY o.fecha_ingreso DESC
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

    // Verificar si una orden tiene detalles
    static public function mdlTieneDetalles($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM orden_detalle 
                WHERE id_orden = :id_orden
            ");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlTieneDetalles: " . $e->getMessage());
            return false;
        }
    }
}
?>