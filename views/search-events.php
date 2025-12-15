<?php
/**
 * Búsqueda y listado de eventos - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Función helper para obtener la URL correcta de la imagen
function getImageUrl($imagePath) {
    // Si la imagen es default.jpg o no existe, usar placeholder
    if (empty($imagePath) || $imagePath === 'default.jpg' || $imagePath === 'imagen/default.jpg') {
        return BASE_URL . '/api/admin/events/uploads/0b10db93db401e3d.jpg';
    }
    
    // Limpiar la ruta: quitar 'uploads/' si existe para evitar duplicación
    $cleanPath = str_replace('uploads/', '', $imagePath);
    
    // Verificar si la imagen existe en /uploads/ (carpeta principal)
    $mainUploadPath = UPLOADS_PATH . '/' . $cleanPath;
    if (file_exists($mainUploadPath)) {
        return UPLOADS_URL . '/' . $cleanPath;
    }
    
    // Verificar si existe en /api/admin/events/uploads/
    $adminUploadPath = BASE_PATH . '/api/admin/events/uploads/' . $cleanPath;
    if (file_exists($adminUploadPath)) {
        return BASE_URL . '/api/admin/events/uploads/' . $cleanPath;
    }
    
    // Si no existe en ningún lado, retornar placeholder
    return BASE_URL . '/api/admin/events/uploads/0b10db93db401e3d.jpg';
}

// Obtener parámetros de búsqueda
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$tipo = isset($_GET['tipo']) ? sanitize_input($_GET['tipo']) : '';
$ubicacion = isset($_GET['ubicacion']) ? sanitize_input($_GET['ubicacion']) : '';
$fecha_desde = isset($_GET['fecha_desde']) ? sanitize_input($_GET['fecha_desde']) : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? sanitize_input($_GET['fecha_hasta']) : '';
$precio_min = isset($_GET['precio_min']) ? (int)$_GET['precio_min'] : 0;
$precio_max = isset($_GET['precio_max']) ? (int)$_GET['precio_max'] : 0;
$orden = isset($_GET['orden']) ? sanitize_input($_GET['orden']) : 'fecha_asc';

// Conectar BD y obtener eventos
$database = new Database();
$db = $database->getConnection();
$eventoModel = new Evento($db);

if (!empty($search) || !empty($tipo) || !empty($fecha_desde) || !empty($fecha_hasta) || !empty($ubicacion) || $precio_min > 0 || $precio_max > 0) {
    $eventos = $eventoModel->buscarEventos($search, $tipo, $fecha_desde, $fecha_hasta, $ubicacion, $precio_min, $precio_max);
} else {
    $eventos = $eventoModel->obtenerEventosDisponibles(50, 0);
}

// Aplicar ordenación en PHP
if (!empty($eventos)) {
    switch ($orden) {
        case 'precio_asc':
            usort($eventos, function($a, $b) {
                return ($a['precio_desde'] ?? 0) <=> ($b['precio_desde'] ?? 0);
            });
            break;
        case 'precio_desc':
            usort($eventos, function($a, $b) {
                return ($b['precio_desde'] ?? 0) <=> ($a['precio_desde'] ?? 0);
            });
            break;
        case 'fecha_asc':
            usort($eventos, function($a, $b) {
                return strtotime($a['fechaInicio']) <=> strtotime($b['fechaInicio']);
            });
            break;
        case 'popularidad':
            usort($eventos, function($a, $b) {
                $ventasA = $a['aforoTotal'] - $a['entradasDisponibles'];
                $ventasB = $b['aforoTotal'] - $b['entradasDisponibles'];
                return $ventasB <=> $ventasA;
            });
            break;
        default:
            // Por defecto: fecha ascendente
            break;
    }
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
                        <input type="hidden" name="orden" value="<?php echo htmlspecialchars($orden); ?>">
                        
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
                        <div class="results-controls">
                            <div class="sort-dropdown">
                                <select id="sort-select" onchange="changeSort(this.value)">
                                    <option value="fecha_asc" <?php echo $orden === 'fecha_asc' ? 'selected' : ''; ?>>Fecha: más próximo</option>
                                    <option value="precio_asc" <?php echo $orden === 'precio_asc' ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                                    <option value="precio_desc" <?php echo $orden === 'precio_desc' ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                                    <option value="popularidad" <?php echo $orden === 'popularidad' ? 'selected' : ''; ?>>Más populares</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="events-grid">
                        <?php if (!empty($eventos)): ?>
                            <?php foreach ($eventos as $evento): 
                                // Calcular si está agotado o casi agotado
                                $entradasDisp = (int)$evento['entradasDisponibles'];
                                $aforoTotal = (int)$evento['aforoTotal'];
                                $porcentajeVendido = $aforoTotal > 0 ? (($aforoTotal - $entradasDisp) / $aforoTotal) * 100 : 0;
                                
                                $isAgotado = $entradasDisp <= 0;
                                $isPocasEntradas = $entradasDisp > 0 && $entradasDisp <= ($aforoTotal * 0.1); // Menos del 10%
                                $isTrending = $porcentajeVendido >= 70; // Más del 70% vendido
                            ?>
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo getImageUrl($evento['imagenPrincipal']); ?>" 
                                             alt="<?php echo htmlspecialchars($evento['nombre']); ?>"
                                             onerror="this.src='<?php echo BASE_URL; ?>/api/admin/events/uploads/0b10db93db401e3d.jpg'">
                                        
                                        <!-- Badge de estado (Trending/Agotado) -->
                                        <?php if ($isAgotado): ?>
                                            <span class="event-badge sold-out">Agotado</span>
                                        <?php elseif ($isTrending): ?>
                                            <span class="event-badge trending">Trending</span>
                                        <?php elseif ($isPocasEntradas): ?>
                                            <span class="event-badge few-tickets">Últimas entradas</span>
                                        <?php endif; ?>
                                        
                                        <!-- Botón favorito -->
                                    <button class="favorite-btn" 
                                            data-evento-id="<?php echo $evento['idEvento']; ?>" 
                                            onclick="toggleFavorito(<?php echo $evento['idEvento']; ?>, this)"
                                            aria-label="Agregar a favoritos">
                                        <i class="far fa-heart"></i>
                                    </button>

                                    </div>
                                    <div class="event-content">
                                        <!-- Categoría del evento -->
                                        <span class="event-category"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                                        
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

<script>
// Verificar favoritos al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    <?php if (is_logged_in()): ?>
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const eventoId = btn.dataset.eventoId;
        verificarFavorito(eventoId, btn);
    });
    <?php endif; ?>
});

// Comprobar si un evento está en favoritos
function verificarFavorito(eventoId, btn) {
    fetch(`../api/favoritos.php?accion=verificar&idEvento=${eventoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok && data.esFavorito) {
                btn.querySelector('i').classList.remove('far');
                btn.querySelector('i').classList.add('fas');
                btn.classList.add('active');
            }
        });
}

// Añadir o quitar un evento de favoritos
function toggleFavorito(eventoId, btn) {
    <?php if (!is_logged_in()): ?>
        window.location.href = 'login.php?redirect=search-events.php';
        return;
    <?php endif; ?>
    
    const icon = btn.querySelector('i');
    const esFavorito = icon.classList.contains('fas');
    const accion = esFavorito ? 'eliminar' : 'agregar';
    
    const formData = new FormData();
    formData.append('idEvento', eventoId);
    
    fetch(`../api/favoritos.php?accion=${accion}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            if (esFavorito) {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.classList.remove('active');
            } else {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.add('active');
                // Animación
                btn.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    btn.style.transform = 'scale(1)';
                }, 200);
            }
        } else {
            alert(data.error || 'Error al procesar favorito');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar favorito');
    });
}

// Cambiar el orden de los resultados
function changeSort(value) {
    const url = new URL(window.location);
    url.searchParams.set('orden', value);
    window.location = url;
}

// Limpiar filtro de precio
function resetPriceFilter() {
    document.getElementById('precio_min').value = '';
    document.getElementById('precio_max').value = '';
    document.querySelector('form').submit();
}
</script>
</html>