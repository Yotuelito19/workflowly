<?php
/**
 * Panel de usuario - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que esté logueado
if (!is_logged_in()) {
    redirect('/views/login.php');
}

// Conectar BD
$database = new Database();
$db = $database->getConnection();

// Obtener datos del usuario
$usuarioModel = new Usuario($db);
$usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);

// Obtener entradas del usuario
$compraModel = new Compra($db);
$entradas = $compraModel->obtenerEntradasUsuario($_SESSION['user_id']);
$compras = $compraModel->obtenerComprasUsuario($_SESSION['user_id']);

// Variable para controlar qué pestaña mostrar
$active_tab = 'profile';
$mensaje_tipo = '';

// Procesar actualización de perfil
$mensaje_perfil = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $usuarioModel->idUsuario = $_SESSION['user_id'];
    $usuarioModel->nombre = sanitize_input($_POST['nombre']);
    $usuarioModel->apellidos = sanitize_input($_POST['apellidos']);
    $usuarioModel->telefono = sanitize_input($_POST['telefono']);
    
    if ($usuarioModel->actualizar()) {
        $mensaje_perfil = 'Perfil actualizado correctamente';
        $_SESSION['user_name'] = $usuarioModel->nombre;
        $usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);
    } else {
        $mensaje_perfil = 'Error al actualizar el perfil';
    }
    $active_tab = 'profile';
}

// Procesar cambio de contraseña
$mensaje_password = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];

    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        $mensaje_password = 'Por favor, completa todos los campos.';
        $mensaje_tipo = 'error';
    } elseif ($password_nueva !== $password_confirmar) {
        $mensaje_password = 'Las contraseñas nuevas no coinciden.';
        $mensaje_tipo = 'error';
    } elseif (strlen($password_nueva) < 8) {
        $mensaje_password = 'La nueva contraseña debe tener al menos 8 caracteres.';
        $mensaje_tipo = 'error';
    } else {
        $usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);

        if (password_verify($password_actual, $usuario['password'])) {
            $usuarioModel->idUsuario = $_SESSION['user_id'];
            $usuarioModel->password = password_hash($password_nueva, PASSWORD_DEFAULT);

            if ($usuarioModel->actualizarPassword()) {
                $mensaje_password = 'Contraseña actualizada correctamente.';
                $mensaje_tipo = 'success';
            } else {
                $mensaje_password = 'Error al actualizar la contraseña.';
                $mensaje_tipo = 'error';
            }
        } else {
            $mensaje_password = 'La contraseña actual es incorrecta.';
            $mensaje_tipo = 'error';
        }
    }
    $active_tab = 'security';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - WorkFlowly</title>
    <link rel="stylesheet" href="../assets/css/account.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
</head>
<body>
    <!-- Header -->
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="account-main">
      <section class="account-nav">
        <div class="container">
            <h1>Mi Cuenta</h1>
            <nav class="account-tabs">
                <a href="#profile" class="tab-btn <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" onclick="showTab(event, 'profile')">
                    <i class="fas fa-user"></i> Perfil
                </a>
                <a href="#tickets" class="tab-btn <?php echo $active_tab === 'tickets' ? 'active' : ''; ?>" onclick="showTab(event, 'tickets')">
                    <i class="fas fa-ticket-alt"></i> Mis Entradas
                </a>
                <a href="#favoritos" class="tab-btn <?php echo $active_tab === 'favoritos' ? 'active' : ''; ?>" onclick="showTab(event, 'favoritos')">
        <i class="fas fa-heart"></i> Favoritos
    </a>
                <a href="#payments" class="tab-btn <?php echo $active_tab === 'payments' ? 'active' : ''; ?>" onclick="showTab(event, 'payments')">
                    <i class="fas fa-credit-card"></i> Pagos
                </a>
                <a href="#security" class="tab-btn <?php echo $active_tab === 'security' ? 'active' : ''; ?>" onclick="showTab(event, 'security')">
                    <i class="fas fa-shield-alt"></i> Seguridad
                </a>
                <a href="#preferences" class="tab-btn <?php echo $active_tab === 'preferences' ? 'active' : ''; ?>" onclick="showTab(event, 'preferences')">
                    <i class="fas fa-sliders-h"></i> Preferencias
                </a>
            </nav>
        </div>
    </section>

    <!-- Main Content -->
    <div class="account-content">
        <div class="container">
           <!-- Mis Entradas -->
<div class="tab-content <?php echo $active_tab === 'tickets' ? 'active' : ''; ?>" id="tickets">
    <div class="tickets-section-header">
        <div>
            <h2>Mis Entradas</h2>
            <p class="subtitle">Gestiona todas tus entradas en un solo lugar</p>
        </div>
        <div class="tickets-stats">
            <div class="stat-badge">
                <i class="fas fa-ticket-alt"></i>
                <span><?php echo count($entradas); ?></span>
                <label>Total</label>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="tickets-controls">
        <div class="tickets-filters">
            <button class="filter-chip active" data-filter="all">
                <i class="fas fa-th"></i> Todas
            </button>
            <button class="filter-chip" data-filter="activa">
                <i class="fas fa-check-circle"></i> Activas
            </button>
            <button class="filter-chip" data-filter="usada">
                <i class="fas fa-history"></i> Usadas
            </button>
            <button class="filter-chip" data-filter="cancelada">
                <i class="fas fa-times-circle"></i> Canceladas
            </button>
        </div>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchTickets" placeholder="Buscar por evento...">
        </div>
    </div>

    <?php if (!empty($entradas)): ?>
        <div class="tickets-grid" id="ticketsContainer">
            <?php foreach ($entradas as $entrada): 
                // Determinar el estado y clase CSS
                $estado = strtolower($entrada['estado_entrada']);
                $estadoClass = $estado;
                $estadoIcon = '';
                
                switch($estado) {
                    case 'activa':
                        $estadoIcon = 'fa-check-circle';
                        break;
                    case 'usada':
                        $estadoIcon = 'fa-history';
                        break;
                    case 'cancelada':
                        $estadoIcon = 'fa-times-circle';
                        break;
                    default:
                        $estadoIcon = 'fa-ticket-alt';
                }
                
                // Calcular días hasta el evento
                $fechaEvento = new DateTime($entrada['fechaInicio']);
                $hoy = new DateTime();
                $diasRestantes = $hoy->diff($fechaEvento)->days;
                $esProximo = $diasRestantes <= 7 && $fechaEvento > $hoy;
            ?>
            
            <div class="ticket-card-modern" data-estado="<?php echo $estado; ?>" data-evento="<?php echo strtolower(htmlspecialchars($entrada['evento_nombre'])); ?>">
                <!-- Header con gradiente -->
                <div class="ticket-header-gradient">
                    <div class="ticket-top-info">
                        <span class="ticket-type"><?php echo htmlspecialchars($entrada['tipo_entrada_nombre']); ?></span>
                        <span class="ticket-status <?php echo $estadoClass; ?>">
                            <i class="fas <?php echo $estadoIcon; ?>"></i>
                            <?php echo htmlspecialchars($entrada['estado_entrada']); ?>
                        </span>
                    </div>
                    
                    <?php if ($esProximo && $estado === 'activa'): ?>
                    <div class="urgent-badge">
                        <i class="fas fa-clock"></i>
                        ¡Próximamente!
                    </div>
                    <?php endif; ?>
                    
                    <div class="ticket-event-info">
                        <h3><?php echo htmlspecialchars($entrada['evento_nombre']); ?></h3>
                        <div class="ticket-meta-row">
                            <span>
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo format_date($entrada['fechaInicio']); ?>
                            </span>
                            <span>
                                <i class="fas fa-clock"></i>
                                <?php echo date('H:i', strtotime($entrada['fechaInicio'])); ?>h
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cuerpo de la tarjeta -->
                <div class="ticket-body">
                    <div class="ticket-details-grid">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <label>Ubicación</label>
                                <span><?php echo htmlspecialchars($entrada['ubicacion']); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($entrada['zona_nombre'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-layer-group"></i>
                            <div>
                                <label>Zona</label>
                                <span><?php echo htmlspecialchars($entrada['zona_nombre']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($entrada['asiento'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-chair"></i>
                            <div>
                                <label>Asiento</label>
                                <span><?php echo htmlspecialchars($entrada['asiento']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-item">
                            <i class="fas fa-hashtag"></i>
                            <div>
                                <label>ID Entrada</label>
                                <span>#<?php echo htmlspecialchars($entrada['idEntrada']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Código QR -->
                    <div class="ticket-qr-section">
                        <div class="qr-container">
                            <div class="qr-code" id="qr-<?php echo $entrada['idEntrada']; ?>"></div>
                            <p class="qr-code-text"><?php echo htmlspecialchars($entrada['codigoQR']); ?></p>
                        </div>
                        <div class="qr-info">
                            <i class="fas fa-info-circle"></i>
                            <p>Presenta este código QR en la entrada del evento</p>
                        </div>
                    </div>
                </div>

                <!-- Footer con acciones -->
                <div class="ticket-footer">
                    <button class="btn-ticket-action btn-primary-ticket" onclick="descargarEntrada(<?php echo $entrada['idEntrada']; ?>)">
                        <i class="fas fa-download"></i>
                        Descargar PDF
                    </button>
                    <button class="btn-ticket-action btn-secondary-ticket" onclick="verDetallesEntrada(<?php echo $entrada['idEntrada']; ?>)">
                        <i class="fas fa-eye"></i>
                        Ver Detalles
                    </button>
                    <button class="btn-ticket-action btn-share-ticket" onclick="compartirEntrada(<?php echo $entrada['idEntrada']; ?>, '<?php echo htmlspecialchars($entrada['evento_nombre']); ?>')">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            </div>
            
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3>No tienes entradas aún</h3>
            <p>Explora eventos increíbles y compra tus primeras entradas</p>
            <a href="search-events.php" class="btn-primary-empty">
                <i class="fas fa-search"></i>
                Buscar eventos
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de detalles de entrada -->
<div id="ticketDetailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content-ticket">
        <button class="modal-close" onclick="cerrarModalEntrada()">
            <i class="fas fa-times"></i>
        </button>
        <div id="ticketDetailContent">
            <!-- El contenido se cargará dinámicamente -->
        </div>
    </div>
</div>

            <!-- Tab Favoritos / Wishlist -->
            <div class="tab-content <?php echo $active_tab === 'favoritos' ? 'active' : ''; ?>" id="favoritos">
                <div class="wishlist-header">
                    <div>
                        <h1>Mi Wishlist</h1>
                        <p class="subtitle">Eventos que has guardado para más tarde</p>
                    </div>
                    <div class="wishlist-stats">
                        <span id="totalFavoritos">0</span> eventos guardados
                    </div>
                </div>
                <div id="favoritosContainer" class="loading-container">
                </div>

            </div>

           <!-- Tab Pagos - Versión Mejorada -->
<div class="tab-content <?php echo $active_tab === 'payments' ? 'active' : ''; ?>" id="payments">
    
    <!-- Header de la sección -->
    <div class="payments-header">
        <div>
            <h2>Métodos de Pago y Facturas</h2>
            <p class="subtitle">Gestiona tus métodos de pago y consulta tu historial de compras</p>
        </div>
    </div>

    <div class="payments-section">
        <!-- Métodos de Pago -->
        <div class="payment-methods">
            <div class="section-header">
                <h3>Métodos de pago guardados</h3>
                <button class="btn-add" onclick="abrirModalAgregarTarjeta()">
                    <i class="fas fa-plus"></i> Añadir método
                </button>
            </div>
            
            <div class="payment-cards" id="paymentCardsContainer">
                <!-- Las tarjetas se cargarán dinámicamente -->
                <div class="payment-card-skeleton">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Cargando métodos de pago...</span>
                </div>
            </div>
        </div>

        <!-- Historial de Compras -->
        <div class="billing-history">
            <div class="section-header">
                <h3>Historial de compras</h3>
                <div class="billing-filters">
                    <select id="filterYear" class="filter-select">
                        <option value="all">Todos los años</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                    <select id="filterStatus" class="filter-select">
                        <option value="all">Todos los estados</option>
                        <option value="completada">Completada</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>

            <?php if (!empty($compras)): ?>
            <div class="billing-table-wrapper">
                <table class="billing-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="purchasesTableBody">
                        <?php foreach ($compras as $compra): ?>
                        <tr data-purchase-id="<?php echo $compra['idCompra']; ?>" 
                            data-year="<?php echo date('Y', strtotime($compra['fechaCompra'])); ?>"
                            data-status="<?php echo strtolower($compra['estado_compra']); ?>">
                            <td>
                                <div class="purchase-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo date('d/m/Y', strtotime($compra['fechaCompra'])); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="purchase-description">
                                    <strong>Compra #<?php echo $compra['idCompra']; ?></strong>
                                    <span class="purchase-items"><?php echo $compra['num_entradas']; ?> entrada(s)</span>
                                </div>
                            </td>
                            <td>
                                <div class="payment-method-badge">
                                    <i class="fas fa-<?php 
                                        echo $compra['metodo_pago'] === 'Tarjeta de crédito' ? 'credit-card' : 
                                             ($compra['metodo_pago'] === 'PayPal' ? 'cc-paypal' : 'money-bill-wave');
                                    ?>"></i>
                                    <span><?php echo htmlspecialchars($compra['metodo_pago']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $compra['estado_compra'])); ?>">
                                    <i class="fas fa-<?php 
                                        echo $compra['estado_compra'] === 'Completada' ? 'check-circle' : 
                                             ($compra['estado_compra'] === 'Pendiente' ? 'clock' : 'times-circle');
                                    ?>"></i>
                                    <?php echo htmlspecialchars($compra['estado_compra']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="amount"><?php echo format_price($compra['total']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons-table">
                                    <button class="btn-icon-table" 
                                            onclick="verDetallesCompra(<?php echo $compra['idCompra']; ?>)"
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon-table" 
                                            onclick="descargarFactura(<?php echo $compra['idCompra']; ?>)"
                                            title="Descargar factura">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Resumen de gastos -->
            <div class="billing-summary">
                <div class="summary-card">
                    <i class="fas fa-shopping-cart"></i>
                    <div>
                        <span class="summary-label">Total de compras</span>
                        <span class="summary-value"><?php echo count($compras); ?></span>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="fas fa-euro-sign"></i>
                    <div>
                        <span class="summary-label">Total gastado</span>
                        <span class="summary-value">
                            <?php echo format_price(array_sum(array_column($compras, 'total'))); ?>
                        </span>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="fas fa-ticket-alt"></i>
                    <div>
                        <span class="summary-label">Total entradas</span>
                        <span class="summary-value">
                            <?php echo array_sum(array_column($compras, 'num_entradas')); ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3>No tienes compras registradas</h3>
                <p>Tu historial de compras aparecerá aquí una vez realices tu primera compra</p>
                <a href="search-events.php" class="btn-primary-empty">
                    <i class="fas fa-search"></i>
                    Explorar eventos
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Agregar Método de Pago -->
<div id="modalAgregarTarjeta" class="modal-overlay" style="display: none;">
    <div class="modal-content-payment">
        <button class="modal-close" onclick="cerrarModalAgregarTarjeta()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="modal-header-payment">
            <i class="fas fa-credit-card"></i>
            <h2>Añadir método de pago</h2>
            <p>Añade una tarjeta de crédito o débito de forma segura</p>
        </div>
        
        <form id="formAgregarTarjeta" class="payment-form">
            <div class="form-row">
                <div class="form-group-payment full-width">
                    <label for="cardNumber">Número de tarjeta</label>
                    <div class="input-with-icon">
                        <input type="text" 
                               id="cardNumber" 
                               placeholder="1234 5678 9012 3456"
                               maxlength="19"
                               required>
                        <i class="fas fa-credit-card"></i>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group-payment">
                    <label for="cardName">Nombre en la tarjeta</label>
                    <input type="text" 
                           id="cardName" 
                           placeholder="JUAN PÉREZ"
                           required>
                </div>
            </div>
            
            <div class="form-row two-cols">
                <div class="form-group-payment">
                    <label for="cardExpiry">Fecha de expiración</label>
                    <input type="text" 
                           id="cardExpiry" 
                           placeholder="MM/AA"
                           maxlength="5"
                           required>
                </div>
                <div class="form-group-payment">
                    <label for="cardCVV">CVV</label>
                    <input type="text" 
                           id="cardCVV" 
                           placeholder="123"
                           maxlength="3"
                           required>
                </div>
            </div>
            
            <div class="form-group-payment checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="setAsDefault">
                    <span>Establecer como método predeterminado</span>
                </label>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="cerrarModalAgregarTarjeta()">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar tarjeta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalles de Compra -->
<div id="modalDetallesCompra" class="modal-overlay" style="display: none;">
    <div class="modal-content-payment">
        <button class="modal-close" onclick="cerrarModalDetalles()">
            <i class="fas fa-times"></i>
        </button>
        <div id="detallesCompraContent">
            <!-- El contenido se cargará dinámicamente -->
        </div>
    </div>
</div>

            <!-- Mi Perfil -->
            <div class="tab-content <?php echo $active_tab === 'profile' ? 'active' : ''; ?>" id="profile">
                <?php if (!empty($mensaje_perfil)): ?>
                <div class="alert alert-success" id="alertPerfil">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $mensaje_perfil; ?>
                </div>
                <?php endif; ?>

                <div class="content-grid">
                    <!-- Tarjeta de perfil -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h2>Información Personal</h2>
                            <button class="btn-edit" onclick="toggleEditMode()">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="profile-header">
                                <div class="avatar-large">
                                    <i class="fas fa-user"></i>
                                    <button class="avatar-upload">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div class="profile-main">
                                    <h3><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h3>
                                    <p class="user-since">Miembro desde <?php echo date('F Y', strtotime($usuario['fechaRegistro'])); ?></p>
                                    <div class="verification-badges">
                                        <?php if (!empty($usuario['telefono'])): ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST" action="" class="profile-form" id="profileForm">
                                <input type="hidden" name="actualizar_perfil" value="1">
                                
                                <div class="profile-details">
                                    <div class="detail-group">
                                        <label>Nombre completo</label>
                                        <div class="detail-display">
                                            <p><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></p>
                                        </div>
                                        <div class="detail-edit" style="display:none;">
                                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                            <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <label>Email</label>
                                        <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                                    </div>
                                    
                                    <div class="detail-group">
                                        <label>Teléfono</label>
                                        <div class="detail-display">
                                            <p><?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?></p>
                                        </div>
                                        <div class="detail-edit" style="display:none;">
                                            <input type="tel" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="detail-group">
                                        <label>Fecha de nacimiento</label>
                                        <p><?php echo !empty($usuario['fechaNacimiento']) ? date('d/m/Y', strtotime($usuario['fechaNacimiento'])) : 'No especificada'; ?></p>
                                    </div>
                                </div>
                                
                                <div class="edit-actions" style="display:none;">
                                    <button type="submit" class="btn-primary">Guardar cambios</button>
                                    <button type="button" class="btn-secondary" onclick="toggleEditMode()">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="stats-card">
                        <h3>Tu actividad</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-value"><?php echo count($entradas); ?></span>
                                    <span class="stat-label">Entradas compradas</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-value"><?php echo count($compras); ?></span>
                                    <span class="stat-label">Compras realizadas</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-euro-sign"></i>
                                </div>
                                <div class="stat-content">
                                    <span class="stat-value">
                                        <?php 
                                        $total = array_sum(array_column($compras, 'total'));
                                        echo format_price($total);
                                        ?>
                                    </span>
                                    <span class="stat-label">Total gastado</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actividad reciente -->
                    <div class="activity-card">
                        <div class="card-header">
                            <h3>Actividad Reciente</h3>
                        </div>
                        <div class="card-body">
                            <div class="activity-list">
                                <?php foreach (array_slice($compras, 0, 3) as $compra): ?>
                                <div class="activity-item">
                                    <div class="activity-icon purchase">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p><strong>Compra realizada</strong></p>
                                        <p class="activity-desc"><?php echo $compra['num_entradas']; ?> entradas</p>
                                        <span class="activity-time"><?php echo time_ago($compra['fechaCompra']); ?></span>
                                    </div>
                                    <div class="activity-amount"><?php echo format_price($compra['total']); ?></div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($compras)): ?>
                                <p class="text-muted">No hay actividad reciente</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Seguridad -->
            <div class="tab-content <?php echo $active_tab === 'security' ? 'active' : ''; ?>" id="security">
                <div class="security-section">
                    <div class="security-card">
                        <div class="card-header">
                            <div>
                                <h3>Contraseña</h3>
                                <p>Última actualización hace 3 meses</p>
                            </div>
                            <button id="btnMostrarPassword" class="btn btn-secondary" type="button">
                                Cambiar contraseña
                            </button>
                        </div>

                        <!-- Mensaje global -->
                        <?php if (!empty($mensaje_password) && $mensaje_tipo === 'success'): ?>
                            <div class="alert alert-success" id="successMessage">
                                <i class="fas fa-check-circle"></i> <?php echo $mensaje_password; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario oculto -->
                        <section id="formPassword" class="mt-3" style="display:<?php echo (!empty($mensaje_password) && $mensaje_tipo === 'error') ? 'block' : 'none'; ?>;">
                            <h3 style="margin-left: 10px;">Cambiar contraseña</h3>

                            <?php if (!empty($mensaje_password) && $mensaje_tipo === 'error'): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $mensaje_password; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="passwordForm">
                                <input type="hidden" name="cambiar_password" value="1">
                                
                                <div class="form-group">
                                    <label for="password_actual">Contraseña actual</label>
                                    <div class="password-wrapper">
                                        <input type="password" name="password_actual" id="password_actual" required>
                                        <button type="button" class="toggle-password" data-target="password_actual">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password_nueva">Nueva contraseña</label>
                                    <div class="password-wrapper">
                                        <input type="password" name="password_nueva" id="password_nueva" required>
                                        <button type="button" class="toggle-password" data-target="password_nueva">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div id="strength-bar"></div>
                                        <p id="strength-text" class="strength-text">Introduce una contraseña</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmar">Confirmar nueva contraseña</label>
                                    <div class="password-wrapper">
                                        <input type="password" name="password_confirmar" id="password_confirmar" required>
                                        <button type="button" class="toggle-password" data-target="password_confirmar">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-2">
                                    <button type="submit" class="btn btn-primary">
                                        Actualizar contraseña
                                    </button>
                                    <button type="button" id="btnCerrarPassword" class="btn btn-outline-secondary">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- Autenticación de dos factores -->
                    <div class="security-card">
                        <div class="card-header">
                            <div>
                                <h3>Autenticación de dos factores</h3>
                                <p>Añade una capa extra de seguridad a tu cuenta</p>
                            </div>
                            <button class="btn-secondary">Configurar</button>
                        </div>
                        <div class="card-body">
                            <div class="tfa-options">
                                <div class="tfa-option">
                                    <i class="fas fa-mobile-alt"></i>
                                    <div>
                                        <strong>SMS</strong>
                                        <p>Recibe un código en tu teléfono móvil</p>
                                    </div>
                                    <span class="status-badge">Inactivo</span>
                                </div>
                                <div class="tfa-option">
                                    <i class="fas fa-shield-alt"></i>
                                    <div>
                                        <strong>App de autenticación</strong>
                                        <p>Usa Google Authenticator o similar</p>
                                    </div>
                                    <span class="status-badge">Inactivo</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sesiones activas -->
                    <div class="security-card">
                        <div class="card-header">
                            <div>
                                <h3>Sesiones activas</h3>
                                <p>Dispositivos donde has iniciado sesión</p>
                            </div>
                            <button class="btn-danger">Cerrar todas</button>
                        </div>
                        <div class="card-body">
                            <div class="sessions-list">
                                <div class="session-item current">
                                    <div class="session-icon">
                                        <i class="fas fa-laptop"></i>
                                    </div>
                                    <div class="session-info">
                                        <strong>Chrome en Windows</strong>
                                        <p>Madrid, España • <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
                                        <span class="session-time">Sesión actual</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seguridad de la cuenta -->
                    <div class="security-card">
                        <div class="card-header">
                            <div>
                                <h3>Seguridad de la cuenta</h3>
                                <p>Registro de actividad sospechosa</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="security-log">
                                <div class="log-empty">
                                    <i class="fas fa-check-circle"></i>
                                    <p>No se ha detectado actividad sospechosa</p>
                                    <span>Tu cuenta está segura</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Preferencias -->
            <div class="tab-content <?php echo $active_tab === 'preferences' ? 'active' : ''; ?>" id="preferences">
                <div class="preferences-section">
                    <!-- Notificaciones -->
                    <div class="pref-card">
                        <h3>Notificaciones por email</h3>
                        <div class="pref-list">
                            <div class="pref-item">
                                <div class="pref-info">
                                    <strong>Confirmaciones de compra</strong>
                                    <p>Recibe confirmación cuando compres entradas</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="pref-item">
                                <div class="pref-info">
                                    <strong>Recordatorios de eventos</strong>
                                    <p>Te avisamos 24h antes del evento</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="pref-item">
                                <div class="pref-info">
                                    <strong>Ofertas y promociones</strong>
                                    <p>Entérate de descuentos y eventos especiales</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="pref-item">
                                <div class="pref-info">
                                    <strong>Newsletter mensual</strong>
                                    <p>Los mejores eventos del mes en tu ciudad</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preferencias de eventos -->
                    <div class="pref-card">
                        <h3>Preferencias de eventos</h3>
                        <div class="categories-grid">
                            <label class="category-checkbox">
                                <input type="checkbox" checked>
                                <span class="category-box">
                                    <i class="fas fa-music"></i>
                                    Música
                                </span>
                            </label>
                            <label class="category-checkbox">
                                <input type="checkbox" checked>
                                <span class="category-box">
                                    <i class="fas fa-theater-masks"></i>
                                    Teatro
                                </span>
                            </label>
                            <label class="category-checkbox">
                                <input type="checkbox">
                                <span class="category-box">
                                    <i class="fas fa-futbol"></i>
                                    Deportes
                                </span>
                            </label>
                            <label class="category-checkbox">
                                <input type="checkbox" checked>
                                <span class="category-box">
                                    <i class="fas fa-palette"></i>
                                    Arte
                                </span>
                            </label>
                            <label class="category-checkbox">
                                <input type="checkbox">
                                <span class="category-box">
                                    <i class="fas fa-graduation-cap"></i>
                                    Formación
                                </span>
                            </label>
                            <label class="category-checkbox">
                                <input type="checkbox" checked>
                                <span class="category-box">
                                    <i class="fas fa-glass-cheers"></i>
                                    Festivales
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Privacidad -->
                    <div class="pref-card">
                        <h3>Privacidad</h3>
                        <div class="pref-list">
                            <div class="pref-item">
                                <div class="pref-info">
                                    <strong>Perfil público</strong>
                                    <p>Permite que otros usuarios vean tu perfil</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="pref-item">
                                <div class="pref-info">
                                    <strong>Mostrar eventos asistidos</strong>
                                    <p>Otros pueden ver a qué eventos has ido</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Zona de peligro -->
                    <div class="pref-card danger-zone">
                        <h3>Zona de peligro</h3>
                        <div class="danger-actions">
                            <div class="danger-item">
                                <div>
                                    <strong>Desactivar cuenta</strong>
                                    <p>Tu cuenta se desactivará temporalmente</p>
                                </div>
                                <button class="btn-outline-danger" id="btnDesactivar">Desactivar</button>
                            </div>
                            <div class="danger-item">
                                <div>
                                    <strong>Eliminar cuenta</strong>
                                    <p>Esta acción no se puede deshacer</p>
                                </div>
                                <button class="btn-danger" id="btnEliminar">Eliminar cuenta</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

        <script>
    // Todo dentro de una IIFE para no ensuciar el global,
    // excepto lo que necesitamos (showTab)
    (function () {
        // ============================================
        // GESTIÓN DE PESTAÑAS
        // ============================================
        function activarTab(tabId) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(function (tab) {
                tab.classList.remove('active');
            });

            // Desactivar todos los botones
            document.querySelectorAll('.tab-btn').forEach(function (btn) {
                btn.classList.remove('active');
            });

            // Mostrar pestaña seleccionada
            var targetTab = document.getElementById(tabId);
            if (targetTab) {
                targetTab.classList.add('active');
            }
        
         // Activar botón correspondiente
            var btn = document.querySelector('.tab-btn[href="#' + tabId + '"]');
            if (btn) {
                btn.classList.add('active');
            }

            // Actualizar URL sin recargar
            if (history && history.replaceState) {
                history.replaceState(null, '', '#' + tabId);
            } else {
                window.location.hash = '#' + tabId;
            }
        }
 

        /// Esta función la usa el HTML en onclick="showTab(event, 'profile')"
window.showTab = function (event, tabId) {
    if (event && event.preventDefault) {
        event.preventDefault();
    }

    activarTab(tabId);

    // Si el usuario abre la pestaña Favoritos, cargamos (solo una vez)
    if (tabId === 'favoritos' && !favoritosCargados) {
        console.log('🎯 Cargando favoritos desde showTab...');
        cargarFavoritos();
        favoritosCargados = true;
    }
};


        // Activar pestaña según el hash al cargar (por si venimos de #tickets, etc.)
       document.addEventListener('DOMContentLoaded', function () {
    var hash = window.location.hash ? window.location.hash.substring(1) : '';

    // Si la URL viene con #algo, activar esa pestaña
    if (hash) {
        activarTab(hash);

        if (hash === 'favoritos' && !favoritosCargados) {
            console.log('🎯 Cargando favoritos desde DOMContentLoaded...');
            cargarFavoritos();
            favoritosCargados = true;
        }
    } else {
        // Si no hay hash, por defecto perfil
        activarTab('profile');
    }


            // ============================================
            // MOSTRAR / OCULTAR FORMULARIO DE CAMBIO DE CONTRASEÑA
            // ============================================
            var btnMostrarPassword = document.getElementById('btnMostrarPassword');
            var btnCerrarPassword  = document.getElementById('btnCerrarPassword');
            var formPassword       = document.getElementById('formPassword');

            if (btnMostrarPassword && formPassword) {
                btnMostrarPassword.addEventListener('click', function () {
                    formPassword.style.display = 'block';
                    formPassword.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            }

            if (btnCerrarPassword && formPassword) {
                btnCerrarPassword.addEventListener('click', function () {
                    formPassword.style.display = 'none';

                    // Limpiar campos si existen
                    var pa = document.getElementById('password_actual');
                    var pn = document.getElementById('password_nueva');
                    var pc = document.getElementById('password_confirmar');
                    if (pa) pa.value = '';
                    if (pn) pn.value = '';
                    if (pc) pc.value = '';

                    // Resetear barra de fuerza si existe
                    var strengthBar  = document.getElementById('strength-bar');
                    var strengthText = document.getElementById('strength-text');
                    if (strengthBar) {
                        strengthBar.style.width = '0%';
                        strengthBar.style.background = '#ccc';
                    }
                    if (strengthText) {
                        strengthText.textContent = 'Introduce una contraseña';
                        strengthText.style.color = '#6C757D';
                    }
                });
            }

            // ============================================
            // MOSTRAR / OCULTAR CONTRASEÑAS
            // ============================================
            document.querySelectorAll('.toggle-password').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var targetId = btn.getAttribute('data-target');
                    var input = document.getElementById(targetId);
                    if (!input) return;

                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    } else {
                        input.type = 'password';
                        btn.innerHTML = '<i class="fas fa-eye"></i>';
                    }
                });
            });

            // ============================================
            // MEDIDOR DE FUERZA DE CONTRASEÑA
            // ============================================
            var passwordInput = document.getElementById('password_nueva');
            var strengthBar   = document.getElementById('strength-bar');
            var strengthText  = document.getElementById('strength-text');
            var passwordStrengthValue = 0;

            if (passwordInput && strengthBar && strengthText) {
                passwordInput.addEventListener('input', function () {
                    var val = passwordInput.value;
                    var strength = 0;

                    if (val.length >= 8) strength += 1;
                    if (/[a-z]+/.test(val)) strength += 1;
                    if (/[A-Z]+/.test(val)) strength += 1;
                    if (/[0-9]+/.test(val)) strength += 1;
                    if (/[$@#&!%*?]/.test(val)) strength += 1;

                    passwordStrengthValue = strength;

                    var width  = (strength / 5) * 100;
                    var color  = '#dc3545';
                    var text   = 'Muy débil';

                    if (strength === 2) {
                        color = '#ffc107';
                        text  = 'Débil';
                    } else if (strength === 3) {
                        color = '#17a2b8';
                        text  = 'Aceptable';
                    } else if (strength === 4) {
                        color = '#28a745';
                        text  = 'Fuerte';
                    } else if (strength === 5) {
                        color = '#1b5e20';
                        text  = 'Muy fuerte';
                    }

                    strengthBar.style.width = width + '%';
                    strengthBar.style.background = color;
                    strengthText.textContent = text;
                    strengthText.style.color = color;
                });
            }

            // ============================================
            // EDICIÓN DE PERFIL
            // ============================================
            window.toggleEditMode = function () {
                var displays = document.querySelectorAll('.detail-display');
                var edits    = document.querySelectorAll('.detail-edit');
                var actions  = document.querySelector('.edit-actions');

                displays.forEach(function (d) {
                    d.style.display = (d.style.display === 'none') ? 'block' : 'none';
                });
                edits.forEach(function (e) {
                    e.style.display = (e.style.display === 'none') ? 'block' : 'none';
                });
                if (actions) {
                    actions.style.display = (actions.style.display === 'none') ? 'flex' : 'none';
                }
            };

            // ============================================
            // DESVANECER MENSAJES DE ALERTA
            // ============================================
            var alertPerfil   = document.getElementById('alertPerfil');
            var alertPassword = document.getElementById('alertPassword');

            if (alertPerfil) {
                setTimeout(function () {
                    alertPerfil.classList.add('fade-out');
                    setTimeout(function () {
                        alertPerfil.style.display = 'none';
                    }, 500);
                }, 3000);
            }

            if (alertPassword) {
                setTimeout(function () {
                    alertPassword.classList.add('fade-out');
                    setTimeout(function () {
                        alertPassword.style.display = 'none';
                    }, 500);
                }, 3000);
            }
        });
    })();
 // ============================================
// GESTIÓN DE FAVORITOS
// ============================================
let favoritosCargados = false;

function cargarFavoritos() {
    const container = document.getElementById('favoritosContainer');
    console.log('🔍 Iniciando carga de favoritos...');
    
    container.innerHTML = `
        <div class="spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando favoritos...</p>
        </div>
    `;
    
    fetch('../api/favoritos.php?accion=listar')
        .then(response => {
            console.log('📥 Respuesta recibida:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Datos parseados:', data);
            
            if (data.ok && data.favoritos && data.favoritos.length > 0) {
                console.log(`📊 Total favoritos: ${data.favoritos.length}`);
                document.getElementById('totalFavoritos').textContent = data.favoritos.length;
                mostrarFavoritos(data.favoritos);
            } else {
                console.log('ℹ️ No hay favoritos');
                document.getElementById('totalFavoritos').textContent = '0';
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-heart-broken"></i>
                        <h3>No tienes eventos favoritos</h3>
                        <p>Explora eventos increíbles y guárdalos aquí para encontrarlos fácilmente</p>
                        <a href="search-events.php" class="btn-primary">
                            <i class="fas fa-search"></i> Explorar eventos
                        </a>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            container.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Error al cargar favoritos</h3>
                    <p>${error.message}</p>
                    <button class="btn-secondary" onclick="favoritosCargados=false; cargarFavoritos()">
                        <i class="fas fa-redo"></i> Reintentar
                    </button>
                </div>
            `;
        });
}

function mostrarFavoritos(favoritos) {
    const container = document.getElementById('favoritosContainer');
    
    let html = '<div class="events-grid-wishlist">';
    
    favoritos.forEach(evento => {
        const fechaEvento = new Date(evento.fechaInicio).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const fechaAgregado = new Date(evento.fechaAgregado).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
        
        // Calcular días hasta el evento
        const hoy = new Date();
        const fechaEv = new Date(evento.fechaInicio);
        const diasHasta = Math.ceil((fechaEv - hoy) / (1000 * 60 * 60 * 24));
        
        // 🔴 MANEJO CORRECTO DE IMÁGENES
        const imagenDefault = '<?php echo BASE_URL; ?>/api/admin/events/uploads/0b10db93db401e3d.jpg';
        let imagenUrl = imagenDefault;
        
        // Solo intentar cargar imagen si existe y no es default
        if (evento.imagenPrincipal && 
            evento.imagenPrincipal !== 'default.jpg' && 
            evento.imagenPrincipal !== 'imagen/default.jpg' &&
            !evento.imagenPrincipal.includes('placeholder')) {
            
            const cleanPath = evento.imagenPrincipal.replace('uploads/', '');
            imagenUrl = '<?php echo BASE_URL; ?>/api/admin/events/uploads/' + cleanPath;
        }
        
        html += `
            <div class="event-card-wishlist" data-evento-id="${evento.idEvento}">
                <div class="event-image-wishlist">
                    <img src="${imagenUrl}" 
                         alt="${escapeHtml(evento.nombre)}" 
                         onerror="this.src='${imagenDefault}'">
                    
                    ${diasHasta > 0 && diasHasta <= 7 ? `
                        <span class="event-badge urgent">
                            <i class="fas fa-clock"></i> ¡Pronto!
                        </span>
                    ` : ''}
                    
                    <span class="event-category-badge">${escapeHtml(evento.tipo)}</span>
                    
                    <button class="btn-remove-wishlist" onclick="eliminarFavorito(${evento.idEvento})" 
                            title="Eliminar de favoritos">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
                
                <div class="event-content-wishlist">
                    <div class="event-header-wish">
                        <h3>${escapeHtml(evento.nombre)}</h3>
                        <span class="fecha-agregado" title="Agregado el ${fechaAgregado}">
                            <i class="fas fa-bookmark"></i> ${fechaAgregado}
                        </span>
                    </div>
                    
                    <p class="event-description">${evento.descripcion ? escapeHtml(evento.descripcion.substring(0, 100)) + '...' : 'Sin descripción'}</p>
                    
                    <div class="event-meta-wishlist">
                        <div class="meta-item-wish">
                            <i class="fas fa-calendar-alt"></i>
                            <span>${fechaEvento}</span>
                        </div>
                        <div class="meta-item-wish">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${escapeHtml(evento.ubicacion)}</span>
                        </div>
                        <div class="meta-item-wish ${evento.entradasDisponibles < 50 ? 'low-stock' : ''}">
                            <i class="fas fa-ticket-alt"></i>
                            <span>${evento.entradasDisponibles} disponibles</span>
                        </div>
                    </div>
                    
                    <div class="event-footer-wishlist">
                        <div class="price-wishlist">
                            <span class="price-label">Desde</span>
                            <span class="price-value">${formatPrice(evento.precio_desde || 0)}</span>
                        </div>
                        <div class="action-buttons">
                            <a href="event-detail.php?id=${evento.idEvento}" class="btn-primary btn-sm">
                                <i class="fas fa-eye"></i> Ver detalles
                            </a>
                            <button class="btn-secondary btn-sm" onclick="compartirEvento(${evento.idEvento}, '${escapeHtml(evento.nombre).replace(/'/g, "\\'")}')">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function eliminarFavorito(idEvento) {
    if (!confirm('¿Eliminar este evento de tus favoritos?')) {
        return;
    }
    
    const card = document.querySelector(`[data-evento-id="${idEvento}"]`);
    if (card) {
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';
    }
    
    const formData = new FormData();
    formData.append('idEvento', idEvento);
    
    fetch('../api/favoritos.php?accion=eliminar', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Animación de eliminación
            if (card) {
                card.style.transform = 'scale(0.8)';
                card.style.opacity = '0';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Actualizar contador
                    const totalElement = document.getElementById('totalFavoritos');
                    const currentTotal = parseInt(totalElement.textContent);
                    totalElement.textContent = currentTotal - 1;
                    
                    // Si no quedan más eventos, mostrar empty state
                    const container = document.getElementById('favoritosContainer');
                    if (!container.querySelector('.event-card-wishlist')) {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-heart-broken"></i>
                                <h3>No tienes eventos favoritos</h3>
                                <p>Explora eventos increíbles y guárdalos aquí</p>
                                <a href="search-events.php" class="btn-primary">
                                    <i class="fas fa-search"></i> Explorar eventos
                                </a>
                            </div>
                        `;
                    }
                }, 300);
            }
            
            mostrarNotificacion('Evento eliminado de favoritos', 'success');
        } else {
            if (card) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
            mostrarNotificacion('Error al eliminar: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (card) {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        }
        mostrarNotificacion('Error al eliminar favorito', 'error');
    });
}

function compartirEvento(idEvento, nombre) {
    const url = window.location.origin + '/workflowly/views/event-detail.php?id=' + idEvento;
    
    if (navigator.share) {
        navigator.share({
            title: nombre,
            text: '¡Mira este evento increíble!',
            url: url
        }).catch(err => console.log('Error al compartir:', err));
    } else {
        // Fallback: copiar al portapapeles
        navigator.clipboard.writeText(url).then(() => {
            mostrarNotificacion('Enlace copiado al portapapeles', 'success');
        }).catch(() => {
            prompt('Copia este enlace:', url);
        });
    }
}

function mostrarNotificacion(mensaje, tipo) {
    const notif = document.createElement('div');
    notif.className = `notification notification-${tipo}`;
    notif.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${mensaje}</span>
    `;
    notif.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${tipo === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.opacity = '1';
        notif.style.transform = 'translateY(0)';
    }, 100);
    
    setTimeout(() => {
        notif.style.opacity = '0';
        notif.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            notif.remove();
        }, 300);
    }, 3000);
}

function formatPrice(price) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

// Función helper para escapar HTML y prevenir XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}



    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
// Generar códigos QR
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($entradas as $entrada): ?>
    new QRCode(document.getElementById("qr-<?php echo $entrada['idEntrada']; ?>"), {
        text: "<?php echo htmlspecialchars($entrada['codigoQR']); ?>",
        width: 120,
        height: 120,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    <?php endforeach; ?>
});

// Filtros de entradas
document.querySelectorAll('.filter-chip').forEach(btn => {
    btn.addEventListener('click', function() {
        // Activar filtro
        document.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.getAttribute('data-filter');
        const tickets = document.querySelectorAll('.ticket-card-modern');
        
        tickets.forEach(ticket => {
            if (filter === 'all') {
                ticket.style.display = 'block';
            } else {
                const estado = ticket.getAttribute('data-estado');
                ticket.style.display = estado === filter ? 'block' : 'none';
            }
        });
    });
});

// Búsqueda de entradas
document.getElementById('searchTickets').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const tickets = document.querySelectorAll('.ticket-card-modern');
    
    tickets.forEach(ticket => {
        const eventoName = ticket.getAttribute('data-evento');
        ticket.style.display = eventoName.includes(searchTerm) ? 'block' : 'none';
    });
});

// Descargar entrada
function descargarEntrada(idEntrada) {
    // Aquí implementarías la descarga real del PDF
    mostrarNotificacion('Descargando entrada...', 'info');
    
    // Simulación de descarga
    setTimeout(() => {
        mostrarNotificacion('Entrada descargada correctamente', 'success');
        
        // En producción, harías algo como:
        // window.location.href = '../api/descargar-entrada.php?id=' + idEntrada;
    }, 1000);
}

// Ver detalles de entrada
function verDetallesEntrada(idEntrada) {
    // Aquí cargarías los detalles completos vía AJAX
    const modal = document.getElementById('ticketDetailModal');
    const content = document.getElementById('ticketDetailContent');
    
    content.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando detalles...</p>
        </div>
    `;
    
    modal.style.display = 'flex';
    
    // Simular carga de datos
    setTimeout(() => {
        content.innerHTML = `
            <h2>Detalles de la entrada</h2>
            <p>Contenido de ejemplo para la entrada #${idEntrada}</p>
            <button class="btn-primary" onclick="cerrarModalEntrada()">Cerrar</button>
        `;
    }, 500);
}

function cerrarModalEntrada() {
    document.getElementById('ticketDetailModal').style.display = 'none';
}

// Compartir entrada
function compartirEntrada(idEntrada, nombreEvento) {
    const url = window.location.origin + '/workflowly/views/event-detail.php?ticket=' + idEntrada;
    
    if (navigator.share) {
        navigator.share({
            title: 'Mi entrada: ' + nombreEvento,
            text: '¡Mira mi entrada para este evento!',
            url: url
        }).catch(err => console.log('Error al compartir:', err));
    } else {
        navigator.clipboard.writeText(url).then(() => {
            mostrarNotificacion('Enlace copiado al portapapeles', 'success');
        }).catch(() => {
            prompt('Copia este enlace:', url);
        });
    }
}

// Notificaciones
function mostrarNotificacion(mensaje, tipo) {
    const notif = document.createElement('div');
    notif.className = `notification notification-${tipo} show`;
    notif.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${mensaje}</span>
    `;
    
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.classList.remove('show');
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}


</script>
<script src="../assets/js/payments.js"></script>
</body>
</html>
