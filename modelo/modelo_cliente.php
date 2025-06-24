<?php
require_once "conexion.php";

class ModeloCliente {
    static public function contarClientes() {
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM cliente");
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['total'];
        $stmt = null;
    }

    static public function guardarCliente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (nombre, apellido, cedula, direccion, telefono, email) VALUES (:nombre, :apellido, :cedula, :direccion, :telefono, :email)");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : "error";
    }

    static public function buscarCliente($tabla, $item = null, $valor = null) {
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

    static public function actualizarCliente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, apellido = :apellido, cedula = :cedula, direccion = :direccion, telefono = :telefono, email = :email WHERE id_cliente = :id");

        $stmt->bindParam(":id", $datos["id_cliente"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : "error";
    }

    static public function eliminarCliente($tabla, $valor) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_cliente = :id");
        $stmt->bindParam(":id", $valor, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
