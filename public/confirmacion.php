<?php
/**
 * WorkFlowly - Confirmación de Compra
 * Conversión de confirmation.html a PHP
 */

// Iniciar sesión
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Verificar que venga el ID de compra
if (!isset($_GET['compra'])) {
    header('Location: index.php');
    exit;
}

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/CompraController.php';

// Crear instancia del controlador
$compraController = new CompraController();

// Obtener detalles de la compra
$idCompra = intval($_GET['compra']);
$compra = $compraController->getCompraById($idCompra, $_SESSION['usuario']['id']);

if (!$compra) {
    header('Location: mis-entradas.php');
    exit;
}

// Obtener entradas de la compra
$entradas = $compraController->getEntradasByCompra($idCompra);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Confirmada - WorkFlowly</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/confirmation.css">
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
                <a href="cuenta.php" class="btn-secondary">Mi Cuenta</a>
            </div>
        </div>
    </header>

    <!-- Confirmation Content -->
    <section class="confirmation-section">
        <div class="container">
            <!-- Success Message -->
            <div class="success-icon">✓</div>
            <h1>¡Compra realizada con éxito!</h1>
            <p class="confirmation-subtitle">
                Tu pedido ha sido confirmado. Recibirás un email con los detalles y tus entradas.
            </p>

            <!-- Order Details -->
            <div class="confirmation-card">
                <div class="order-header">
                    <div class="order-info">
                        <h3>Pedido #<?php echo str_pad($compra['idCompra'], 6, '0', STR_PAD_LEFT); ?></h3>
                        <p class="order-date">
                            Fecha: <?php echo date('d/m/Y H:i', strtotime($compra['fechaCompra'])); ?>
                        </p>
                    </div>
                    <div class="order-status">
                        <span class="status-badge status-success">
                            <?php echo htmlspecialchars($compra['estado']); ?>
                        </span>
                    </div>
                </div>

                <!-- Event Info -->
                <div class="event-details">
                    <div class="event-image">
                        <img src="<?php echo htmlspecialchars($compra['evento']['imagenPrincipal']); ?>" 
                             alt="<?php echo htmlspecialchars($compra['evento']['nombre']); ?>">
                    </div>
                    <div class="event-info">
                        <h2><?php echo htmlspecialchars($compra['evento']['nombre']); ?></h2>
                        <p class="event-meta">
                            <span>📅 <?php echo date('d/m/Y H:i', strtotime($compra['evento']['fechaInicio'])); ?></span>
                            <span>📍 <?php echo htmlspecialchars($compra['evento']['ubicacion']); ?></span>
                        </p>
                    </div>
                </div>

                <!-- Tickets List -->
                <div class="tickets-section">
                    <h3>Tus Entradas</h3>
                    
                    <?php foreach ($entradas as $entrada): ?>
                        <div class="ticket-card">
                            <div class="ticket-details">
                                <h4><?php echo htmlspecialchars($entrada['tipoEntrada']); ?></h4>
                                <p class="ticket-meta">
                                    Entrada #<?php echo str_pad($entrada['idEntrada'], 8, '0', STR_PAD_LEFT); ?>
                                </p>
                                <?php if ($entrada['asiento']): ?>
                                    <p class="ticket-seat">
                                        🪑 Zona: <?php echo htmlspecialchars($entrada['zona']); ?> - 
                                        Fila <?php echo htmlspecialchars($entrada['fila']); ?> - 
                                        Asiento <?php echo htmlspecialchars($entrada['numero']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="ticket-qr">
                                <?php if ($entrada['codigoQR']): ?>
                                    <img src="<?php echo htmlspecialchars($entrada['codigoQR']); ?>" 
                                         alt="Código QR">
                                    <p class="qr-label">Escanea en el evento</p>
                                <?php else: ?>
                                    <div class="qr-placeholder">QR generándose...</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Payment Summary -->
                <div class="payment-summary">
                    <h3>Resumen de Pago</h3>
                    
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>€<?php echo number_format($compra['total'], 2); ?></span>
                    </div>
                    
                    <div class="summary-line">
                        <span>Comisión de servicio</span>
                        <span>€0.00</span>
                    </div>
                    
                    <div class="summary-line total">
                        <strong>Total Pagado</strong>
                        <strong>€<?php echo number_format($compra['total'], 2); ?></strong>
                    </div>
                    
                    <div class="payment-method-used">
                        <span>Método de pago:</span>
                        <span><?php echo htmlspecialchars($compra['metodoPago']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Info Boxes -->
            <div class="info-boxes">
                <div class="info-box">
                    <div class="info-icon">📧</div>
                    <h4>Email enviado</h4>
                    <p>
                        Hemos enviado la confirmación y las entradas a 
                        <strong><?php echo htmlspecialchars($_SESSION['usuario']['email']); ?></strong>
                    </p>
                </div>

                <div class="info-box">
                    <div class="info-icon">📱</div>
                    <h4>Entradas digitales</h4>
                    <p>
                        Presenta el código QR desde tu móvil o impreso en la entrada del evento.
                    </p>
                </div>

                <div class="info-box">
                    <div class="info-icon">❓</div>
                    <h4>¿Necesitas ayuda?</h4>
                    <p>
                        Contacta con nuestro soporte en 
                        <a href="mailto:soporte@workflowly.com">soporte@workflowly.com</a>
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="confirmation-actions">
                <a href="mis-entradas.php" class="btn-primary">
                    Ver mis entradas
                </a>
                <button onclick="window.print()" class="btn-secondary">
                    Imprimir entradas
                </button>
                <a href="eventos.php" class="btn-secondary">
                    Buscar más eventos
                </a>
            </div>

            <!-- Important Notes -->
            <div class="important-notes">
                <h4>⚠️ Información Importante</h4>
                <ul>
                    <li>Guarda bien tus entradas. Los códigos QR son únicos e intransferibles.</li>
                    <li>Llega con antelación al evento para evitar colas en el acceso.</li>
                    <li>Si tienes algún problema, contacta con soporte antes del evento.</li>
                    <li>No compartas capturas de pantalla de tus códigos QR en redes sociales.</li>
                </ul>
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
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Animación de confeti al cargar (opcional)
        window.addEventListener('load', function() {
            console.log('¡Compra confirmada! 🎉');
        });
    </script>
</body>
</html>
