<?php

require_once "conexion.php";

class ModeloVehiculo
{
    // Contar total de vehículos
    static public function mdlContarVehiculos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM vehiculo");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en mdlContarVehiculos: " . $e->getMessage());
            return 0;
        }
    }

    // Listar vehículos de un cliente específico
    static public function mdlListarVehiculosCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM vehiculo 
                WHERE id_cliente = :id_cliente
                ORDER BY marca, modelo
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarVehiculosCliente: " . $e->getMessage());
            return array();
        }
    }

    // Listar todos los vehículos
    static public function mdlListarVehiculos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT v.*, CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente
                FROM vehiculo v
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                ORDER BY v.marca, v.modelo
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarVehiculos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un vehículo específico
    static public function mdlObtenerVehiculo($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT v.*, CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente, c.email as email_cliente
                FROM vehiculo v
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE v.id_vehiculo = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerVehiculo: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo vehículo
    static public function mdlRegistrarVehiculo($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO vehiculo (id_cliente, marca, modelo, año, matricula, color, numero_motor, numero_chasis, combustible, observaciones)
                VALUES (:id_cliente, :marca, :modelo, :año, :matricula, :color, :numero_motor, :numero_chasis, :combustible, :observaciones)
            ");

            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
            $stmt->bindParam(":modelo", $datos["modelo"], PDO::PARAM_STR);
            $stmt->bindParam(":año", $datos["año"], PDO::PARAM_INT);
            $stmt->bindParam(":matricula", $datos["matricula"], PDO::PARAM_STR);
            $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_motor", $datos["numero_motor"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_chasis", $datos["numero_chasis"], PDO::PARAM_STR);
            $stmt->bindParam(":combustible", $datos["combustible"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarVehiculo: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar vehículo
    static public function mdlActualizarVehiculo($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE vehiculo 
                SET id_cliente = :id_cliente,
                    marca = :marca,
                    modelo = :modelo,
                    año = :año,
                    matricula = :matricula,
                    color = :color,
                    numero_motor = :numero_motor,
                    numero_chasis = :numero_chasis,
                    combustible = :combustible,
                    observaciones = :observaciones
                WHERE id_vehiculo = :id_vehiculo
            ");

            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
            $stmt->bindParam(":modelo", $datos["modelo"], PDO::PARAM_STR);
            $stmt->bindParam(":año", $datos["año"], PDO::PARAM_INT);
            $stmt->bindParam(":matricula", $datos["matricula"], PDO::PARAM_STR);
            $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_motor", $datos["numero_motor"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_chasis", $datos["numero_chasis"], PDO::PARAM_STR);
            $stmt->bindParam(":combustible", $datos["combustible"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarVehiculo: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar vehículo
    static public function mdlEliminarVehiculo($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM vehiculo WHERE id_vehiculo = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();
                
                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró el vehículo con ID: " . $id);
                    return "error";
                }
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarVehiculo: " . $e->getMessage());
            return "error";
        }
    }

    // Buscar vehículos por matrícula
    static public function mdlBuscarPorMatricula($matricula)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT v.*, CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM vehiculo v
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE v.matricula LIKE :matricula
            ");
            $matriculaBusqueda = "%" . $matricula . "%";
            $stmt->bindParam(":matricula", $matriculaBusqueda, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarPorMatricula: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si matrícula ya existe
    static public function mdlVerificarMatricula($matricula, $id_vehiculo = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM vehiculo WHERE matricula = :matricula";
            
            if ($id_vehiculo) {
                $sql .= " AND id_vehiculo != :id_vehiculo";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":matricula", $matricula, PDO::PARAM_STR);
            
            if ($id_vehiculo) {
                $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlVerificarMatricula: " . $e->getMessage());
            return false;
        }
    }

    // Obtener marcas más comunes
    static public function mdlObtenerMarcasComunes($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT marca, COUNT(*) as cantidad
                FROM vehiculo
                GROUP BY marca
                ORDER BY cantidad DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerMarcasComunes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de vehículos
    static public function mdlObtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_vehiculos,
                    COUNT(DISTINCT marca) as total_marcas,
                    COUNT(CASE WHEN combustible = 'gasolina' THEN 1 END) as gasolina,
                    COUNT(CASE WHEN combustible = 'diesel' THEN 1 END) as diesel,
                    COUNT(CASE WHEN combustible = 'hibrido' THEN 1 END) as hibrido,
                    AVG(año) as año_promedio,
                    MIN(año) as año_mas_antiguo,
                    MAX(año) as año_mas_nuevo
                FROM vehiculo
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener historial de servicios de un vehículo
    static public function mdlObtenerHistorialServicios($id_vehiculo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT o.*, p.nombre as nombre_personal,
                       COUNT(od.id_detalle) as cantidad_servicios
                FROM ordentrabajo o
                INNER JOIN personal p ON o.id_personal = p.id_personal
                LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                WHERE o.id_vehiculo = :id_vehiculo
                GROUP BY o.id_orden
                ORDER BY o.fecha_ingreso DESC
            ");
            $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerHistorialServicios: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si un vehículo puede ser eliminado
    static public function mdlPuedeEliminar($id_vehiculo)
    {
        try {
            // Verificar si tiene órdenes de trabajo
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM ordentrabajo WHERE id_vehiculo = :id_vehiculo
            ");
            $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $stmt->execute();
            $ordenes = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si tiene presupuestos
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM presupuesto WHERE id_vehiculo = :id_vehiculo
            ");
            $stmt->bindParam(":id_vehiculo", $id_vehiculo, PDO::PARAM_INT);
            $stmt->execute();
            $presupuestos = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($ordenes['total'] == 0 && $presupuestos['total'] == 0);
        } catch (PDOException $e) {
            error_log("Error en mdlPuedeEliminar: " . $e->getMessage());
            return false;
        }
    }
}
?>