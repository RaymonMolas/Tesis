<?php
require_once "conexion.php";

class ModeloCliente
{

    /**
     * Buscar todos los clientes activos
     */
    static public function mdlBuscarClientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM cliente 
                WHERE estado = 'activo' 
                ORDER BY nombre, apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarClientes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener cliente por ID
     */
    static public function mdlObtenerCliente($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM cliente 
                WHERE id_cliente = :id AND estado = 'activo'
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar nuevo cliente con soporte para RUC
     */
    static public function mdlRegistrarCliente($datos)
    {
        try {
            // Verificar que no existe la cédula
            $stmt = Conexion::conectar()->prepare("
                SELECT id_cliente FROM cliente WHERE cedula = :cedula
            ");
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->fetch()) {
                return "cedula_duplicada";
            }

            // Verificar RUC si se proporciona
            if (!empty($datos["ruc"])) {
                $stmt = Conexion::conectar()->prepare("
                    SELECT id_cliente FROM cliente WHERE ruc = :ruc
                ");
                $stmt->bindParam(":ruc", $datos["ruc"], PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->fetch()) {
                    return "ruc_duplicado";
                }
            }

            // Insertar nuevo cliente
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO cliente (nombre, apellido, cedula, ruc, telefono, email, direccion, estado)
                VALUES (:nombre, :apellido, :cedula, :ruc, :telefono, :email, :direccion, 'activo')
            ");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->bindParam(":ruc", $datos["ruc"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }

        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarCliente: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Actualizar cliente existente
     */
    static public function mdlActualizarCliente($datos)
    {
        try {
            // Verificar que no existe la cédula en otro cliente
            $stmt = Conexion::conectar()->prepare("
                SELECT id_cliente FROM cliente 
                WHERE cedula = :cedula AND id_cliente != :id
            ");
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetch()) {
                return "cedula_duplicada";
            }

            // Verificar RUC si se proporciona
            if (!empty($datos["ruc"])) {
                $stmt = Conexion::conectar()->prepare("
                    SELECT id_cliente FROM cliente 
                    WHERE ruc = :ruc AND id_cliente != :id
                ");
                $stmt->bindParam(":ruc", $datos["ruc"], PDO::PARAM_STR);
                $stmt->bindParam(":id", $datos["id_cliente"], PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->fetch()) {
                    return "ruc_duplicado";
                }
            }

            // Actualizar cliente
            $stmt = Conexion::conectar()->prepare("
                UPDATE cliente 
                SET nombre = :nombre, apellido = :apellido, cedula = :cedula, 
                    ruc = :ruc, telefono = :telefono, email = :email, direccion = :direccion,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_cliente = :id
            ");

            $stmt->bindParam(":id", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->bindParam(":ruc", $datos["ruc"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }

        } catch (PDOException $e) {
            error_log("Error en mdlActualizarCliente: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar cliente (cambiar estado a inactivo)
     */
    static public function mdlEliminarCliente($id)
    {
        try {
            // Verificar que no tenga vehículos asociados
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM vehiculo 
                WHERE id_cliente = :id AND estado = 'activo'
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                return "tiene_vehiculos";
            }

            // Cambiar estado a inactivo
            $stmt = Conexion::conectar()->prepare("
                UPDATE cliente 
                SET estado = 'inactivo', fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_cliente = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }

        } catch (PDOException $e) {
            error_log("Error en mdlEliminarCliente: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Buscar cliente por cédula o RUC
     */
    static public function mdlBuscarClientePorDocumento($documento)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM cliente 
                WHERE (cedula = :documento OR ruc = :documento) AND estado = 'activo'
                LIMIT 1
            ");
            $stmt->bindParam(":documento", $documento, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarClientePorDocumento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de clientes
     */
    static public function mdlEstadisticasClientes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_clientes,
                    SUM(CASE WHEN ruc IS NOT NULL AND ruc != '' THEN 1 ELSE 0 END) as clientes_con_ruc,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as clientes_activos,
                    COUNT(DISTINCT DATE(fecha_registro)) as dias_con_registros
                FROM cliente
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlEstadisticasClientes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar clientes con filtros
     */
    static public function mdlBuscarClientesConFiltros($filtros = array())
    {
        try {
            $sql = "SELECT * FROM cliente WHERE estado = 'activo'";
            $params = array();

            if (!empty($filtros['busqueda'])) {
                $sql .= " AND (nombre LIKE :busqueda OR apellido LIKE :busqueda OR cedula LIKE :busqueda OR ruc LIKE :busqueda)";
                $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            }

            if (!empty($filtros['con_ruc'])) {
                $sql .= " AND ruc IS NOT NULL AND ruc != ''";
            }

            $sql .= " ORDER BY nombre, apellido";

            if (!empty($filtros['limite'])) {
                $sql .= " LIMIT " . intval($filtros['limite']);
            }

            $stmt = Conexion::conectar()->prepare($sql);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlBuscarClientesConFiltros: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener información completa del cliente con vehículos
     */
    static public function mdlObtenerClienteCompleto($id)
    {
        try {
            // Obtener datos del cliente
            $cliente = self::mdlObtenerCliente($id);
            if (!$cliente) {
                return false;
            }

            // Obtener vehículos del cliente
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM vehiculo 
                WHERE id_cliente = :id AND estado = 'activo'
                ORDER BY marca, modelo
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $cliente['vehiculos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener estadísticas del cliente
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(DISTINCT o.id_orden) as total_ordenes,
                    COUNT(DISTINCT p.id_presupuesto) as total_presupuestos,
                    COUNT(DISTINCT f.id_factura) as total_facturas,
                    COALESCE(SUM(f.total), 0) as total_facturado
                FROM cliente c
                LEFT JOIN vehiculo v ON c.id_cliente = v.id_cliente
                LEFT JOIN ordentrabajo o ON v.id_vehiculo = o.id_vehiculo
                LEFT JOIN presupuesto p ON v.id_vehiculo = p.id_vehiculo
                LEFT JOIN factura f ON c.id_cliente = f.id_cliente
                WHERE c.id_cliente = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

            $cliente['estadisticas'] = $estadisticas;

            return $cliente;

        } catch (PDOException $e) {
            error_log("Error en mdlObtenerClienteCompleto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar formato de RUC paraguayo
     */
    static public function mdlValidarRUC($ruc)
    {
        if (empty($ruc)) {
            return true; // RUC es opcional
        }

        // Formato básico de RUC paraguayo: 12345678-9
        if (!preg_match('/^\d{8}-\d$/', $ruc)) {
            return false;
        }

        // Aquí se podría agregar validación del dígito verificador
        // Para simplificar, por ahora solo validamos el formato

        return true;
    }

    /**
     * Obtener documento principal del cliente (RUC si tiene, sino cédula)
     */
    static public function mdlObtenerDocumentoPrincipal($id)
    {
        try {
            $cliente = self::mdlObtenerCliente($id);
            if (!$cliente) {
                return false;
            }

            // Priorizar RUC si existe
            if (!empty($cliente['ruc'])) {
                return array(
                    'tipo' => 'ruc',
                    'documento' => $cliente['ruc'],
                    'nombre' => $cliente['nombre'] . ' ' . $cliente['apellido']
                );
            } else {
                return array(
                    'tipo' => 'cedula',
                    'documento' => $cliente['cedula'],
                    'nombre' => $cliente['nombre'] . ' ' . $cliente['apellido']
                );
            }

        } catch (Exception $e) {
            error_log("Error en mdlObtenerDocumentoPrincipal: " . $e->getMessage());
            return false;
        }
    }
}
?>