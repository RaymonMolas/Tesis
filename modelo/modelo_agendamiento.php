<?php
require_once "conexion.php";

class ModeloAgendamiento
{

    /**
     * Guardar una nueva cita
     */
    static public function guardarCita($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO agendamiento (id_cliente, id_vehiculo, fecha_cita, hora_cita, motivo, observaciones, estado) 
                VALUES (:id_cliente, :id_vehiculo, :fecha_cita, :hora_cita, :motivo, :observaciones, :estado)
            ");

            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cita", $datos["fecha"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_cita", $datos["hora"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"] ?? "", PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlGuardarCita: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener citas confirmadas para mostrar en calendario
     */
    static public function obtenerCitas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    a.id_cita, a.fecha_cita, a.hora_cita, a.motivo, a.estado, a.observaciones,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(p.nombre, ' ', p.apellido) AS personal_asignado
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                LEFT JOIN vehiculo v ON a.id_vehiculo = v.id_vehiculo
                LEFT JOIN personal p ON a.id_personal = p.id_personal
                WHERE a.estado = 'confirmada'
                ORDER BY a.fecha_cita ASC, a.hora_cita ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCitas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Listar solicitudes pendientes
     */
    static public function listarPendientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    a.id_cita, a.fecha_cita, a.hora_cita, a.motivo, a.estado, a.observaciones,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    c.telefono, c.email,
                    v.matricula, v.marca, v.modelo
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                LEFT JOIN vehiculo v ON a.id_vehiculo = v.id_vehiculo
                WHERE a.estado = 'pendiente'
                ORDER BY a.fecha_cita ASC, a.hora_cita ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlListarPendientes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Actualizar estado de una cita
     */
    static public function actualizarEstado($id, $estado)
    {
        try {
            $conexion = Conexion::conectar();

            // Obtener información de la cita
            $stmt = $conexion->prepare("
                SELECT fecha_cita, id_cliente, hora_cita 
                FROM agendamiento 
                WHERE id_cita = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $cita = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cita)
                return "cita_no_encontrada";

            // Si se confirma, verificar límite de 6 citas por día
            if ($estado === "confirmada") {
                $stmt = $conexion->prepare("
                    SELECT COUNT(*) 
                    FROM agendamiento 
                    WHERE fecha_cita = :fecha AND estado = 'confirmada'
                ");
                $stmt->bindParam(":fecha", $cita["fecha_cita"], PDO::PARAM_STR);
                $stmt->execute();
                $cantidad = $stmt->fetchColumn();

                if ($cantidad >= 6) {
                    return "limite_excedido";
                }
            }

            // Actualizar estado
            $stmt = $conexion->prepare("
                UPDATE agendamiento 
                SET estado = :estado, fecha_actualizacion = CURRENT_TIMESTAMP 
                WHERE id_cita = :id
            ");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Si se confirma, enviar notificación
                if ($estado === "confirmada") {
                    $fecha = date("d/m/Y", strtotime($cita["fecha_cita"]));
                    $hora = date("H:i", strtotime($cita["hora_cita"]));
                    $mensaje = "Tu cita para el $fecha a las $hora ha sido confirmada. ✅";
                    self::insertarNotificacion($cita["id_cliente"], $mensaje);
                }
                return "ok";
            }
            return "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarEstado: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener cita por ID
     */
    static public function obtenerCitaPorId($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    a.*, 
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    c.telefono, c.email, c.direccion,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(p.nombre, ' ', p.apellido) AS personal_asignado
                FROM agendamiento a
                INNER JOIN cliente c ON a.id_cliente = c.id_cliente
                LEFT JOIN vehiculo v ON a.id_vehiculo = v.id_vehiculo
                LEFT JOIN personal p ON a.id_personal = p.id_personal
                WHERE a.id_cita = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCitaPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un cliente tiene cita activa
     */
    static public function clienteTieneCitaActiva($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) 
                FROM agendamiento 
                WHERE id_cliente = :id_cliente 
                AND estado IN ('pendiente', 'confirmada')
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en mdlClienteTieneCitaActiva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener citas de un cliente específico
     */
    static public function obtenerCitasCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    a.id_cita, a.fecha_cita, a.hora_cita, a.motivo, a.estado, a.observaciones,
                    v.matricula, v.marca, v.modelo
                FROM agendamiento a
                LEFT JOIN vehiculo v ON a.id_vehiculo = v.id_vehiculo
                WHERE a.id_cliente = :id_cliente
                ORDER BY a.fecha_cita DESC, a.hora_cita DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerCitasCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Contar citas activas por fecha
     */
    static public function contarCitasActivasPorFecha($fecha)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) 
                FROM agendamiento 
                WHERE fecha_cita = :fecha AND estado = 'confirmada'
            ");
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error en mdlContarCitasActivasPorFecha: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mover cita completada al historial
     */
    static public function moverCitaAHistorial($id_cita, $estado_final)
    {
        try {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();

            // Obtener datos de la cita
            $stmt = $conexion->prepare("SELECT * FROM agendamiento WHERE id_cita = :id");
            $stmt->bindParam(":id", $id_cita, PDO::PARAM_INT);
            $stmt->execute();
            $cita = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cita) {
                $conexion->rollBack();
                return "error";
            }

            // Insertar en historial
            $stmt = $conexion->prepare("
                INSERT INTO historicocitas 
                (id_cita, id_cliente, id_vehiculo, fecha_cita, hora_cita, motivo, estado_final, observaciones)
                VALUES (:id_cita, :id_cliente, :id_vehiculo, :fecha_cita, :hora_cita, :motivo, :estado_final, :observaciones)
            ");
            $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
            $stmt->bindParam(":id_cliente", $cita["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vehiculo", $cita["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cita", $cita["fecha_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_cita", $cita["hora_cita"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $cita["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":estado_final", $estado_final, PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $cita["observaciones"], PDO::PARAM_STR);
            $stmt->execute();

            // Actualizar estado de la cita original
            $stmt = $conexion->prepare("
                UPDATE agendamiento 
                SET estado = :estado_final 
                WHERE id_cita = :id
            ");
            $stmt->bindParam(":estado_final", $estado_final, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id_cita, PDO::PARAM_INT);
            $stmt->execute();

            $conexion->commit();
            return "ok";
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlMoverCitaAHistorial: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Insertar notificación para cliente
     */
    static public function insertarNotificacion($id_cliente, $mensaje)
    {
        try {
            // Crear tabla de notificaciones si no existe
            $conexion = Conexion::conectar();
            $conexion->exec("
                CREATE TABLE IF NOT EXISTS notificaciones_cliente (
                    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
                    id_cliente INT NOT NULL,
                    mensaje TEXT NOT NULL,
                    leida TINYINT(1) DEFAULT 0,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE
                )
            ");

            $stmt = $conexion->prepare("
                INSERT INTO notificaciones_cliente (id_cliente, mensaje) 
                VALUES (:id_cliente, :mensaje)
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(":mensaje", $mensaje, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en mdlInsertarNotificacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener notificaciones de un cliente
     */
    static public function obtenerNotificacionesCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM notificaciones_cliente 
                WHERE id_cliente = :id_cliente 
                ORDER BY fecha_creacion DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerNotificacionesCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Marcar notificaciones como leídas
     */
    static public function marcarNotificacionesLeidas($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE notificaciones_cliente 
                SET leida = 1 
                WHERE id_cliente = :id_cliente
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en mdlMarcarNotificacionesLeidas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de agendamiento
     */
    static public function obtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
                FROM agendamiento
                WHERE fecha_cita >= CURDATE() - INTERVAL 30 DAY
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Asignar personal a una cita
     */
    static public function asignarPersonal($id_cita, $id_personal)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE agendamiento 
                SET id_personal = :id_personal, fecha_actualizacion = CURRENT_TIMESTAMP 
                WHERE id_cita = :id_cita
            ");
            $stmt->bindParam(":id_personal", $id_personal, PDO::PARAM_INT);
            $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlAsignarPersonal: " . $e->getMessage());
            return "error";
        }
    }
}
?>