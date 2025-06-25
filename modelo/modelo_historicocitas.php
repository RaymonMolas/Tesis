<?php

require_once "conexion.php";

class ModeloHistoricocitas
{
    // Listar todas las citas históricas
    static public function mdlListarHistoricocitas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT h.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente,
                       c.email as email_cliente,
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN personal p ON h.id_personal = p.id_personal
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarHistoricocitas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una cita histórica específica
    static public function mdlObtenerHistoricocita($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT h.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente,
                       c.email as email_cliente,
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN personal p ON h.id_personal = p.id_personal
                WHERE h.id_historicocita = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerHistoricocita: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva cita histórica
    static public function mdlRegistrarHistoricocita($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO historicocitas (id_cliente, id_personal, fecha_cita, hora_cita, motivo, observaciones, estado, fecha_registro)
                VALUES (:id_cliente, :id_personal, :fecha_cita, :hora_cita, :motivo, :observaciones, :estado, :fecha_registro)
            ");

            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cita", $datos["fecha_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_cita", $datos["hora_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_registro", $datos["fecha_registro"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarHistoricocita: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar cita histórica
    static public function mdlActualizarHistoricocita($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE historicocitas 
                SET id_cliente = :id_cliente,
                    fecha_cita = :fecha_cita,
                    hora_cita = :hora_cita,
                    motivo = :motivo,
                    observaciones = :observaciones,
                    estado = :estado
                WHERE id_historicocita = :id_historicocita
            ");

            $stmt->bindParam(":id_historicocita", $datos["id_historicocita"], PDO::PARAM_INT);
            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cita", $datos["fecha_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_cita", $datos["hora_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarHistoricocita: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar cita histórica
    static public function mdlEliminarHistoricocita($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM historicocitas WHERE id_historicocita = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarHistoricocita: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener historial de un cliente específico
    static public function mdlObtenerHistorialCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT h.*, 
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historicocitas h
                LEFT JOIN personal p ON h.id_personal = p.id_personal
                WHERE h.id_cliente = :id_cliente 
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerHistorialCliente: " . $e->getMessage());
            return array();
        }
    }

    // Mover cita desde agendamiento a historial
    static public function mdlMoverDesdeAgendamiento($id_agendamiento)
    {
        try {
            // Obtener datos de la cita agendada
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM agendamiento 
                WHERE id_agendamiento = :id_agendamiento
            ");
            $stmt->bindParam(":id_agendamiento", $id_agendamiento, PDO::PARAM_INT);
            $stmt->execute();
            $cita = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cita) {
                return "error";
            }
            
            // Insertar en historial
            $datos = array(
                "id_cliente" => $cita["id_cliente"],
                "id_personal" => isset($_SESSION["id_personal"]) ? $_SESSION["id_personal"] : null,
                "fecha_cita" => $cita["fecha_cita"],
                "hora_cita" => $cita["hora_cita"],
                "motivo" => $cita["motivo_cita"],
                "observaciones" => $cita["observaciones"] ?? "",
                "estado" => "completada",
                "fecha_registro" => date('Y-m-d H:i:s')
            );
            
            $resultado = self::mdlRegistrarHistoricocita($datos);
            
            if ($resultado == "ok") {
                // Eliminar de agendamiento
                $stmtDelete = Conexion::conectar()->prepare("
                    DELETE FROM agendamiento WHERE id_agendamiento = :id_agendamiento
                ");
                $stmtDelete->bindParam(":id_agendamiento", $id_agendamiento, PDO::PARAM_INT);
                $stmtDelete->execute();
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en mdlMoverDesdeAgendamiento: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener citas por rango de fechas
    static public function mdlObtenerCitasPorFechas($fecha_inicio, $fecha_fin)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT h.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente,
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN personal p ON h.id_personal = p.id_personal
                WHERE h.fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
            ");
            $stmt->bindParam(":fecha_inicio", $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCitasPorFechas: " . $e->getMessage());
            return array();
        }
    }

    // Buscar citas históricas
    static public function mdlBuscarHistoricocitas($termino)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT h.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente,
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_personal
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                LEFT JOIN personal p ON h.id_personal = p.id_personal
                WHERE CONCAT(c.nombre, ' ', c.apellido) LIKE :termino
                   OR h.motivo LIKE :termino
                   OR h.observaciones LIKE :termino
                   OR c.telefono LIKE :termino
                ORDER BY h.fecha_cita DESC, h.hora_cita DESC
            ");
            $termino = "%" . $termino . "%";
            $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarHistoricocitas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de citas históricas
    static public function mdlObtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_citas,
                    COUNT(CASE WHEN estado = 'completada' THEN 1 END) as citas_completadas,
                    COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as citas_canceladas,
                    COUNT(CASE WHEN estado = 'no_asistio' THEN 1 END) as citas_no_asistio,
                    COUNT(CASE WHEN fecha_cita >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as citas_mes_actual,
                    COUNT(CASE WHEN fecha_cita >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as citas_semana_actual,
                    COUNT(DISTINCT id_cliente) as clientes_atendidos
                FROM historicocitas
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de citas históricas
    static public function mdlContarHistoricocitas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM historicocitas");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en mdlContarHistoricocitas: " . $e->getMessage());
            return 0;
        }
    }

    // Obtener citas más recientes
    static public function mdlObtenerCitasRecientes($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT h.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente
                FROM historicocitas h
                INNER JOIN cliente c ON h.id_cliente = c.id_cliente
                ORDER BY h.fecha_registro DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCitasRecientes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener reporte mensual de citas
    static public function mdlObtenerReporteMensual($año, $mes)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    DAY(fecha_cita) as dia,
                    COUNT(*) as total_citas,
                    COUNT(CASE WHEN estado = 'completada' THEN 1 END) as completadas,
                    COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as canceladas
                FROM historicocitas 
                WHERE YEAR(fecha_cita) = :año AND MONTH(fecha_cita) = :mes
                GROUP BY DAY(fecha_cita)
                ORDER BY DAY(fecha_cita)
            ");
            $stmt->bindParam(":año", $año, PDO::PARAM_INT);
            $stmt->bindParam(":mes", $mes, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerReporteMensual: " . $e->getMessage());
            return array();
        }
    }

    // Obtener motivos más comunes
    static public function mdlObtenerMotivosFrecuentes($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    motivo,
                    COUNT(*) as frecuencia
                FROM historicocitas 
                WHERE motivo IS NOT NULL AND motivo != ''
                GROUP BY motivo
                ORDER BY COUNT(*) DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerMotivosFrecuentes: " . $e->getMessage());
            return array();
        }
    }
}
?>