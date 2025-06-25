<?php

require_once "conexion.php";

class ModeloAgendamiento
{
    // Listar todas las citas agendadas
    static public function mdlListarAgendamientos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente,
                       c.email as email_cliente
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                ORDER BY a.fecha_cita DESC, a.hora_cita DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarAgendamientos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una cita específica
    static public function mdlObtenerAgendamiento($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente,
                       c.email as email_cliente
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.id_agendamiento = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerAgendamiento: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva cita
    static public function mdlRegistrarAgendamiento($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO agendamiento (id_cliente, fecha_cita, hora_cita, motivo_cita, observaciones, estado, fecha_solicitud)
                VALUES (:id_cliente, :fecha_cita, :hora_cita, :motivo_cita, :observaciones, :estado, :fecha_solicitud)
            ");

            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cita", $datos["fecha_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_cita", $datos["hora_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo_cita", $datos["motivo_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_solicitud", $datos["fecha_solicitud"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarAgendamiento: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar cita
    static public function mdlActualizarAgendamiento($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE agendamiento 
                SET fecha_cita = :fecha_cita,
                    hora_cita = :hora_cita,
                    motivo_cita = :motivo_cita,
                    observaciones = :observaciones,
                    estado = :estado
                WHERE id_agendamiento = :id_agendamiento
            ");

            $stmt->bindParam(":id_agendamiento", $datos["id_agendamiento"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cita", $datos["fecha_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_cita", $datos["hora_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo_cita", $datos["motivo_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarAgendamiento: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar cita
    static public function mdlEliminarAgendamiento($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM agendamiento WHERE id_agendamiento = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarAgendamiento: " . $e->getMessage());
            return "error";
        }
    }

    // Cambiar estado de cita
    static public function mdlCambiarEstadoCita($id, $estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE agendamiento 
                SET estado = :estado 
                WHERE id_agendamiento = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlCambiarEstadoCita: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener citas de un cliente
    static public function mdlObtenerCitasCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM agendamiento 
                WHERE id_cliente = :id_cliente 
                ORDER BY fecha_cita DESC, hora_cita DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCitasCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener citas por fecha
    static public function mdlObtenerCitasPorFecha($fecha)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.fecha_cita = :fecha
                ORDER BY a.hora_cita
            ");
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCitasPorFecha: " . $e->getMessage());
            return array();
        }
    }

    // Obtener citas pendientes
    static public function mdlObtenerCitasPendientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.estado = 'pendiente'
                ORDER BY a.fecha_cita, a.hora_cita
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCitasPendientes: " . $e->getMessage());
            return array();
        }
    }

    // Verificar disponibilidad de horario
    static public function mdlVerificarDisponibilidad($fecha, $hora, $id_agendamiento = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM agendamiento 
                    WHERE fecha_cita = :fecha AND hora_cita = :hora 
                    AND estado IN ('pendiente', 'confirmada')";
            
            if ($id_agendamiento) {
                $sql .= " AND id_agendamiento != :id_agendamiento";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":hora", $hora, PDO::PARAM_STR);
            
            if ($id_agendamiento) {
                $stmt->bindParam(":id_agendamiento", $id_agendamiento, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] == 0;
        } catch (PDOException $e) {
            error_log("Error en mdlVerificarDisponibilidad: " . $e->getMessage());
            return false;
        }
    }

    // Obtener estadísticas de agendamientos
    static public function mdlObtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_citas,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as citas_pendientes,
                    COUNT(CASE WHEN estado = 'confirmada' THEN 1 END) as citas_confirmadas,
                    COUNT(CASE WHEN estado = 'completada' THEN 1 END) as citas_completadas,
                    COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as citas_canceladas,
                    COUNT(CASE WHEN fecha_cita = CURDATE() THEN 1 END) as citas_hoy,
                    COUNT(CASE WHEN fecha_cita >= CURDATE() AND fecha_cita <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as citas_semana
                FROM agendamiento
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Buscar citas
    static public function mdlBuscarCitas($termino)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE CONCAT(c.nombre, ' ', c.apellido) LIKE :termino
                   OR a.motivo_cita LIKE :termino
                   OR c.telefono LIKE :termino
                ORDER BY a.fecha_cita DESC, a.hora_cita DESC
            ");
            $termino = "%" . $termino . "%";
            $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarCitas: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de citas
    static public function mdlContarCitas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM agendamiento");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en mdlContarCitas: " . $e->getMessage());
            return 0;
        }
    }

    // Obtener próximas citas
    static public function mdlObtenerProximasCitas($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.fecha_cita >= CURDATE() 
                AND a.estado IN ('pendiente', 'confirmada')
                ORDER BY a.fecha_cita, a.hora_cita
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProximasCitas: " . $e->getMessage());
            return array();
        }
    }
}
?>