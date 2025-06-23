<?php
require_once "conexion.php";

class otmodelo
{
    // Obtener siguiente ID automático
    static public function buscarid() {
        $stmt = Conexion::conectar()->prepare("SELECT IFNULL(MAX(id_orden), 0) + 1 AS nuevo_id FROM ordentrabajo");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Buscar clientes (puedes luego cambiar por JOIN con vehículos si hace falta)
    static public function buscarprofactura($item, $valor) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM cliente");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Insertar la orden de trabajo
    static public function guardarfactura($datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO ordentrabajo 
            (id_orden, id_vehiculo, id_personal, fecha_ingreso, fecha_salida, estado, descripcion)
            VALUES (:id_orden, :id_vehiculo, :id_personal, :fecha_ingreso, :fecha_salida, :estado, :descripcion)");

        $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
        $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
        $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha_ingreso", $datos["fecha_ingreso"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_salida", $datos["fecha_salida"], PDO::PARAM_NULL);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : "error";
    }
}
?>
