<?php
require_once "conexion.php";

class ModeloPresupuesto {

    /**
     * Crear nuevo presupuesto
     */
    static public function mdlCrearPresupuesto($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO presupuesto (id_vehiculo, id_personal, fecha_validez, total, estado, observaciones)
                VALUES (:id_vehiculo, :id_personal, :fecha_validez, :total, :estado, :observaciones)
            ");

            $stmt->bindParam(":id_vehiculo", $datos["id_vehiculo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_validez", $datos["fecha_validez"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return Conexion::conectar()->lastInsertId();
            }
            return "error";
        } catch (Exception $e) {
            error_log("Error en mdlCrearPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Insertar detalle de presupuesto
     */
    static public function mdlInsertarDetallePresupuesto($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO detalle_presupuesto (id_presupuesto, tipo, id_producto, descripcion, cantidad, precio_unitario, subtotal)
                VALUES (:id_presupuesto, :tipo, :id_producto, :descripcion, :cantidad, :precio_unitario, :subtotal)
            ");

            $stmt->bindParam(":id_presupuesto", $datos["id_presupuesto"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlInsertarDetallePresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener lista de presupuestos con información completa
     */
    static public function mdlListarPresupuestos() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.*,
                    v.matricula, v.marca, v.modelo, v.anho,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono, c.email,
                    CONCAT(per.nombre, ' ', per.apellido) AS nombre_personal,
                    DATEDIFF(p.fecha_validez, CURDATE()) AS dias_restantes
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal per ON p.id_personal = per.id_personal
                ORDER BY p.fecha_emision DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlListarPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener presupuesto por ID
     */
    static public function mdlObtenerPresupuesto($id) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.*,
                    v.matricula, v.marca, v.modelo, v.anho, v.color,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.cedula, c.ruc, c.telefono, c.email, c.direccion,
                    CONCAT(per.nombre, ' ', per.apellido) AS nombre_personal,
                    DATEDIFF(p.fecha_validez, CURDATE()) AS dias_restantes
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal per ON p.id_personal = per.id_personal
                WHERE p.id_presupuesto = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerPresupuesto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener detalles de un presupuesto
     */
    static public function mdlObtenerDetallesPresupuesto($id_presupuesto) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    dp.*,
                    p.codigo_producto, p.nombre AS nombre_producto, p.marca
                FROM detalle_presupuesto dp
                LEFT JOIN producto p ON dp.id_producto = p.id_producto
                WHERE dp.id_presupuesto = :id_presupuesto
                ORDER BY dp.id_detalle
            ");
            $stmt->bindParam(":id_presupuesto", $id_presupuesto, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerDetallesPresupuesto: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Actualizar presupuesto
     */
    static public function mdlActualizarPresupuesto($id, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE presupuesto SET
                    fecha_validez = :fecha_validez,
                    total = :total,
                    estado = :estado,
                    observaciones = :observaciones,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_presupuesto = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":fecha_validez", $datos["fecha_validez"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Cambiar estado del presupuesto
     */
    static public function mdlCambiarEstado($id, $estado) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE presupuesto SET 
                    estado = :estado,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_presupuesto = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCambiarEstado: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Marcar presupuesto como facturado
     */
    static public function mdlMarcarComoFacturado($id) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE presupuesto SET 
                    facturado = 1,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_presupuesto = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlMarcarComoFacturado: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar presupuesto
     */
    static public function mdlEliminarPresupuesto($id) {
        try {
            // Verificar si ya fue facturado
            $stmt = Conexion::conectar()->prepare("SELECT facturado FROM presupuesto WHERE id_presupuesto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $presupuesto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($presupuesto && $presupuesto['facturado'] == 1) {
                return "ya_facturado";
            }

            // Eliminar presupuesto (los detalles se eliminan por CASCADE)
            $stmt = Conexion::conectar()->prepare("DELETE FROM presupuesto WHERE id_presupuesto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar detalle de presupuesto
     */
    static public function mdlEliminarDetalle($id_detalle) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM detalle_presupuesto WHERE id_detalle = :id");
            $stmt->bindParam(":id", $id_detalle, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener presupuestos pendientes
     */
    static public function mdlPresupuestosPendientes() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono,
                    DATEDIFF(p.fecha_validez, CURDATE()) AS dias_restantes
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE p.estado = 'pendiente' AND p.fecha_validez >= CURDATE()
                ORDER BY p.fecha_validez ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlPresupuestosPendientes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener presupuestos próximos a vencer
     */
    static public function mdlPresupuestosProximosVencer($dias = 7) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono, c.email,
                    DATEDIFF(p.fecha_validez, CURDATE()) AS dias_restantes
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                WHERE p.estado = 'pendiente' 
                AND DATEDIFF(p.fecha_validez, CURDATE()) BETWEEN 0 AND :dias
                ORDER BY p.fecha_validez ASC
            ");
            $stmt->bindParam(":dias", $dias, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlPresupuestosProximosVencer: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener presupuestos de un cliente
     */
    static public function mdlPresupuestosCliente($id_cliente) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(per.nombre, ' ', per.apellido) AS nombre_personal,
                    DATEDIFF(p.fecha_validez, CURDATE()) AS dias_restantes
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN personal per ON p.id_personal = per.id_personal
                WHERE v.id_cliente = :id_cliente
                ORDER BY p.fecha_emision DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlPresupuestosCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener estadísticas de presupuestos
     */
    static public function mdlEstadisticasPresupuestos() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_presupuestos,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
                    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
                    SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) as vencidos,
                    SUM(CASE WHEN facturado = 1 THEN 1 ELSE 0 END) as facturados,
                    SUM(total) as monto_total,
                    AVG(total) as monto_promedio,
                    SUM(CASE WHEN estado = 'aprobado' THEN total ELSE 0 END) as monto_aprobado,
                    COUNT(CASE WHEN DATEDIFF(fecha_validez, CURDATE()) BETWEEN 0 AND 7 THEN 1 END) as proximos_vencer
                FROM presupuesto
                WHERE fecha_emision >= CURDATE() - INTERVAL 3 MONTH
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlEstadisticasPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Marcar presupuestos vencidos
     */
    static public function mdlMarcarPresupuestosVencidos() {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE presupuesto SET 
                    estado = 'vencido',
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE estado = 'pendiente' AND fecha_validez < CURDATE()
            ");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error en mdlMarcarPresupuestosVencidos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Buscar presupuestos
     */
    static public function mdlBuscarPresupuestos($termino) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.*,
                    v.matricula, v.marca, v.modelo,
                    CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                    c.telefono,
                    CONCAT(per.nombre, ' ', per.apellido) AS nombre_personal,
                    DATEDIFF(p.fecha_validez, CURDATE()) AS dias_restantes
                FROM presupuesto p
                INNER JOIN vehiculo v ON p.id_vehiculo = v.id_vehiculo
                INNER JOIN cliente c ON v.id_cliente = c.id_cliente
                INNER JOIN personal per ON p.id_personal = per.id_personal
                WHERE (c.nombre LIKE :termino OR c.apellido LIKE :termino2 OR v.matricula LIKE :termino3 
                      OR p.id_presupuesto LIKE :termino4)
                ORDER BY p.fecha_emision DESC
            ");
            $termino_like = '%' . $termino . '%';
            $stmt->bindParam(":termino", $termino_like, PDO::PARAM_STR);
            $stmt->bindParam(":termino2", $termino_like, PDO::PARAM_STR);
            $stmt->bindParam(":termino3", $termino_like, PDO::PARAM_STR);
            $stmt->bindParam(":termino4", $termino_like, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlBuscarPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Duplicar presupuesto
     */
    static public function mdlDuplicarPresupuesto($id_presupuesto, $nueva_fecha_validez) {
        try {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();

            // Obtener presupuesto original
            $presupuesto_original = self::mdlObtenerPresupuesto($id_presupuesto);
            if (!$presupuesto_original) {
                $conexion->rollBack();
                return "presupuesto_no_encontrado";
            }

            // Crear nuevo presupuesto
            $datos_nuevo = array(
                "id_vehiculo" => $presupuesto_original["id_vehiculo"],
                "id_personal" => $presupuesto_original["id_personal"],
                "fecha_validez" => $nueva_fecha_validez,
                "total" => $presupuesto_original["total"],
                "estado" => "pendiente",
                "observaciones" => "Duplicado del presupuesto #" . $id_presupuesto . " - " . $presupuesto_original["observaciones"]
            );

            $nuevo_id = self::mdlCrearPresupuesto($datos_nuevo);
            if ($nuevo_id === "error") {
                $conexion->rollBack();
                return "error";
            }

            // Copiar detalles
            $detalles_originales = self::mdlObtenerDetallesPresupuesto($id_presupuesto);
            foreach ($detalles_originales as $detalle) {
                $datos_detalle = array(
                    "id_presupuesto" => $nuevo_id,
                    "tipo" => $detalle["tipo"],
                    "id_producto" => $detalle["id_producto"],
                    "descripcion" => $detalle["descripcion"],
                    "cantidad" => $detalle["cantidad"],
                    "precio_unitario" => $detalle["precio_unitario"],
                    "subtotal" => $detalle["subtotal"]
                );

                if (self::mdlInsertarDetallePresupuesto($datos_detalle) === "error") {
                    $conexion->rollBack();
                    return "error";
                }
            }

            $conexion->commit();
            return $nuevo_id;
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlDuplicarPresupuesto: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Convertir presupuesto a orden de trabajo
     */
    static public function mdlConvertirAOrden($id_presupuesto, $datos_orden) {
        try {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();

            // Verificar que el presupuesto existe y está aprobado
            $presupuesto = self::mdlObtenerPresupuesto($id_presupuesto);
            if (!$presupuesto || $presupuesto['estado'] !== 'aprobado') {
                $conexion->rollBack();
                return "presupuesto_no_valido";
            }

            // Crear orden de trabajo
            require_once "modelo_orden_trabajo.php";
            $datos_orden_completos = array(
                "id_vehiculo" => $presupuesto["id_vehiculo"],
                "id_personal" => $datos_orden["id_personal"] ?? $presupuesto["id_personal"],
                "fecha_ingreso" => $datos_orden["fecha_ingreso"] ?? date('Y-m-d H:i:s'),
                "kilometraje_actual" => $datos_orden["kilometraje_actual"] ?? 0,
                "observaciones" => "Convertido desde presupuesto #" . $id_presupuesto
            );

            $id_orden = ModeloOrdenTrabajo::mdlCrearOrdenTrabajo($datos_orden_completos);
            if ($id_orden === "error") {
                $conexion->rollBack();
                return "error_crear_orden";
            }

            // Copiar detalles del presupuesto a la orden
            $detalles = self::mdlObtenerDetallesPresupuesto($id_presupuesto);
            require_once "modelo_orden_detalle.php";
            
            foreach ($detalles as $detalle) {
                $datos_detalle_orden = array(
                    "id_orden" => $id_orden,
                    "tipo_servicio" => ($detalle["tipo"] === "servicio") ? "otro" : "otro",
                    "descripcion" => $detalle["descripcion"],
                    "cantidad" => $detalle["cantidad"],
                    "precio_unitario" => $detalle["precio_unitario"],
                    "subtotal" => $detalle["subtotal"]
                );

                if (ModeloOrdenDetalle::mdlInsertarDetalle($datos_detalle_orden) === "error") {
                    $conexion->rollBack();
                    return "error_insertar_detalle";
                }
            }

            // Marcar presupuesto como usado
            self::mdlCambiarEstado($id_presupuesto, "aprobado");

            $conexion->commit();
            return $id_orden;
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlConvertirAOrden: " . $e->getMessage());
            return "error";
        }
    }
}
?>