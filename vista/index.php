<?php
#EL INDEX: En él mostraremos la salida de las vistas al usuario y también a traves de él enviaremos las distintas acciones que el usuario envíe al controlador.
#require() establece que el código del archivo invocado es requerido, es decir, obligatorio para el funcionamiento del programa. Por ello, si el archivo especificado en la función require() no se encuentra saltará un error "PHP Fatal error" y el programa PHP se detendrá.
#La versión require_once() funcionan de la misma forma que sus respectivo, salvo que, al utilizar la versión _once, se impide la carga de un mismo archivo más de una vez.
#Si requerimos el mismo código más de una vez corremos el riesgo de redeclaraciones de variables, funciones o clases. 
require_once "../controlador/plantilla_controlador.php";
require_once "../controlador/login_controlador.php";
require_once "../controlador/cliente_controlador.php";
require_once "../controlador/agendamiento_controlador.php";
require_once "../controlador/personal_controlador.php";
require_once "../controlador/usuario_controlador.php";
require_once "../controlador/producto_controlador.php";
require_once "../controlador/historicocitas_controlador.php";
require_once "../controlador/orden_trabajo_controlador.php";
require_once "../controlador/vehiculo_controlador.php";
require_once "../controlador/presupuesto_controlador.php";
require_once "../modelo/modelo_orden_trabajo.php";
require_once "../modelo/modelo_vehiculo.php";
require_once "../modelo/modelo_historicocitas.php";
require_once "../modelo/modelo_producto.php";
require_once "../modelo/modelo_usuario.php";
require_once "../modelo/modelo_personal.php";
require_once "../modelo/modelo_agendamiento.php";
require_once "../modelo/modelo_cliente.php";
require_once "../modelo/modelo_presupuesto.php";
require_once "../modelo/login_modelo.php";
require_once "../controlador/factura_controlador.php";
require_once "../modelo/modelo_factura.php";
require_once "../modelo/modelo_detalle_factura.php";
$plantilla = new ControladorPlantilla();
$plantilla->ctrTraerPlantilla();
?>