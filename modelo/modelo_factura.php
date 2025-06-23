<?php
require_once "conexion.php";

class ModeloFactura {
    
    // Listar todas las facturas
    static public function mdlListarFacturas() {
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

    // Obtener una factura específica con sus detalles
    static public function mdlObtenerFactura($id) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT f.*, 
                       CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente,
                       c.cedula,
                       c.direccion,
                       c.telefono,
                       c.email,
                       p.nombre as nombre_personal
                FROM factura f
                INNER JOIN cliente c ON f.id_cliente = c.id_cliente
                INNER JOIN personal p ON f.id_personal = p.id_personal
                WHERE f.id_factura = :id
            ");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($factura) {
                // Obtener detalles de la factura
                $factura['detalles'] = ModeloDetalleFactura::mdlObtenerDetalles($id);
            }
            
            return $factura;
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerFactura: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva factura
    static public function mdlRegistrarFactura($datos) {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            // Generar número de factura
            $stmt = $pdo->prepare("SELECT generar_numero_factura() as numero");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $numero_factura = $resultado['numero'];

            $stmt = $pdo->prepare("
                INSERT INTO factura 
                (numero_factura, id_cliente, id_personal, id_orden, id_presupuesto, 
                 fecha_emision, tipo_factura, subtotal, descuento, iva, total, estado, metodo_pago, observaciones)
                VALUES 
                (:numero_factura, :id_cliente, :id_personal, :id_orden, :id_presupuesto,
                 :fecha_emision, :tipo_factura, :subtotal, :descuento, :iva, :total, :estado, :metodo_pago, :observaciones)
            ");

            $stmt->bindParam(":numero_factura", $numero_factura, PDO::PARAM_STR);
            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":id_orden", $datos["id_orden"], PDO::PARAM_INT);
            $stmt->bindParam(":id_presupuesto", $datos["id_presupuesto"], PDO::PARAM_INT);
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
                
                if ($id_factura > 0) {
                    // Marcar orden como facturada si aplica
                    if ($datos["id_orden"]) {
                        $stmt = $pdo->prepare("UPDATE ordentrabajo SET facturado = 1 WHERE id_orden = :id");
                        $stmt->bindParam(":id", $datos["id_orden"], PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    
                    // Marcar presupuesto como facturado si aplica
                    if ($datos["id_presupuesto"]) {
                        $stmt = $pdo->prepare("UPDATE presupuesto SET facturado = 1 WHERE id_presupuesto = :id");
                        $stmt->bindParam(":id", $datos["id_presupuesto"], PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    
                    $pdo->commit();
                    return $id_factura;
                }
            }
            
            $pdo->rollBack();
            return "error";
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en mdlRegistrarFactura: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar estado de factura
    static public function mdlActualizarEstadoFactura($id, $estado) {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            
            // Obtener datos de la factura
            $stmt = $pdo->prepare("SELECT * FROM factura WHERE id_factura = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$factura) {
                $pdo->rollBack();
                return "error";
            }
            
            // Actualizar estado
            $stmt = $pdo->prepare("UPDATE factura SET estado = :estado WHERE id_factura = :id");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
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
            error_log("Error en mdlActualizarEstadoFactura: " . $e->getMessage());
            return "error";
        }
    }

    // Anular factura
    static public function mdlAnularFactura($id, $motivo) {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            
            // Obtener factura
            $stmt = $pdo->prepare("SELECT * FROM factura WHERE id_factura = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$factura) {
                $pdo->rollBack();
                return "error";
            }
            
            // Obtener detalles para restaurar stock
            $detalles = ModeloDetalleFactura::mdlObtenerDetalles($id);
            
            // Restaurar stock de productos
            foreach ($detalles as $detalle) {
                if ($detalle['tipo'] == 'producto' && $detalle['id_producto']) {
                    // Restaurar stock
                    $stmt = $pdo->prepare("UPDATE producto SET stock = stock + :cantidad WHERE id_producto = :id");
                    $stmt->bindParam(":cantidad", $detalle["cantidad"], PDO::PARAM_INT);
                    $stmt->bindParam(":id", $detalle["id_producto"], PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Registrar en historial
                    $stmt = $pdo->prepare("
                        INSERT INTO historial_stock 
                        (id_producto, id_factura, tipo_movimiento, cantidad_anterior, cantidad_movimiento, cantidad_actual, motivo, fecha, id_personal)
                        VALUES 
                        (:id_producto, :id_factura, 'entrada', 
                         (SELECT stock - :cantidad FROM producto WHERE id_producto = :id_producto2), 
                         :cantidad2, 
                         (SELECT stock FROM producto WHERE id_producto = :id_producto3), 
                         :motivo, NOW(), :id_personal)
                    ");
                    $stmt->bindParam(":id_producto", $detalle["id_producto"], PDO::PARAM_INT);
                    $stmt->bindParam(":id_factura", $id, PDO::PARAM_INT);
                    $stmt->bindParam(":cantidad", $detalle["cantidad"], PDO::PARAM_INT);
                    $stmt->bindParam(":id_producto2", $detalle["id_producto"], PDO::PARAM_INT);
                    $stmt->bindParam(":cantidad2", $detalle["cantidad"], PDO::PARAM_INT);
                    $stmt->bindParam(":id_producto3", $detalle["id_producto"], PDO::PARAM_INT);
                    $stmt->bindParam(":motivo", $motivo, PDO::PARAM_STR);
                    $stmt->bindParam(":id_personal", $factura["id_personal"], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            
            // Anular factura
            $stmt = $pdo->prepare("UPDATE factura SET estado = 'anulada', observaciones = CONCAT(IFNULL(observaciones, ''), ' - ANULADA: ', :motivo) WHERE id_factura = :id");
            $stmt->bindParam(":motivo", $motivo, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Desmarcar orden como facturada
                if ($factura["id_orden"]) {
                    $stmt = $pdo->prepare("UPDATE ordentrabajo SET facturado = 0 WHERE id_orden = :id");
                    $stmt->bindParam(":id", $factura["id_orden"], PDO::PARAM_INT);
                    $stmt->execute();
                }
                
                // Desmarcar presupuesto como facturado
                if ($factura["id_presupuesto"]) {
                    $stmt = $pdo->prepare("UPDATE presupuesto SET facturado = 0 WHERE id_presupuesto = :id");
                    $stmt->bindParam(":id", $factura["id_presupuesto"], PDO::PARAM_INT);
                    $stmt->execute();
                }
                
                $pdo->commit();
                return "ok";
            }
            
            $pdo->rollBack();
            return "error";
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en mdlAnularFactura: " . $e->getMessage());
            return "error";
        }
    }
}
