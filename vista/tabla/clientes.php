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

// Obtener lista de clientes
$clientes = ClienteControlador::ctrListarClientes();
?>

<title>CLIENTES - Gestión de Clientes</title>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    .client-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: 1rem;
    }

    .client-info {
        display: flex;
        align-items: center;
    }

    .client-details h6 {
        margin: 0;
        color: #2c3e50;
        font-weight: 600;
    }

    .client-details small {
        color: #7f8c8d;
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6c757d;
        font-size: 0.85rem;
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
    }
</style>

<div class="container-fluid">
    <!-- Encabezado de la página -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-people-fill"></i> Gestión de Clientes
        </h1>
        <p class="page-subtitle">Administra toda la información de tus clientes</p>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-people text-primary"></i>
            </div>
            <div class="stat-number"><?php echo count($clientes); ?></div>
            <div class="stat-label">Total Clientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-person-check text-success"></i>
            </div>
            <div class="stat-number"><?php echo count(array_filter($clientes, function($c) { return $c['estado'] == 'activo'; })); ?></div>
            <div class="stat-label">Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-car-front text-info"></i>
            </div>
            <div class="stat-number"><?php echo array_sum(array_column($clientes, 'total_vehiculos')); ?></div>
            <div class="stat-label">Vehículos Registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-plus text-warning"></i>
            </div>
            <div class="stat-number"><?php echo count(array_filter($clientes, function($c) { return date('Y-m-d', strtotime($c['fecha_creacion'])) == date('Y-m-d'); })); ?></div>
            <div class="stat-label">Nuevos Hoy</div>
        </div>
    </div>

    <!-- Tabla de clientes -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="mb-0">
                <i class="bi bi-list-ul"></i> Lista de Clientes
            </h3>
            <div class="search-container">
                <input type="text" class="search-input" id="searchClientes" placeholder="Buscar por nombre, teléfono o email...">
                <a href="index.php?pagina=nuevo/cliente" class="btn-nuevo">
                    <i class="bi bi-person-plus"></i> Agregar Cliente
                </a>
            </div>
        </div>

        <?php if (!empty($clientes)): ?>
            <div class="table-responsive">
                <table class="table custom-table" id="tablaClientes">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Documento</th>
                            <th>Vehículos</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <div class="client-info">
                                        <div class="client-avatar">
                                            <?php echo strtoupper(substr($cliente['nombre'], 0, 1) . substr($cliente['apellido'], 0, 1)); ?>
                                        </div>
                                        <div class="client-details">
                                            <h6><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></h6>
                                            <small><?php echo htmlspecialchars($cliente['direccion'] ?: 'Sin dirección'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <div class="contact-item">
                                            <i class="bi bi-telephone"></i>
                                            <span><?php echo htmlspecialchars($cliente['telefono']); ?></span>
                                        </div>
                                        <?php if (!empty($cliente['email'])): ?>
                                            <div class="contact-item">
                                                <i class="bi bi-envelope"></i>
                                                <span><?php echo htmlspecialchars($cliente['email']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($cliente['cedula'])): ?>
                                        <div class="contact-item">
                                            <i class="bi bi-card-text"></i>
                                            <span><?php echo htmlspecialchars($cliente['cedula']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($cliente['ruc'])): ?>
                                        <div class="contact-item">
                                            <i class="bi bi-building"></i>
                                            <span><?php echo htmlspecialchars($cliente['ruc']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (empty($cliente['cedula']) && empty($cliente['ruc'])): ?>
                                        <small class="text-muted">Sin documento</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="badge bg-info rounded-pill">
                                            <?php echo $cliente['total_vehiculos']; ?> vehículo<?php echo $cliente['total_vehiculos'] != 1 ? 's' : ''; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $cliente['estado'] == 'activo' ? 'status-activo' : 'status-inactivo'; ?>">
                                        <?php echo ucfirst($cliente['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="contact-item">
                                        <i class="bi bi-calendar3"></i>
                                        <span><?php echo date('d/m/Y', strtotime($cliente['fecha_creacion'])); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-action btn-view" 
                                                onclick="verCliente(<?php echo $cliente['id_cliente']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?pagina=editar/cliente&id=<?php echo $cliente['id_cliente']; ?>" 
                                           class="btn btn-action btn-edit" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-action btn-delete" 
                                                onclick="eliminarCliente(<?php echo $cliente['id_cliente']; ?>, '<?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>')"
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
                <i class="bi bi-people"></i>
                <h4>No hay clientes registrados</h4>
                <p>Comienza agregando tu primer cliente</p>
                <a href="index.php?pagina=nuevo/cliente" class="btn-nuevo">
                    <i class="bi bi-person-plus"></i> Agregar Primer Cliente
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para ver detalles del cliente -->
<div class="modal fade" id="modalVerCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle"></i> Detalles del Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalClienteContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
    // Búsqueda en tiempo real
    document.getElementById('searchClientes').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('#tablaClientes tbody tr');
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Ver detalles del cliente
    function verCliente(id) {
        // Cargar detalles del cliente via AJAX
        fetch(`index.php?pagina=obtener_cliente&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cliente = data.cliente;
                    document.getElementById('modalClienteContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-person"></i> Información Personal</h6>
                                <p><strong>Nombre:</strong> ${cliente.nombre} ${cliente.apellido}</p>
                                <p><strong>Cédula:</strong> ${cliente.cedula || 'No especificado'}</p>
                                <p><strong>RUC:</strong> ${cliente.ruc || 'No especificado'}</p>
                                <p><strong>Fecha de Nacimiento:</strong> ${cliente.fecha_nacimiento || 'No especificado'}</p>
                                <p><strong>Sexo:</strong> ${cliente.sexo || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-telephone"></i> Información de Contacto</h6>
                                <p><strong>Teléfono:</strong> ${cliente.telefono}</p>
                                <p><strong>Email:</strong> ${cliente.email || 'No especificado'}</p>
                                <p><strong>Dirección:</strong> ${cliente.direccion || 'No especificado'}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h6><i class="bi bi-car-front"></i> Vehículos Registrados</h6>
                                <p><strong>Total:</strong> ${cliente.total_vehiculos} vehículo(s)</p>
                                <p><strong>Órdenes de Trabajo:</strong> ${cliente.total_ordenes || 0}</p>
                            </div>
                        </div>
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalVerCliente'));
                    modal.show();
                } else {
                    Swal.fire('Error', 'No se pudieron cargar los detalles del cliente', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    }

    // Eliminar cliente
    function eliminarCliente(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar al cliente "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Realizar petición AJAX para eliminar
                fetch('index.php?pagina=eliminar_cliente', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_cliente=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', 'Cliente eliminado correctamente', 'success')
                            .then(() => {
                                location.reload();
                            });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el cliente', 'error');
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
        const rows = document.querySelectorAll('#tablaClientes tbody tr');
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