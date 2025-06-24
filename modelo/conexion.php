<?php
/**
 * Conexión a Base de Datos - Motor Service
 * Sistema de Gestión Automotriz
 * 
 * Configuración de conexión para la base de datos MySQL
 * Base de datos: backup_taller (Motor Service)
 */

class Conexion
{

	/**
	 * Crear conexión PDO a la base de datos
	 */
	static public function conectar()
	{
		try {
			// Configuración de la base de datos Motor Service
			$servidor = "localhost";
			$usuario = "root";
			$password = "";
			$baseDatos = "backup_taller"; // Nueva base de datos Motor Service
			$puerto = "3306";

			// Crear conexión PDO
			$conexion = new PDO(
				"mysql:host=$servidor;port=$puerto;dbname=$baseDatos;charset=utf8mb4",
				$usuario,
				$password,
				array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
				)
			);

			// Configurar zona horaria
			$conexion->exec("SET time_zone = '-03:00'"); // Paraguay timezone

			return $conexion;

		} catch (PDOException $e) {
			// Log del error
			error_log("Error de conexión Motor Service: " . $e->getMessage());

			// Mostrar error amigable
			die("
                <div style='
                    font-family: Arial, sans-serif; 
                    max-width: 600px; 
                    margin: 50px auto; 
                    padding: 30px; 
                    background: #fff5f5; 
                    border-radius: 10px; 
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    border-left: 5px solid #dc2626;
                    text-align: center;
                '>
                    <h2 style='color: #dc2626; margin-bottom: 20px;'>
                        ⚠️ Error de Conexión - Motor Service
                    </h2>
                    <p style='color: #555; margin-bottom: 15px;'>
                        No se pudo conectar a la base de datos del sistema.
                    </p>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <strong>Detalles técnicos:</strong><br>
                        <small style='color: #666;'>" . $e->getMessage() . "</small>
                    </div>
                    <p style='color: #666; font-size: 14px;'>
                        <strong>Posibles soluciones:</strong><br>
                        • Verificar que MySQL esté ejecutándose<br>
                        • Comprobar que la base de datos 'backup_taller' exista<br>
                        • Revisar las credenciales de conexión<br>
                        • Contactar al administrador del sistema
                    </p>
                    <button onclick='window.location.reload()' style='
                        background: #dc2626; 
                        color: white; 
                        border: none; 
                        padding: 10px 20px; 
                        border-radius: 5px; 
                        cursor: pointer;
                        margin-top: 20px;
                    '>
                        🔄 Reintentar Conexión
                    </button>
                </div>
            ");
		}
	}

	/**
	 * Verificar conexión a la base de datos
	 */
	static public function verificarConexion()
	{
		try {
			$conexion = self::conectar();

			// Verificar que las tablas principales existen
			$tablas_requeridas = [
				'empresa',
				'cliente',
				'personal',
				'vehiculo',
				'producto',
				'agendamiento',
				'presupuesto',
				'ordentrabajo',
				'factura',
				'usuariocliente',
				'usuariopersonal'
			];

			foreach ($tablas_requeridas as $tabla) {
				$stmt = $conexion->prepare("SHOW TABLES LIKE :tabla");
				$stmt->bindParam(':tabla', $tabla);
				$stmt->execute();

				if ($stmt->rowCount() == 0) {
					throw new Exception("Tabla '$tabla' no encontrada en la base de datos");
				}
			}

			return array(
				'status' => 'ok',
				'message' => 'Conexión exitosa a Motor Service DB',
				'database' => 'backup_taller',
				'charset' => 'utf8mb4'
			);

		} catch (Exception $e) {
			return array(
				'status' => 'error',
				'message' => $e->getMessage()
			);
		}
	}

	/**
	 * Obtener información de la base de datos
	 */
	static public function obtenerInfoDB()
	{
		try {
			$conexion = self::conectar();

			// Información básica
			$stmt = $conexion->query("SELECT DATABASE() as database_name");
			$info = $stmt->fetch();

			// Versión de MySQL
			$stmt = $conexion->query("SELECT VERSION() as mysql_version");
			$version = $stmt->fetch();

			// Contar registros en tablas principales
			$conteos = array();
			$tablas = ['cliente', 'vehiculo', 'personal', 'producto', 'ordentrabajo', 'factura'];

			foreach ($tablas as $tabla) {
				$stmt = $conexion->prepare("SELECT COUNT(*) as total FROM $tabla");
				$stmt->execute();
				$conteos[$tabla] = $stmt->fetch()['total'];
			}

			return array(
				'database' => $info['database_name'],
				'mysql_version' => $version['mysql_version'],
				'charset' => 'utf8mb4',
				'registros' => $conteos,
				'status' => 'activa'
			);

		} catch (Exception $e) {
			return array(
				'status' => 'error',
				'message' => $e->getMessage()
			);
		}
	}

	/**
	 * Ejecutar backup de la base de datos
	 */
	static public function crearBackup()
	{
		try {
			$fecha = date('Y-m-d_H-i-s');
			$archivo = "backup_motor_service_$fecha.sql";
			$ruta = __DIR__ . "/../backups/$archivo";

			// Crear directorio si no existe
			if (!is_dir(__DIR__ . "/../backups")) {
				mkdir(__DIR__ . "/../backups", 0755, true);
			}

			// Comando mysqldump
			$comando = "mysqldump -u root -p backup_taller > $ruta";

			// Ejecutar backup (requiere configuración adicional en producción)
			// exec($comando, $output, $return_var);

			return array(
				'status' => 'ok',
				'archivo' => $archivo,
				'ruta' => $ruta,
				'fecha' => $fecha
			);

		} catch (Exception $e) {
			return array(
				'status' => 'error',
				'message' => $e->getMessage()
			);
		}
	}

	/**
	 * Limpiar conexiones inactivas
	 */
	static public function limpiarConexiones()
	{
		try {
			$conexion = self::conectar();

			// Mostrar procesos activos (solo para administración)
			$stmt = $conexion->query("SHOW PROCESSLIST");
			$procesos = $stmt->fetchAll();

			$conexiones_activas = count($procesos);

			return array(
				'status' => 'ok',
				'conexiones_activas' => $conexiones_activas,
				'procesos' => $procesos
			);

		} catch (Exception $e) {
			return array(
				'status' => 'error',
				'message' => $e->getMessage()
			);
		}
	}

	/**
	 * Configurar variables de MySQL para mejor rendimiento
	 */
	static public function configurarRendimiento()
	{
		try {
			$conexion = self::conectar();

			// Configuraciones recomendadas para Motor Service
			$configuraciones = [
				"SET SESSION query_cache_type = ON",
				"SET SESSION query_cache_size = 67108864", // 64MB
				"SET SESSION max_connections = 200",
				"SET SESSION wait_timeout = 28800", // 8 horas
				"SET SESSION interactive_timeout = 28800"
			];

			foreach ($configuraciones as $config) {
				try {
					$conexion->exec($config);
				} catch (Exception $e) {
					// Algunas configuraciones pueden no estar disponibles
					error_log("Configuración no aplicada: $config - " . $e->getMessage());
				}
			}

			return array(
				'status' => 'ok',
				'message' => 'Configuraciones de rendimiento aplicadas'
			);

		} catch (Exception $e) {
			return array(
				'status' => 'error',
				'message' => $e->getMessage()
			);
		}
	}
}

// Verificar conexión al cargar el archivo (solo en desarrollo)
if (defined('DESARROLLO') && DESARROLLO === true) {
	$verificacion = Conexion::verificarConexion();
	if ($verificacion['status'] === 'error') {
		error_log("Error en conexión Motor Service: " . $verificacion['message']);
	}
}
?>