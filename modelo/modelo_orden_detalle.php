<?php
require_once "conexion.php";

class ModeloOrdenDetalle
{

    /**
     * Registrar nuevo detalle de servicio
     */
    static public function mdlRegistrarDetalle($datos)
    {
        try {
            // Validar tipo de servicio
            if (!ModeloOrdenTrabajo::mdlValidarTipoServicio($datos["tipo_servicio"])) {
                error_log("Tipo de servicio inválido: " . $datos["tipo_servicio"]);
                return "tipo_servicio_invalido";
            }

            $stmt = Conexion::conectar()->prepare("
                INSERT INTO orden_detalle 
                (id_orden, tipo_servicio, descripcion, cantidad, precio_unitario, subtotal)
                VALUES 
                (:id_orden, :tipo_servicio, :descripcion, :cantidad, :precio_unitario, :subtotal)
            ");

            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $datos["tipo_servicio"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                error_log("Error en execute() del detalle");
                error_log("Error info: " . print_r($stmt->errorInfo(), true));
                return "error";
            }

        } catch (PDOException $e) {
            error_log("Error PDO en mdlRegistrarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener detalles de una orden específica
     */
    static public function mdlObtenerDetalles($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    od.*,
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
                WHERE od.id_orden = :id_orden 
                ORDER BY od.id_detalle
            ");

            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlObtenerDetalles: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Calcular total de una orden
     */
    static public function mdlCalcularTotal($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COALESCE(SUM(subtotal), 0) as total
                FROM orden_detalle 
                WHERE id_orden = :id_orden
            ");

            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['total'] : 0;

        } catch (PDOException $e) {
            error_log("Error en mdlCalcularTotal: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Actualizar detalle existente
     */
    static public function mdlActualizarDetalle($datos)
    {
        try {
            // Validar tipo de servicio
            if (!ModeloOrdenTrabajo::mdlValidarTipoServicio($datos["tipo_servicio"])) {
                return "tipo_servicio_invalido";
            }

            $stmt = Conexion::conectar()->prepare("
                UPDATE orden_detalle 
                SET tipo_servicio = :tipo_servicio, descripcion = :descripcion, 
                    cantidad = :cantidad, precio_unitario = :precio_unitario, 
                    subtotal = :subtotal
                WHERE id_detalle = :id_detalle
            ");

            $stmt->bindParam(":id_detalle", $datos["id_detalle"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_servicio", $datos["tipo_servicio"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";

        } catch (PDOException $e) {
            error_log("Error PDO en mdlActualizarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar detalle específico
     */
    static public function mdlEliminarDetalle($id_detalle)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                DELETE FROM orden_detalle WHERE id_detalle = :id_detalle
            ");

            $stmt->bindParam(":id_detalle", $id_detalle, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";

        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetalle: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Eliminar todos los detalles de una orden
     */
    static public function mdlEliminarDetallesOrden($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                DELETE FROM orden_detalle WHERE id_orden = :id_orden
            ");

            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";

        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDetallesOrden: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Registrar múltiples detalles en una transacción
     */
    static public function mdlRegistrarMultiplesDetalles($id_orden, $servicios)
    {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            // Primero eliminar detalles existentes
            $stmt = $pdo->prepare("DELETE FROM orden_detalle WHERE id_orden = :id_orden");
            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();

            // Preparar statement para inserción
            $stmt = $pdo->prepare("
                INSERT INTO orden_detalle 
                (id_orden, tipo_servicio, descripcion, cantidad, precio_unitario, subtotal)
                VALUES 
                (:id_orden, :tipo_servicio, :descripcion, :cantidad, :precio_unitario, :subtotal)
            ");

            $errores = array();

            foreach ($servicios as $servicio) {
                // Validar estructura del servicio
                if (
                    !isset($servicio['tipo']) || !isset($servicio['descripcion']) ||
                    !isset($servicio['cantidad']) || !isset($servicio['precioUnitario']) ||
                    !isset($servicio['subtotal'])
                ) {
                    $errores[] = "Estructura de servicio inválida";
                    continue;
                }

                // Validar tipo de servicio
                if (!ModeloOrdenTrabajo::mdlValidarTipoServicio($servicio['tipo'])) {
                    $errores[] = "Tipo de servicio inválido: " . $servicio['tipo'];
                    continue;
                }

                $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
                $stmt->bindParam(":tipo_servicio", $servicio['tipo'], PDO::PARAM_STR);
                $stmt->bindParam(":descripcion", $servicio['descripcion'], PDO::PARAM_STR);
                $stmt->bindParam(":cantidad", $servicio['cantidad'], PDO::PARAM_INT);
                $stmt->bindParam(":precio_unitario", $servicio['precioUnitario'], PDO::PARAM_STR);
                $stmt->bindParam(":subtotal", $servicio['subtotal'], PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    $errores[] = "Error insertando servicio: " . $servicio['tipo'];
                }
            }

            if (count($errores) > 0) {
                $pdo->rollBack();
                error_log("Errores en mdlRegistrarMultiplesDetalles: " . implode(", ", $errores));
                return "error";
            } else {
                $pdo->commit();
                return "ok";
            }

        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log("Error PDO en mdlRegistrarMultiplesDetalles: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener resumen de servicios por categoría
     */
    static public function mdlObtenerResumenPorCategoria($id_orden)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    CASE 
                        WHEN tipo_servicio IN ('aceite_motor', 'aceite_dif_trasero', 'aceite_dif_delantero', 'aceite_caja', 'aceite_reductora') 
                        THEN 'Aceites'
                        WHEN tipo_servicio IN ('filtro_aire', 'filtro_aceite', 'filtro_combustible') 
                        THEN 'Filtros'
                        WHEN tipo_servicio = 'bujias' 
                        THEN 'Sistema Ignición'
                        WHEN tipo_servicio = 'liquidos' 
                        THEN 'Líquidos'
                        WHEN tipo_servicio = 'mano_obra' 
                        THEN 'Mano de Obra'
                        ELSE 'Otros'
                    END as categoria,
                    COUNT(*) as cantidad_servicios,
                    SUM(cantidad) as total_items,
                    SUM(subtotal) as total_categoria
                FROM orden_detalle 
                WHERE id_orden = :id_orden
                GROUP BY categoria
                ORDER BY total_categoria DESC
            ");

            $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlObtenerResumenPorCategoria: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Verificar si una orden tiene servicios específicos
     */
    static public function mdlTieneServicios($id_orden, $tipos_servicios = array())
    {
        try {
            if (empty($tipos_servicios)) {
                // Verificar si tiene cualquier servicio
                $stmt = Conexion::conectar()->prepare("
                    SELECT COUNT(*) as total FROM orden_detalle WHERE id_orden = :id_orden
                ");
                $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
            } else {
                // Verificar servicios específicos
                $placeholders = str_repeat('?,', count($tipos_servicios) - 1) . '?';
                $stmt = Conexion::conectar()->prepare("
                    SELECT COUNT(*) as total FROM orden_detalle 
                    WHERE id_orden = ? AND tipo_servicio IN ($placeholders)
                ");
                $params = array_merge([$id_orden], $tipos_servicios);
                $stmt->execute($params);
            }

            if (empty($tipos_servicios)) {
                $stmt->execute();
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['total'] > 0;

        } catch (PDOException $e) {
            error_log("Error en mdlTieneServicios: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de servicios
     */
    static public function mdlEstadisticasServicios($fecha_desde = null, $fecha_hasta = null)
    {
        try {
            $sql = "
                SELECT 
                    od.tipo_servicio,
                    COUNT(DISTINCT od.id_orden) as ordenes_con_servicio,
                    SUM(od.cantidad) as total_cantidad,
                    AVG(od.precio_unitario) as precio_promedio,
                    MIN(od.precio_unitario) as precio_minimo,
                    MAX(od.precio_unitario) as precio_maximo,
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
                WHERE 1=1
            ";

            $params = array();

            if ($fecha_desde) {
                $sql .= " AND DATE(o.fecha_ingreso) >= :fecha_desde";
                $params[':fecha_desde'] = $fecha_desde;
            }

            if ($fecha_hasta) {
                $sql .= " AND DATE(o.fecha_ingreso) <= :fecha_hasta";
                $params[':fecha_hasta'] = $fecha_hasta;
            }

            $sql .= " GROUP BY od.tipo_servicio ORDER BY total_facturado DESC";

            $stmt = Conexion::conectar()->prepare($sql);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlEstadisticasServicios: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener servicios más rentables
     */
    static public function mdlServiciosMasRentables($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    od.tipo_servicio,
                    COUNT(*) as frecuencia,
                    AVG(od.precio_unitario) as precio_promedio,
                    SUM(od.subtotal) as total_generado,
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
                HAVING frecuencia >= 3
                ORDER BY total_generado DESC, frecuencia DESC
                LIMIT :limite
            ");

            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlServiciosMasRentables: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Validar datos del detalle antes de insertar/actualizar
     */
    static public function mdlValidarDatosDetalle($datos)
    {
        $errores = array();

        // Validar tipo de servicio
        if (empty($datos['tipo_servicio']) || !ModeloOrdenTrabajo::mdlValidarTipoServicio($datos['tipo_servicio'])) {
            $errores[] = "Tipo de servicio inválido";
        }

        // Validar descripción
        if (empty($datos['descripcion']) || strlen(trim($datos['descripcion'])) < 3) {
            $errores[] = "La descripción debe tener al menos 3 caracteres";
        }

        // Validar cantidad
        if (!isset($datos['cantidad']) || $datos['cantidad'] <= 0 || !is_numeric($datos['cantidad'])) {
            $errores[] = "La cantidad debe ser un número mayor a 0";
        }

        // Validar precio unitario
        if (!isset($datos['precio_unitario']) || $datos['precio_unitario'] <= 0 || !is_numeric($datos['precio_unitario'])) {
            $errores[] = "El precio unitario debe ser un número mayor a 0";
        }

        // Validar subtotal
        if (isset($datos['cantidad']) && isset($datos['precio_unitario']) && isset($datos['subtotal'])) {
            $subtotal_calculado = $datos['cantidad'] * $datos['precio_unitario'];
            if (abs($subtotal_calculado - $datos['subtotal']) > 0.01) {
                $errores[] = "El subtotal no coincide con cantidad × precio unitario";
            }
        }

        return $errores;
    }
}
?>