<?php
require_once "conexion.php";

class ModeloHistoricoCitas
{

    /**
     * Insertar cita en historial
     */
    static public function mdlInsertarHistorico($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO historicocitas (id_cita, id_cliente, id_vehiculo, fecha_cita, hora_cita, motivo, estado_final, observaciones)
                VALUES (:id_cita, :id_cliente, :id_vehiculo, :fecha_cita, :hora_cita, :motivo, :estado_final, :observaciones)
            ");

            $stmt->bindParam(":id_cita", $datos["id_cita"], PDO::PARAM_INT);
            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cita", $datos["fecha_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_cita", $datos["hora_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":estado_final", $datos["estado_final"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlInsertarHistorico: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener historial completo de citas
     */
    static public function mdlObtenerHistorialCompleto()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono, c.email,
                    v.matricula, v.marca, v.modelo, v.anho
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN vehiculo v ON h.id_vehiculo = v.id_vehiculo
                ORDER BY h.fecha_registro DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerHistorialCompleto: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener historial de un cliente específico
     */
    static public function mdlObtenerHistorialCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    v.matricula, v.marca, v.modelo, v.anho
                FROM historicocitas h
                LEFT JOIN vehiculo v ON h.id_vehiculo = v.id_vehiculo
                WHERE h.id_cliente = :id_cliente
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
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
     * Obtener historial de un vehículo específico
     */
    static public function mdlObtenerHistorialVehiculo($id_vehiculo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                WHERE h.id_vehiculo = :id_vehiculo
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
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
     * Obtener estadísticas del historial de citas
     */
    static public function mdlEstadisticasHistorial($periodo = 30)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN estado_final = 'completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado_final = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
                    SUM(CASE WHEN estado_final = 'no_asistio' THEN 1 ELSE 0 END) as no_asistio,
                    COUNT(DISTINCT id_cliente) as clientes_unicos,
                    COUNT(DISTINCT id_vehiculo) as vehiculos_unicos
                FROM historicocitas
                WHERE fecha_cita >= CURDATE() - INTERVAL :periodo DAY
            ");
            $stmt->bindParam(":periodo", $periodo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlEstadisticasHistorial: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener historial por rango de fechas
     */
    static public function mdlObtenerHistorialPorFechas($fecha_inicio, $fecha_fin)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono, c.email,
                    v.matricula, v.marca, v.modelo
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN vehiculo v ON h.id_vehiculo = v.id_vehiculo
                WHERE h.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
            ");
            $stmt->bindParam(":fecha_inicio", $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerHistorialPorFechas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar en historial
     */
    static public function mdlBuscarHistorial($termino)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    h.*,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono, c.email,
                    v.matricula, v.marca, v.modelo
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN vehiculo v ON h.id_vehiculo = v.id_vehiculo
                WHERE (c.nombre LIKE :termino OR c.apellido LIKE :termino2 
                      OR v.matricula LIKE :termino3 OR h.motivo LIKE :termino4)
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
            ");
            $termino_like = '%' . $termino . '%';
            $stmt->bindParam(":termino", $termino_like, PDO::PARAM_STR);
            $stmt->bindParam(":termino2", $termino_like, PDO::PARAM_STR);
            $stmt->bindParam(":termino3", $termino_like, PDO::PARAM_STR);
            $stmt->bindParam(":termino4", $termino_like, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlBuscarHistorial: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener clientes más frecuentes
     */
    static public function mdlClientesMasFrecuentes($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    c.id_cliente,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono, c.email,
                    COUNT(h.id_historico) as total_citas,
                    SUM(CASE WHEN h.estado_final = 'completada' THEN 1 ELSE 0 END) as citas_completadas,
                    MAX(h.fecha_cita) as ultima_cita
                FROM cliente c
                INNER JOIN historicocitas h ON c.id_cliente = h.id_cliente
                GROUP BY c.id_cliente
                ORDER BY total_citas DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlClientesMasFrecuentes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener motivos más comunes
     */
    static public function mdlMotivosMasComunes($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    motivo,
                    COUNT(*) as frecuencia,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM historicocitas)), 2) as porcentaje
                FROM historicocitas
                WHERE motivo IS NOT NULL AND motivo != ''
                GROUP BY motivo
                ORDER BY frecuencia DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlMotivosMasComunes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener resumen mensual
     */
    static public function mdlResumenMensual($anho = null)
    {
        try {
            if (!$anho) {
                $anho = date('Y');
            }

            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    MONTH(fecha_cita) as mes,
                    MONTHNAME(fecha_cita) as nombre_mes,
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN estado_final = 'completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado_final = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
                    SUM(CASE WHEN estado_final = 'no_asistio' THEN 1 ELSE 0 END) as no_asistio
                FROM historicocitas
                WHERE YEAR(fecha_cita) = :anho
                GROUP BY MONTH(fecha_cita), MONTHNAME(fecha_cita)
                ORDER BY mes
            ");
            $stmt->bindParam(":anho", $anho, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlResumenMensual: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener citas por estado final
     */
    static public function mdlCitasPorEstado($periodo = 30)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    estado_final,
                    COUNT(*) as cantidad,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM historicocitas WHERE fecha_cita >= CURDATE() - INTERVAL :periodo2 DAY)), 2) as porcentaje
                FROM historicocitas
                WHERE fecha_cita >= CURDATE() - INTERVAL :periodo DAY
                GROUP BY estado_final
                ORDER BY cantidad DESC
            ");
            $stmt->bindParam(":periodo", $periodo, PDO::PARAM_INT);
            $stmt->bindParam(":periodo2", $periodo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlCitasPorEstado: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Actualizar observaciones de una cita histórica
     */
    static public function mdlActualizarObservaciones($id_historico, $observaciones)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE historicocitas SET 
                    observaciones = :observaciones
                WHERE id_historico = :id_historico
            ");
            $stmt->bindParam(":id_historico", $id_historico, PDO::PARAM_INT);
            $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarObservaciones: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar registro del historial
     */
    static public function mdlEliminarHistorico($id_historico)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM historicocitas WHERE id_historico = :id_historico");
            $stmt->bindParam(":id_historico", $id_historico, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarHistorico: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Limpiar historial antiguo
     */
    static public function mdlLimpiarHistorialAntiguo($dias = 365)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                DELETE FROM historicocitas 
                WHERE fecha_cita < CURDATE() - INTERVAL :dias DAY
            ");
            $stmt->bindParam(":dias", $dias, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error en mdlLimpiarHistorialAntiguo: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Exportar historial a CSV
     */
    static public function mdlExportarHistorial($fecha_inicio = null, $fecha_fin = null)
    {
        try {
            $sql = "
                SELECT 
                    h.id_historico,
                    h.fecha_cita,
                    h.hora_cita,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    c.cedula,
                    c.telefono,
                    v.matricula,
                    v.marca,
                    v.modelo,
                    h.motivo,
                    h.estado_final,
                    h.observaciones,
                    h.fecha_registro
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN vehiculo v ON h.id_vehiculo = v.id_vehiculo
            ";

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " WHERE h.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin";
            }

            $sql .= " ORDER BY h.fecha_cita DESC, h.hora_cita DESC";

            $stmt = Conexion::conectar()->prepare($sql);

            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(":fecha_inicio", $fecha_inicio, PDO::PARAM_STR);
                $stmt->bindParam(":fecha_fin", $fecha_fin, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlExportarHistorial: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener tendencias de asistencia
     */
    static public function mdlTendenciasAsistencia($meses = 12)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    DATE_FORMAT(fecha_cita, '%Y-%m') as periodo,
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN estado_final = 'completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado_final = 'no_asistio' THEN 1 ELSE 0 END) as no_asistio,
                    ROUND((SUM(CASE WHEN estado_final = 'completada' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as tasa_asistencia
                FROM historicocitas
                WHERE fecha_cita >= CURDATE() - INTERVAL :meses MONTH
                GROUP BY DATE_FORMAT(fecha_cita, '%Y-%m')
                ORDER BY periodo DESC
            ");
            $stmt->bindParam(":meses", $meses, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlTendenciasAsistencia: " . $e->getMessage());
            return array();
        }
    }
}
?>