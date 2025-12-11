<?php
/**
 * Página principal de WorkFlowly
 * Punto de entrada de la aplicación
 */

require_once 'config/config.php';
require_once 'config/database.php';

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

// Verificar timeout de sesión
if (is_logged_in()) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        redirect('/views/login.php?timeout=1');
    }
    $_SESSION['last_activity'] = time();
}

// Obtener eventos disponibles
$database = new Database();
$db = $database->getConnection();

$eventoModel = new Evento($db);
$eventos = $eventoModel->obtenerEventosDisponibles(8, 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkFlowly - Tu plataforma de entradas sin reventa</title>
    <link rel="stylesheet" href="assets/css/inicio.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Carrusel de fondo -->
        <div class="hero-carousel">
            <div class="carousel-slide active" style="background-image: url('assets/images/carousel-1.jpg');"></div>
            <div class="carousel-slide" style="background-image: url('assets/images/carousel-2.jpg');"></div>
            <div class="carousel-slide" style="background-image: url('assets/images/carousel-3.jpg');"></div>
            <div class="carousel-slide" style="background-image: url('assets/images/carousel-4.jpg');"></div>
            <div class="carousel-slide" style="background-image: url('assets/images/carousel-5.jpg');"></div>
        </div>
        <!-- Overlay oscuro -->
        <div class="hero-overlay"></div>
        
        <div class="hero-content">
            <h1>Encuentra eventos increíbles<br><span class="highlight">sin precios inflados</span></h1>
            <p class="hero-subtitle">La plataforma de ticketing que combate la reventa abusiva y garantiza precios justos para todos.</p>
            
            <!-- Buscador de eventos -->
            <div class="search-container">
                <form action="views/search-events.php" method="GET" class="search-box">
                    <div class="search-field">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="¿Qué evento buscas?">
                    </div>
                    <div class="search-field">
                        <i class="fas fa-map-marker-alt"></i>
                        <select name="ubicacion">
                            <option value="">Todas las ciudades</option>
                            <option value="Madrid">Madrid</option>
                            <option value="Barcelona">Barcelona</option>
                            <option value="Valencia">Valencia</option>
                            <option value="Sevilla">Sevilla</option>
                        </select>
                    </div>
                    <div class="search-field date-range">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="fecha_desde" placeholder="Desde">
                        <span class="date-separator">-</span>
                        <input type="date" name="fecha_hasta" placeholder="Hasta">
                    </div>
                    <button type="submit" class="btn-search">Buscar Eventos</button>
                </form>
            </div>

            <!-- Estadísticas -->
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number">50K+</span>
                    <span class="stat-label">Eventos disponibles</span>
                </div>
                <div class="stat">
                    <span class="stat-number">0%</span>
                    <span class="stat-label">Costos ocultos</span>
                </div>
                <div class="stat">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Soporte al cliente</span>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-cards">
                <?php 
                $eventosHero = array_slice($eventos, 0, 3);
                foreach ($eventosHero as $evento): 
                ?>
                    <a href="views/event-detail.php?id=<?php echo $evento['idEvento']; ?>" class="event-card">
                        <div class="event-image" style="background-image: url('<?php echo getImageUrl($evento['imagenPrincipal']); ?>'); background-size: cover; background-position: center;"></div>
                        <div class="event-info">
                            <h4><?php echo htmlspecialchars(substr($evento['nombre'], 0, 30)); ?></h4>
                            <p>Desde <?php echo format_price($evento['precio_desde'] ?? 25); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Eventos Destacados -->
    <section class="featured-events">
        <div class="container">
            <div class="section-header">
                <h2>Eventos Destacados</h2>
                <p>Los mejores eventos cerca de ti, sin reventa</p>
            </div>
            <div class="events-grid">
                <?php foreach ($eventos as $evento): ?>
                    <div class="event-item">
                        <div class="event-img">
                            <img src="<?php echo getImageUrl($evento['imagenPrincipal']); ?>" 
                                 alt="<?php echo htmlspecialchars($evento['nombre']); ?>"
                                 onerror="this.src='<?php echo BASE_URL; ?>/api/admin/events/uploads/0b10db93db401e3d.jpg'"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="event-content">
                            <span class="event-category"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                            <h3><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                            <div class="event-details">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($evento['fechaInicio'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evento['ubicacion']); ?></span>
                            </div>
                            <div class="event-price">
                                <span class="price">Desde <?php echo format_price($evento['precio_desde'] ?? 0); ?></span>
                                <a href="views/event-detail.php?id=<?php echo $evento['idEvento']; ?>" class="btn-secondary">Ver entradas</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Características -->
    <section class="features" id="como-funciona">
        <div class="container">
            <div class="section-header">
                <h2>¿Por qué elegir WorkFlowly?</h2>
                <p>Somos diferentes porque ponemos a los fans primero</p>
            </div>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Sin Reventa Abusiva</h3>
                    <p>Sistemas anti-bots y límites por usuario para garantizar precios justos.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Precios Transparentes</h3>
                    <p>Sin costos ocultos. El precio que ves es el precio final.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Compra Rápida Móvil</h3>
                    <p>Optimizado para compras desde el móvil en segundos.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Soporte 24/7</h3>
                    <p>Atención al cliente siempre disponible cuando lo necesites.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h3>Entradas Digitales</h3>
                    <p>QR seguro directo a tu móvil. Sin papeles, sin problemas.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Descuentos Grupales</h3>
                    <p>Mejores precios para familias y grupos de amigos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>¿Listo para vivir experiencias increíbles?</h2>
                <p>Únete a miles de usuarios que ya disfrutan de eventos sin reventa abusiva</p>
                <div class="cta-buttons">
                    <a href="views/search-events.php" class="btn-primary large">Explorar Eventos</a>
                    <?php if (!is_logged_in()): ?>
                        <a href="views/login.php" class="btn-secondary large">Soy Organizador</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
