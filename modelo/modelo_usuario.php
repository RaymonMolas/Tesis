<?php
require_once "conexion.php";

class ModeloUsuario {

    public static function buscarUsuarios($tabla, $tipoRelacion) {
        $db = Conexion::conectar();

        if ($tipoRelacion === "cliente") {
            $sql = "SELECT uc.id_usuario_cliente, uc.usuario, uc.contrasena, uc.id_cliente,
                           CONCAT(c.nombre, ' ', c.apellido) AS nombre
                    FROM usuariocliente uc
                    INNER JOIN cliente c ON uc.id_cliente = c.id_cliente";
        } else {
            $sql = "SELECT up.id_usuario_personal, up.usuario, up.contrasena, up.id_personal,
                           CONCAT(p.nombre, ' ', p.apellido) AS nombre
                    FROM usuariopersonal up
                    INNER JOIN personal p ON up.id_personal = p.id_personal";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function eliminarUsuario($tabla, $campo, $valor) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE $campo = :id");
        $stmt->bindParam(":id", $valor, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    public static function guardarUsuario($tabla, $datos) {
        $stmt = null;
        $db = Conexion::conectar();
    
        if ($tabla === "usuariocliente") {
            $stmt = $db->prepare("INSERT INTO $tabla (id_cliente, usuario, contrasena) VALUES (:id, :usuario, :contrasena)");
            $id = $datos["id_cliente"];
        } else {
            $stmt = $db->prepare("INSERT INTO $tabla (id_personal, usuario, contrasena) VALUES (:id, :usuario, :contrasena)");
            $id = $datos["id_personal"];
        }
    
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
        $stmt->bindParam(":contrasena", $datos["contrasena"], PDO::PARAM_STR);
    
        return $stmt->execute() ? "ok" : "error";
    }

    public static function buscarUsuarioPorId($tabla, $campo, $id) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $campo = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public static function actualizarUsuario($tabla, $campo, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET usuario = :usuario, contrasena = :contrasena WHERE $campo = :id");
    
        $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
        $stmt->bindParam(":contrasena", $datos["contrasena"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
    
        return $stmt->execute() ? "ok" : "error";
    }
    
}
