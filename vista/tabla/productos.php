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

// Obtener lista de productos
$productos = ProductoControlador::ctrListarProductos();
?>

<title>PRODUCTOS - Gestión de Inventario</title>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
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
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
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

    .product-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .product-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .product-details h6 {
        margin: 0;
        color: #2c3e50;
        font-weight: 600;
        font-size: 1rem;
    }

    .product-details small {
        color: #7f8c8d;
        display: block;
        margin-top: 0.25rem;
    }

    .product-code {
        background: #e9ecef;
        color: #495057;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        font-weight: bold;
    }

    .category-badge {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .price-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .price-sell {
        font-weight: bold;
        color: #28a745;
        font-size: 1.1rem;
    }

    .price-buy {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .stock-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .stock-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: #2c3e50;
    }

    .stock-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .stock-alto {
        background: #d4edda;
        color: #155724;
    }

    .stock-medio {
        background: #fff3cd;
        color: #856404;
    }

    .stock-bajo {
        background: #f8d7da;
        color: #721c24;
    }

    .stock-agotado {
        background: #f5c6cb;
        color: #721c24;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
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
        width: 100%;
    }

    .filter-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1);
        outline: none;
    }

    .btn-filter {
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
    }

    .alert-stock-bajo {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border: 1px solid #f5c6cb;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 2rem;
        color: #721c24;
    }

    .alert-stock-bajo h5 {
        color: #721c24;
        font-weight: bold;
        margin-bottom: 0.5rem;
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

        .product-info {
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
            <i class="bi bi-box-seam"></i> Gestión de Productos
        </h1>
        <p class="page-subtitle">Administra el inventario y catálogo de productos</p>
    </div>

    <!-- Alerta de stock bajo -->
    <?php 
    $productosStockBajo = array_filter($productos, function($p) { 
        return $p['stock'] <= $p['stock_minimo'] && $p['stock'] > 0; 
    });
    $productosAgotados = array_filter($productos, function($p) { 
        return $p['stock'] == 0; 
    });
    
    if (!empty($productosStockBajo) || !empty($productosAgotados)): ?>
        <div class="alert-stock-bajo">
            <h5><i class="bi bi-exclamation-triangle"></i> Alerta de Inventario</h5>
            <?php if (!empty($productosStockBajo)): ?>
                <p><strong><?php echo count($productosStockBajo); ?></strong> producto(s) con stock bajo.</p>
            <?php endif; ?>
            <?php if (!empty($productosAgotados)): ?>
                <p><strong><?php echo count($productosAgotados); ?></strong> producto(s) agotado(s).</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-box text-info"></i>
            </div>
            <div class="stat-number"><?php echo count($productos); ?></div>
            <div class="stat-label">Total Productos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle text-success"></i>
            </div>
            <div class="stat-number"><?php echo count(array_filter($productos, function($p) { return $p['estado'] == 'activo'; })); ?></div>
            <div class="stat-label">Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-tags text-primary"></i>
            </div>
            <div class="stat-number"><?php echo count(array_unique(array_column($productos, 'categoria'))); ?></div>
            <div class="stat-label">Categorías</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-exclamation-triangle text-warning"></i>
            </div>
            <div class="stat-number"><?php echo count($productosStockBajo) + count($productosAgotados); ?></div>
            <div class="stat-label">Stock Crítico</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-container">
        <h5 class="mb-3"><i class="bi bi-funnel"></i> Filtros</h5>
        <div class="filter-row">
            <div class="filter-group">
                <label>Categoría</label>
                <select class="filter-control" id="filtroCategoria">
                    <option value="">Todas las categorías</option>
                    <?php
                    $categorias = array_unique(array_column($productos, 'categoria'));
                    sort($categorias);
                    foreach($categorias as $categoria): ?>
                        <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Estado de Stock</label>
                <select class="filter-control" id="filtroStock">
                    <option value="">Todos</option>
                    <option value="alto">Stock Alto</option>
                    <option value="medio">Stock Medio</option>
                    <option value="bajo">Stock Bajo</option>
                    <option value="agotado">Agotado</option>
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
                <label>Precio</label>
                <select class="filter-control" id="filtroPrecio">
                    <option value="">Todos</option>
                    <option value="0-50000">Hasta ₲50.000</option>
                    <option value="50000-100000">₲50.000 - ₲100.000</option>
                    <option value="100000-200000">₲100.000 - ₲200.000</option>
                    <option value="200000-999999999">Más de ₲200.000</option>
                </select>
            </div>
            <div class="filter-group">
                <button class="btn-filter" onclick="limpiarFiltros()">
                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de productos -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="mb-0">
                <i class="bi bi-list-ul"></i> Lista de Productos
            </h3>
            <div class="search-container">
                <input type="text" class="search-input" id="searchProductos" placeholder="Buscar por nombre, código o categoría...">
                <a href="index.php?pagina=nuevo/producto" class="btn-nuevo">
                    <i class="bi bi-plus-circle"></i> Agregar Producto
                </a>
            </div>
        </div>

        <?php if (!empty($productos)): ?>
            <div class="table-responsive">
                <table class="table custom-table" id="tablaProductos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Categoría</th>
                            <th>Precios</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <?php
                            // Determinar estado del stock
                            $stockStatus = 'alto';
                            if ($producto['stock'] == 0) {
                                $stockStatus = 'agotado';
                            } elseif ($producto['stock'] <= $producto['stock_minimo']) {
                                $stockStatus = 'bajo';
                            } elseif ($producto['stock'] <= $producto['stock_minimo'] * 2) {
                                $stockStatus = 'medio';
                            }
                            ?>
                            <tr data-categoria="<?php echo htmlspecialchars($producto['categoria']); ?>" 
                                data-stock="<?php echo $stockStatus; ?>" 
                                data-estado="<?php echo $producto['estado']; ?>"
                                data-precio="<?php echo $producto['precio']; ?>">
                                <td>
                                    <div class="product-info">
                                        <div class="product-icon">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div class="product-details">
                                            <h6><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                                            <small>
                                                <?php if (!empty($producto['marca'])): ?>
                                                    <i class="bi bi-tag"></i> <?php echo htmlspecialchars($producto['marca']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($producto['descripcion'])): ?>
                                                    | <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 30)); ?>...
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-code">
                                        <?php echo htmlspecialchars($producto['codigo']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="category-badge">
                                        <?php echo htmlspecialchars($producto['categoria']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="price-info">
                                        <div class="price-sell">
                                            ₲ <?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                                        </div>
                                        <?php if (!empty($producto['precio_compra'])): ?>
                                            <div class="price-buy">
                                                Compra: ₲ <?php echo number_format($producto['precio_compra'], 0, ',', '.'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="stock-info">
                                        <div class="stock-number"><?php echo $producto['stock']; ?></div>
                                        <div class="stock-status stock-<?php echo $stockStatus; ?>">
                                            <?php 
                                            switch($stockStatus) {
                                                case 'agotado': echo 'Agotado'; break;
                                                case 'bajo': echo 'Stock Bajo'; break;
                                                case 'medio': echo 'Stock Medio'; break;
                                                case 'alto': echo 'Stock OK'; break;
                                            }
                                            ?>
                                        </div>
                                        <small>Mín: <?php echo $producto['stock_minimo']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $producto['estado'] == 'activo' ? 'status-activo' : 'status-inactivo'; ?>">
                                        <?php echo ucfirst($producto['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-action btn-view" 
                                                onclick="verProducto(<?php echo $producto['id_producto']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?pagina=editar/producto&id=<?php echo $producto['id_producto']; ?>" 
                                           class="btn btn-action btn-edit" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-action btn-delete" 
                                                onclick="eliminarProducto(<?php echo $producto['id_producto']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>')"
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
                <i class="bi bi-box-seam"></i>
                <h4>No hay productos registrados</h4>
                <p>Comienza agregando tu primer producto al inventario</p>
                <a href="index.php?pagina=nuevo/producto" class="btn-nuevo">
                    <i class="bi bi-plus-circle"></i> Agregar Primer Producto
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para ver detalles del producto -->
<div class="modal fade" id="modalVerProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i> Detalles del Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalProductoContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
    // Búsqueda en tiempo real
    document.getElementById('searchProductos').addEventListener('input', function() {
        filtrarTabla();
    });

    // Filtros
    document.getElementById('filtroCategoria').addEventListener('change', filtrarTabla);
    document.getElementById('filtroStock').addEventListener('change', filtrarTabla);
    document.getElementById('filtroEstado').addEventListener('change', filtrarTabla);
    document.getElementById('filtroPrecio').addEventListener('change', filtrarTabla);

    function filtrarTabla() {
        const searchTerm = document.getElementById('searchProductos').value.toLowerCase();
        const categoriaFilter = document.getElementById('filtroCategoria').value.toLowerCase();
        const stockFilter = document.getElementById('filtroStock').value;
        const estadoFilter = document.getElementById('filtroEstado').value;
        const precioFilter = document.getElementById('filtroPrecio').value;
        
        const tableRows = document.querySelectorAll('#tablaProductos tbody tr');
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const categoria = row.getAttribute('data-categoria').toLowerCase();
            const stock = row.getAttribute('data-stock');
            const estado = row.getAttribute('data-estado');
            const precio = parseFloat(row.getAttribute('data-precio'));
            
            const matchesSearch = text.includes(searchTerm);
            const matchesCategoria = !categoriaFilter || categoria.includes(categoriaFilter);
            const matchesStock = !stockFilter || stock === stockFilter;
            const matchesEstado = !estadoFilter || estado === estadoFilter;
            
            let matchesPrecio = true;
            if (precioFilter) {
                const [min, max] = precioFilter.split('-').map(Number);
                matchesPrecio = precio >= min && precio <= max;
            }
            
            row.style.display = matchesSearch && matchesCategoria && matchesStock && matchesEstado && matchesPrecio ? '' : 'none';
        });
    }

    function limpiarFiltros() {
        document.getElementById('searchProductos').value = '';
        document.getElementById('filtroCategoria').value = '';
        document.getElementById('filtroStock').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroPrecio').value = '';
        filtrarTabla();
    }

    // Ver detalles del producto
    function verProducto(id) {
        fetch(`index.php?pagina=obtener_producto&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const producto = data.producto;
                    document.getElementById('modalProductoContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-box-seam"></i> Información General</h6>
                                <p><strong>Nombre:</strong> ${producto.nombre}</p>
                                <p><strong>Código:</strong> ${producto.codigo}</p>
                                <p><strong>Categoría:</strong> ${producto.categoria}</p>
                                <p><strong>Marca:</strong> ${producto.marca || 'No especificado'}</p>
                                <p><strong>Descripción:</strong> ${producto.descripcion || 'Sin descripción'}</p>
                                <p><strong>Unidad:</strong> ${producto.unidad_medida}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-currency-dollar"></i> Precios e Inventario</h6>
                                <p><strong>Precio de Venta:</strong> ₲ ${new Intl.NumberFormat('es-PY').format(producto.precio)}</p>
                                <p><strong>Precio de Compra:</strong> ${producto.precio_compra ? '₲ ' + new Intl.NumberFormat('es-PY').format(producto.precio_compra) : 'No especificado'}</p>
                                <p><strong>Stock Actual:</strong> ${producto.stock}</p>
                                <p><strong>Stock Mínimo:</strong> ${producto.stock_minimo}</p>
                                <p><strong>Ubicación:</strong> ${producto.ubicacion || 'No especificado'}</p>
                                <p><strong>Proveedor:</strong> ${producto.proveedor || 'No especificado'}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6><i class="bi bi-info-circle"></i> Estado y Fechas</h6>
                                <p><strong>Estado:</strong> <span class="badge ${producto.estado === 'activo' ? 'bg-success' : 'bg-secondary'}">${producto.estado}</span></p>
                                <p><strong>Fecha de Registro:</strong> ${new Date(producto.fecha_creacion).toLocaleDateString('es-PY')}</p>
                                <p><strong>Última Actualización:</strong> ${new Date(producto.fecha_actualizacion).toLocaleDateString('es-PY')}</p>
                            </div>
                        </div>
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalVerProducto'));
                    modal.show();
                } else {
                    Swal.fire('Error', 'No se pudieron cargar los detalles del producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
    }

    // Eliminar producto
    function eliminarProducto(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el producto "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('index.php?pagina=eliminar_producto', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_producto=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', 'Producto eliminado correctamente', 'success')
                            .then(() => {
                                location.reload();
                            });
                    } else {
                        Swal.fire('Error', data.message || 'Error al eliminar el producto', 'error');
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
        const rows = document.querySelectorAll('#tablaProductos tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 30);
        });
    });
</script>