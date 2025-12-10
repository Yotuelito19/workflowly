<?php
/**
 * Proceso de compra - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Compra.php';

// Verificar que esté logueado
if (!is_logged_in()) {
    redirect('/views/login.php');
}

// Conectar BD una vez
$database = new Database();
$db = $database->getConnection();

// Helper para obtener la URL correcta de la imagen del evento
if (!function_exists('getEventImageUrl')) {
    function getEventImageUrl($imagePath) {
        // Placeholder por defecto
        $placeholder = BASE_URL . '/api/admin/events/uploads/0b10db93db401e3d.jpg';

        // Si no hay imagen o es la default
        if (empty($imagePath) || $imagePath === 'default.jpg' || $imagePath === 'imagen/default.jpg') {
            return $placeholder;
        }

        // Quitar 'uploads/' para evitar duplicados
        $cleanPath = str_replace('uploads/', '', $imagePath);

        // 1) ¿Existe en /uploads ?
        $mainUploadPath = UPLOADS_PATH . '/' . $cleanPath;
        if (file_exists($mainUploadPath)) {
            return UPLOADS_URL . '/' . $cleanPath;
        }

        // 2) ¿Existe en /api/admin/events/uploads ?
        $adminUploadPath = BASE_PATH . '/api/admin/events/uploads/' . $cleanPath;
        if (file_exists($adminUploadPath)) {
            return BASE_URL . '/api/admin/events/uploads/' . $cleanPath;
        }

        // 3) Si no existe en ningún sitio, placeholder
        return $placeholder;
    }
}


// Carrito y timer
$carrito          = [];
$total            = 0;
$remainingSeconds = 0;
$idEvento         = null;

// 0) Si viene ?timeout=1 desde el JS → limpiar checkout y volver al evento
if (isset($_GET['timeout']) && isset($_SESSION['checkout'])) {
    $idEventoSesion = $_SESSION['checkout']['idEvento'] ?? null;
    unset($_SESSION['checkout']);

    if ($idEventoSesion) {
        redirect('/views/event-detail.php?id=' . (int)$idEventoSesion . '&timeout=1');
    } else {
        redirect('/views/search-events.php?timeout=1');
    }
}

// 1) Llegamos desde event-detail con las entradas seleccionadas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entradas']) && !isset($_POST['confirmar_compra'])) {
    $carrito = [];
    $total   = 0;

    foreach ($_POST['entradas'] as $idTipoEntrada => $cantidad) {
        $cantidad = (int)$cantidad;
        if ($cantidad <= 0) {
            continue;
        }

        $query = "SELECT 
            te.*, 
            e.idEvento,
            e.nombre AS evento_nombre, 
            e.fechaInicio, 
            e.ubicacion,
            e.imagenPrincipal
          FROM TipoEntrada te
          INNER JOIN Evento e ON te.idEvento = e.idEvento
          WHERE te.idTipoEntrada = :id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $idTipoEntrada, PDO::PARAM_INT);
        $stmt->execute();
        $entrada = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($entrada) {
            $entrada['cantidad'] = $cantidad;
            $entrada['subtotal'] = $entrada['precio'] * $cantidad;
            $carrito[] = $entrada;
            $total    += $entrada['subtotal'];

            if ($idEvento === null && !empty($entrada['idEvento'])) {
                $idEvento = (int)$entrada['idEvento'];
            }
        }
    }

    if (!empty($carrito)) {
        $expiresAt = time() + (15 * 60); // 15 minutos
        $_SESSION['checkout'] = [
            'carrito'    => $carrito,
            'total'      => $total,
            'expires_at' => $expiresAt,
            'idEvento'   => $idEvento ?? (int)($_POST['idEvento'] ?? 0),
        ];
    }
}

// 2) Recuperar carrito de la sesión si existe
if (isset($_SESSION['checkout'])) {
    $checkoutData = $_SESSION['checkout'];
    $expiresAt    = $checkoutData['expires_at'] ?? (time() + 15 * 60);

    // Si ya ha caducado, limpiar y volver al evento
    if (time() >= $expiresAt) {
        $idEventoSesion = $checkoutData['idEvento'] ?? null;
        unset($_SESSION['checkout']);

        if ($idEventoSesion) {
            redirect('/views/event-detail.php?id=' . (int)$idEventoSesion . '&timeout=1');
        } else {
            redirect('/views/search-events.php?timeout=1');
        }
    }

    $carrito          = $checkoutData['carrito'] ?? [];
    $total            = $checkoutData['total'] ?? 0;
    $idEvento         = $checkoutData['idEvento'] ?? null;
    $remainingSeconds = max(0, $expiresAt - time());
}

// Si tras todo esto no hay carrito, fuera
if (empty($carrito)) {
    redirect('/views/search-events.php');
}

// Procesar compra
$compra_exitosa = false;
$error_compra   = '';

// 3) Confirmar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_compra'])) {
    // Revalidar que siga habiendo datos en sesión y no haya caducado
    if (!isset($_SESSION['checkout'])) {
        if ($idEvento) {
            redirect('/views/event-detail.php?id=' . (int)$idEvento . '&timeout=1');
        } else {
            redirect('/views/search-events.php?timeout=1');
        }
    }

    $checkoutData = $_SESSION['checkout'];
    $expiresAt    = $checkoutData['expires_at'] ?? (time() - 1);

    if (time() >= $expiresAt) {
        $idEventoSesion = $checkoutData['idEvento'] ?? null;
        unset($_SESSION['checkout']);

        if ($idEventoSesion) {
            redirect('/views/event-detail.php?id=' . (int)$idEventoSesion . '&timeout=1');
        } else {
            redirect('/views/search-events.php?timeout=1');
        }
    }

    $carrito = $checkoutData['carrito'] ?? [];
    $total   = $checkoutData['total'] ?? 0;

    try {
        // Crear método de pago
        $queryMetodo = "INSERT INTO MetodoPago (idUsuario, tipo, nombreTitular) 
                        VALUES (:idUsuario, :tipo, :titular)";
        $stmtMetodo = $db->prepare($queryMetodo);
        $stmtMetodo->execute([
            ':idUsuario' => $_SESSION['user_id'],
            ':tipo'      => 'Tarjeta',
            ':titular'   => $_POST['nombre_titular']
        ]);
        $idMetodoPago = $db->lastInsertId();

        // Crear compra usando el modelo
        $compraModel = new Compra($db);
        $compraModel->idUsuario    = $_SESSION['user_id'];
        $compraModel->idMetodoPago = $idMetodoPago;
        $compraModel->total        = $total;

        // Obtener estado "Pagado" para la compra
        $queryEstado = "SELECT idEstado FROM Estado 
                        WHERE nombre = 'Pagado' AND tipoEntidad = 'Compra' 
                        LIMIT 1";
        $stmtEstado = $db->prepare($queryEstado);
        $stmtEstado->execute();
        $rowEstado = $stmtEstado->fetch(PDO::FETCH_ASSOC);
        $compraModel->idEstadoCompra = $rowEstado['idEstado'] ?? null;

        if ($compraModel->idEstadoCompra === null) {
            throw new Exception('Estado "Pagado" no encontrado en la tabla Estado.');
        }

        if ($compraModel->crear()) {
            // Detalles de compra (actualiza disponibilidad + genera Entradas)
            foreach ($carrito as $item) {
                $compraModel->agregarDetalle($item['idTipoEntrada'], $item['cantidad'], $item['precio']);
            }

            $compra_exitosa = true;
            $_SESSION['ultima_compra'] = $compraModel->idCompra;

            // Limpiamos el checkout para que no se pueda reutilizar
            unset($_SESSION['checkout']);

            // Redirigir a confirmación
            redirect('/views/confirmation.php?compra=' . $compraModel->idCompra);
        }
    } catch (Exception $e) {
        $error_compra = 'Error al procesar la compra. Inténtalo de nuevo.';
        error_log("Error en checkout: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - WorkFlowly</title>
    <link rel="stylesheet" href="../assets/css/checkout.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="checkout-content">

        <div class="container">
            <h1>Finalizar compra</h1>
            
            <?php if (!empty($error_compra)): ?>
                <div class="alert alert-error"><?php echo $error_compra; ?></div>
            <?php endif; ?>

            <?php if (!empty($carrito)): ?>
                <div class="checkout-timer"
                     data-remaining="<?php echo (int)$remainingSeconds; ?>"
                     data-expire-url="/views/checkout.php?timeout=1">
                    <div class="checkout-timer-text">
                        <i class="fas fa-clock"></i>
                        <span>Tus entradas estarán reservadas durante:</span>
                    </div>
                    <div class="checkout-timer-value" id="checkout-timer">--:--</div>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">
             


            <div class="checkout-layout">
                <div class="checkout-form">
                    <form method="POST" action="">
                        <input type="hidden" name="confirmar_compra" value="1">
                        
                        <div class="form-section">
                            <h2>Información de facturación</h2>
                            <div class="form-group">
                                <label>Nombre completo</label>
                                <input type="text" name="nombre_titular" required value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>">
                            </div>
                        </div>
<br>
                        <div class="form-section">
                            <h2>Método de pago</h2>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" name="metodo_pago" value="tarjeta" checked>
                                    <i class="fas fa-credit-card"></i>
                                    <span>Tarjeta de crédito/débito</span>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>Número de tarjeta</label>
                                <input
                                    type="text"
                                    name="numero_tarjeta"
                                    id="numero_tarjeta"
                                    placeholder="1234 5678 9012 3456"
                                    maxlength="19"
                                    inputmode="numeric"
                                    autocomplete="cc-number"
                                    required
                                    pattern="\d{4} \d{4} \d{4} \d{4}"
                                    oninput="formatCardNumber(this)"
                                >
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Fecha de expiración</label>
                                    <input
                                        type="text"
                                        name="fecha_expiracion"
                                        id="fecha_expiracion"
                                        placeholder="MM/AA"
                                        maxlength="5"
                                        inputmode="numeric"
                                        autocomplete="cc-exp"
                                        required
                                        pattern="(0[1-9]|1[0-2])\/\d{2}"
                                        oninput="formatExpiry(this)"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input
                                        type="text"
                                        name="cvv"
                                        id="cvv"
                                        placeholder="123"
                                        maxlength="3"
                                        inputmode="numeric"
                                        autocomplete="cc-csc"
                                        required
                                        pattern="\d{3}"
                                        oninput="formatCVV(this)"
                                    >
                                </div>
                            </div>

                        </div>
                        <br>

                        <button type="submit" class="btn-primary btn-large">
                            Confirmar y pagar <?php echo format_price($total); ?>
                        </button>
                    </form>
                </div>

<aside class="order-summary">
    <div class="summary-card">
        <div class="summary-header">
            <h3>Resumen del pedido</h3>
        </div>

        <?php
        // Primer item para datos del evento
        $eventoResumen = $carrito[0] ?? null;
        $imgUrl = $eventoResumen ? getEventImageUrl($eventoResumen['imagenPrincipal'] ?? '') : null;
        ?>

        <?php if ($eventoResumen): ?>
            <div class="event-info">
                <div class="event-image"
                     style="background-image: url('<?php echo htmlspecialchars($imgUrl, ENT_QUOTES); ?>');
                            background-size: cover;
                            background-position: center;">
                </div>
                <div class="event-details">
                    <h4><?php echo htmlspecialchars($eventoResumen['evento_nombre']); ?></h4>
                    <div class="event-meta">
                        <span>
                            <i class="fas fa-calendar"></i>
                            <?php echo format_date($eventoResumen['fechaInicio']); ?>
                        </span>
                        <span>
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($eventoResumen['ubicacion']); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

       <div class="tickets-summary">
    <?php foreach ($carrito as $item): ?>
        <div class="ticket-item">
            <span class="ticket-type">
                <?php echo htmlspecialchars($item['nombre']); ?>
            </span>
            <span class="ticket-price">
                <span class="ticket-amount">
                    <?php echo format_price($item['precio']); ?>
                </span>
                <span class="ticket-quantity">
                    x<?php echo (int)$item['cantidad']; ?>
                </span>
            </span>
        </div>
    <?php endforeach; ?>
</div>


        <div class="summary-breakdown">
            <div class="breakdown-total">
                <span>Total</span>
                <span><?php echo format_price($total); ?></span>
            </div>
        </div>
    </div>

    <div class="security-badges">
        <div class="security-item">
            <i class="fas fa-shield-alt"></i>
            <div>
                <strong>Compra 100% segura</strong>
                <small>Tus datos se envían cifrados</small>
            </div>
        </div>
        <div class="security-item">
            <i class="fas fa-lock"></i>
            <div>
                <strong>Protección de datos</strong>
                <small>Cumplimos la normativa RGPD</small>
            </div>
        </div>
        <div class="security-item">
            <i class="fas fa-headset"></i>
            <div>
                <strong>Soporte al cliente</strong>
                <small>Te ayudamos si hay problemas con tus entradas</small>


            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

 <script>
    (function () {
        var container = document.querySelector('.checkout-timer');
        if (!container) return;

        var remaining = parseInt(container.dataset.remaining || '0', 10);
        if (isNaN(remaining) || remaining <= 0) return;

        var expireUrl = container.dataset.expireUrl;
        var labelEl   = document.getElementById('checkout-timer');

        function formatTime(s) {
            var m = Math.floor(s / 60);
            var r = s % 60;
            var mm = m < 10 ? '0' + m : '' + m;
            var ss = r < 10 ? '0' + r : '' + r;
            return mm + ':' + ss;
        }

        function tick() {
            if (remaining <= 0) {
                window.location.href = expireUrl;
                return;
            }

            if (labelEl) {
                labelEl.textContent = formatTime(remaining);
            }
            remaining -= 1;
            setTimeout(tick, 1000);
        }

        if (labelEl) {
            labelEl.textContent = formatTime(remaining);
        }
        tick();
    })();
    function onlyDigits(value) {
    return value.replace(/\D/g, '');
}

        function formatCardNumber(el) {
            let value = onlyDigits(el.value).slice(0, 16); // máximo 16 dígitos
            let groups = value.match(/.{1,4}/g);
            el.value = groups ? groups.join(' ') : '';
        }

        function formatExpiry(el) {
    let value = onlyDigits(el.value).slice(0, 4); // MMYY

    // Formateo visual (MM/YY)
    if (value.length >= 3) {
        el.value = value.slice(0, 2) + '/' + value.slice(2);
    } else {
        el.value = value;
    }

    // Validación únicamente cuando MMYY está completo
    if (value.length === 4) {
        const mm = parseInt(value.slice(0, 2));
        const yy = parseInt(value.slice(2));

        // Validación de mes válido: 1–12
        if (mm < 1 || mm > 12) {
            el.setCustomValidity("Introduce un mes válido (01–12)");
            return;
        }

        // Obtener fecha actual
        const today = new Date();
        const currentMM = today.getMonth() + 1; // 0–11 → 1–12
        const currentYY = today.getFullYear() % 100; // últimos dos dígitos

        // Comparar año
        if (yy < currentYY) {
            el.setCustomValidity("La tarjeta está expirada");
            return;
        }

        // Si es el mismo año, comparar mes
        if (yy === currentYY && mm < currentMM) {
            el.setCustomValidity("La tarjeta está expirada");
            return;
        }

        // Si pasa todo → válido
        el.setCustomValidity("");
    } else {
        // Mientras no esté completo, no bloquear
        el.setCustomValidity("");
    }
}

        function formatCVV(el) {
            el.value = onlyDigits(el.value).slice(0, 3); // máximo 3 dígitos
        }
            </script>
</body>
</html>