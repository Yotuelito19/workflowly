<?php
/**
 * WorkFlowly - Página de Inicio (Homepage)
 * Conversión de inicio.html a PHP
 */

// Iniciar sesión
session_start();

// Incluir configuración de base de datos
require_once '../config/database.php';
require_once '../app/controllers/EventoController.php';

// Crear instancia del controlador de eventos
$eventoController = new EventoController();

// Obtener eventos destacados para mostrar en homepage
$eventosDestacados = $eventoController->getEventosDestacados(4);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkFlowly - Encuentra eventos increíbles sin precios inflados</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/inicio.css">
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
                <a href="eventos.php">Busca Eventos</a>
                <a href="#organizadores">Organizadores</a>
            </div>
            
            <div class="nav-actions">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <!-- Usuario logueado -->
                    <a href="cuenta.php" class="btn-secondary">Mi Cuenta</a>
                    <a href="../app/controllers/AuthController.php?action=logout" class="btn-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <!-- Usuario no logueado -->
                    <a href="login.php" class="btn-secondary">Iniciar Sesión</a>
                    <a href="login.php?registro=1" class="btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>
                    Encuentra eventos increíbles<br>
                    <span class="highlight">sin precios inflados</span>
                </h1>
                <p class="hero-subtitle">
                    Compra tus entradas de forma segura y rápida. 
                    Sin bots, sin reventas, sin precios abusivos.
                </p>
                
                <!-- Buscador -->
                <div class="search-container">
                    <form action="eventos.php" method="GET" class="search-box">
                        <div class="search-field">
                            <label for="search">¿Qué evento buscas?</label>
                            <input type="text" id="search" name="q" placeholder="Concierto, deporte, teatro...">
                        </div>
                        
                        <div class="search-field">
                            <label for="location">Ubicación</label>
                            <input type="text" id="location" name="ubicacion" placeholder="Ciudad o lugar">
                        </div>
                        
                        <div class="search-field">
                            <label for="date">Fecha</label>
                            <input type="date" id="date" name="fecha">
                        </div>
                        
                        <button type="submit" class="btn-primary large">Buscar eventos</button>
                    </form>
                </div>

                <!-- Stats -->
                <div class="stats">
                    <div class="stat-item">
                        <strong>50K+</strong>
                        <span>Eventos publicados</span>
                    </div>
                    <div class="stat-item">
                        <strong>0%</strong>
                        <span>Comisión oculta</span>
                    </div>
                    <div class="stat-item">
                        <strong>24/7</strong>
                        <span>Soporte disponible</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Eventos Destacados -->
    <section class="featured-events">
        <div class="container">
            <h2 class="section-title">Eventos Destacados</h2>
            <p class="section-subtitle">Los mejores eventos cerca de ti</p>
            
            <div class="events-grid">
                <?php if (!empty($eventosDestacados)): ?>
                    <?php foreach ($eventosDestacados as $evento): ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars($evento['imagenPrincipal']); ?>" 
                                     alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
                                <span class="event-badge"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                            </div>
                            <div class="event-info">
                                <h3><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                <p class="event-location">
                                    📍 <?php echo htmlspecialchars($evento['ubicacion']); ?>
                                </p>
                                <p class="event-date">
                                    📅 <?php echo date('d/m/Y', strtotime($evento['fechaInicio'])); ?>
                                </p>
                                <div class="event-footer">
                                    <span class="event-price">
                                        Desde <strong>€<?php echo number_format($evento['precioMinimo'], 2); ?></strong>
                                    </span>
                                    <a href="detalle-evento.php?id=<?php echo $evento['idEvento']; ?>" 
                                       class="btn-primary">Ver detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-events">No hay eventos destacados disponibles en este momento.</p>
                <?php endif; ?>
            </div>
            
            <div class="view-all">
                <a href="eventos.php" class="btn-secondary large">Ver todos los eventos</a>
            </div>
        </div>
    </section>

    <!-- Por qué elegir WorkFlowly -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">¿Por qué elegir WorkFlowly?</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Sin Reventa Abusiva</h3>
                    <p>Sistema anti-bot que garantiza precios justos y evita la reventa inflada.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Precios Transparentes</h3>
                    <p>Sin comisiones ocultas. El precio que ves es el precio que pagas.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">✅</div>
                    <h3>Compra Rápida Segura</h3>
                    <p>Proceso de compra simple y seguro en pocos clics.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Soporte 24/7</h3>
                    <p>Nuestro equipo está disponible para ayudarte en todo momento.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Entradas Digitales</h3>
                    <p>Recibe tus entradas con código QR al instante en tu email.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🎉</div>
                    <h3>Descuentos Grupales</h3>
                    <p>Ofertas especiales cuando compras múltiples entradas.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>¿Listo para vivir experiencias increíbles?</h2>
            <p>Únete a miles de usuarios que ya disfrutan de eventos sin precios inflados</p>
            <div class="cta-buttons">
                <a href="eventos.php" class="btn-primary large">Explorar eventos</a>
                <a href="login.php?registro=1" class="btn-secondary large">Crear cuenta gratis</a>
            </div>
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
                        <a href="eventos.php?tipo=Teatro">Teatro</a>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Soporte</h4>
                        <a href="#">Ayuda</a>
                        <a href="#">Contacto</a>
                        <a href="#">Preguntas frecuentes</a>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Legal</h4>
                        <a href="#">Términos</a>
                        <a href="#">Privacidad</a>
                        <a href="#">Cookies</a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
