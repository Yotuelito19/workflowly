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
                    <a href="../api/logout.php" class="btn-primary">Cerrar Sesión</a>
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

    <!-- Main Content -->
    <section class="main-content">
        <div class="container">
            <div class="content-layout">
                <!-- Event Details -->
                <main class="event-details">
                    <div class="details-card">
                        <h2>Sobre el evento</h2>
                        <div class="event-description">
                            <p><?php echo nl2br(htmlspecialchars($evento['descripcion'] ?? 'Sin descripción disponible')); ?></p>
                        </div>
                    </div>

                    <div class="venue-card">
                        <h2>Localización</h2>
                        <div class="venue-info">
                            <div class="venue-details">
                                <h3><?php echo htmlspecialchars($evento['ubicacion']); ?></h3>
                                <p class="venue-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Av. Felipe II, s/n, 28009 Madrid CAMBIAR
                                </p>
                                <div class="venue-features">
                                    <span class="feature">
                                        <i class="fas fa-users"></i>
                                        Capacidad: 17,000 personas CAMBIAR
                                    </span>
                                    <span class="feature">
                                        <i class="fas fa-wheelchair"></i>
                                        Acceso para discapacitados CAMBIAR
                                    </span>
                                    <span class="feature">
                                        <i class="fas fa-car"></i>
                                        Parking disponible CAMBIAR
                                    </span>
                                    <span class="feature">
                                        <i class="fas fa-subway"></i>
                                        Metro: Goya (L2, L4) CAMBIAR
                                    </span>
                                </div>
                            </div>
                            <div class="venue-map">
                                <div class="map-placeholder">
                                    <i class="fas fa-map"></i>
                                    <p>Mapa interactivo</p>
                                    <button class="btn-map">Ver en Google Maps</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="organizer-card">
                        <h2>Información del organizador</h2>
                        <div class="organizer-info">
                            <div class="organizer-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="organizer-details">
                                <h3><?php echo htmlspecialchars($evento['organizador_nombre'] . ' ' . $evento['organizador_apellidos']); ?></h3>
                                 <p class="organizer-description">
                                    Líder mundial en entretenimiento en vivo, organizando los mejores eventos musicales con más de 25 años de experiencia. CAMBIAR
                                </p>
                                <div class="organizer-stats">
                                    <span class="stat">
                                        <strong>500+</strong> eventos organizados CAMBIAR
                                    </span>
                                    <span class="stat">
                                        <strong>4.8★</strong> valoración promedio CAMBIAR 
                                    </span>
                                    <span class="stat">
                                        <strong>2M+</strong> asistentes satisfechos CAMBIAR
                                    </span>
                                </div>
                             
                                <a href="mailto:<?php echo htmlspecialchars($evento['organizador_email']); ?>" class="btn-contact">Contactar</a>
                            </div>
                        </div>
                    </div>
                    <div class="policies-card">
                        <h2>Políticas del evento CAMBIAR TODO, TODO TIENE QUE IR POR BD</h2>
                        <div class="policies-content">
                            <div class="policy-section">
                                <h3><i class="fas fa-ticket-alt"></i> Entradas</h3>
                                <ul>
                                    <li>Las entradas son nominativas y no transferibles</li>
                                    <li>Prohibida la reventa de entradas</li>
                                    <li>Entrada digital vía QR code</li>
                                    <li>Requerido documento de identidad para el acceso</li>
                                </ul>
                            </div>
                            <div class="policy-section">
                                <h3><i class="fas fa-undo"></i> Cancelaciones</h3>
                                <ul>
                                    <li>Reembolso completo hasta 48h antes del evento</li>
                                    <li>En caso de cancelación del evento: reembolso automático</li>
                                    <li>Cambio de fecha: las entradas siguen siendo válidas</li>
                                </ul>
                            </div>
                            <div class="policy-section">
                                <h3><i class="fas fa-shield-alt"></i> Seguridad</h3>
                                <ul>
                                    <li>Control de seguridad obligatorio en la entrada</li>
                                    <li>Prohibidas bebidas y comida exterior</li>
                                    <li>Cámaras profesionales requieren acreditación</li>
                                    <li>Personal de seguridad y sanitario en el recinto</li>
                                </ul>
                            </div>
                            <div class="policy-section">
                                <h3><i class="fas fa-info-circle"></i> Información adicional</h3>
                                <ul>
                                    <li>Evento para mayores de 16 años</li>
                                    <li>Menores de edad requieren autorización parental</li>
                                    <li>Recomendable llegar 1 hora antes del inicio</li>
                                    <li>Consulta el tiempo antes de asistir</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    
                    </main>

                <!-- Sidebar - Ticket Selection -->
                <aside class="tickets-sidebar">
                    <div class="tickets-card">
                        <div class="card-header">
                            <h3>Selecciona tus entradas</h3>
                            <?php
                            // Estado de disponibilidad por TOTAL de entradas (solo al cargar)
                            $sumDisponibles = 0; 
                            foreach (($tiposEntrada ?? []) as $t) {
                                $sumDisponibles += (int)($t['disponibles'] ?? 0);
                            }

                            if ($sumDisponibles <= 0) {
                                $stockClass = 'soldout';
                                $stockText  = 'Entradas agotadas';
                            } elseif ($sumDisponibles <= 10) {
                                $stockClass = 'low';
                                $stockText  = 'Pocas entradas disponibles';
                            } elseif ($sumDisponibles <= 50) {
                                $stockClass = 'medium';
                                $stockText  = 'Entradas limitadas';
                            } else {
                                $stockClass = 'available';
                                $stockText  = 'Entradas disponibles';
                            }
                            ?>
                            <div class="availability-status">
                                <span class="status-indicator <?php echo $stockClass; ?>"></span>
                                <span><?php echo $stockText; ?></span>
                            </div>
                        </div>

                        <?php if (!empty($tiposEntrada)): ?>
                            <form action="checkout.php" method="POST" id="ticketForm">
                                <input type="hidden" name="idEvento" value="<?php echo (int)$evento['idEvento']; ?>">

                                <div class="ticket-types">
                                    <?php foreach ($tiposEntrada as $tipo): 
                                        $id = (int)$tipo['idTipoEntrada'];
                                        $max = min((int)$tipo['disponibles'], 10);
                                    ?>
                                    <div class="ticket-type" data-id="<?php echo $id; ?>">
                                        <div class="ticket-info">
                                            <h4><?php echo htmlspecialchars($tipo['nombre'] ?? 'Entrada'); ?></h4>

                                            <?php if (!empty($tipo['descripcion'])): ?>
                                                <p><?php echo htmlspecialchars($tipo['descripcion']); ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($tipo['zona_nombre'])): ?>
                                                <ul class="ticket-includes">
                                                    <li>✓ Zona: <?php echo htmlspecialchars($tipo['zona_nombre']); ?></li>
                                                </ul>
                                            <?php endif; ?>
                                        </div>

                                        <div class="ticket-purchase">
                                            <div class="price-info">
                                                <span class="price"><?php echo format_price($tipo['precio']); ?></span>
                                                <!-- Si manejas gastos, muéstralos aquí -->
                                                <?php if (isset($tipo['gastos'])): ?>
                                                    <span class="taxes">+ <?php echo format_price($tipo['gastos']); ?> gastos</span>
                                                <?php endif; ?>
                                                <span class="available-count"><?php echo (int)$tipo['disponibles']; ?> disponibles</span>
                                            </div>

                                            <div class="quantity-selector">
                                                <button type="button" class="qty-btn minus" onclick="decreaseQuantity(<?php echo $id; ?>)">-</button>
                                                <span class="quantity" id="qty_label_<?php echo $id; ?>">0</span>

                                                <!-- Input real (usado por tu JS y el form). Lo escondemos visualmente. -->
                                                <input
                                                    type="number"
                                                    id="qty_<?php echo $id; ?>"
                                                    name="entradas[<?php echo $id; ?>]"
                                                    value="0"
                                                    min="0"
                                                    max="<?php echo $max; ?>"
                                                    onchange="syncQtyLabel(<?php echo $id; ?>); updateTotal();"
                                                    style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;"
                                                />

                                                <button type="button" class="qty-btn plus" onclick="increaseQuantity(<?php echo $id; ?>, <?php echo $max; ?>)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="purchase-summary">
                                    <div class="summary-content" style="display:none;">
                                        <h4>Resumen de compra</h4>
                                        <div class="summary-items"></div>
                                        <div class="summary-total">
                                            <div class="total-line">
                                                <span>Subtotal:</span>
                                                <span class="subtotal">0€</span>
                                            </div>
                                            <div class="total-line">
                                                <span>Gastos de gestión:</span>
                                                <span class="fees">0€</span>
                                            </div>
                                            <div class="total-line final">
                                                <span>Total:</span>
                                                <span class="total" id="totalPrice">0,00 €</span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (is_logged_in()): ?>
                                        <button type="submit" class="btn-continue" id="buyButton" disabled>
                                            Continuar con la compra
                                        </button>
                                    <?php else: ?>
                                        <a href="login.php?redirect=event-detail.php?id=<?php echo (int)$evento['idEvento']; ?>" class="btn-continue" id="buyButton">
                                            Inicia sesión para comprar
                                        </a>
                                    <?php endif; ?>

                                    <p class="security-note">
                                        <i class="fas fa-shield-alt"></i>
                                        Compra 100% segura y sin costos ocultos
                                    </p>
                                </div>
                            </form>
                        <?php else: ?>
                            <p style="text-align:center; padding:20px;">No hay entradas disponibles en este momento</p>
                        <?php endif; ?>
                    </div>

                    <div class="trust-badges">
                        <div class="badge-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Compra Segura</strong>
                                <p>Certificado SSL</p>
                            </div>
                        </div>
                        <div class="badge-item">
                            <i class="fas fa-undo"></i>
                            <div>
                                <strong>Reembolso garantizado</strong>
                                <p>Hasta 48h antes</p>
                            </div>
                        </div>
                        <div class="badge-item">
                            <i class="fas fa-headset"></i>
                            <div>
                                <strong>Soporte 24/7</strong>
                                <p>Siempre disponible</p>
                            </div>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </section>
    

    <!-- Footer -->
   <?php include __DIR__ . '/../includes/footer.php'; ?>


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
    const current = parseInt(input.value || '0', 10);
    if (current < max) {
        input.value = current + 1;
        syncQtyLabel(id);        // 🔴 actualizar el <span>
        updateTotal();
    }
}
        function syncQtyLabel(id){
    const input = document.getElementById('qty_' + id);
    const label = document.getElementById('qty_label_' + id); // span del HTML
    if (input && label) {
        const val = Math.max(0, parseInt(input.value || '0', 10));
        label.textContent = String(val);
    }
}

      function decreaseQuantity(id) {
    const input = document.getElementById('qty_' + id);
    const current = parseInt(input.value || '0', 10);
    if (current > 0) {
        input.value = current - 1;
        syncQtyLabel(id);        // 🔴 actualizar el <span>
        updateTotal();
    }
}
    </script>
</body>
</html>