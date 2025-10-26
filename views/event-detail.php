<?php
/**
 * Detalle de evento - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Obtener ID del evento
$idEvento = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idEvento === 0) {
    redirect('/views/search-events.php');
}

// Conectar BD y obtener evento
$database = new Database();
$db = $database->getConnection();
$eventoModel = new Evento($db);

$evento = $eventoModel->obtenerPorId($idEvento);

if (!$evento) {
    redirect('/views/search-events.php');
}

// Obtener tipos de entrada disponibles
$tiposEntrada = $eventoModel->obtenerTiposEntrada($idEvento);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($evento['nombre']); ?> - WorkFlowly</title>
    <link rel="stylesheet" href="../assets/css/event-detail.css">
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
                <a href="search-events.php">Eventos</a>
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

    <!-- Breadcrumbs -->
    <nav class="breadcrumbs">
        <div class="container">
            <a href="../index.php">Inicio</a>
            <i class="fas fa-chevron-right"></i>
            <a href="search-events.php">Eventos</a>
            <i class="fas fa-chevron-right"></i>
            <a href="search-events.php?tipo=<?php echo urlencode($evento['tipo']); ?>"><?php echo htmlspecialchars($evento['tipo']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($evento['nombre']); ?></span>
        </div>
    </nav>

    <!-- Event Hero -->
    <section class="event-hero">
        <div class="hero-background">
            <div class="hero-image" style="background-image: url('<?php echo UPLOADS_URL . '/' . $evento['imagenPrincipal']; ?>');"></div>
            <div class="hero-overlay"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="event-badges">
                    <span class="badge category"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                    <?php if ($evento['entradasDisponibles'] < 100): ?>
                        <span class="badge trending">¡Últimas entradas!</span>
                    <?php endif; ?>
                </div>
                <h1><?php echo htmlspecialchars($evento['nombre']); ?></h1>
                <div class="event-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <strong><?php echo date('d M Y', strtotime($evento['fechaInicio'])); ?></strong>
                            <span><?php echo date('l, H:i', strtotime($evento['fechaInicio'])); ?></span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong><?php echo htmlspecialchars($evento['ubicacion']); ?></strong>
                            <span>España</span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-ticket-alt"></i>
                        <div>
                            <strong><?php echo $evento['entradasDisponibles']; ?> disponibles</strong>
                            <span>de <?php echo $evento['aforoTotal']; ?> totales</span>
                        </div>
                    </div>
                </div>
                <div class="hero-actions">
                    <button class="btn-favorite">
                        <i class="far fa-heart"></i>
                        Guardar evento
                    </button>
                    <button class="btn-share">
                        <i class="fas fa-share-alt"></i>
                        Compartir
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Event Content -->
    <section class="event-content">
        <div class="container">
            <div class="content-layout">
                <!-- Main Content -->
                <div class="main-content">
                    <div class="content-section">
                        <h2>Descripción del evento</h2>
                        <p><?php echo nl2br(htmlspecialchars($evento['descripcion'] ?? 'Sin descripción disponible')); ?></p>
                    </div>

                    <div class="content-section">
                        <h2>Información del organizador</h2>
                        <div class="organizer-info">
                            <div class="organizer-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="organizer-details">
                                <h3><?php echo htmlspecialchars($evento['organizador_nombre'] . ' ' . $evento['organizador_apellidos']); ?></h3>
                                <p>Organizador verificado</p>
                                <a href="mailto:<?php echo htmlspecialchars($evento['organizador_email']); ?>" class="btn-link">Contactar</a>
                            </div>
                        </div>
                    </div>

                    <div class="content-section">
                        <h2>Ubicación</h2>
                        <div class="location-info">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong><?php echo htmlspecialchars($evento['ubicacion']); ?></strong>
                                <p>España</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Ticket Selection -->
                <aside class="ticket-sidebar">
                    <div class="ticket-card">
                        <h3>Selecciona tus entradas</h3>
                        
                        <?php if (!empty($tiposEntrada)): ?>
                            <form action="checkout.php" method="POST" id="ticketForm">
                                <input type="hidden" name="idEvento" value="<?php echo $evento['idEvento']; ?>">
                                
                                <?php foreach ($tiposEntrada as $tipo): ?>
                                    <div class="ticket-type">
                                        <div class="ticket-info">
                                            <h4><?php echo htmlspecialchars($tipo['nombre'] ?? 'Entrada'); ?></h4>
                                            <?php if (!empty($tipo['descripcion'])): ?>
                                                <p><?php echo htmlspecialchars($tipo['descripcion']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($tipo['zona_nombre'])): ?>
                                                <span class="zone-badge"><?php echo htmlspecialchars($tipo['zona_nombre']); ?></span>
                                            <?php endif; ?>
                                            <div class="ticket-price">
                                                <strong><?php echo format_price($tipo['precio']); ?></strong>
                                                <span><?php echo $tipo['disponibles']; ?> disponibles</span>
                                            </div>
                                        </div>
                                        <div class="ticket-quantity">
                                            <label>Cantidad:</label>
                                            <div class="quantity-selector">
                                                <button type="button" onclick="decreaseQuantity(<?php echo $tipo['idTipoEntrada']; ?>)">-</button>
                                                <input type="number" 
                                                       id="qty_<?php echo $tipo['idTipoEntrada']; ?>" 
                                                       name="entradas[<?php echo $tipo['idTipoEntrada']; ?>]" 
                                                       value="0" 
                                                       min="0" 
                                                       max="<?php echo min($tipo['disponibles'], 10); ?>"
                                                       onchange="updateTotal()">
                                                <button type="button" onclick="increaseQuantity(<?php echo $tipo['idTipoEntrada']; ?>, <?php echo min($tipo['disponibles'], 10); ?>)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="ticket-total">
                                    <span>Total:</span>
                                    <strong id="totalPrice">0,00 €</strong>
                                </div>

                                <?php if (is_logged_in()): ?>
                                    <button type="submit" class="btn-primary" id="buyButton" disabled>
                                        Continuar con la compra
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="login.php?redirect=event-detail.php?id=<?php echo $evento['idEvento']; ?>" class="btn-primary">
                                        Inicia sesión para comprar
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <p style="text-align: center; padding: 20px;">No hay entradas disponibles en este momento</p>
                        <?php endif; ?>

                        <div class="security-badges">
                            <div class="badge-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Compra segura</span>
                            </div>
                            <div class="badge-item">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Entrada digital</span>
                            </div>
                            <div class="badge-item">
                                <i class="fas fa-undo"></i>
                                <span>Reembolso garantizado</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        // Precios de entradas (PHP a JS)
        const ticketPrices = {
            <?php foreach ($tiposEntrada as $tipo): ?>
                <?php echo $tipo['idTipoEntrada']; ?>: <?php echo $tipo['precio']; ?>,
            <?php endforeach; ?>
        };

        function updateTotal() {
            let total = 0;
            let hasTickets = false;

            for (let id in ticketPrices) {
                const input = document.getElementById('qty_' + id);
                if (input) {
                    const quantity = parseInt(input.value) || 0;
                    total += quantity * ticketPrices[id];
                    if (quantity > 0) hasTickets = true;
                }
            }

            document.getElementById('totalPrice').textContent = total.toFixed(2).replace('.', ',') + ' €';
            document.getElementById('buyButton').disabled = !hasTickets;
        }

        function increaseQuantity(id, max) {
            const input = document.getElementById('qty_' + id);
            if (input.value < max) {
                input.value = parseInt(input.value) + 1;
                updateTotal();
            }
        }

        function decreaseQuantity(id) {
            const input = document.getElementById('qty_' + id);
            if (input.value > 0) {
                input.value = parseInt(input.value) - 1;
                updateTotal();
            }
        }
    </script>
</body>
</html>