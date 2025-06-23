<?php
require_once "conexion.php";

class ModeloCaja {
    
    // Abrir caja
    static public function mdlAbrirCaja($datos) {
        try {
            // Verificar que no haya caja abierta
            $stmt = Conexion::conectar()->prepare("
                SELECT id_caja FROM caja 
                WHERE estado = 'abierta' AND id_personal = :id_personal
            ");
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return "caja_ya_abierta";
            }

            $stmt = Conexion::conectar()->prepare("
                INSERT INTO caja (id_personal, fecha_apertura, monto_inicial, estado, observaciones_apertura)
                VALUES (:id_personal, :fecha_apertura, :monto_inicial, 'abierta', :observaciones)
            ");

            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_apertura", $datos["fecha_apertura"], PDO::PARAM_STR);
            $stmt->bindParam(":monto_inicial", $datos["monto_inicial"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $id_caja = Conexion::conectar()->lastInsertId();
                
                // Registrar movimiento inicial
                self::mdlRegistrarMovimiento(
                    $id_caja, 
                    null, 
                    "ingreso", 
                    "Apertura de caja", 
                    $datos["monto_inicial"], 
                    "Monto inicial de apertura"
                );
                
                return $id_caja;
            }
            
            return "error";
        } catch (PDOException $e) {
            error_log("Error en mdlAbrirCaja: " . $e->getMessage());
            return "error";
        }
    }

    // Cerrar caja
    static public function mdlCerrarCaja($datos) {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            // Calcular totales
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) as total_ingresos,
                    SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END) as total_egresos
                FROM movimiento_caja 
                WHERE id_caja = :id_caja
            ");
            $stmt->bindParam(":id_caja", $datos["id_caja"], PDO::PARAM_INT);
            $stmt->execute();
            $totales = $stmt->fetch(PDO::FETCH_ASSOC);

            // Actualizar caja
            $stmt = $pdo->prepare("
                UPDATE caja 
                SET fecha_cierre = :fecha_cierre,
                    monto_final = :monto_final,
                    total_ingresos = :total_ingresos,
                    total_egresos = :total_egresos,
                    estado = 'cerrada',
                    observaciones_cierre = :observaciones
                WHERE id_caja = :id_caja
            ");

            $stmt->bindParam(":id_caja", $datos["id_caja"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_cierre", $datos["fecha_cierre"], PDO::PARAM_STR);
            $stmt->bindParam(":monto_final", $datos["monto_final"], PDO::PARAM_STR);
            $stmt->bindParam(":total_ingresos", $totales["total_ingresos"], PDO::PARAM_STR);
            $stmt->bindParam(":total_egresos", $totales["total_egresos"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $pdo->commit();
                return "ok";
            }
            
            $pdo->rollBack();
            return "error";
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en mdlCerrarCaja: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener caja actual (abierta)
    static public function mdlObtenerCajaActual($id_personal) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.*, p.nombre as nombre_personal
                FROM caja c
                INNER JOIN personal p ON c.id_personal = p.id_personal
                WHERE c.estado = 'abierta' AND c.id_personal = :id_personal
                ORDER BY c.fecha_apertura DESC 
                LIMIT 1
            ");
            
            $stmt->bindParam(":id_personal", $id_personal, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCajaActual: " . $e->getMessage());
            return false;
        }
    }

    // Listar historial de cajas
    static public function mdlListarHistorialCajas() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT c.*, p.nombre as nombre_personal
                FROM caja c
                INNER JOIN personal p ON c.id_personal = p.id_personal
                ORDER BY c.fecha_apertura DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarHistorialCajas: " . $e->getMessage());
            return array();
        }
    }

    // Registrar movimiento en caja
    static public function mdlRegistrarMovimiento($id_caja, $id_factura, $tipo, $concepto, $monto, $observaciones = null) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO movimiento_caja (id_caja, id_factura, tipo, concepto, monto, fecha, observaciones)
                VALUES (:id_caja, :id_factura, :tipo, :concepto, :monto, NOW(), :observaciones)
            ");
            
            $stmt->bindParam(":id_caja", $id_caja, PDO::PARAM_INT);
            $stmt->bindParam(":id_factura", $id_factura, PDO::PARAM_INT);
            $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->bindParam(":concepto", $concepto, PDO::PARAM_STR);
            $stmt->bindParam(":monto", $monto, PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarMovimiento: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener movimientos de una caja
    static public function mdlObtenerMovimientos($id_caja) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT mc.*, f.numero_factura
                FROM movimiento_caja mc
                LEFT JOIN factura f ON mc.id_factura = f.id_factura
                WHERE mc.id_caja = :id_caja
                ORDER BY mc.fecha ASC
            ");
            
            $stmt->bindParam(":id_caja", $id_caja, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerMovimientos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener resumen de caja actual
    static public function mdlResumenCajaActual($id_caja) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    c.monto_inicial,
                    COALESCE(SUM(CASE WHEN mc.tipo = 'ingreso' THEN mc.monto ELSE 0 END), 0) as total_ingresos,
                    COALESCE(SUM(CASE WHEN mc.tipo = 'egreso' THEN mc.monto ELSE 0 END), 0) as total_egresos,
                    (c.monto_inicial + COALESCE(SUM(CASE WHEN mc.tipo = 'ingreso' THEN mc.monto ELSE -mc.monto END), 0)) as saldo_actual,
                    COUNT(CASE WHEN mc.tipo = 'ingreso' THEN 1 END) as num_ingresos,
                    COUNT(CASE WHEN mc.tipo = 'egreso' THEN 1 END) as num_egresos
                FROM caja c
                LEFT JOIN movimiento_caja mc ON c.id_caja = mc.id_caja
                WHERE c.id_caja = :id_caja
                GROUP BY c.id_caja, c.monto_inicial
            ");
            
            $stmt->bindParam(":id_caja", $id_caja, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlResumenCajaActual: " . $e->getMessage());
            return array();
        }
    }

    // Verificar si hay caja abierta
    static public function mdlVerificarCajaAbierta($id_personal) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total FROM caja 
                WHERE estado = 'abierta' AND id_personal = :id_personal
            ");
            
            $stmt->bindParam(":id_personal", $id_personal, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlVerificarCajaAbierta: " . $e->getMessage());
            return false;
        }
    }

    // Obtener estadísticas de ventas del día
    static public function mdlEstadisticasVentasHoy($id_caja) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(f.id_factura) as facturas_emitidas,
                    COUNT(CASE WHEN f.estado = 'pagada' THEN 1 END) as facturas_pagadas,
                    COUNT(CASE WHEN f.estado = 'pendiente' THEN 1 END) as facturas_pendientes,
                    COALESCE(SUM(CASE WHEN f.estado = 'pagada' THEN f.total ELSE 0 END), 0) as total_cobrado,
                    COALESCE(SUM(CASE WHEN f.estado = 'pendiente' THEN f.total ELSE 0 END), 0) as total_pendiente
                FROM factura f
                WHERE f.id_caja = :id_caja
            ");
            
            $stmt->bindParam(":id_caja", $id_caja, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlEstadisticasVentasHoy: " . $e->getMessage());
            return array();
        }
    }
}
?>