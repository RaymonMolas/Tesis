<?php
require_once "conexion.php";

class ModeloAgendamiento {

    static public function guardarCita($datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO AgendamientoCita (id_cliente, fecha, hora, motivo, estado)
                                               VALUES (:id_cliente, :fecha, :hora, :motivo, :estado)");
        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
        $stmt->bindParam(":hora", $datos["hora"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function obtenerCitas() {
        $stmt = Conexion::conectar()->prepare("SELECT a.id_cita, a.fecha, a.hora, a.motivo, a.estado, 
                                                      CONCAT(c.nombre, ' ', c.apellido) AS cliente
                                               FROM AgendamientoCita a
                                               JOIN cliente c ON a.id_cliente = c.id_cliente
                                               WHERE a.estado = 'aprobado'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function listarPendientes() {
        $stmt = Conexion::conectar()->prepare("SELECT a.id_cita, a.fecha, a.hora, a.motivo, a.estado, 
                                                      CONCAT(c.nombre, ' ', c.apellido) AS cliente
                                               FROM AgendamientoCita a
                                               JOIN cliente c ON a.id_cliente = c.id_cliente
                                               WHERE a.estado = 'pendiente'
                                               ORDER BY a.fecha ASC, a.hora ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function actualizarEstado($id, $estado) {
        $conexion = Conexion::conectar();

        // Obtener la fecha de la cita que se desea aprobar
        $stmt = $conexion->prepare("SELECT fecha, id_cliente FROM AgendamientoCita WHERE id_cita = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cita) return "cita_no_encontrada";

        if ($estado === "aprobado") {
            // Contar cuántas citas ya están aprobadas en esa fecha
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM AgendamientoCita WHERE fecha = :fecha AND estado = 'aprobado'");
            $stmt->bindParam(":fecha", $cita["fecha"], PDO::PARAM_STR);
            $stmt->execute();
            $cantidad = $stmt->fetchColumn();

            if ($cantidad >= 6) {
                return "limite_excedido";
            }
        }

        // Actualizar estado
        $stmt = $conexion->prepare("UPDATE AgendamientoCita SET estado = :estado WHERE id_cita = :id");
        $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($estado === "aprobado") {
                $fecha = isset($cita["fecha"]) ? date("d/m/Y", strtotime($cita["fecha"])) : "Fecha desconocida";
                $hora = isset($cita["hora"]) && $cita["hora"] !== null ? date("H:i", strtotime($cita["hora"])) : "Hora desconocida";
        
                $mensaje = "Tu cita para el $fecha a las $hora ha sido aprobada. ✅";
                self::insertarNotificacion($cita["id_cliente"], $mensaje);
            }
            return "ok";
        }
    }

    static public function obtenerCitaPorId($id) {
        $stmt = Conexion::conectar()->prepare("SELECT a.*, CONCAT(c.nombre, ' ', c.apellido) AS cliente 
                                               FROM AgendamientoCita a
                                               JOIN cliente c ON a.id_cliente = c.id_cliente
                                               WHERE a.id_cita = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function clienteTieneCitaActiva($id_cliente) {
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM AgendamientoCita 
                                               WHERE id_cliente = :id_cliente 
                                               AND estado IN ('pendiente', 'aprobado')");
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    static public function obtenerCitasCliente($id_cliente) {
        $stmt = Conexion::conectar()->prepare("SELECT id_cita, fecha, hora, motivo, estado 
                                               FROM AgendamientoCita 
                                               WHERE id_cliente = :id_cliente");
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerClientesSinCitaActiva() {
        $sql = "SELECT id_cliente FROM cliente WHERE id_cliente NOT IN (
                    SELECT id_cliente FROM agendamientocita
                    WHERE estado IN ('pendiente', 'aprobado')
                )";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insertarNotificacion($id_cliente, $mensaje) {
        $sql = "INSERT INTO notificaciones_cliente (id_cliente, mensaje) VALUES (:id_cliente, :mensaje)";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(":mensaje", $mensaje, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public static function obtenerNotificacionesCliente($id_cliente) {
        $sql = "SELECT * FROM notificaciones_cliente WHERE id_cliente = :id_cliente ORDER BY fecha_creacion DESC";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function marcarNotificacionesLeidas($id_cliente) {
        $sql = "UPDATE notificaciones_cliente SET leida = 1 WHERE id_cliente = :id_cliente";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        return $stmt->execute();
    }
    static public function contarCitasActivasPorFecha($fecha) {
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM AgendamientoCita WHERE fecha = :fecha AND estado = 'aprobado'");
        $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?>