<?php
/**
 * WorkFlowly - Búsqueda y Listado de Eventos
 * Conversión de search-events.html a PHP
 */

// Iniciar sesión
session_start();

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/EventoController.php';

// Crear instancia del controlador
$eventoController = new EventoController();

// Obtener parámetros de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$ubicacion = isset($_GET['ubicacion']) ? trim($_GET['ubicacion']) : '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Buscar eventos
$eventos = $eventoController->buscarEventos($busqueda, $ubicacion, $fecha, $tipo);

// Obtener tipos de evento para filtros
$tiposEvento = ['Concierto', 'Deporte', 'Teatro', 'Festival', 'Conferencia', 'Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Eventos - WorkFlowly</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/search-events.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav-brand">
                <a href="index.php" class="logo">
                    <div class="logo-circle">W</div>
                    <span class="brand-name">WorkFlowly</span>
                </a>
            </nav>
            
            <div class="nav-menu">
                <a href="index.php">Inicio</a>
                <a href="eventos.php" class="active">Busca Eventos</a>
                <a href="#organizadores">Organizadores</a>
            </div>
            
            <div class="nav-actions">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="cuenta.php" class="btn-secondary">Mi Cuenta</a>
                    <a href="../app/controllers/AuthController.php?action=logout" class="btn-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-secondary">Iniciar Sesión</a>
                    <a href="login.php?registro=1" class="btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Buscador -->
    <section class="search-section">
        <div class="container">
            <h1>Encuentra tu evento perfecto</h1>
            
            <form action="eventos.php" method="GET" class="search-form">
                <div class="search-grid">
                    <div class="search-field">
                        <label for="search">Buscar evento</label>
                        <input type="text" 
                               id="search" 
                               name="q" 
                               placeholder="Nombre del evento..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    
                    <div class="search-field">
                        <label for="location">Ubicación</label>
                        <input type="text" 
                               id="location" 
                               name="ubicacion" 
                               placeholder="Ciudad..." 
                               value="<?php echo htmlspecialchars($ubicacion); ?>">
                    </div>
                    
                    <div class="search-field">
                        <label for="date">Fecha</label>
                        <input type="date" 
                               id="date" 
                               name="fecha" 
                               value="<?php echo htmlspecialchars($fecha); ?>">
                    </div>
                    
                    <div class="search-field">
                        <label for="type">Tipo</label>
                        <select id="type" name="tipo">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($tiposEvento as $tipoEvento): ?>
                                <option value="<?php echo $tipoEvento; ?>" 
                                        <?php echo ($tipo === $tipoEvento) ? 'selected' : ''; ?>>
                                    <?php echo $tipoEvento; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="search-actions">
                    <button type="submit" class="btn-primary">Buscar</button>
                    <a href="eventos.php" class="btn-secondary">Limpiar filtros</a>
                </div>
            </form>
        </div>
    </section>

    <!-- Filtros rápidos -->
    <section class="quick-filters">
        <div class="container">
            <h3>Categorías populares:</h3>
            <div class="filter-buttons">
                <a href="eventos.php?tipo=Concierto" 
                   class="filter-btn <?php echo ($tipo === 'Concierto') ? 'active' : ''; ?>">
                    🎵 Conciertos
                </a>
                <a href="eventos.php?tipo=Deporte" 
                   class="filter-btn <?php echo ($tipo === 'Deporte') ? 'active' : ''; ?>">
                    ⚽ Deportes
                </a>
                <a href="eventos.php?tipo=Teatro" 
                   class="filter-btn <?php echo ($tipo === 'Teatro') ? 'active' : ''; ?>">
                    🎭 Teatro
                </a>
                <a href="eventos.php?tipo=Festival" 
                   class="filter-btn <?php echo ($tipo === 'Festival') ? 'active' : ''; ?>">
                    🎪 Festivales
                </a>
                <a href="eventos.php?tipo=Conferencia" 
                   class="filter-btn <?php echo ($tipo === 'Conferencia') ? 'active' : ''; ?>">
                    💼 Conferencias
                </a>
            </div>
        </div>
    </section>

    <!-- Resultados -->
    <section class="results-section">
        <div class="container">
            <div class="results-header">
                <h2>
                    <?php if (!empty($busqueda) || !empty($ubicacion) || !empty($fecha) || !empty($tipo)): ?>
                        Resultados de búsqueda
                    <?php else: ?>
                        Todos los eventos
                    <?php endif; ?>
                </h2>
                <p class="results-count">
                    <?php echo count($eventos); ?> evento(s) encontrado(s)
                </p>
            </div>

            <?php if (!empty($eventos)): ?>
                <div class="events-list">
                    <?php foreach ($eventos as $evento): ?>
                        <div class="event-card-horizontal">
                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars($evento['imagenPrincipal']); ?>" 
                                     alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
                                <span class="event-badge"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                            </div>
                            
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                
                                <p class="event-description">
                                    <?php echo htmlspecialchars(substr($evento['descripcion'], 0, 150)) . '...'; ?>
                                </p>
                                
                                <div class="event-meta">
                                    <span class="meta-item">
                                        📅 <?php echo date('d/m/Y H:i', strtotime($evento['fechaInicio'])); ?>
                                    </span>
                                    <span class="meta-item">
                                        📍 <?php echo htmlspecialchars($evento['ubicacion']); ?>
                                    </span>
                                    <span class="meta-item">
                                        🎫 <?php echo $evento['entradasDisponibles']; ?> disponibles
                                    </span>
                                </div>
                                
                                <div class="event-footer">
                                    <div class="event-price">
                                        Desde <strong>€<?php echo number_format($evento['precioMinimo'], 2); ?></strong>
                                    </div>
                                    <a href="detalle-evento.php?id=<?php echo $evento['idEvento']; ?>" 
                                       class="btn-primary">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">🔍</div>
                    <h3>No se encontraron eventos</h3>
                    <p>Intenta ajustar tus filtros de búsqueda o explora todas las categorías.</p>
                    <a href="eventos.php" class="btn-primary">Ver todos los eventos</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <div class="logo-circle">W</div>
                        <span class="brand-name">WorkFlowly</span>
                    </div>
                    <p>La plataforma de ticketing sin comisiones abusivas</p>
                </div>
                
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Eventos</h4>
                        <a href="eventos.php">Buscar</a>
                        <a href="eventos.php?tipo=Concierto">Conciertos</a>
                        <a href="eventos.php?tipo=Deporte">Deportes</a>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Soporte</h4>
                        <a href="#">Ayuda</a>
                        <a href="#">Contacto</a>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Legal</h4>
                        <a href="#">Términos</a>
                        <a href="#">Privacidad</a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/eventos.js"></script>
</body>
</html>
