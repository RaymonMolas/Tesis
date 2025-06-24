<?php
require_once "../modelo/modelo_agendamiento.php";

class ControladorAgendamiento {
    // Obtener citas de un cliente específico
    static public function obtenerCitasCliente($id_cliente) {
        return ModeloAgendamiento::obtenerCitasCliente($id_cliente);
    }


    // Guardar una nueva cita (desde el cliente)
    static public function guardarCita() {
        if (isset($_POST["id_cliente"])) {
            // Validar que no tenga cita activa
            if (ModeloAgendamiento::clienteTieneCitaActiva($_POST["id_cliente"])) {
                return "ya_tiene_cita";
            }

            $datos = array(
                "id_cliente" => $_POST["id_cliente"],
                "fecha"      => $_POST["fecha"],
                "hora"       => $_POST["hora"],
                "motivo"     => $_POST["motivo"],
                "estado"     => "pendiente"
            );
            return ModeloAgendamiento::guardarCita($datos);
        }
    }

    // Obtener citas aprobadas (para mostrar en calendario)
    static public function obtenerCitas() {
        return ModeloAgendamiento::obtenerCitas();
    }

    // Obtener solicitudes pendientes (para campana y modal)
    static public function listarSolicitudesPendientes() {
        return ModeloAgendamiento::listarPendientes();
    }

    // Actualizar el estado de una cita (aprobado o rechazado)
    static public function actualizarEstado($id, $estado) {
        $cita = ModeloAgendamiento::obtenerCitaPorId($id);
        if (!$cita) return "no_encontrada";

        if ($estado === "aprobado") {
            $fecha = $cita["fecha"];
            $hora = $cita["hora"];
            $totalActivas = ModeloAgendamiento::contarCitasActivasPorFecha($fecha);
            if ($totalActivas >= 6) return "limite_excedido";
        
            // Enviar notificación con fecha y hora
            $fechaFormateada = date("d/m/Y", strtotime($fecha));
            $horaFormateada = date("H:i", strtotime($hora));
            $mensaje = "Tu cita para el $fechaFormateada a las $horaFormateada ha sido aprobada. ✅";
            ModeloAgendamiento::insertarNotificacion($cita["id_cliente"], $mensaje);
        }

        return ModeloAgendamiento::actualizarEstado($id, $estado);
    }
    
    // Obtener detalles de una cita por ID (para cargar al modal)
    static public function obtenerCitaPorId($id) {
        return ModeloAgendamiento::obtenerCitaPorId($id);
    }

    // Notificaciones para clientes
    static public function obtenerNotificacionesCliente($id_cliente) {
        return ModeloAgendamiento::obtenerNotificacionesCliente($id_cliente);
    }

    static public function marcarNotificacionesLeidas($id_cliente) {
        return ModeloAgendamiento::marcarNotificacionesLeidas($id_cliente);
    }
}
?>