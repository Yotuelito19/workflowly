<?php
/**
 * Búsqueda y listado de eventos - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Obtener parámetros de búsqueda
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$tipo = isset($_GET['tipo']) ? sanitize_input($_GET['tipo']) : '';
$ubicacion = isset($_GET['ubicacion']) ? sanitize_input($_GET['ubicacion']) : '';
$fecha_desde = isset($_GET['fecha_desde']) ? sanitize_input($_GET['fecha_desde']) : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? sanitize_input($_GET['fecha_hasta']) : '';

// Conectar BD y obtener eventos
$database = new Database();
$db = $database->getConnection();
$eventoModel = new Evento($db);

if (!empty($search) || !empty($tipo) || !empty($fecha_desde) || !empty($fecha_hasta)) {
    $eventos = $eventoModel->buscarEventos($search, $tipo, $fecha_desde, $fecha_hasta);
} else {
    $eventos = $eventoModel->obtenerEventosDisponibles(50, 0);
}

// Obtener tipos de eventos únicos para el filtro
$queryTipos = "SELECT DISTINCT tipo FROM Evento WHERE idEstadoEvento = (SELECT idEstado FROM Estado WHERE nombre = 'Activo' LIMIT 1)";
$stmtTipos = $db->prepare($queryTipos);
$stmtTipos->execute();
$tiposEventos = $stmtTipos->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Eventos - WorkFlowly</title>
    <link rel="stylesheet" href="../assets/css/search-events.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <a href="../index.php" class="logo">
                    <div class="logo-circle">
                        <span>W</span>
                    </div>
                    <span class="brand-name">WorkFlowly</span>
                </a>
            </div>
            <nav class="nav-menu">
                <a href="search-events.php" class="active">Eventos</a>
                <a href="../index.php">Inicio</a>
            </nav>
            <div class="nav-actions">
                <?php if (is_logged_in()): ?>
                    <a href="account.php" class="btn-secondary">Mi Cuenta</a>
                    <a href="../api/logout.php" class="btn-link">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-secondary">Iniciar Sesión</a>
                    <a href="login.php" class="btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-header">
                <h1>Encuentra tu evento perfecto</h1>
                <p>Descubre eventos increíbles cerca de ti sin precios inflados</p>
            </div>
            
            <form class="search-box" method="GET" action="">
                <div class="search-field">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="¿Qué evento buscas?" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="search-field">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" name="ubicacion" placeholder="Ciudad" value="<?php echo htmlspecialchars($ubicacion); ?>">
                </div>
                <div class="search-field">
                    <i class="fas fa-calendar"></i>
                    <input type="date" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                </div>
                <button type="submit" class="btn-search">Buscar</button>
            </form>
        </div>
    </section>

    <!-- Filters and Results -->
    <section class="results-section">
        <div class="container">
            <div class="results-layout">
                <!-- Sidebar Filters -->
                <aside class="filters-sidebar">
                    <div class="filters-header">
                        <h3>Filtros</h3>
                        <a href="search-events.php" class="clear-filters">Limpiar todo</a>
                    </div>

                    <form method="GET" action="">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        
                        <div class="filter-group">
                            <h4>Categorías</h4>
                            <div class="filter-options">
                                <?php foreach ($tiposEventos as $tipoEvento): ?>
                                <label class="filter-checkbox">
                                    <input type="radio" name="tipo" value="<?php echo htmlspecialchars($tipoEvento); ?>" 
                                           <?php echo $tipo === $tipoEvento ? 'checked' : ''; ?>
                                           onchange="this.form.submit()">
                                    <span class="checkmark"></span>
                                    <?php echo htmlspecialchars($tipoEvento); ?>
                                </label>
                                <?php endforeach; ?>
                                
                                <?php if (!empty($tipo)): ?>
                                <label class="filter-checkbox">
                                    <input type="radio" name="tipo" value="" onchange="this.form.submit()">
                                    <span class="checkmark"></span>
                                    Todos
                                </label>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4>Rango de fechas</h4>
                            <div class="filter-options">
                                <div class="date-range">
                                    <label>Desde:</label>
                                    <input type="date" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                                </div>
                                <div class="date-range">
                                    <label>Hasta:</label>
                                    <input type="date" name="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                                </div>
                                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Aplicar</button>
                            </div>
                        </div>
                    </form>
                </aside>

                <!-- Results Grid -->
                <div class="results-content">
                    <div class="results-header">
                        <h2><?php echo count($eventos); ?> eventos encontrados</h2>
                        <div class="results-sort">
                            <label>Ordenar por:</label>
                            <select>
                                <option>Fecha (más cercano)</option>
                                <option>Precio (menor a mayor)</option>
                                <option>Precio (mayor a menor)</option>
                                <option>Popularidad</option>
                            </select>
                        </div>
                    </div>

                    <div class="events-grid">
                        <?php if (!empty($eventos)): ?>
                            <?php foreach ($eventos as $evento): ?>
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo UPLOADS_URL . '/' . $evento['imagenPrincipal']; ?>" 
                                             alt="<?php echo htmlspecialchars($evento['nombre']); ?>"
                                             onerror="this.src='https://via.placeholder.com/400x250?text=<?php echo urlencode($evento['nombre']); ?>'">
                                        <span class="event-category"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                                        <button class="favorite-btn">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="event-content">
                                        <h3><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                        <div class="event-details">
                                            <div class="detail-item">
                                                <i class="fas fa-calendar"></i>
                                                <span><?php echo format_date($evento['fechaInicio']); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($evento['ubicacion']); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-ticket-alt"></i>
                                                <span><?php echo $evento['entradasDisponibles']; ?> disponibles</span>
                                            </div>
                                        </div>
                                        <div class="event-footer">
                                            <div class="price-info">
                                                <span class="price-label">Desde</span>
                                                <span class="price"><?php echo format_price($evento['precio_desde'] ?? 0); ?></span>
                                            </div>
                                            <a href="event-detail.php?id=<?php echo $evento['idEvento']; ?>" class="btn-primary">Ver detalles</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-results">
                                <i class="fas fa-search"></i>
                                <h3>No se encontraron eventos</h3>
                                <p>Intenta ajustar tus filtros de búsqueda</p>
                                <a href="search-events.php" class="btn-primary">Ver todos los eventos</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
