<?php

require_once "conexion.php";

class ModeloVehiculo {
    // Contar total de vehículos
    static public function mdlContarVehiculos() {
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM vehiculo");
        $stmt->execute();
        $resultado = $stmt->fetch();
        return $resultado['total'];
        $stmt = null;
    }

    // Listar vehículos de un cliente específico
    static public function mdlListarVehiculosCliente($id_cliente) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM vehiculo WHERE id_cliente = :id_cliente");
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt = null;
    }

    // Listar todos los vehículos
    static public function mdlListarVehiculos() {
        $stmt = Conexion::conectar()->prepare("SELECT v.*, c.nombre as nombre_cliente 
            FROM vehiculo v 
            LEFT JOIN cliente c ON v.id_cliente = c.id_cliente 
            ORDER BY v.id_vehiculo DESC");
        
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt = null;
    }

    // Obtener un vehículo específico
    static public function mdlObtenerVehiculo($id) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM vehiculo WHERE id_vehiculo = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
        $stmt = null;
    }

    // Registrar nuevo vehículo
    static public function mdlRegistrarVehiculo($datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO vehiculo (matricula, marca, modelo, anho, color, id_cliente) 
            VALUES (:matricula, :marca, :modelo, :anho, :color, :id_cliente)");

        $stmt->bindParam(":matricula", $datos["matricula"], PDO::PARAM_STR);
        $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
        $stmt->bindParam(":modelo", $datos["modelo"], PDO::PARAM_STR);
        $stmt->bindParam(":anho", $datos["anho"], PDO::PARAM_INT);
        $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt = null;
    }

    // Actualizar vehículo
    static public function mdlActualizarVehiculo($datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE vehiculo 
            SET matricula = :matricula, 
                marca = :marca, 
                modelo = :modelo, 
                anho = :anho, 
                color = :color, 
                id_cliente = :id_cliente 
            WHERE id_vehiculo = :id_vehiculo");

        $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
        $stmt->bindParam(":matricula", $datos["matricula"], PDO::PARAM_STR);
        $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
        $stmt->bindParam(":modelo", $datos["modelo"], PDO::PARAM_STR);
        $stmt->bindParam(":anho", $datos["anho"], PDO::PARAM_INT);
        $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt = null;
    }

    // Eliminar vehículo
    static public function mdlEliminarVehiculo($id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM vehiculo WHERE id_vehiculo = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt = null;
    }
}
