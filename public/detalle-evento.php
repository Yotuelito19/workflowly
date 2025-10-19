<?php
/**
 * WorkFlowly - Detalle de Evento
 * Conversión de event-detail.html a PHP
 */

// Iniciar sesión
session_start();

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/EventoController.php';

// Crear instancia del controlador
$eventoController = new EventoController();

// Obtener ID del evento
$idEvento = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idEvento <= 0) {
    header('Location: eventos.php');
    exit;
}

// Obtener detalles del evento
$evento = $eventoController->getEventoById($idEvento);

if (!$evento) {
    header('Location: eventos.php');
    exit;
}

// Obtener tipos de entrada disponibles
$tiposEntrada = $eventoController->getTiposEntradaByEvento($idEvento);

// Obtener zonas del evento
$zonas = $eventoController->getZonasByEvento($idEvento);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($evento['nombre']); ?> - WorkFlowly</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/event-detail.css">
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
                    <a href="cuenta.php" class="btn-secondary">Mi Cuenta</a>
                    <a href="../app/controllers/AuthController.php?action=logout" class="btn-primary">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-secondary">Iniciar Sesión</a>
                    <a href="login.php?registro=1" class="btn-primary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Inicio</a> / 
            <a href="eventos.php">Eventos</a> / 
            <span><?php echo htmlspecialchars($evento['nombre']); ?></span>
        </div>
    </div>

    <!-- Hero del Evento -->
    <section class="event-hero">
        <div class="event-hero-image">
            <img src="<?php echo htmlspecialchars($evento['imagenPrincipal']); ?>" 
                 alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
            <div class="event-hero-overlay"></div>
        </div>
        
        <div class="container">
            <div class="event-hero-content">
                <span class="event-category"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                <h1><?php echo htmlspecialchars($evento['nombre']); ?></h1>
                <div class="event-meta">
                    <span>📅 <?php echo date('d/m/Y H:i', strtotime($evento['fechaInicio'])); ?></span>
                    <span>📍 <?php echo htmlspecialchars($evento['ubicacion']); ?></span>
                    <span>🎫 <?php echo $evento['entradasDisponibles']; ?> entradas disponibles</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido Principal -->
    <section class="event-content">
        <div class="container">
            <div class="event-layout">
                <!-- Información del Evento -->
                <div class="event-info">
                    <div class="info-section">
                        <h2>Descripción del Evento</h2>
                        <p><?php echo nl2br(htmlspecialchars($evento['descripcion'])); ?></p>
                    </div>

                    <div class="info-section">
                        <h2>Detalles</h2>
                        <ul class="event-details-list">
                            <li>
                                <strong>Fecha de inicio:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($evento['fechaInicio'])); ?>
                            </li>
                            <li>
                                <strong>Fecha de fin:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($evento['fechaFin'])); ?>
                            </li>
                            <li>
                                <strong>Ubicación:</strong> 
                                <?php echo htmlspecialchars($evento['ubicacion']); ?>
                            </li>
                            <li>
                                <strong>Aforo total:</strong> 
                                <?php echo $evento['aforoTotal']; ?> personas
                            </li>
                            <li>
                                <strong>Tipo de evento:</strong> 
                                <?php echo htmlspecialchars($evento['tipo']); ?>
                            </li>
                        </ul>
                    </div>

                    <?php if (!empty($zonas)): ?>
                    <div class="info-section">
                        <h2>Zonas Disponibles</h2>
                        <div class="zones-list">
                            <?php foreach ($zonas as $zona): ?>
                                <div class="zone-card">
                                    <h4><?php echo htmlspecialchars($zona['nombre']); ?></h4>
                                    <p><?php echo htmlspecialchars($zona['tipo']); ?></p>
                                    <span class="zone-capacity">
                                        Capacidad: <?php echo $zona['capacidad']; ?> personas
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar de Compra -->
                <div class="event-sidebar">
                    <div class="purchase-card">
                        <h3>Selecciona tus entradas</h3>
                        
                        <form action="checkout.php" method="POST" id="ticket-form">
                            <input type="hidden" name="evento_id" value="<?php echo $evento['idEvento']; ?>">
                            
                            <?php if (!empty($tiposEntrada)): ?>
                                <?php foreach ($tiposEntrada as $tipoEntrada): ?>
                                    <?php 
                                    $disponible = $tipoEntrada['cantidadDisponible'] > 0;
                                    $enVenta = (
                                        strtotime($tipoEntrada['fechaInicioVenta']) <= time() && 
                                        strtotime($tipoEntrada['fechaFinVenta']) >= time()
                                    );
                                    ?>
                                    <div class="ticket-type <?php echo (!$disponible || !$enVenta) ? 'disabled' : ''; ?>">
                                        <div class="ticket-info">
                                            <h4><?php echo htmlspecialchars($tipoEntrada['nombre']); ?></h4>
                                            <p class="ticket-description">
                                                <?php echo htmlspecialchars($tipoEntrada['descripcion']); ?>
                                            </p>
                                            <p class="ticket-availability">
                                                <?php if (!$enVenta): ?>
                                                    ⏰ Venta no disponible
                                                <?php elseif (!$disponible): ?>
                                                    ❌ Agotado
                                                <?php else: ?>
                                                    ✅ <?php echo $tipoEntrada['cantidadDisponible']; ?> disponibles
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        
                                        <div class="ticket-price">
                                            <strong>€<?php echo number_format($tipoEntrada['precio'], 2); ?></strong>
                                            
                                            <?php if ($disponible && $enVenta): ?>
                                                <div class="ticket-quantity">
                                                    <label for="cantidad_<?php echo $tipoEntrada['idTipoEntrada']; ?>">
                                                        Cantidad:
                                                    </label>
                                                    <select name="entradas[<?php echo $tipoEntrada['idTipoEntrada']; ?>]" 
                                                            id="cantidad_<?php echo $tipoEntrada['idTipoEntrada']; ?>"
                                                            class="quantity-select">
                                                        <option value="0">0</option>
                                                        <?php 
                                                        $maxCantidad = min(4, $tipoEntrada['cantidadDisponible']);
                                                        for ($i = 1; $i <= $maxCantidad; $i++): 
                                                        ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-tickets">No hay entradas disponibles para este evento.</p>
                            <?php endif; ?>
                            
                            <div class="purchase-summary">
                                <div class="summary-line">
                                    <span>Total:</span>
                                    <strong id="total-price">€0.00</strong>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-primary btn-block" id="buy-button">
                                Comprar Entradas
                            </button>
                            
                            <p class="purchase-note">
                                ⚠️ Límite máximo: 4 entradas por compra
                            </p>
                        </form>
                    </div>

                    <!-- Información Adicional -->
                    <div class="info-card">
                        <h4>💳 Métodos de pago</h4>
                        <p>Tarjeta de crédito, PayPal, Bizum</p>
                    </div>

                    <div class="info-card">
                        <h4>🔒 Compra segura</h4>
                        <p>Tu información está protegida con cifrado SSL</p>
                    </div>

                    <div class="info-card">
                        <h4>📱 Entradas digitales</h4>
                        <p>Recibirás tus entradas al instante por email</p>
                    </div>
                </div>
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
    <script src="assets/js/detalle-evento.js"></script>
    <script>
        // Calcular total en tiempo real
        document.querySelectorAll('.quantity-select').forEach(select => {
            select.addEventListener('change', calcularTotal);
        });

        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('.ticket-type').forEach(ticket => {
                const select = ticket.querySelector('.quantity-select');
                if (select) {
                    const cantidad = parseInt(select.value) || 0;
                    const precio = parseFloat(
                        ticket.querySelector('.ticket-price strong').textContent.replace('€', '')
                    );
                    total += cantidad * precio;
                }
            });
            
            document.getElementById('total-price').textContent = '€' + total.toFixed(2);
            
            // Habilitar/deshabilitar botón de compra
            const buyButton = document.getElementById('buy-button');
            buyButton.disabled = (total === 0);
        }

        // Inicializar
        calcularTotal();
    </script>
</body>
</html>
