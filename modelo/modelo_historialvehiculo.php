<?php
require_once "conexion.php";

class ModeloHistorialVehiculo {

    /**
     * Insertar registro en historial de vehículo
     */
    static public function mdlInsertarHistorial($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO historialvehiculo 
                (id_vehiculo, id_orden, id_personal, tipo_servicio, descripcion, kilometraje, costo, observaciones)
                VALUES (:id_vehiculo, :id_orden, :id_personal, :tipo_servicio, :descripcion, :kilometraje, :costo, :observaciones)
            ");

            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $datos["tipo_servicio"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":kilometraje", $datos["kilometraje"], PDO::PARAM_INT);
            $stmt->bindParam(":costo", $datos["costo"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlInsertarHistorial: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener historial completo de un vehículo
     */
    static public function mdlObtenerHistorialVehiculo($id_vehiculo) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal,
                    o.fecha_ingreso, o.fecha_salida, o.estado as estado_orden
                FROM historialvehiculo h
                INNER JOIN personal p ON h.id_personal = p.id_personal
                LEFT JOIN ordentrabajo o ON h.id_orden = o.id_orden
                WHERE h.id_vehiculo = :id_vehiculo
                ORDER BY h.fecha_servicio DESC
            ");
            $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerHistorialVehiculo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener historial de un cliente (todos sus vehículos)
     */
    static public function mdlObtenerHistorialCliente($id_cliente) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal,
                    o.fecha_ingreso, o.fecha_salida, o.estado as estado_orden
                FROM historialvehiculo h
                INNER JOIN vehiculo v ON h.id_vehiculo = v.id_vehiculo
                INNER JOIN personal p ON h.id_personal = p.id_personal
                LEFT JOIN ordentrabajo o ON h.id_orden = o.id_orden
                WHERE v.id_cliente = :id_cliente
                ORDER BY h.fecha_servicio DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerHistorialCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener último servicio de un vehículo
     */
    static public function mdlObtenerUltimoServicio($id_vehiculo) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historialvehiculo h
                INNER JOIN personal p ON h.id_personal = p.id_personal
                WHERE h.id_vehiculo = :id_vehiculo
                ORDER BY h.fecha_servicio DESC
                LIMIT 1
            ");
            $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerUltimoServicio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener servicios por tipo
     */
    static public function mdlObtenerServicioPorTipo($id_vehiculo, $tipo_servicio) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historialvehiculo h
                INNER JOIN personal p ON h.id_personal = p.id_personal
                WHERE h.id_vehiculo = :id_vehiculo AND h.tipo_servicio = :tipo_servicio
                ORDER BY h.fecha_servicio DESC
            ");
            $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $tipo_servicio, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerServicioPorTipo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener estadísticas de servicios de un vehículo
     */
    static public function mdlEstadisticasServiciosVehiculo($id_vehiculo) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_servicios,
                    SUM(costo) as costo_total,
                    AVG(costo) as costo_promedio,
                    MAX(kilometraje) as ultimo_kilometraje,
                    MIN(fecha_servicio) as primer_servicio,
                    MAX(fecha_servicio) as ultimo_servicio,
                    COUNT(DISTINCT tipo_servicio) as tipos_servicio_diferentes
                FROM historialvehiculo
                WHERE id_vehiculo = :id_vehiculo
            ");
            $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlEstadisticasServiciosVehiculo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener servicios por rango de fechas
     */
    static public function mdlObtenerServiciosPorFechas($fecha_inicio, $fecha_fin, $id_vehiculo = null) {
        try {
            $sql = "
                SELECT 
                    h.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historialvehiculo h
                INNER JOIN vehiculo v ON h.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal p ON h.id_personal = p.id_personal
                WHERE DATE(h.fecha_servicio) BETWEEN :fecha_inicio AND :fecha_fin
            ";

            if ($id_vehiculo) {
                $sql .= " AND h.id_vehiculo = :id_vehiculo";
            }

            $sql .= " ORDER BY h.fecha_servicio DESC";

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":fecha_inicio", $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $fecha_fin, PDO::PARAM_STR);
            
            if ($id_vehiculo) {
                $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerServiciosPorFechas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Actualizar registro de historial
     */
    static public function mdlActualizarHistorial($id_historial, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE historialvehiculo SET
                    tipo_servicio = :tipo_servicio,
                    descripcion = :descripcion,
                    kilometraje = :kilometraje,
                    costo = :costo,
                    observaciones = :observaciones
                WHERE id_historial = :id_historial
            ");

            $stmt->bindParam(":id_historial", $id_historial, PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $datos["tipo_servicio"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":kilometraje", $datos["kilometraje"], PDO::PARAM_INT);
            $stmt->bindParam(":costo", $datos["costo"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarHistorial: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar registro de historial
     */
    static public function mdlEliminarHistorial($id_historial) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM historialvehiculo WHERE id_historial = :id_historial");
            $stmt->bindParam(":id_historial", $id_historial, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarHistorial: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener resumen de servicios por tipo (para reportes)
     */
    static public function mdlResumenServiciosPorTipo($periodo = '30') {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    tipo_servicio,
                    COUNT(*) as cantidad,
                    SUM(costo) as costo_total,
                    AVG(costo) as costo_promedio
                FROM historialvehiculo
                WHERE fecha_servicio >= CURDATE() - INTERVAL :periodo DAY
                GROUP BY tipo_servicio
                ORDER BY cantidad DESC
            ");
            $stmt->bindParam(":periodo", $periodo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlResumenServiciosPorTipo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Generar historial automático desde orden de trabajo
     */
    static public function mdlGenerarHistorialDesdeOrden($id_orden) {
        try {
            $conexion = Conexion::conectar();

            // Obtener datos de la orden
            $stmt = $conexion->prepare("SELECT * FROM ordentrabajo WHERE id_orden = :id_orden");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orden) return "orden_no_encontrada";

            // Obtener detalles de la orden
            $stmt = $conexion->prepare("SELECT * FROM orden_detalle WHERE id_orden = :id_orden");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Crear registros en historial para cada servicio
            foreach ($detalles as $detalle) {
                $datos = array(
                    "id_vehiculo" => $orden["id_vehiculo"],
                    "id_orden" => $id_orden,
                    "id_personal" => $orden["id_personal"],
                    "tipo_servicio" => $detalle["tipo_servicio"],
                    "descripcion" => $detalle["descripcion"],
                    "kilometraje" => $orden["kilometraje_actual"],
                    "costo" => $detalle["subtotal"],
                    "observaciones" => $orden["observaciones"] ?? ""
                );

                self::mdlInsertarHistorial($datos);
            }

            return "ok";
        } catch (Exception $e) {
            error_log("Error en mdlGenerarHistorialDesdeOrden: " . $e->getMessage());
            return "error";
        }
    }
}
?>