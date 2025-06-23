<?php
require_once "../modelo/modelo_ot.php";

class otcontrolador {

    // Buscar ID automático para nueva OT
    static public function buscarid() {
        return otmodelo::buscarid();
    }

    // Buscar clientes para mostrar en el modal
    static public function buscarprofactura($item, $valor) {
        return otmodelo::buscarprofactura($item, $valor);
    }

    // Guardar la orden de trabajo
    static public function guardarfactura() {
        if (isset($_POST["txtcodigo"])) {
            $datos = [
                "id_orden" => $_POST["txtcodigo"],
                "id_vehiculo" => $_POST["txtvehiculoid"], // debes tener este input oculto
                "id_personal" => $_SESSION["id_personal"] ?? 1, // ejemplo: el personal que registra la OT
                "fecha_ingreso" => $_POST["txtfecha"],
                "fecha_salida" => null, // aún no se conoce
                "estado" => "pendiente", // estado inicial
                "descripcion" => self::concatenarCheckboxes()
            ];
            return otmodelo::guardarfactura($datos);
        }
    }

    // Concatenar trabajos seleccionados por checkboxes
    static private function concatenarCheckboxes() {
        $checks = [
            'checkAceiteMotor' => 'Aceite Motor',
            'checkAceiteDTrasero' => 'Aceite diferencial Trasero',
            'checkFiltroCombustible' => 'Filtro de combustible',
            'checkAceiteCaja' => 'Aceite Caja',
            'CheckFiltroAceite' => 'Filtro de aceite',
            'checkAditivoRadiador' => 'Aditivo Radiador',
            'checkAceiteDelantero' => 'Aceite diferencial delantero',
            'checkFiltroAire' => 'Filtro de aire',
            'checkFluidoFreno' => 'Fluido de Freno'
        ];
        $descripcion = [];
        foreach ($checks as $campo => $texto) {
            if (isset($_POST[$campo])) {
                $descripcion[] = $texto;
            }
        }
        return implode(", ", $descripcion);
    }
}
?>
