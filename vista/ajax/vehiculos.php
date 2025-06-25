<?php
require_once "../../controlador/vehiculo_controlador.php";

if(isset($_GET['id_cliente'])) {
    $id_cliente = $_GET['id_cliente'];
    $vehiculos = VehiculoControlador::ctrListarVehiculosCliente($id_cliente);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($vehiculos);
} else {
    // Return empty array if no client ID provided
    header('Content-Type: application/json');
    echo json_encode([]);
}
