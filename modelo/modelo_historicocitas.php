<?php
require_once "conexion.php";

class ModeloHistorialCitas {

    // Método para obtener todas las citas completadas
    static public function obtenerCitasCompletadas() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT c.id_cita, cl.nombre, cl.apellido, c.fecha, c.hora, c.motivo
             FROM agendamientocita c
             INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente
             WHERE c.estado = 'completado'
             ORDER BY c.fecha DESC, c.hora DESC"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
