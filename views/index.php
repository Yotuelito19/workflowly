<?php
/**
 * Página principal de WorkFlowly
 * Punto de entrada de la aplicación
 */

require_once 'config/config.php';
require_once 'config/database.php';

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
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <div class="logo">
                    <div class="logo-circle">
                        <span>W</span>
                    </div>
                    <span class="brand-name">WorkFlowly</span>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="views/search-events.php">Eventos</a>
                <a href="#como-funciona">Cómo funciona</a>
                <?php if (is_organizer()): ?>
                    <a href="views/account.php#mis-eventos">Mis Eventos</a>
                <?php endif; ?>
            </nav>
            <div class="nav-actions">
                <?php if (is_logged_in()): ?>
                    <a href="views/account.php" class="btn-secondary">Mi Cuenta</a>
                    <a href="api/logout.php" class="btn-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="views/login.php" class="btn-secondary">Iniciar Sesión</a>
                    <a href="views/login.php" class="btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
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
                        <select>
                            <option>Todas las ciudades</option>
                            <option>Madrid</option>
                            <option>Barcelona</option>
                            <option>Valencia</option>
                            <option>Sevilla</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="fecha_desde">
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
                    <div class="event-card">
                        <div class="event-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                        <div class="event-info">
                            <h4><?php echo htmlspecialchars(substr($evento['nombre'], 0, 30)); ?></h4>
                            <p>Desde <?php echo format_price($evento['precio_desde'] ?? 25); ?></p>
                        </div>
                    </div>
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
                        <div class="event-img" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
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
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <div class="logo-circle">
                            <span>W</span>
                        </div>
                        <span class="brand-name">WorkFlowly</span>
                    </div>
                    <p>Tu plataforma de confianza para comprar entradas sin reventa.</p>
                </div>
                <div class="footer-section">
                    <h4>Enlaces útiles</h4>
                    <ul>
                        <li><a href="#">Sobre nosotros</a></li>
                        <li><a href="#como-funciona">Cómo funciona</a></li>
                        <li><a href="#">Ayuda</a></li>
                        <li><a href="#">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Términos y condiciones</a></li>
                        <li><a href="#">Política de privacidad</a></li>
                        <li><a href="#">Política de cookies</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Síguenos</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
