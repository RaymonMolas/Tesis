<?php
require_once "conexion.php";

class ModeloPersonal {

    /* GUARDAR PERSONAL */
    static public function guardarPersonal($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (nombre, apellido, cargo, telefono, email) 
                                               VALUES (:nombre, :apellido, :cargo, :telefono, :email)");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":cargo", $datos["cargo"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : "error";
    }

    /* BUSCAR PERSONAL */
    static public function buscarPersonal($tabla, $item = null, $valor = null) {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /* ACTUALIZAR PERSONAL */
    static public function actualizarPersonal($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, apellido = :apellido, cargo = :cargo, telefono = :telefono, email = :email WHERE id_personal = :id");

        $stmt->bindParam(":id", $datos["id_personal"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":cargo", $datos["cargo"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : "error";
    }

    /* ELIMINAR PERSONAL */
    static public function eliminarPersonal($tabla, $valor) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_personal = :id");
        $stmt->bindParam(":id", $valor, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>
