<?php
/**
 * WorkFlowly - Proceso de Compra (Checkout)
 * Conversión de checkout.html a PHP
 */

// Iniciar sesión
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php?redirect=checkout');
    exit;
}

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/EventoController.php';
require_once '../app/controllers/CompraController.php';

// Verificar que vengan datos del formulario de selección de entradas
if (!isset($_POST['evento_id']) && !isset($_SESSION['carrito'])) {
    header('Location: eventos.php');
    exit;
}

// Crear instancias de controladores
$eventoController = new EventoController();
$compraController = new CompraController();

// Obtener datos del evento y entradas seleccionadas
$eventoId = isset($_POST['evento_id']) ? intval($_POST['evento_id']) : $_SESSION['carrito']['evento_id'];
$evento = $eventoController->getEventoById($eventoId);

if (!$evento) {
    header('Location: eventos.php');
    exit;
}

// Procesar entradas seleccionadas
$entradasSeleccionadas = [];
$totalCompra = 0;

if (isset($_POST['entradas'])) {
    foreach ($_POST['entradas'] as $tipoEntradaId => $cantidad) {
        if ($cantidad > 0) {
            $tipoEntrada = $eventoController->getTipoEntradaById($tipoEntradaId);
            if ($tipoEntrada) {
                $subtotal = $tipoEntrada['precio'] * $cantidad;
                $entradasSeleccionadas[] = [
                    'idTipoEntrada' => $tipoEntradaId,
                    'nombre' => $tipoEntrada['nombre'],
                    'precio' => $tipoEntrada['precio'],
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal
                ];
                $totalCompra += $subtotal;
            }
        }
    }
}

// Verificar que haya entradas seleccionadas
if (empty($entradasSeleccionadas)) {
    header('Location: detalle-evento.php?id=' . $eventoId);
    exit;
}

// Guardar en sesión por si hay errores
$_SESSION['carrito'] = [
    'evento_id' => $eventoId,
    'entradas' => $entradasSeleccionadas,
    'total' => $totalCompra
];

// Procesar el formulario de pago si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_pago'])) {
    $resultado = $compraController->procesarCompra(
        $_SESSION['usuario']['id'],
        $eventoId,
        $entradasSeleccionadas,
        $_POST
    );
    
    if ($resultado['success']) {
        // Limpiar carrito
        unset($_SESSION['carrito']);
        // Redirigir a confirmación
        header('Location: confirmacion.php?compra=' . $resultado['idCompra']);
        exit;
    } else {
        $error = $resultado['mensaje'];
    }
}

// Obtener métodos de pago del usuario
$metodosPago = $compraController->getMetodosPagoUsuario($_SESSION['usuario']['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - WorkFlowly</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/checkout.css">
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

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Inicio</a> / 
            <a href="eventos.php">Eventos</a> / 
            <a href="detalle-evento.php?id=<?php echo $evento['idEvento']; ?>">
                <?php echo htmlspecialchars($evento['nombre']); ?>
            </a> / 
            <span>Checkout</span>
        </div>
    </div>

    <!-- Checkout Content -->
    <section class="checkout-section">
        <div class="container">
            <h1>Completar Compra</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">
                <!-- Formulario de Pago -->
                <div class="checkout-form">
                    <form action="" method="POST" id="payment-form">
                        <input type="hidden" name="procesar_pago" value="1">
                        
                        <!-- Step 1: Datos de Contacto -->
                        <div class="checkout-step">
                            <h2>1. Datos de Contacto</h2>
                            <div class="form-group">
                                <label for="nombre">Nombre completo</label>
                                <input type="text" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($_SESSION['usuario']['nombre'] . ' ' . $_SESSION['usuario']['apellidos']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($_SESSION['usuario']['email']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?php echo htmlspecialchars($_SESSION['usuario']['telefono'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>

                        <!-- Step 2: Método de Pago -->
                        <div class="checkout-step">
                            <h2>2. Método de Pago</h2>
                            
                            <div class="payment-methods">
                                <label class="payment-method">
                                    <input type="radio" name="metodo_pago" value="tarjeta" checked>
                                    <div class="payment-option">
                                        <span class="payment-icon">💳</span>
                                        <span>Tarjeta de Crédito/Débito</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="metodo_pago" value="paypal">
                                    <div class="payment-option">
                                        <span class="payment-icon">🅿️</span>
                                        <span>PayPal</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="metodo_pago" value="bizum">
                                    <div class="payment-option">
                                        <span class="payment-icon">📱</span>
                                        <span>Bizum</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Datos de Tarjeta -->
                            <div id="card-details" class="card-form">
                                <div class="form-group">
                                    <label for="card_number">Número de tarjeta</label>
                                    <input type="text" 
                                           id="card_number" 
                                           name="card_number" 
                                           placeholder="1234 5678 9012 3456"
                                           maxlength="19">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="card_expiry">Fecha de expiración</label>
                                        <input type="text" 
                                               id="card_expiry" 
                                               name="card_expiry" 
                                               placeholder="MM/AA"
                                               maxlength="5">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="card_cvv">CVV</label>
                                        <input type="text" 
                                               id="card_cvv" 
                                               name="card_cvv" 
                                               placeholder="123"
                                               maxlength="3">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Términos y Condiciones -->
                        <div class="checkout-step">
                            <h2>3. Términos y Condiciones</h2>
                            
                            <div class="terms-box">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="acepto_terminos" required>
                                    <span>
                                        He leído y acepto los 
                                        <a href="#" target="_blank">términos y condiciones</a> 
                                        y la 
                                        <a href="#" target="_blank">política de privacidad</a>
                                    </span>
                                </label>
                                
                                <label class="checkbox-label">
                                    <input type="checkbox" name="acepto_compra">
                                    <span>
                                        Confirmo que los datos son correctos y autorizo el cargo
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Botón de Pago -->
                        <div class="checkout-actions">
                            <a href="detalle-evento.php?id=<?php echo $evento['idEvento']; ?>" 
                               class="btn-secondary">
                                Volver al evento
                            </a>
                            <button type="submit" class="btn-primary btn-large">
                                Pagar €<?php echo number_format($totalCompra, 2); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resumen de Compra -->
                <div class="order-summary">
                    <h3>Resumen de tu pedido</h3>
                    
                    <div class="event-summary">
                        <h4><?php echo htmlspecialchars($evento['nombre']); ?></h4>
                        <p class="event-date">
                            📅 <?php echo date('d/m/Y H:i', strtotime($evento['fechaInicio'])); ?>
                        </p>
                        <p class="event-location">
                            📍 <?php echo htmlspecialchars($evento['ubicacion']); ?>
                        </p>
                    </div>

                    <div class="tickets-summary">
                        <h4>Entradas seleccionadas</h4>
                        <?php foreach ($entradasSeleccionadas as $entrada): ?>
                            <div class="ticket-item">
                                <div class="ticket-info">
                                    <span class="ticket-name">
                                        <?php echo htmlspecialchars($entrada['nombre']); ?>
                                    </span>
                                    <span class="ticket-quantity">
                                        x <?php echo $entrada['cantidad']; ?>
                                    </span>
                                </div>
                                <span class="ticket-price">
                                    €<?php echo number_format($entrada['subtotal'], 2); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-line">
                            <span>Subtotal</span>
                            <span>€<?php echo number_format($totalCompra, 2); ?></span>
                        </div>
                        <div class="price-line">
                            <span>Comisión de servicio</span>
                            <span>€0.00</span>
                        </div>
                        <div class="price-line total">
                            <strong>Total</strong>
                            <strong>€<?php echo number_format($totalCompra, 2); ?></strong>
                        </div>
                    </div>

                    <div class="security-badges">
                        <div class="badge">🔒 Pago seguro SSL</div>
                        <div class="badge">✓ Sin comisiones ocultas</div>
                        <div class="badge">📱 Entradas digitales</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/checkout.js"></script>
    <script>
        // Formatear número de tarjeta
        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        // Formatear fecha de expiración
        document.getElementById('card_expiry')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });

        // Solo números en CVV
        document.getElementById('card_cvv')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>
