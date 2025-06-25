<?php

require_once "conexion.php";

class ModeloAgendamiento
{
    // Guardar nueva cita
    static public function guardarCita($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO AgendamientoCita (id_cliente, fecha, hora, motivo, estado)
                VALUES (:id_cliente, :fecha, :hora, :motivo, :estado)
            ");
            
            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
            $stmt->bindParam(":hora", $datos["hora"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en guardarCita: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener citas aprobadas
    static public function obtenerCitas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.id_cita, a.fecha, a.hora, a.motivo, a.estado, 
                       CONCAT(c.nombre, ' ', c.apellido) AS cliente
                FROM AgendamientoCita a
                JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.estado = 'aprobado'
                ORDER BY a.fecha ASC, a.hora ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCitas: " . $e->getMessage());
            return array();
        }
    }

    // Listar citas pendientes
    static public function listarPendientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.id_cita, a.fecha, a.hora, a.motivo, a.estado, 
                       CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                       c.telefono as telefono_cliente
                FROM AgendamientoCita a
                JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.estado = 'pendiente'
                ORDER BY a.fecha ASC, a.hora ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarPendientes: " . $e->getMessage());
            return array();
        }
    }

    // Actualizar estado de cita
    static public function actualizarEstado($id, $estado)
    {
        try {
            $conexion = Conexion::conectar();

            // Obtener la fecha de la cita que se desea aprobar
            $stmt = $conexion->prepare("SELECT fecha, id_cliente FROM AgendamientoCita WHERE id_cita = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $cita = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cita) return "cita_no_encontrada";

            if ($estado === "aprobado") {
                // Contar cuántas citas ya están aprobadas en esa fecha
                $stmt = $conexion->prepare("
                    SELECT COUNT(*) FROM AgendamientoCita 
                    WHERE fecha = :fecha AND estado = 'aprobado'
                ");
                $stmt->bindParam(":fecha", $cita["fecha"], PDO::PARAM_STR);
                $stmt->execute();
                $cantidad = $stmt->fetchColumn();

                if ($cantidad >= 6) {
                    return "limite_excedido";
                }
            }

            // Actualizar estado
            $stmt = $conexion->prepare("
                UPDATE AgendamientoCita 
                SET estado = :estado 
                WHERE id_cita = :id
            ");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if ($estado === "aprobado") {
                    $fecha = isset($cita["fecha"]) ? date("d/m/Y", strtotime($cita["fecha"])) : "Fecha desconocida";
                    $hora = "Hora por confirmar"; // Se puede mejorar obteniendo la hora real
            
                    $mensaje = "Tu cita para el $fecha ha sido aprobada. ✅";
                    self::insertarNotificacion($cita["id_cliente"], $mensaje);
                }

                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en actualizarEstado: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener cita por ID
    static public function obtenerCitaPorId($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                       c.telefono as telefono_cliente
                FROM AgendamientoCita a
                JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.id_cita = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCitaPorId: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si cliente tiene cita activa
    static public function clienteTieneCitaActiva($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total
                FROM AgendamientoCita 
                WHERE id_cliente = :id_cliente 
                AND estado IN ('pendiente', 'aprobado')
                AND fecha >= CURDATE()
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en clienteTieneCitaActiva: " . $e->getMessage());
            return false;
        }
    }

    // Contar citas activas por fecha
    static public function contarCitasActivasPorFecha($fecha)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total
                FROM AgendamientoCita 
                WHERE fecha = :fecha AND estado = 'aprobado'
            ");
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarCitasActivasPorFecha: " . $e->getMessage());
            return 0;
        }
    }

    // Obtener citas de un cliente específico
    static public function obtenerCitasCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM AgendamientoCita 
                WHERE id_cliente = :id_cliente
                ORDER BY fecha DESC, hora DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCitasCliente: " . $e->getMessage());
            return array();
        }
    }

    // Insertar notificación
    static public function insertarNotificacion($id_cliente, $mensaje)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO notificaciones_cliente (id_cliente, mensaje, fecha, leido)
                VALUES (:id_cliente, :mensaje, NOW(), 0)
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(":mensaje", $mensaje, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en insertarNotificacion: " . $e->getMessage());
            return false;
        }
    }

    // Obtener notificaciones de cliente
    static public function obtenerNotificacionesCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM notificaciones_cliente 
                WHERE id_cliente = :id_cliente
                ORDER BY fecha DESC
                LIMIT 10
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerNotificacionesCliente: " . $e->getMessage());
            return array();
        }
    }

    // Marcar notificaciones como leídas
    static public function marcarNotificacionesLeidas($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE notificaciones_cliente 
                SET leido = 1 
                WHERE id_cliente = :id_cliente AND leido = 0
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en marcarNotificacionesLeidas: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener estadísticas de citas
    static public function obtenerEstadisticasCitas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_citas,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                    COUNT(CASE WHEN estado = 'aprobado' THEN 1 END) as aprobadas,
                    COUNT(CASE WHEN estado = 'rechazado' THEN 1 END) as rechazadas,
                    COUNT(CASE WHEN DATE(fecha) = CURDATE() THEN 1 END) as hoy,
                    COUNT(CASE WHEN WEEK(fecha) = WEEK(NOW()) THEN 1 END) as esta_semana
                FROM AgendamientoCita
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasCitas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener citas por fecha
    static public function obtenerCitasPorFecha($fecha)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT a.*, CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                       c.telefono as telefono_cliente
                FROM AgendamientoCita a
                JOIN cliente c ON a.id_cliente = c.id_cliente
                WHERE a.fecha = :fecha
                ORDER BY a.hora ASC
            ");
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerCitasPorFecha: " . $e->getMessage());
            return array();
        }
    }

    // Cancelar cita
    static public function cancelarCita($id_cita, $motivo_cancelacion = null)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE AgendamientoCita 
                SET estado = 'cancelado',
                    motivo_cancelacion = :motivo
                WHERE id_cita = :id
            ");
            $stmt->bindParam(":id", $id_cita, PDO::PARAM_INT);
            $stmt->bindParam(":motivo", $motivo_cancelacion, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en cancelarCita: " . $e->getMessage());
            return "error";
        }
    }

    // Verificar disponibilidad de fecha/hora
    static public function verificarDisponibilidad($fecha, $hora)
    {
        try {
            // Verificar si ya hay 6 citas aprobadas en esa fecha
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total
                FROM AgendamientoCita 
                WHERE fecha = :fecha AND estado = 'aprobado'
            ");
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado['total'] >= 6) {
                return false;
            }

            // Verificar si ya hay una cita a esa hora específica
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total
                FROM AgendamientoCita 
                WHERE fecha = :fecha AND hora = :hora AND estado IN ('aprobado', 'pendiente')
            ");
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":hora", $hora, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['total'] == 0;
        } catch (PDOException $e) {
            error_log("Error en verificarDisponibilidad: " . $e->getMessage());
            return false;
        }
    }
}
?>