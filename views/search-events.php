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
$precio_min = isset($_GET['precio_min']) ? (int)$_GET['precio_min'] : 0;
$precio_max = isset($_GET['precio_max']) ? (int)$_GET['precio_max'] : 0;

// Conectar BD y obtener eventos
$database = new Database();
$db = $database->getConnection();
$eventoModel = new Evento($db);

if (!empty($search) || !empty($tipo) || !empty($fecha_desde) || !empty($fecha_hasta) || !empty($ubicacion) || $precio_min > 0 || $precio_max > 0) {
    $eventos = $eventoModel->buscarEventos($search, $tipo, $fecha_desde, $fecha_hasta, $ubicacion, $precio_min, $precio_max);
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
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

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
                    <select name="ubicacion">
                        <option value="">Todas las ciudades</option>
                        <option value="Madrid" <?php echo $ubicacion === 'Madrid' ? 'selected' : ''; ?>>Madrid</option>
                        <option value="Barcelona" <?php echo $ubicacion === 'Barcelona' ? 'selected' : ''; ?>>Barcelona</option>
                        <option value="Valencia" <?php echo $ubicacion === 'Valencia' ? 'selected' : ''; ?>>Valencia</option>
                        <option value="Sevilla" <?php echo $ubicacion === 'Sevilla' ? 'selected' : ''; ?>>Sevilla</option>
                    </select>
                </div>
                <div class="search-field date-range">
                    <i class="fas fa-calendar"></i>
                    <input type="date" name="fecha_desde" placeholder="Desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                    <span class="date-separator">-</span>
                    <input type="date" name="fecha_hasta" placeholder="Hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
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
                        <input type="hidden" name="ubicacion" value="<?php echo htmlspecialchars($ubicacion); ?>">
                        <input type="hidden" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                        <input type="hidden" name="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                        
                        <!-- Categorías (Radio buttons) -->
                        <div class="filter-group">
                            <h4>Categorías</h4>
                            <div class="filter-options">
                                <?php foreach ($tiposEventos as $tipoEvento): ?>
                                <label class="filter-radio">
                                    <input type="radio" name="tipo" value="<?php echo htmlspecialchars($tipoEvento); ?>" 
                                           <?php echo $tipo === $tipoEvento ? 'checked' : ''; ?>
                                           onchange="this.form.submit()">
                                    <span class="radiomark"></span>
                                    <?php echo htmlspecialchars($tipoEvento); ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Filtro de Precio -->
                        <div class="filter-group">
                            <div class="filter-group-header">
                                <h4>Precio</h4>
                                <?php if ($precio_min > 0 || $precio_max > 0): ?>
                                    <button type="button" class="reset-price-btn" onclick="resetPriceFilter()">Resetear</button>
                                <?php endif; ?>
                            </div>
                            <div class="price-range">
                                <div class="price-inputs">
                                    <input type="number" name="precio_min" id="precio_min" placeholder="Min" min="0" 
                                           value="<?php echo $precio_min > 0 ? $precio_min : ''; ?>">
                                    <span>-</span>
                                    <input type="number" name="precio_max" id="precio_max" placeholder="Max" min="0" 
                                           value="<?php echo $precio_max > 0 ? $precio_max : ''; ?>">
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
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>