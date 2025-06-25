<?php

require_once "conexion.php";

class ModeloFactura
{
    // Listar todas las facturas
    static public function mdlListarFacturas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT f.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       p.nombre as nombre_personal
                FROM factura f
                INNER JOIN cliente c ON f.id_cliente = c.id_cliente
                INNER JOIN personal p ON f.id_personal = p.id_personal
                ORDER BY f.fecha_emision DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarFacturas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una factura específica
    static public function mdlObtenerFactura($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT f.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.telefono as telefono_cliente,
                       c.email as email_cliente,
                       p.nombre as nombre_personal
                FROM factura f
                INNER JOIN cliente c ON f.id_cliente = c.id_cliente
                INNER JOIN personal p ON f.id_personal = p.id_personal
                WHERE f.id_factura = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerFactura: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva factura
    static public function mdlRegistrarFactura($datos)
    {
        try {
            $pdo = Conexion::conectar();
            
            // Iniciar transacción
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO factura (id_cliente, id_personal, id_orden, id_presupuesto, numero_factura, fecha_emision, 
                                   tipo_factura, subtotal, descuento, iva, total, estado, metodo_pago, observaciones)
                VALUES (:id_cliente, :id_personal, :id_orden, :id_presupuesto, :numero_factura, :fecha_emision,
                        :tipo_factura, :subtotal, :descuento, :iva, :total, :estado, :metodo_pago, :observaciones)
            ");

            // Generar número de factura
            $numero_factura = self::mdlGenerarNumeroFactura();

            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":id_presupuesto", $datos["id_presupuesto"], PDO::PARAM_INT);
            $stmt->bindParam(":numero_factura", $numero_factura, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_emision", $datos["fecha_emision"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_factura", $datos["tipo_factura"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);
            $stmt->bindParam(":descuento", $datos["descuento"], PDO::PARAM_STR);
            $stmt->bindParam(":iva", $datos["iva"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $id_factura = $pdo->lastInsertId();

                // Verificar que el ID es válido
                if ($id_factura && $id_factura > 0) {
                    // Confirmar transacción
                    $pdo->commit();
                    return $id_factura;
                } else {
                    $pdo->rollBack();
                    return "error";
                }
            } else {
                $pdo->rollBack();
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarFactura: " . $e->getMessage());

            // Rollback si hay transacción activa
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return "error";
        }
    }

    // Actualizar factura
    static public function mdlActualizarFactura($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE factura 
                SET tipo_factura = :tipo_factura,
                    subtotal = :subtotal,
                    descuento = :descuento,
                    iva = :iva,
                    total = :total,
                    estado = :estado,
                    metodo_pago = :metodo_pago,
                    observaciones = :observaciones
                WHERE id_factura = :id_factura
            ");

            $stmt->bindParam(":id_factura", $datos["id_factura"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_factura", $datos["tipo_factura"], PDO::PARAM_STR);
            $stmt->bindParam(":subtotal", $datos["subtotal"], PDO::PARAM_STR);
            $stmt->bindParam(":descuento", $datos["descuento"], PDO::PARAM_STR);
            $stmt->bindParam(":iva", $datos["iva"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarFactura: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar factura
    static public function mdlEliminarFactura($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM factura WHERE id_factura = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $filasAfectadas = $stmt->rowCount();

                if ($filasAfectadas > 0) {
                    return "ok";
                } else {
                    error_log("No se encontró la factura con ID: " . $id);
                    return "error";
                }
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarFactura: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar estado de factura
    static public function mdlActualizarEstado($id, $estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE factura SET estado = :estado WHERE id_factura = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEstado: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener facturas por cliente
    static public function mdlObtenerFacturasPorCliente($id_cliente)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT f.*, p.nombre as nombre_personal
                FROM factura f
                INNER JOIN personal p ON f.id_personal = p.id_personal
                WHERE f.id_cliente = :id_cliente
                ORDER BY f.fecha_emision DESC
            ");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerFacturasPorCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener facturas por estado
    static public function mdlObtenerFacturasPorEstado($estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT f.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       p.nombre as nombre_personal
                FROM factura f
                INNER JOIN cliente c ON f.id_cliente = c.id_cliente
                INNER JOIN personal p ON f.id_personal = p.id_personal
                WHERE f.estado = :estado
                ORDER BY f.fecha_emision DESC
            ");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerFacturasPorEstado: " . $e->getMessage());
            return array();
        }
    }

    // Generar número de factura automático
    static public function mdlGenerarNumeroFactura()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) + 1 as siguiente_numero 
                FROM factura 
                WHERE YEAR(fecha_emision) = YEAR(NOW())
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $year = date('Y');
            $numero = str_pad($resultado['siguiente_numero'], 6, '0', STR_PAD_LEFT);
            
            return "FAC-{$year}-{$numero}";
        } catch (PDOException $e) {
            error_log("Error en mdlGenerarNumeroFactura: " . $e->getMessage());
            return "FAC-" . date('Y') . "-000001";
        }
    }

    // Obtener estadísticas de facturación
    static public function mdlEstadisticasFacturacion($fecha_inicio = null, $fecha_fin = null)
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_facturas,
                    SUM(CASE WHEN estado = 'pagada' THEN 1 ELSE 0 END) as facturas_pagadas,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as facturas_pendientes,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as facturas_canceladas,
                    SUM(total) as total_facturado,
                    SUM(CASE WHEN estado = 'pagada' THEN total ELSE 0 END) as total_cobrado,
                    AVG(total) as promedio_factura
                FROM factura
            ";

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " WHERE fecha_emision BETWEEN :fecha_inicio AND :fecha_fin";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            
            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(":fecha_inicio", $fecha_inicio, PDO::PARAM_STR);
                $stmt->bindParam(":fecha_fin", $fecha_fin, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlEstadisticasFacturacion: " . $e->getMessage());
            return array();
        }
    }

    // Obtener facturas recientes para el dashboard
    static public function mdlObtenerFacturasRecientes($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT f.id_factura, f.numero_factura, f.fecha_emision, f.estado, f.total,
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM factura f
                INNER JOIN cliente c ON f.id_cliente = c.id_cliente
                ORDER BY f.fecha_emision DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerFacturasRecientes: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si una factura puede ser eliminada
    static public function mdlPuedeEliminar($id_factura)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT estado 
                FROM factura 
                WHERE id_factura = :id
            ");
            $stmt->bindParam(":id", $id_factura, PDO::PARAM_INT);
            $stmt->execute();
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($factura) {
                // Solo se puede eliminar si está pendiente
                return $factura['estado'] == 'pendiente';
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en mdlPuedeEliminar: " . $e->getMessage());
            return false;
        }
    }

    // Obtener total de ventas del mes actual
    static public function mdlObtenerVentasMes()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT SUM(total) as total_mes
                FROM factura 
                WHERE MONTH(fecha_emision) = MONTH(NOW()) 
                AND YEAR(fecha_emision) = YEAR(NOW())
                AND estado = 'pagada'
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total_mes'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerVentasMes: " . $e->getMessage());
            return 0;
        }
    }
}
?>