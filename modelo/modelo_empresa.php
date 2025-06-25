<?php

require_once "conexion.php";

class ModeloEmpresa
{
    // Obtener información de la empresa
    static public function mdlObtenerInfoEmpresa()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM empresa 
                WHERE id_empresa = 1 
                LIMIT 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no existe registro, crear uno por defecto
            if (!$resultado) {
                $datosDefault = array(
                    "nombre_empresa" => "Sistema de Taller",
                    "ruc" => "",
                    "direccion" => "",
                    "telefono" => "",
                    "email" => "",
                    "sitio_web" => "",
                    "logo" => "",
                    "eslogan" => "",
                    "descripcion" => "",
                    "fecha_fundacion" => null,
                    "propietario" => "",
                    "estado" => "activo"
                );
                
                self::mdlCrearEmpresaDefault($datosDefault);
                return $datosDefault;
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerInfoEmpresa: " . $e->getMessage());
            return array(
                "nombre_empresa" => "Sistema de Taller",
                "ruc" => "",
                "direccion" => "",
                "telefono" => "",
                "email" => "",
                "sitio_web" => "",
                "logo" => "",
                "eslogan" => "",
                "descripcion" => "",
                "fecha_fundacion" => null,
                "propietario" => "",
                "estado" => "activo"
            );
        }
    }

    // Crear empresa por defecto
    static private function mdlCrearEmpresaDefault($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO empresa (nombre_empresa, ruc, direccion, telefono, email, sitio_web, logo, eslogan, descripcion, fecha_fundacion, propietario, estado, fecha_creacion)
                VALUES (:nombre_empresa, :ruc, :direccion, :telefono, :email, :sitio_web, :logo, :eslogan, :descripcion, :fecha_fundacion, :propietario, :estado, NOW())
            ");

            $stmt->bindParam(":nombre_empresa", $datos["nombre_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":ruc", $datos["ruc"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":sitio_web", $datos["sitio_web"], PDO::PARAM_STR);
            $stmt->bindParam(":logo", $datos["logo"], PDO::PARAM_STR);
            $stmt->bindParam(":eslogan", $datos["eslogan"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fundacion", $datos["fecha_fundacion"], PDO::PARAM_STR);
            $stmt->bindParam(":propietario", $datos["propietario"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlCrearEmpresaDefault: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar información de la empresa
    static public function mdlActualizarEmpresa($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE empresa 
                SET nombre_empresa = :nombre_empresa,
                    ruc = :ruc,
                    direccion = :direccion,
                    telefono = :telefono,
                    email = :email,
                    sitio_web = :sitio_web,
                    logo = :logo,
                    eslogan = :eslogan,
                    descripcion = :descripcion,
                    fecha_fundacion = :fecha_fundacion,
                    propietario = :propietario,
                    estado = :estado,
                    fecha_actualizacion = NOW()
                WHERE id_empresa = 1
            ");

            $stmt->bindParam(":nombre_empresa", $datos["nombre_empresa"], PDO::PARAM_STR);
            $stmt->bindParam(":ruc", $datos["ruc"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":sitio_web", $datos["sitio_web"], PDO::PARAM_STR);
            $stmt->bindParam(":logo", $datos["logo"], PDO::PARAM_STR);
            $stmt->bindParam(":eslogan", $datos["eslogan"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fundacion", $datos["fecha_fundacion"], PDO::PARAM_STR);
            $stmt->bindParam(":propietario", $datos["propietario"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEmpresa: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener configuración de facturación
    static public function mdlObtenerConfiguracionFacturacion()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM configuracion_facturacion 
                WHERE id_configuracion = 1 
                LIMIT 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no existe, crear configuración por defecto
            if (!$resultado) {
                $configDefault = array(
                    "timbrado" => "",
                    "numero_establecimiento" => "001",
                    "punto_expedicion" => "001",
                    "numero_inicio" => 1,
                    "numero_fin" => 999999,
                    "numero_actual" => 1,
                    "fecha_inicio_vigencia" => date('Y-m-d'),
                    "fecha_fin_vigencia" => date('Y-m-d', strtotime('+1 year')),
                    "iva_porcentaje" => 10,
                    "estado" => "activo"
                );
                
                self::mdlCrearConfiguracionFacturacion($configDefault);
                return $configDefault;
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerConfiguracionFacturacion: " . $e->getMessage());
            return array();
        }
    }

    // Crear configuración de facturación por defecto
    static private function mdlCrearConfiguracionFacturacion($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO configuracion_facturacion (timbrado, numero_establecimiento, punto_expedicion, numero_inicio, numero_fin, numero_actual, fecha_inicio_vigencia, fecha_fin_vigencia, iva_porcentaje, estado, fecha_creacion)
                VALUES (:timbrado, :numero_establecimiento, :punto_expedicion, :numero_inicio, :numero_fin, :numero_actual, :fecha_inicio_vigencia, :fecha_fin_vigencia, :iva_porcentaje, :estado, NOW())
            ");

            $stmt->bindParam(":timbrado", $datos["timbrado"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_establecimiento", $datos["numero_establecimiento"], PDO::PARAM_STR);
            $stmt->bindParam(":punto_expedicion", $datos["punto_expedicion"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_inicio", $datos["numero_inicio"], PDO::PARAM_INT);
            $stmt->bindParam(":numero_fin", $datos["numero_fin"], PDO::PARAM_INT);
            $stmt->bindParam(":numero_actual", $datos["numero_actual"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_inicio_vigencia", $datos["fecha_inicio_vigencia"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin_vigencia", $datos["fecha_fin_vigencia"], PDO::PARAM_STR);
            $stmt->bindParam(":iva_porcentaje", $datos["iva_porcentaje"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlCrearConfiguracionFacturacion: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar configuración de facturación
    static public function mdlActualizarConfiguracionFacturacion($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE configuracion_facturacion 
                SET timbrado = :timbrado,
                    numero_establecimiento = :numero_establecimiento,
                    punto_expedicion = :punto_expedicion,
                    numero_inicio = :numero_inicio,
                    numero_fin = :numero_fin,
                    fecha_inicio_vigencia = :fecha_inicio_vigencia,
                    fecha_fin_vigencia = :fecha_fin_vigencia,
                    iva_porcentaje = :iva_porcentaje,
                    estado = :estado,
                    fecha_actualizacion = NOW()
                WHERE id_configuracion = 1
            ");

            $stmt->bindParam(":timbrado", $datos["timbrado"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_establecimiento", $datos["numero_establecimiento"], PDO::PARAM_STR);
            $stmt->bindParam(":punto_expedicion", $datos["punto_expedicion"], PDO::PARAM_STR);
            $stmt->bindParam(":numero_inicio", $datos["numero_inicio"], PDO::PARAM_INT);
            $stmt->bindParam(":numero_fin", $datos["numero_fin"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_inicio_vigencia", $datos["fecha_inicio_vigencia"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin_vigencia", $datos["fecha_fin_vigencia"], PDO::PARAM_STR);
            $stmt->bindParam(":iva_porcentaje", $datos["iva_porcentaje"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarConfiguracionFacturacion: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener siguiente número de factura
    static public function mdlObtenerSiguienteNumeroFactura()
    {
        try {
            $config = self::mdlObtenerConfiguracionFacturacion();
            
            if (empty($config)) {
                return "001-001-0000001";
            }
            
            $numero_actual = $config["numero_actual"];
            $establecimiento = str_pad($config["numero_establecimiento"], 3, "0", STR_PAD_LEFT);
            $punto_expedicion = str_pad($config["punto_expedicion"], 3, "0", STR_PAD_LEFT);
            $numero_factura = str_pad($numero_actual, 7, "0", STR_PAD_LEFT);
            
            return "$establecimiento-$punto_expedicion-$numero_factura";
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSiguienteNumeroFactura: " . $e->getMessage());
            return "001-001-0000001";
        }
    }

    // Incrementar número de factura
    static public function mdlIncrementarNumeroFactura()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE configuracion_facturacion 
                SET numero_actual = numero_actual + 1
                WHERE id_configuracion = 1
            ");
            
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlIncrementarNumeroFactura: " . $e->getMessage());
            return "error";
        }
    }

    // Verificar si el timbrado está vigente
    static public function mdlVerificarTimbradoVigente()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as vigente 
                FROM configuracion_facturacion 
                WHERE fecha_inicio_vigencia <= CURDATE() 
                AND fecha_fin_vigencia >= CURDATE()
                AND estado = 'activo'
                AND id_configuracion = 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['vigente'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlVerificarTimbradoVigente: " . $e->getMessage());
            return false;
        }
    }

    // Obtener información completa para facturas
    static public function mdlObtenerInfoFacturacion()
    {
        try {
            $empresa = self::mdlObtenerInfoEmpresa();
            $configuracion = self::mdlObtenerConfiguracionFacturacion();
            
            return array_merge($empresa, $configuracion);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerInfoFacturacion: " . $e->getMessage());
            return array();
        }
    }
}
?>