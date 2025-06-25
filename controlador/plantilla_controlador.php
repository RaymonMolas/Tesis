<?php

class ControladorPlantilla
{
    // Método para traer la plantilla principal
    public function ctrTraerPlantilla()
    {
        include "vista/plantilla.php";
    }

    // Obtener información de la empresa
    static public function ctrObtenerInfoEmpresa()
    {
        try {
            require_once __DIR__ . "/../modelo/modelo_empresa.php";
            return ModeloEmpresa::mdlObtenerInfoEmpresa();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerInfoEmpresa: " . $e->getMessage());
            return array(
                "nombre_empresa" => "Sistema de Taller",
                "direccion" => "",
                "telefono" => "",
                "email" => "",
                "ruc" => "",
                "logo" => ""
            );
        }
    }

    // Obtener configuración del sistema
    static public function ctrObtenerConfiguracion()
    {
        try {
            return array(
                "moneda" => "₲",
                "zona_horaria" => "America/Asuncion",
                "formato_fecha" => "d/m/Y",
                "formato_hora" => "H:i",
                "items_por_pagina" => 10,
                "theme" => "default"
            );
        } catch (Exception $e) {
            error_log("Error en ctrObtenerConfiguracion: " . $e->getMessage());
            return array();
        }
    }

    // Validar permisos de usuario
    static public function ctrValidarPermisos($pagina)
    {
        // Páginas públicas (no requieren autenticación)
        $paginasPublicas = array("login", "");
        
        if (in_array($pagina, $paginasPublicas)) {
            return true;
        }

        // Verificar si hay sesión activa
        if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
            return false;
        }

        // Páginas para personal
        $paginasPersonal = array(
            "inicio", "tabla/clientes", "tabla/usuarios", "tabla/personales", 
            "tabla/productos", "tabla/vehiculos", "tabla/presupuestos", 
            "tabla/facturas", "tabla/orden_trabajo", "tabla/historicocitas",
            "nuevo/cliente", "nuevo/usuario", "nuevo/personal", "nuevo/producto", 
            "nuevo/vehiculo", "nuevo/presupuesto", "nuevo/factura", "nuevo/orden_trabajo",
            "editar/cliente", "editar/usuario", "editar/personal", "editar/producto", 
            "editar/vehiculo", "editar/presupuesto", "editar/orden_trabajo",
            "ver/presupuesto", "ver/factura", "ver/orden_trabajo",
            "obtener_vehiculo", "marcar_leidas"
        );

        // Páginas para clientes
        $paginasCliente = array(
            "agendamiento", "tabla/historial"
        );

        $tipoUsuario = $_SESSION["tipo_usuario"] ?? "";

        if ($tipoUsuario == "personal") {
            return in_array($pagina, $paginasPersonal);
        } elseif ($tipoUsuario == "cliente") {
            return in_array($pagina, $paginasCliente);
        }

        return false;
    }

    // Obtener páginas disponibles
    static public function ctrObtenerPaginasDisponibles()
    {
        return array(
            "inicio",
            "login",
            "obtener_vehiculo",
            "agendamiento",
            "marcar_leidas",
            "tabla/clientes",
            "tabla/usuarios",
            "tabla/personales",
            "tabla/productos",
            "tabla/vehiculos",
            "tabla/presupuestos",
            "tabla/facturas",
            "tabla/orden_trabajo",
            "tabla/historicocitas",
            "tabla/historial",
            "nuevo/cliente",
            "nuevo/usuario", 
            "nuevo/personal",
            "nuevo/producto",
            "nuevo/vehiculo",
            "nuevo/presupuesto",
            "nuevo/factura",
            "nuevo/orden_trabajo",
            "editar/cliente",
            "editar/usuario",
            "editar/personal",
            "editar/producto",
            "editar/vehiculo",
            "editar/presupuesto",
            "editar/orden_trabajo",
            "ver/presupuesto",
            "ver/factura",
            "ver/orden_trabajo",
            "salir"
        );
    }

    // Generar breadcrumbs
    static public function ctrGenerarBreadcrumbs($pagina)
    {
        $breadcrumbs = array();
        
        switch ($pagina) {
            case "inicio":
                $breadcrumbs = array("Inicio");
                break;
                
            case "tabla/clientes":
                $breadcrumbs = array("Inicio", "Clientes");
                break;
                
            case "nuevo/cliente":
                $breadcrumbs = array("Inicio", "Clientes", "Nuevo Cliente");
                break;
                
            case "editar/cliente":
                $breadcrumbs = array("Inicio", "Clientes", "Editar Cliente");
                break;
                
            case "tabla/productos":
                $breadcrumbs = array("Inicio", "Productos");
                break;
                
            case "nuevo/producto":
                $breadcrumbs = array("Inicio", "Productos", "Nuevo Producto");
                break;
                
            case "tabla/presupuestos":
                $breadcrumbs = array("Inicio", "Presupuestos");
                break;
                
            case "nuevo/presupuesto":
                $breadcrumbs = array("Inicio", "Presupuestos", "Nuevo Presupuesto");
                break;
                
            case "tabla/orden_trabajo":
                $breadcrumbs = array("Inicio", "Órdenes de Trabajo");
                break;
                
            case "nuevo/orden_trabajo":
                $breadcrumbs = array("Inicio", "Órdenes de Trabajo", "Nueva Orden");
                break;
                
            case "tabla/facturas":
                $breadcrumbs = array("Inicio", "Facturas");
                break;
                
            case "tabla/historicocitas":
                $breadcrumbs = array("Inicio", "Historial de Citas");
                break;
                
            case "agendamiento":
                $breadcrumbs = array("Inicio", "Agendar Cita");
                break;
                
            default:
                $breadcrumbs = array("Inicio");
        }
        
        return $breadcrumbs;
    }

    // Formatear moneda
    static public function ctrFormatearMoneda($valor)
    {
        return "₲ " . number_format($valor, 0, ',', '.');
    }

    // Formatear fecha
    static public function ctrFormatearFecha($fecha, $formato = 'd/m/Y')
    {
        if (empty($fecha)) return "";
        
        try {
            $fechaObj = new DateTime($fecha);
            return $fechaObj->format($formato);
        } catch (Exception $e) {
            return $fecha;
        }
    }

    // Formatear fecha y hora
    static public function ctrFormatearFechaHora($fechaHora, $formato = 'd/m/Y H:i')
    {
        if (empty($fechaHora)) return "";
        
        try {
            $fechaObj = new DateTime($fechaHora);
            return $fechaObj->format($formato);
        } catch (Exception $e) {
            return $fechaHora;
        }
    }

    // Obtener estadísticas del dashboard
    static public function ctrObtenerEstadisticasDashboard()
    {
        try {
            require_once __DIR__ . "/cliente_controlador.php";
            require_once __DIR__ . "/vehiculo_controlador.php";
            require_once __DIR__ . "/orden_trabajo_controlador.php";
            require_once __DIR__ . "/presupuesto_controlador.php";
            
            return array(
                "total_clientes" => ClienteControlador::ctrContarClientes(),
                "total_vehiculos" => VehiculoControlador::ctrContarVehiculos(),
                "ordenes_pendientes" => OrdenTrabajoControlador::ctrContarOrdenesPorEstado("en_proceso"),
                "presupuestos_pendientes" => PresupuestoControlador::ctrContarPresupuestosPorEstado("pendiente")
            );
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasDashboard: " . $e->getMessage());
            return array();
        }
    }
}
?>