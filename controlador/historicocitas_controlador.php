<?php
require_once "../modelo/modelo_historicocitas.php";

class ControladorHistoricoCitas {

    // Método para obtener citas completadas
    static public function ctrObtenerCitasCompletadas() {
        $respuesta = ModeloHistorialCitas::obtenerCitasCompletadas();
        return $respuesta;
    }
}
