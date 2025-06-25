<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

// Verificar permisos
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener lista de vehículos
$vehiculos = VehiculoControlador::ctrListarVehiculos();
?>

<title>VEHÍCULOS - Gestión de Vehículos</title>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 15px;
        text-align: center;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .table-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .table-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .search-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .search-input {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 25px;
        color: white;
        padding: 0.75rem 1.5rem;
        min-width: 300px;
        transition: all 0.3s ease;
    }

    .search-input::placeholder {
        color: rgba(255, 255, 255, 0.8);
    }

    .search-input:focus {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        outline: none;
        color: white;
    }

    .btn-nuevo {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    }

    .btn-nuevo:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
    }

    .custom-table {
        margin: 0;
        border: none;
    }

    .custom-table thead th {
        background: #f8f9fa;
        border: none;
        padding: 1rem;
        font-weight: 600;
        color: #2c3e50;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .custom-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #e9ecef;
    }

    .custom-table tbody tr:hover {
        background: #f8f9fa;
        transform: scale(1.01);
    }

    .custom-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border: none;
    }

    .vehicle-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .vehicle-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .vehicle-details h6 {
        margin: 0;
        color: #2c3e50;
        font-weight: 600;
        font-size: 1rem;
    }

    .vehicle-details small {
        color: #7f8c8d;
        display: block;
        margin-top: 0.25rem;
    }

    .client-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .client-name {
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .client-contact {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c757d;
        font-size: 0.85rem;
    }

    .vehicle-specs {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .spec-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c757d;
        font-size: 0.85rem;
    }

    .license-plate {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 5px;
        font-weight: bold;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        display: inline-block;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-activo {
        background: #d4edda;
        color: #155724;
    }

    .status-inactivo {
        background: #f8d7da;
        color: #721c24;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .btn-action {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .btn-view {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
    }

    .btn-edit {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
    }

    .btn-delete {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #2c3e50;
    }

    .stat-label {
        color: #7f8c8d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .filter-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: block;
    }

    .filter-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .filter-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        outline: none;
    }

    .btn-filter {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    @media (max-width: 768px) {
        .table-header {
            flex-direction: column;
            text-align: center;
        }
        
        .search-input {
            min-width: 100%;
        }
        
        .custom-table {
            font-size: 0.9rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }

        .vehicle-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .filter-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid">
    <!-- Encabezado de la página -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-car-front-fill"></i> Gestión de Vehículos
        </h1>
        <p class="page-subtitle">Administra toda la información de los vehículos registrados</p>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-car-front text-success"></i>
            </div>
            <div class="stat-number"><?php echo count($vehiculos); ?></div>
            <div class="stat-label">Total Vehículos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle text-success"></i>
            </div>
            <div class="stat-number"><?php echo count(array_filter($vehiculos, function($v) { return $v['estado'] == 'activo'; })); ?></div>
            <div class="stat-label">Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-tools text-warning"></i>
            </div>
            <div class="stat-number"><?php echo count(array_unique(array_column($vehiculos, 'marca'))); ?></div>
            <div class="stat-label">Marcas</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-plus text-info"></i>
            </div>
            <div class="stat-number"><?php echo count(array_filter($vehiculos, function($v) { return date('Y-m-d', strtotime($v['fecha_creacion'])) == date('Y-m-d'); })); ?></div>
            <div class="stat-label">Nuevos Hoy</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-container">
        <h5 class="mb-3"><i class="bi bi-funnel"></i> Filtros</h5>
        <div class="filter-row">
            <div class="filter-group">
                <label>Marca</label>
                <select class="filter-control" id="filtroMarca">
                    <option value="">Todas las marcas</option>
                    <?php
                    $marcas = array_unique(array_column($vehiculos, 'marca'));
                    sort($marcas);
                    foreach($marcas as $marca): ?>
                        <option value="<?php echo htmlspecialchars($marca); ?>"><?php echo htmlspecialchars($marca); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Año</label>
                <select class="filter-control" id="filtroAño">
                    <option value="">Todos los años</option>
                    <?php
                    $años = array_unique(array_column($vehiculos, 'año'));
                    rsort($años);
                    foreach($años as $año): ?>
                        <option value="<?php echo $año; ?>"><?php echo $año; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Combustible</label>
                <select class="filter-control" id="filtroCombustible">
                    <option value="">Todos</option>
                    <option value="gasolina">Gasolina</option>
                    <option value="diesel">Diésel</option>
                    <option value="electrico">Eléctrico</option>
                    <option value="hibrido">Híbrido</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Estado</label>
                <select class="filter-control" id="filtroEstado">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <button class="btn-filter" onclick="limpiarFiltros()">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de vehículos -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="mb-0">
                <i class="bi bi-list-ul"></i> Lista de Vehículos
            </h3>
            <div class="search-container">
                <input type="text" class="search-input" id="searchVehiculos" placeholder="Buscar por marca, modelo, matrícula o cliente...">
                <a href="index.php?pagina=nuevo/vehiculo" class="btn-nuevo">
                    <i class="bi bi-car-front"></i> Agregar Vehículo
                </a>
            </div>
        </div>

        <?php if (!empty($vehiculos)): ?>
            <div class="table-responsive">
                <table class="table custom-table" id="tablaVehiculos">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Cliente</th>
                            <th>Especificaciones</th>
                            <th>Matrícula</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <tr>
                                <td>
                                    <div class="vehicle-info">
                                        <div class="vehicle-icon">
                                            <i class="bi bi-car-front-fill"></i>
                                        </div>
                                        <div class="vehicle-details">
                                            <h6><?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></h6>
                                            <small>
                                                <i class="bi bi-calendar"></i> <?php echo htmlspecialchars($vehiculo['año']); ?>
                                                | <i class="bi bi-palette"></i> <?php echo htmlspecialchars($vehiculo['color']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="client-info">
                                        <h6 class="client-name"><?php echo htmlspecialchars($vehiculo['nombre_cliente']); ?></h6>
                                        <div class="client-contact">
                                            <i class="bi bi-telephone"></i>
                                            <span><?php echo htmlspecialchars($vehiculo['telefono_cliente']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="vehicle-specs">
                                        <div class="spec-item">
                                            <i class="bi bi-fuel-pump"></i>
                                            <span><?php echo ucfirst($vehiculo['combustible']); ?></span>
                                        </div>
                                        <div class="spec-item">
                                            <i class="bi bi-gear"></i>
                                            <span><?php echo ucfirst($vehiculo['transmision']); ?></span>
                                        </div>
                                        <?php if (!empty($vehiculo['kilometraje'])): ?>
                                            <div class="spec-item">
                                                <i class="bi bi-speedometer2"></i>
                                                <span><?php echo number_format($vehiculo['kilometraje']); ?> km</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="license-plate">
                                        <?php echo htmlspecialchars($vehiculo['matricula']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $vehiculo['estado'] == 'activo' ? 'status-activo' : 'status-inactivo'; ?>">
                                        <?php echo ucfirst($vehiculo['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="spec-item">
                                        <i class="bi bi-calendar3"></i>
                                        <span><?php echo date('d/m/Y', strtotime($vehiculo['fecha_creacion'])); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-action btn-view" 
                                                onclick="verVehiculo(<?php echo $vehiculo['id_vehiculo']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?pagina=editar/vehiculo&id=<?php echo $vehiculo['id_vehiculo']; ?>" 
                                           class="btn btn-action btn-edit" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-action btn-delete" 
                                                onclick="eliminarVehiculo(<?php echo $vehiculo['id_vehiculo']; ?>, '<?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' (' . $vehiculo['matricula'] . ')'); ?>')"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-car-front"></i>
                <h4>No hay vehículos registrados</h4>
                <p>Comienza agregando el primer vehículo</p>
                <a href="index.php?pagina=nuevo/vehiculo" class="btn-nuevo">
                    <i class="bi bi-car-front"></i> Agregar Primer Vehículo
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para ver detalles del vehículo -->
<div class="modal fade" id="modalVerVehiculo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-car-front"></i> Detalles del Vehículo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalVehiculoContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
    // Búsqueda en tiempo real
    document.getElementById('searchVehiculos').addEventListener('input', function() {
        filtrarTabla();
    });

    // Filtros
    document.getElementById('filtroMarca').addEventListener('change', filtrarTabla);
    document.getElementById('filtroAño').addEventListener('change', filtrarTabla);
    document.getElementById('filtroCombustible').addEventListener('change', filtrarTabla);
    document.getElementById('filtroEstado').addEventListener('change', filtrarTabla);

    function filtrarTabla() {
        const searchTerm = document.getElementById('searchVehiculos').value.toLowerCase();
        const marcaFilter = document.getElementById('filtroMarca').value.toLowerCase();
        const añoFilter = document.getElementById('filtroAño').value;
        const combustibleFilter = document.getElementById('filtroCombustible').value.toLowerCase();
        const estadoFilter = document.getElementById('filtroEstado').value.toLowerCase();
        
        const tableRows = document.querySelectorAll('#tablaVehiculos tbody tr');
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const marca = row.querySelector('.vehicle-details h6').textContent.toLowerCase();
            const año = row.querySelector('.vehicle-details small').textContent;
            const combustible = row.querySelector('.spec-item:first-child span').textContent.toLowerCase();
            const estado = row.querySelector('.status-badge').textContent.toLowerCase();
            
            const matchesSearch = text.includes(searchTerm);
            const matchesMarca = !marcaFilter || marca.includes(marcaFilter);
            const matchesAño = !añoFilter || año.includes(añoFilter);
            const matchesCombustible = !combustibleFilter || combustible.includes(combustibleFilter);
            const matchesEstado = !estadoFilter || estado.includes(estadoFilter);
            
            row.style.display = matchesSearch && matchesMarca && matchesAño && matchesCombustible && matchesEstado ? '' : 'none';
        });
    }

    function limpiarFiltros() {
        document.getElementById('searchVehiculos').value = '';
        document.getElementById('filtroMarca').value = '';
        document.getElementById('filtroAño').value = '';
        document.getElementById('filtroCombustible').value = '';
        document.getElementById('filtroEstado').value = '';
        filtrarTabla();
    }

    // Ver detalles del vehículo
    function verVehiculo(id) {
        fetch(`index.php?pagina=obtener_vehiculo&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const vehiculo = data.vehiculo;
                    document.getElementById('modalVehiculoContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-car-front"></i> Información del Vehículo</h6>
                                <p><strong>Marca:</strong> ${vehiculo.marca}</p>
                                <p><strong>Modelo:</strong> ${vehiculo.modelo}</p>
                                <p><strong>Año:</strong> ${vehiculo.año}</p>
                                <p><strong>Color:</strong> ${vehiculo.color}</p>
                                <p><strong>Matrícula:</strong> ${vehiculo.matricula}</p>
                                <p><strong>Combustible:</strong> ${vehiculo.combustible}</p>
                                <p><strong>Transmisión:</strong> ${vehiculo.transmision}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-person"></i> Información del Propietario</h6>
                                <p><strong>Cliente:</strong> ${vehiculo.nombre_cliente}</p>
                                <p><strong>Teléfono:</strong> ${vehiculo.telefono_cliente}</p>
                                <p><strong>Email:</strong> ${vehiculo.email_cliente || 'No especificado'}</p>
                                
                                <h6><i class="bi bi-gear"></i> Datos Técnicos</h6>
                                <p><strong>Chasis:</strong> ${vehiculo.chasis || 'No especificado'}</p>
                                <p><strong>Motor:</strong> ${vehiculo.motor || 'No especificado'}</p>
                                <p><strong>Kilometraje:</strong> ${vehiculo.kilometraje ? vehiculo.kilometraje + ' km' : 'No especificado'}</p>
                            </div>
                        </div>
                        ${vehiculo.observaciones ? `
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6><i class="bi bi-chat-left-text"></i> Observaciones</h6>
                                    <p>${vehiculo.observaciones}</p>
                                </div>
                            </div>
                        ` : ''}
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalVerVehiculo'));
                    modal.show();
                } else {
                    Swal.fire('Error', 'No se pudieron cargar los detalles del vehículo', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    }

    // Eliminar vehículo
    function eliminarVehiculo(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el vehículo "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('index.php?pagina=eliminar_vehiculo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_vehiculo=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', 'Vehículo eliminado correctamente', 'success')
                            .then(() => {
                                location.reload();
                            });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el vehículo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                });
            }
        });
    }

    // Animaciones de entrada
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('#tablaVehiculos tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 50);
        });
    });
</script>