<?php

require_once "conexion.php";

class ModeloCliente
{
    // Contar total de clientes
    static public function mdlContarClientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM cliente");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en mdlContarClientes: " . $e->getMessage());
            return 0;
        }
    }

    // Listar todos los clientes
    static public function mdlListarClientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.*, 
                       COUNT(v.id_vehiculo) as total_vehiculos
                FROM cliente c
                LEFT JOIN vehiculo v ON c.id_cliente = v.id_cliente
                GROUP BY c.id_cliente
                ORDER BY c.nombre, c.apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarClientes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un cliente específico
    static public function mdlObtenerCliente($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.*,
                       COUNT(v.id_vehiculo) as total_vehiculos,
                       COUNT(o.id_orden) as total_ordenes
                FROM cliente c
                LEFT JOIN vehiculo v ON c.id_cliente = v.id_cliente
                LEFT JOIN ordentrabajo o ON v.id_vehiculo = o.id_vehiculo
                WHERE c.id_cliente = :id
                GROUP BY c.id_cliente
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCliente: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo cliente
    static public function mdlRegistrarCliente($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO cliente (nombre, apellido, telefono, email, direccion, fecha_registro)
                VALUES (:nombre, :apellido, :telefono, :email, :direccion, NOW())
            ");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarCliente: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar cliente
    static public function mdlActualizarCliente($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE cliente 
                SET nombre = :nombre,
                    apellido = :apellido,
                    telefono = :telefono,
                    email = :email,
                    direccion = :direccion
                WHERE id_cliente = :id_cliente
            ");

            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarCliente: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar cliente
    static public function mdlEliminarCliente($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM cliente WHERE id_cliente = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                
                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró el cliente con ID: " . $id);
                    return "error";
                }
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarCliente: " . $e->getMessage());
            return "error";
        }
    }

    // Buscar clientes por nombre
    static public function mdlBuscarPorNombre($nombre)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.*, 
                       COUNT(v.id_vehiculo) as total_vehiculos
                FROM cliente c
                LEFT JOIN vehiculo v ON c.id_cliente = v.id_cliente
                WHERE CONCAT(c.nombre, ' ', c.apellido) LIKE :nombre
                GROUP BY c.id_cliente
                ORDER BY c.nombre, c.apellido
            ");
            $nombreBusqueda = "%" . $nombre . "%";
            $stmt->bindParam(":nombre", $nombreBusqueda, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarPorNombre: " . $e->getMessage());
            return array();
        }
    }

    // Buscar clientes por teléfono
    static public function mdlBuscarPorTelefono($telefono)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.*, 
                       COUNT(v.id_vehiculo) as total_vehiculos
                FROM cliente c
                LEFT JOIN vehiculo v ON c.id_cliente = v.id_cliente
                WHERE c.telefono LIKE :telefono
                GROUP BY c.id_cliente
                ORDER BY c.nombre, c.apellido
            ");
            $telefonoBusqueda = "%" . $telefono . "%";
            $stmt->bindParam(":telefono", $telefonoBusqueda, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarPorTelefono: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si email ya existe
    static public function mdlVerificarEmail($email, $id_cliente = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM cliente WHERE email = :email";
            
            if ($id_cliente) {
                $sql .= " AND id_cliente != :id_cliente";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            
            if ($id_cliente) {
                $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlVerificarEmail: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si teléfono ya existe
    static public function mdlVerificarTelefono($telefono, $id_cliente = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM cliente WHERE telefono = :telefono";
            
            if ($id_cliente) {
                $sql .= " AND id_cliente != :id_cliente";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
            
            if ($id_cliente) {
                $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlVerificarTelefono: " . $e->getMessage());
            return false;
        }
    }

    // Obtener clientes más frecuentes
    static public function mdlObtenerClientesFrecuentes($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.*, 
                       COUNT(o.id_orden) as total_ordenes,
                       SUM(CASE WHEN f.estado = 'pagada' THEN f.total ELSE 0 END) as total_facturado
                FROM cliente c
                INNER JOIN vehiculo v ON c.id_cliente = v.id_cliente
                INNER JOIN ordentrabajo o ON v.id_vehiculo = o.id_vehiculo
                LEFT JOIN factura f ON o.id_orden = f.id_orden
                GROUP BY c.id_cliente
                ORDER BY total_ordenes DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerClientesFrecuentes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de clientes
    static public function mdlObtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN DATE(fecha_registro) = CURDATE() THEN 1 END) as nuevos_hoy,
                    COUNT(CASE WHEN MONTH(fecha_registro) = MONTH(NOW()) AND YEAR(fecha_registro) = YEAR(NOW()) THEN 1 END) as nuevos_mes,
                    AVG(DATEDIFF(NOW(), fecha_registro)) as promedio_dias_registro
                FROM cliente
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener clientes recientes para el dashboard
    static public function mdlObtenerClientesRecientes($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.id_cliente, c.nombre, c.apellido, c.fecha_registro, c.telefono,
                       COUNT(v.id_vehiculo) as total_vehiculos
                FROM cliente c
                LEFT JOIN vehiculo v ON c.id_cliente = v.id_cliente
                GROUP BY c.id_cliente
                ORDER BY c.fecha_registro DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerClientesRecientes: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si un cliente puede ser eliminado
    static public function mdlPuedeEliminar($id_cliente)
    {
        try {
            // Verificar si tiene vehículos
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM vehiculo WHERE id_cliente = :id_cliente
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            $vehiculos = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si tiene facturas
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM factura WHERE id_cliente = :id_cliente
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            $facturas = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($vehiculos['total'] == 0 && $facturas['total'] == 0);
        } catch (PDOException $e) {
            error_log("Error en mdlPuedeEliminar: " . $e->getMessage());
            return false;
        }
    }

    // Obtener historial completo de un cliente
    static public function mdlObtenerHistorialCompleto($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 'orden' as tipo, o.id_orden as id, o.fecha_ingreso as fecha, 
                       CONCAT('Orden de trabajo - ', v.marca, ' ', v.modelo) as descripcion,
                       o.estado, NULL as total
                FROM ordentrabajo o
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                WHERE v.id_cliente = :id_cliente
                
                UNION ALL
                
                SELECT 'factura' as tipo, f.id_factura as id, f.fecha_emision as fecha,
                       CONCAT('Factura ', f.numero_factura) as descripcion,
                       f.estado, f.total
                FROM factura f
                WHERE f.id_cliente = :id_cliente
                
                ORDER BY fecha DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerHistorialCompleto: " . $e->getMessage());
            return array();
        }
    }
}
?>