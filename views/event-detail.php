<?php
/**
 * Detalle de evento - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Evento.php'; 

// Obtener ID del evento
$idEvento = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idEvento === 0) {
    redirect('/views/search-events.php');
}

// Conectar BD y obtener evento
$database = new Database();
$db = $database->getConnection();
$eventoModel = new Evento($db);


$evento = $eventoModel->obtenerDetalleCompleto($idEvento);

if (!$evento) {
    redirect('/views/search-events.php');
}

// Obtener tipos de entrada disponibles
$tiposEntrada = $eventoModel->obtenerTiposEntrada($idEvento);


$politicas = [];
try {
    $stmtPol = $db->prepare("SELECT categoria, titulo, descripcion
                             FROM PoliticaEvento
                             WHERE idEvento = :id
                             ORDER BY categoria, idPolitica");
    $stmtPol->execute([':id' => $idEvento]);
    $politicas = $stmtPol->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $politicas = [];
}
?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_contact'])) {
  $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($evento['nombre']); ?> - WorkFlowly</title>
    
    <link rel="stylesheet" href="../assets/css/event-detail.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>
<div id="contactToast" class="contact-toast" aria-live="polite">
  ¡Hemos recibido tu mensaje! Te responderemos lo antes posible.
</div>

    <!-- Breadcrumbs -->
    <nav class="breadcrumbs">
        <div class="container">
            <a href="../index.php">Inicio</a>
            <i class="fas fa-chevron-right"></i>
            <a href="search-events.php">Eventos</a>
            <i class="fas fa-chevron-right"></i>
            <a href="search-events.php?tipo=<?= urlencode($evento['tipo']); ?>"><?= htmlspecialchars($evento['tipo']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($evento['nombre']); ?></span>
        </div>
    </nav>

    <!-- Event Hero -->
    <section class="event-hero">
        <div class="container">
            <div class="hero-layout">
                <!-- Imagen del evento -->
                <div class="hero-image-container">
                    <img src="<?= UPLOADS_URL . '/' . $evento['imagenPrincipal']; ?>" 
                         alt="<?= htmlspecialchars($evento['nombre']); ?>"
                         class="event-main-image">
                </div>
                
                <!-- Información del evento -->
                <div class="hero-info">
                    <div class="event-badges">
                        <span class="badge category"><?= htmlspecialchars($evento['tipo']); ?></span>
                        <?php if ($evento['entradasDisponibles'] < 100): ?>
                            <span class="badge trending">¡Últimas entradas!</span>
                        <?php endif; ?>
                    </div>
                    
                    <h1><?= htmlspecialchars($evento['nombre']); ?></h1>
                    
                    <div class="event-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <strong><?= date('d M Y', strtotime($evento['fechaInicio'])); ?></strong>
                                <span><?= date('l, H:i', strtotime($evento['fechaInicio'])); ?></span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <?php
                                if (!empty($evento['lugar_nombre'])) {
                                    $tituloLugar = $evento['lugar_nombre'];
                                    $subLugar = trim(($evento['lugar_ciudad'] ?? '') . ', ' . ($evento['lugar_pais'] ?? ''), ' ,');
                                } else {
                                    $tituloLugar = $evento['ubicacion'];
                                    $subLugar = 'España';
                                }
                                ?>
                                <strong><?= htmlspecialchars($tituloLugar); ?></strong>
                                <?php if ($subLugar): ?>
                                    <span><?= htmlspecialchars($subLugar); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-ticket-alt"></i>
                            <div>
                                <strong><?= (int)$evento['entradasDisponibles']; ?> disponibles</strong>
                                <span>de <?= (int)$evento['aforoTotal']; ?> totales</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hero-actions">
                        <button class="btn-favorite"
                        data-evento-id="<?php echo $evento['idEvento']; ?>" 
                        onclick="toggleFavorito(<?php echo $evento['idEvento']; ?>, this)"
                        aria-label="Agregar a favoritos">
                        <i class="far fa-heart"></i>
                        Guardar evento
                    </button>
                        <button class="btn-share" 
                            onclick="compartirEvento(<?php echo (int)$evento['idEvento']; ?>, '<?php echo htmlspecialchars($evento['nombre'], ENT_QUOTES); ?>')">
                            <i class="fas fa-share-alt"></i>
                            Compartir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php
    // Calcular entradas disponibles reales a partir de los tipos (sin duplicar por zonas)
    $sumDisponiblesHeader = 0;
    $tiposContados = [];

    foreach (($tiposEntrada ?? []) as $t) {
        $idTipo = (int)($t['idTipoEntrada'] ?? 0);

        // Solo sumar la primera vez que se vea ese idTipoEntrada
        if ($idTipo && !isset($tiposContados[$idTipo])) {
            $sumDisponiblesHeader += (int)($t['disponibles'] ?? 0);
            $tiposContados[$idTipo] = true;
        }
    }
?>
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
                            <p><?= nl2br(htmlspecialchars($evento['descripcion'] ?? 'Sin descripción disponible')); ?></p>
                        </div>
                    </div>

                    <!-- Localización -->
                    <div class="venue-card">
                        <h2>Localización</h2>
                        <div class="venue-info">
                            <div class="venue-details">
                                <h3>
                                    <?php
                                    if (!empty($evento['lugar_nombre'])) {
                                        echo htmlspecialchars($evento['lugar_nombre']);
                                    } else {
                                        echo htmlspecialchars($evento['ubicacion']);
                                    }
                                    ?>
                                </h3>
                                <p class="venue-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php
                                    
                                    if (!empty($evento['lugar_direccion'])) {
                                        echo htmlspecialchars($evento['lugar_direccion']);
                                    } elseif (!empty($evento['lugar_ciudad']) || !empty($evento['lugar_pais'])) {
                                        echo htmlspecialchars(trim(($evento['lugar_ciudad'] ?? '') . ', ' . ($evento['lugar_pais'] ?? ''), ' ,'));
                                    } else {
                                        echo 'Dirección pendiente';
                                    }
                                    ?>
                                </p>
                                <div class="venue-features">
                                    <span class="feature">
                                        <i class="fas fa-users"></i>
                                        Capacidad:
                                        <?php
                                        if (!empty($evento['lugar_capacidad'])) {
                                            echo (int)$evento['lugar_capacidad'] . ' personas';
                                        } else {
                                            echo (int)$evento['aforoTotal'] . ' personas';
                                        }
                                        ?>
                                    </span>

                                    <?php if (!empty($evento['lugar_acceso'])): ?>
                                        <span class="feature">
                                            <i class="fas fa-wheelchair"></i>
                                            Acceso para discapacitados
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($evento['lugar_parking'])): ?>
                                        <span class="feature">
                                            <i class="fas fa-car"></i>
                                            <?= htmlspecialchars($evento['lugar_parking']); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($evento['lugar_transporte'])): ?>
                                        <span class="feature">
                                            <i class="fas fa-subway"></i>
                                            <?= htmlspecialchars($evento['lugar_transporte']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="venue-map">
                                <div class="map-placeholder">
                                    <i class="fas fa-map"></i>
                                    <p>Mapa interactivo</p>
                                    <?php if (!empty($evento['lugar_mapa_url'])): ?>
                                        <a href="<?= htmlspecialchars($evento['lugar_mapa_url']); ?>" target="_blank" class="btn-map">Ver en Google Maps</a>
                                    <?php else: ?>
                                        <button class="btn-map" disabled>Mapa no disponible</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Organizador -->
                    <div class="organizer-card">
                        <h2>Información del organizador</h2>
                        <div class="organizer-info">
                            <div class="organizer-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="organizer-details">
                                <h3>
                                    <?php
                                    if (!empty($evento['org_nombre'])) {
                                        echo htmlspecialchars(trim($evento['org_nombre'] . ' ' . ($evento['org_apellidos'] ?? '')));
                                    } else {
                                        
                                        echo htmlspecialchars(($evento['organizador_nombre'] ?? 'Organizador') . ' ' . ($evento['organizador_apellidos'] ?? ''));
                                    }
                                    ?>
                                </h3>
                                <p class="organizer-description">
                                    <?= htmlspecialchars($evento['org_descripcion'] ?? 'Empresa líder en gestión de espectáculos y conciertos.'); ?>
                                </p>
                                <div class="organizer-stats">
                                    <span class="stat">
                                        <strong><?= (int)($evento['org_total_eventos'] ?? 24); ?>+</strong> eventos organizados
                                    </span>
                                    <span class="stat">
                                        <strong><?= $evento['org_valoracion'] !== null ? htmlspecialchars($evento['org_valoracion']) . '★' : '4.9★'; ?></strong> valoración promedio
                                    </span>
                                    <span class="stat">
                                        <strong><?= (int)($evento['org_total_asistentes'] ?? 120000); ?></strong> asistentes
                                    </span>
                                </div>
                               <?php
                                $mailOrg = $evento['org_email'] ?? $evento['organizador_email'] ?? '';
                                if (!empty($mailOrg)): ?>
                                <button type="button"
                                        class="btn-contact"
                                        id="btnContactOrganizer"
                                        data-organizer-email="<?= htmlspecialchars($mailOrg); ?>"
                                        data-organizer-id="<?= (int)($evento['idOrganizador'] ?? 0); ?>">
                                    Contactar
                                </button>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <!-- Políticas -->
                    <div class="policies-card">
                        <h2>Políticas del evento</h2>
                        <div class="policies-content">
                            <?php if (!empty($politicas)): ?>
                                <?php
                                // agrupamos por categoría 
                                $porCat = [];
                                foreach ($politicas as $p) {
                                    $cat = $p['categoria'] ?: 'Información';
                                    $porCat[$cat][] = $p;
                                }
                                foreach ($porCat as $cat => $items): ?>
                                    <div class="policy-section">
                                        <h3>
                                            <?php if ($cat === 'Entradas'): ?>
                                                <i class="fas fa-ticket-alt"></i>
                                            <?php elseif ($cat === 'Cancelaciones'): ?>
                                                <i class="fas fa-undo"></i>
                                            <?php elseif ($cat === 'Seguridad'): ?>
                                                <i class="fas fa-shield-alt"></i>
                                            <?php else: ?>
                                                <i class="fas fa-info-circle"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($cat); ?>
                                        </h3>
                                        <ul>
                                            <?php foreach ($items as $it): ?>
                                                <li><?= htmlspecialchars($it['titulo'] ?: $it['descripcion']); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                               
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
                            <?php endif; ?>
                        </div>
                    </div>

                </main>

                <!-- Sidebar - Ticket Selection -->
                 <aside class="tickets-sidebar">
                    <div class="tickets-card">
                        <div class="card-header">
                            <h3>Selecciona tus entradas</h3>
                            <?php
                           $sumDisponibles = 0;
                            $tiposContados = [];

                                foreach (($tiposEntrada ?? []) as $t) {
                                $idTipo = (int)($t['idTipoEntrada'] ?? 0);

                        if ($idTipo && !isset($tiposContados[$idTipo])) {
                            $sumDisponibles += (int)($t['disponibles'] ?? 0);
                            $tiposContados[$idTipo] = true;
                        }
                        }   


                            $aforoTotal = (int)($evento['aforoTotal'] ?? 0);

                          
                            $percentLeftFloat = ($aforoTotal > 0) ? ($sumDisponibles / $aforoTotal * 100) : 0.0;

                            
                            if ($sumDisponibles === 0) {
                                $percentText = '0%';
                            } elseif ($percentLeftFloat < 1) {
                                $percentText = '<1%';
                            } else {
                                $percentText = (string)round($percentLeftFloat) . '%';
                            }

                            // Umbrales por % 
                            if ($sumDisponibles === 0) {
                                $stockClass = 'soldout';
                                $stockText  = 'Entradas agotadas';
                            } elseif ($percentLeftFloat <= 10) {
                                $stockClass = 'low';
                                $stockText  = '¡Últimas entradas! (' . $percentText . ')';
                            } elseif ($percentLeftFloat <= 35) {
                                $stockClass = 'medium';
                                $stockText  = 'Entradas limitadas (' . $percentText . ')';
                            } else {
                                $stockClass = 'available';
                                $stockText  = 'Entradas disponibles (' . $percentText . ')';
                            }
                            ?>
                            <div class="availability-status">
                            <span class="status-indicator <?php echo $stockClass; ?>"></span>
                            <span><?php echo $stockText; ?></span>
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
                                               
                                                <?php if (isset($tipo['gastos'])): ?>
                                                    <span class="taxes">+ <?php echo format_price($tipo['gastos']); ?> gastos</span>
                                                <?php endif; ?>
                                                <span class="available-count"><?php echo (int)$tipo['disponibles']; ?> disponibles</span>
                                            </div>

                                            <div class="quantity-selector">
                                                <button type="button" class="qty-btn minus" onclick="decreaseQuantity(<?php echo $id; ?>)">-</button>
                                                <span class="quantity" id="qty_label_<?php echo $id; ?>">0</span>

                                               
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
    <div
        class="co-modal"
        id="contactOrganizerModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="contactOrganizerTitle"
        data-api-endpoint="<?= BASE_URL ?>/api/contact/contact_organizer.php"
        >
        <div class="co-dialog">
            <div class="co-head">
            <h3 class="co-title" id="contactOrganizerTitle">Contactar con el organizador</h3>
            <button class="co-close" id="coClose" aria-label="Cerrar">×</button>
            </div>

            <form id="contactOrganizerForm" class="co-body" novalidate>
            <div class="co-grid">
                <div class="co-field">
                <label for="coNombre">Nombre *</label>
                <input type="text" id="coNombre" name="nombre" required
                        value="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
                </div>

                <div class="co-field">
                <label for="coApellidos">Apellidos *</label>
                <input type="text" id="coApellidos" name="apellidos" required>
                </div>

              <div class="co-field">
                <label for="coEmail">Tu correo electrónico *</label>
                <input type="email" id="coEmail" name="email" required
                        placeholder="tucorreo@ejemplo.com"
                        value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                </div>



                <div class="co-field">
                <label for="coPedido">Número de pedido (opcional)</label>
                <input type="text" id="coPedido" name="num_pedido" placeholder="Ej.: 12345">
                </div>

                <div class="co-field full">
                <label for="coDesc">Descripción de lo que pasa *</label>
                <textarea id="coDesc" name="descripcion" required minlength="10"
                            placeholder="Cuéntanos el problema con detalle"></textarea>
                </div>
            </div>

            <!-- Hidden -->
            <input type="hidden" name="evento_id" value="<?= (int)$idEvento ?>">
            <input type="hidden" name="organizer_email" id="coOrgEmail" value="">
            <input type="hidden" name="organizer_id" id="coOrgId" value="">
            </form>

            <div class="co-foot">
            <button type="button" class="co-btn co-btn-secondary" id="coCancel">Cancelar</button>
            <button type="submit" form="contactOrganizerForm" class="co-btn co-btn-accent" id="coSend">Enviar</button>
            </div>
        </div>
        </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

     <script>

// Verificar favoritos al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    <?php if (is_logged_in()): ?>
    document.querySelectorAll('.btn-favorite').forEach(btn => {
        const eventoId = btn.dataset.eventoId;
        verificarFavorito(eventoId, btn);
    });
    <?php endif; ?>
});

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
        syncQtyLabel(id);        
        updateTotal();
    }
}
        function syncQtyLabel(id){
    const input = document.getElementById('qty_' + id);
    const label = document.getElementById('qty_label_' + id); 
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
        syncQtyLabel(id);        
        updateTotal();
    }
}
(function () {
  const modal    = document.getElementById('contactOrganizerModal');
  const form     = document.getElementById('contactOrganizerForm');
  const toast    = document.getElementById('contactToast');
  const orgEmail = document.getElementById('coOrgEmail');
  const orgId    = document.getElementById('coOrgId');

  if (!modal || !form) return;
  const endpoint = modal.dataset.apiEndpoint || '/api/contact/contact_organizer.php';

  function openModal(btn){
    if (btn?.dataset?.organizerEmail) orgEmail.value = btn.dataset.organizerEmail;
    if (btn?.dataset?.organizerId)    orgId.value    = btn.dataset.organizerId;
    modal.classList.add('is-open');
    setTimeout(()=> document.getElementById('coNombre')?.focus(), 30);
  }
  function closeModal(){ 
    modal.classList.remove('is-open'); 
  }

  // Cerrar al hacer click fuera sin romper la selección de texto
  let coMouseDownOnOverlay = false;

  modal.addEventListener('mousedown', (e) => {
    // Solo marcamos si el botón se ha pulsado directamente sobre el overlay,
    // no dentro del contenido del modal.
    coMouseDownOnOverlay = (e.target === modal);
  });

  modal.addEventListener('mouseup', (e) => {
    // Cerramos solo si el mouse se pulsó y se soltó en el overlay.
    if (coMouseDownOnOverlay && e.target === modal) {
      closeModal();
    }
    coMouseDownOnOverlay = false;
  });

  document.addEventListener('click', (e)=>{
    const trigger = e.target.closest('#btnContactOrganizer, .organizer-card .btn-contact');
    if (trigger) { 
      e.preventDefault(); 
      openModal(trigger); 
      return; 
    }

    // Botón de cerrar o cancelar
    if (e.target.id === 'coClose' || e.target.id === 'coCancel') {
      e.preventDefault(); 
      closeModal();
    }
  });

  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!form.checkValidity()) { 
      form.reportValidity(); 
      return; 
    }

    const sendBtn = document.getElementById('coSend');
    sendBtn.disabled = true;

    try{
      const res = await fetch(endpoint, { 
        method:'POST', 
        body:new FormData(form), 
        credentials:'same-origin' 
      });

      let json, text;
      try { 
        json = await res.json(); 
      } catch(_) { 
        text = await res.text(); 
      }

      if (json?.ok) {
        closeModal(); 
        form.reset();
        if (toast) {
          toast.classList.add('is-visible');
          window.scrollTo({ top: 0, behavior: 'smooth' });
          setTimeout(()=> toast.classList.remove('is-visible'), 6000);
        }
      } else {
        console.warn('[WF] Respuesta no OK', json || text);
        alert((json && json.msg) || 'No se pudo enviar el mensaje. Revisa los campos e inténtalo de nuevo.');
      }
    } catch(err){
      console.error('[WF] Error envío', err);
      alert('Ha ocurrido un error inesperado.');
    } finally{
      sendBtn.disabled = false;
    }
  });
})();

// Compartir evento
function compartirEvento(idEvento, nombreEvento) {
    // Construir URL del evento
    const url = `${window.location.origin}/workflowly/views/event-detail.php?id=${idEvento}`;
    
    // Verificar si el navegador soporta Web Share API
    if (navigator.share) {
        navigator.share({
            title: nombreEvento,
            text: `¡No te pierdas este evento increíble! ${nombreEvento}`,
            url: url
        })
        .then(() => {
            console.log('Evento compartido exitosamente');
        })
        .catch((error) => {
            // Si el usuario cancela, no mostramos error
            if (error.name !== 'AbortError') {
                console.error('Error al compartir:', error);
                // Fallback si falla Web Share
                copiarAlPortapapeles(url);
            }
        });
    } else {
        // Fallback: copiar al portapapeles
        copiarAlPortapapeles(url);
    }
}
</script>
</body>
</html>
