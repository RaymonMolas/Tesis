<?php
require_once "conexion.php";

class ModeloOrdenTrabajo
{

    /**
     * Listar todas las órdenes de trabajo con información completa
     */
    static public function mdlListarOrdenesTrabajo()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    o.*,
                    v.matricula, v.marca, v.modelo, v.anho, v.color,
                    CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                    c.cedula, c.ruc, c.telefono, c.email,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal,
                    COALESCE(SUM(od.subtotal), 0) as total_orden,
                    COUNT(od.id_detalle) as total_servicios
                FROM ordentrabajo o
                JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                JOIN cliente c ON v.id_cliente = c.id_cliente
                JOIN personal p ON o.id_personal = p.id_personal
                LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                GROUP BY o.id_orden
                ORDER BY o.fecha_ingreso DESC
            ");

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlListarOrdenesTrabajo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener orden específica con todos sus detalles
     */
    static public function mdlObtenerOrdenTrabajo($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    o.*,
                    v.matricula, v.marca, v.modelo, v.anho, v.color, v.id_cliente,
                    CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                    c.cedula, c.ruc, c.telefono, c.email, c.direccion,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal,
                    COALESCE(SUM(od.subtotal), 0) as total
                FROM ordentrabajo o
                JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                JOIN cliente c ON v.id_cliente = c.id_cliente
                JOIN personal p ON o.id_personal = p.id_personal
                LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                WHERE o.id_orden = :id
                GROUP BY o.id_orden
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenTrabajo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar nueva orden de trabajo
     */
    static public function mdlRegistrarOrdenTrabajo($datos)
    {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO ordentrabajo (id_vehiculo, id_personal, fecha_ingreso, fecha_salida, 
                                        kilometraje_actual, estado, observaciones)
                VALUES (:id_vehiculo, :id_personal, :fecha_ingreso, :fecha_salida, 
                        :kilometraje_actual, :estado, :observaciones)
            ");

            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_ingreso", $datos["fecha_ingreso"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_salida", $datos["fecha_salida"], PDO::PARAM_STR);
            $stmt->bindParam(":kilometraje_actual", $datos["kilometraje_actual"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $id_orden = $pdo->lastInsertId();

                if ($id_orden && $id_orden > 0) {
                    $pdo->commit();
                    return $id_orden;
                } else {
                    $pdo->rollBack();
                    return "error";
                }
            } else {
                $pdo->rollBack();
                return "error";
            }

        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarOrdenTrabajo: " . $e->getMessage());
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            return "error";
        }
    }

    /**
     * Actualizar orden de trabajo existente
     */
    static public function mdlActualizarOrdenTrabajo($datos)
    {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE ordentrabajo 
                SET id_vehiculo = :id_vehiculo, fecha_ingreso = :fecha_ingreso, 
                    fecha_salida = :fecha_salida, kilometraje_actual = :kilometraje_actual,
                    estado = :estado, observaciones = :observaciones,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_orden = :id_orden
            ");

            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_ingreso", $datos["fecha_ingreso"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_salida", $datos["fecha_salida"], PDO::PARAM_STR);
            $stmt->bindParam(":kilometraje_actual", $datos["kilometraje_actual"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $pdo->commit();
                return "ok";
            } else {
                $pdo->rollBack();
                return "error";
            }

        } catch (PDOException $e) {
            error_log("Error en mdlActualizarOrdenTrabajo: " . $e->getMessage());
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            return "error";
        }
    }

    /**
     * Eliminar orden de trabajo
     */
    static public function mdlEliminarOrdenTrabajo($id)
    {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            // Primero eliminar detalles
            $stmt = $pdo->prepare("DELETE FROM orden_detalle WHERE id_orden = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            // Luego eliminar la orden
            $stmt = $pdo->prepare("DELETE FROM ordentrabajo WHERE id_orden = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $pdo->commit();
                return "ok";
            } else {
                $pdo->rollBack();
                return "error";
            }

        } catch (PDOException $e) {
            error_log("Error en mdlEliminarOrdenTrabajo: " . $e->getMessage());
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            return "error";
        }
    }

    /**
     * Actualizar estado de orden
     */
    static public function mdlActualizarEstado($datos)
    {
        try {
            $sql = "UPDATE ordentrabajo SET estado = :estado";
            $params = array(":estado" => $datos["estado"], ":id_orden" => $datos["id_orden"]);

            if (isset($datos["fecha_salida"]) && $datos["fecha_salida"]) {
                $sql .= ", fecha_salida = :fecha_salida";
                $params[":fecha_salida"] = $datos["fecha_salida"];
            }

            $sql .= " WHERE id_orden = :id_orden";

            $stmt = Conexion::conectar()->prepare($sql);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            return $stmt->execute() ? "ok" : "error";

        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEstado: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener estadísticas de órdenes de trabajo
     */
    static public function mdlEstadisticasOrdenes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_ordenes,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregadas,
                    SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as canceladas,
                    SUM(CASE WHEN facturado = 1 THEN 1 ELSE 0 END) as facturadas,
                    COUNT(DISTINCT id_vehiculo) as vehiculos_atendidos,
                    COUNT(DISTINCT DATE(fecha_ingreso)) as dias_con_ordenes
                FROM ordentrabajo
                WHERE fecha_ingreso >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlEstadisticasOrdenes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener órdenes por estado
     */
    static public function mdlObtenerOrdenesPorEstado($estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    o.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal,
                    COALESCE(SUM(od.subtotal), 0) as total_orden
                FROM ordentrabajo o
                JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                JOIN cliente c ON v.id_cliente = c.id_cliente
                JOIN personal p ON o.id_personal = p.id_personal
                LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                WHERE o.estado = :estado
                GROUP BY o.id_orden
                ORDER BY o.fecha_ingreso DESC
            ");

            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenesPorEstado: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Marcar orden como facturada
     */
    static public function mdlMarcarComoFacturada($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE ordentrabajo 
                SET facturado = 1, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_orden = :id
            ");
            $stmt->bindParam(":id", $id_orden, PDO::PARAM_INT);

            return $stmt->execute() ? "ok" : "error";

        } catch (PDOException $e) {
            error_log("Error en mdlMarcarComoFacturada: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener órdenes pendientes de facturación
     */
    static public function mdlObtenerOrdenesPendientesFacturacion()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    o.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                    COALESCE(SUM(od.subtotal), 0) as total_orden
                FROM ordentrabajo o
                JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                JOIN cliente c ON v.id_cliente = c.id_cliente
                LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                WHERE o.estado = 'completado' AND o.facturado = 0
                GROUP BY o.id_orden
                ORDER BY o.fecha_ingreso ASC
            ");

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlObtenerOrdenesPendientesFacturacion: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener resumen de servicios más solicitados
     */
    static public function mdlObtenerServiciosMasSolicitados($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    od.tipo_servicio,
                    COUNT(*) as cantidad_ordenes,
                    SUM(od.cantidad) as total_servicios,
                    AVG(od.precio_unitario) as precio_promedio,
                    SUM(od.subtotal) as total_facturado,
                    CASE od.tipo_servicio
                        WHEN 'aceite_motor' THEN 'Aceite Motor'
                        WHEN 'aceite_dif_trasero' THEN 'Aceite Dif. Trasero'
                        WHEN 'aceite_dif_delantero' THEN 'Aceite Dif. Delantero'
                        WHEN 'aceite_caja' THEN 'Aceite Caja'
                        WHEN 'aceite_reductora' THEN 'Aceite Reductora'
                        WHEN 'filtro_aire' THEN 'Filtro de Aire'
                        WHEN 'filtro_aceite' THEN 'Filtro de Aceite'
                        WHEN 'filtro_combustible' THEN 'Filtro de Combustible'
                        WHEN 'bujias' THEN 'Bujías'
                        WHEN 'liquidos' THEN 'Líquidos'
                        WHEN 'mano_obra' THEN 'Mano de Obra'
                        WHEN 'otro' THEN 'Otro'
                        ELSE od.tipo_servicio
                    END as nombre_servicio
                FROM orden_detalle od
                JOIN ordentrabajo o ON od.id_orden = o.id_orden
                WHERE o.fecha_ingreso >= DATE_SUB(CURRENT_DATE, INTERVAL 90 DAY)
                GROUP BY od.tipo_servicio
                ORDER BY cantidad_ordenes DESC, total_facturado DESC
                LIMIT :limite
            ");

            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlObtenerServiciosMasSolicitados: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Validar tipos de servicios permitidos
     */
    static public function mdlValidarTipoServicio($tipo)
    {
        $serviciosPermitidos = [
            'aceite_motor',
            'aceite_dif_trasero',
            'aceite_dif_delantero',
            'aceite_caja',
            'aceite_reductora',
            'filtro_aire',
            'filtro_aceite',
            'filtro_combustible',
            'bujias',
            'liquidos',
            'mano_obra',
            'otro'
        ];

        return in_array($tipo, $serviciosPermitidos);
    }

    /**
     * Obtener nombre legible del tipo de servicio
     */
    static public function mdlObtenerNombreServicio($tipo)
    {
        $nombres = [
            'aceite_motor' => 'Aceite Motor',
            'aceite_dif_trasero' => 'Aceite Diferencial Trasero',
            'aceite_dif_delantero' => 'Aceite Diferencial Delantero',
            'aceite_caja' => 'Aceite de Caja',
            'aceite_reductora' => 'Aceite Reductora',
            'filtro_aire' => 'Filtro de Aire',
            'filtro_aceite' => 'Filtro de Aceite',
            'filtro_combustible' => 'Filtro de Combustible',
            'bujias' => 'Bujías',
            'liquidos' => 'Líquidos',
            'mano_obra' => 'Mano de Obra',
            'otro' => 'Otro'
        ];

        return isset($nombres[$tipo]) ? $nombres[$tipo] : $tipo;
    }

    /**
     * Buscar órdenes con filtros avanzados
     */
    static public function mdlBuscarOrdenesConFiltros($filtros = array())
    {
        try {
            $sql = "
                SELECT 
                    o.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_personal,
                    COALESCE(SUM(od.subtotal), 0) as total_orden
                FROM ordentrabajo o
                JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                JOIN cliente c ON v.id_cliente = c.id_cliente
                JOIN personal p ON o.id_personal = p.id_personal
                LEFT JOIN orden_detalle od ON o.id_orden = od.id_orden
                WHERE 1=1
            ";

            $params = array();

            if (!empty($filtros['estado'])) {
                $sql .= " AND o.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            if (!empty($filtros['cliente'])) {
                $sql .= " AND (c.nombre LIKE :cliente OR c.apellido LIKE :cliente OR c.cedula LIKE :cliente OR c.ruc LIKE :cliente)";
                $params[':cliente'] = '%' . $filtros['cliente'] . '%';
            }

            if (!empty($filtros['matricula'])) {
                $sql .= " AND v.matricula LIKE :matricula";
                $params[':matricula'] = '%' . $filtros['matricula'] . '%';
            }

            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(o.fecha_ingreso) >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }

            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(o.fecha_ingreso) <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }

            $sql .= " GROUP BY o.id_orden ORDER BY o.fecha_ingreso DESC";

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
            error_log("Error en mdlBuscarOrdenesConFiltros: " . $e->getMessage());
            return array();
        }
    }
}
?>