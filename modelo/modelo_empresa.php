<?php
require_once "conexion.php";

class ModeloEmpresa
{

    /**
     * Obtener información de la empresa
     */
    static public function mdlObtenerInfoEmpresa()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM empresa WHERE id_empresa = 1
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerInfoEmpresa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar información de la empresa
     */
    static public function mdlActualizarEmpresa($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE empresa SET 
                    nombre_empresa = :nombre_empresa,
                    ruc_empresa = :ruc_empresa,
                    direccion_empresa = :direccion_empresa,
                    telefono_empresa = :telefono_empresa,
                    email_empresa = :email_empresa,
                    website_empresa = :website_empresa,
                    timbrado_numero = :timbrado_numero,
                    timbrado_vencimiento = :timbrado_vencimiento,
                    logo_path = :logo_path,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_empresa = 1
            ");

            $stmt->bindParam(":nombre_empresa", $datos["nombre_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":ruc_empresa", $datos["ruc_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion_empresa", $datos["direccion_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono_empresa", $datos["telefono_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":email_empresa", $datos["email_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":website_empresa", $datos["website_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":timbrado_numero", $datos["timbrado_numero"], PDO::PARAM_STR);
            $stmt->bindParam(":timbrado_vencimiento", $datos["timbrado_vencimiento"], PDO::PARAM_STR);
            $stmt->bindParam(":logo_path", $datos["logo_path"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarEmpresa: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener y actualizar número de factura
     */
    static public function mdlObtenerSiguienteNumeroFactura()
    {
        try {
            $conexion = Conexion::conectar();

            // Obtener número actual
            $stmt = $conexion->prepare("SELECT numero_factura_actual FROM empresa WHERE id_empresa = 1");
            $stmt->execute();
            $numero_actual = $stmt->fetchColumn();

            // Incrementar número
            $nuevo_numero = $numero_actual + 1;
            $stmt = $conexion->prepare("UPDATE empresa SET numero_factura_actual = :nuevo_numero WHERE id_empresa = 1");
            $stmt->bindParam(":nuevo_numero", $nuevo_numero, PDO::PARAM_INT);
            $stmt->execute();

            // Formatear número de factura
            return sprintf("001-001-%07d", $numero_actual);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSiguienteNumeroFactura: " . $e->getMessage());
            return sprintf("001-001-%07d", 1);
        }
    }

    /**
     * Verificar vencimiento del timbrado
     */
    static public function mdlVerificarTimbrado()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    timbrado_numero,
                    timbrado_vencimiento,
                    DATEDIFF(timbrado_vencimiento, CURDATE()) as dias_restantes
                FROM empresa WHERE id_empresa = 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                $resultado['esta_vencido'] = $resultado['dias_restantes'] < 0;
                $resultado['vence_pronto'] = $resultado['dias_restantes'] <= 30 && $resultado['dias_restantes'] >= 0;
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en mdlVerificarTimbrado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de facturación
     */
    static public function mdlEstadisticasFacturacion($periodo = 'mensual')
    {
        try {
            $sql = "";
            switch ($periodo) {
                case 'diaria':
                    $sql = "
                        SELECT 
                            DATE(fecha_emision) as periodo,
                            COUNT(*) as total_facturas,
                            SUM(total) as total_monto
                        FROM factura 
                        WHERE DATE(fecha_emision) >= CURDATE() - INTERVAL 7 DAY
                        GROUP BY DATE(fecha_emision)
                        ORDER BY periodo DESC
                    ";
                    break;
                case 'mensual':
                    $sql = "
                        SELECT 
                            DATE_FORMAT(fecha_emision, '%Y-%m') as periodo,
                            COUNT(*) as total_facturas,
                            SUM(total) as total_monto
                        FROM factura 
                        WHERE fecha_emision >= CURDATE() - INTERVAL 12 MONTH
                        GROUP BY DATE_FORMAT(fecha_emision, '%Y-%m')
                        ORDER BY periodo DESC
                    ";
                    break;
                case 'anual':
                    $sql = "
                        SELECT 
                            YEAR(fecha_emision) as periodo,
                            COUNT(*) as total_facturas,
                            SUM(total) as total_monto
                        FROM factura 
                        WHERE fecha_emision >= CURDATE() - INTERVAL 5 YEAR
                        GROUP BY YEAR(fecha_emision)
                        ORDER BY periodo DESC
                    ";
                    break;
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlEstadisticasFacturacion: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Configuración del sistema
     */
    static public function mdlObtenerConfiguracion()
    {
        try {
            $info_empresa = self::mdlObtenerInfoEmpresa();
            $timbrado = self::mdlVerificarTimbrado();

            return array(
                'empresa' => $info_empresa,
                'timbrado' => $timbrado,
                'sistema' => array(
                    'version' => '2.0',
                    'fecha_actualizacion' => date('Y-m-d'),
                    'desarrollado_para' => 'Motor Service - Servicio Integral Automotriz'
                )
            );
        } catch (Exception $e) {
            error_log("Error en mdlObtenerConfiguracion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear backup de la configuración de empresa
     */
    static public function mdlBackupConfiguracion()
    {
        try {
            $info = self::mdlObtenerInfoEmpresa();
            $fecha = date('Y-m-d_H-i-s');
            $backup_file = "../backups/empresa_config_$fecha.json";

            // Crear directorio si no existe
            if (!is_dir('../backups')) {
                mkdir('../backups', 0755, true);
            }

            return file_put_contents($backup_file, json_encode($info, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            error_log("Error en mdlBackupConfiguracion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restaurar configuración desde backup
     */
    static public function mdlRestaurarConfiguracion($archivo_backup)
    {
        try {
            if (!file_exists($archivo_backup)) {
                return "archivo_no_existe";
            }

            $datos = json_decode(file_get_contents($archivo_backup), true);
            if (!$datos) {
                return "archivo_invalido";
            }

            return self::mdlActualizarEmpresa($datos);
        } catch (Exception $e) {
            error_log("Error en mdlRestaurarConfiguracion: " . $e->getMessage());
            return "error";
        }
    }
}
?>