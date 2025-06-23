<?php
require_once "conexion.php";

class ModeloProducto {
    // Listar todos los productos
    static public function mdlListarProductos() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE estado = 'activo'
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarProductos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un producto específico
    static public function mdlObtenerProducto($id) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE id_producto = :id
            ");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProducto: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo producto
    static public function mdlRegistrarProducto($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO producto (codigo, nombre, descripcion, precio, stock, estado)
                VALUES (:codigo, :nombre, :descripcion, :precio, :stock, :estado)
            ");

            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarProducto: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar producto
    static public function mdlActualizarProducto($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE producto 
                SET codigo = :codigo,
                    nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    stock = :stock,
                    estado = :estado
                WHERE id_producto = :id_producto
            ");

            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarProducto: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar producto (cambiar estado a inactivo)
    static public function mdlEliminarProducto($id) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE producto 
                SET estado = 'inactivo' 
                WHERE id_producto = :id
            ");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarProducto: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar stock
    static public function mdlActualizarStock($id_producto, $cantidad) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE producto 
                SET stock = stock - :cantidad 
                WHERE id_producto = :id_producto
            ");
            
            $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
            $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarStock: " . $e->getMessage());
            return "error";
        }
    }
}
