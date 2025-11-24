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
                <?php if (!empty($entradas)): ?>
                    <div class="tickets-grid">
                        <?php foreach ($entradas as $entrada): ?>
                            <div class="ticket-card">
                                <div class="ticket-header">
                                    <h3><?php echo htmlspecialchars($entrada['evento_nombre']); ?></h3>
                                    <span class="ticket-status <?php echo strtolower($entrada['estado_entrada']); ?>">
                                        <?php echo htmlspecialchars($entrada['estado_entrada']); ?>
                                    </span>
                                </div>
                                <div class="ticket-details">
                                    <p><i class="fas fa-calendar"></i> <?php echo format_date($entrada['fechaInicio']); ?></p>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($entrada['ubicacion']); ?></p>
                                    <p><i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($entrada['tipo_entrada_nombre']); ?></p>
                                    <?php if (!empty($entrada['zona_nombre'])): ?>
                                        <p><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($entrada['zona_nombre']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="ticket-qr">
                                    <div class="qr-code">
                                        <i class="fas fa-qrcode" style="font-size: 60px;"></i>
                                    </div>
                                    <p class="qr-code-text"><?php echo htmlspecialchars($entrada['codigoQR']); ?></p>
                                </div>
                                <button class="btn-secondary">
                                    <i class="fas fa-download"></i> Descargar entrada
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        
                        <h3>No tienes entradas aún</h3>
                        <p>Explora eventos y compra tus primeras entradas</p><br>
                        <a href="search-events.php" class="btn-primary">Buscar eventos</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Historial de Compras -->
            <div class="tab-content <?php echo $active_tab === 'payments' ? 'active' : ''; ?>" id="payments">
            
                
                <?php if (!empty($compras)): ?>
                    <div class="purchases-list">
                        <?php foreach ($compras as $compra): ?>
                            <div class="purchase-card">
                                <div class="purchase-header">
                                    <div>
                                        <h3>Compra #<?php echo $compra['idCompra']; ?></h3>
                                        <p><?php echo format_date($compra['fechaCompra']); ?></p>
                                    </div>
                                    <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $compra['estado_compra'])); ?>">
                                        <?php echo htmlspecialchars($compra['estado_compra']); ?>
                                    </span>
                                </div>
                                <div class="purchase-details">
                                    <p><strong>Total:</strong> <?php echo format_price($compra['total']); ?></p>
                                    <p><strong>Método de pago:</strong> <?php echo htmlspecialchars($compra['metodo_pago']); ?></p>
                                    <p><strong>Entradas:</strong> <?php echo $compra['num_entradas']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        
                        <h3>No has realizado compras</h3>
                        <p>Tu historial de compras aparecerá aquí</p>
                    </div>
                <?php endif; ?>
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
                                <button class="btn-outline-danger">Desactivar</button>
                            </div>
                            <div class="danger-item">
                                <div>
                                    <strong>Eliminar cuenta</strong>
                                    <p>Esta acción no se puede deshacer</p>
                                </div>
                                <button class="btn-danger">Eliminar cuenta</button>
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
    // ============================================
    // GESTIÓN DE PESTAÑAS
    // ============================================
    function showTab(event, tabId) {
        event.preventDefault();
        
        // Ocultar todas las pestañas
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Desactivar todos los botones
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Mostrar pestaña seleccionada
        const targetTab = document.getElementById(tabId);
        if (targetTab) {
            targetTab.classList.add('active');
        }
        
        // Activar botón seleccionado
        event.currentTarget.classList.add('active');
        
        // Actualizar URL sin recargar
        history.replaceState(null, null, '#' + tabId);
    }

    // ============================================
    // CAMBIO DE CONTRASEÑA
    // ============================================
    const btnMostrarPassword = document.getElementById('btnMostrarPassword');
    const btnCerrarPassword = document.getElementById('btnCerrarPassword');
    const formPassword = document.getElementById('formPassword');

    if (btnMostrarPassword) {
        btnMostrarPassword.addEventListener('click', () => {
            formPassword.style.display = 'block';
            formPassword.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

    if (btnCerrarPassword) {
        btnCerrarPassword.addEventListener('click', () => {
            formPassword.style.display = 'none';
            // Limpiar campos
            document.getElementById('password_actual').value = '';
            document.getElementById('password_nueva').value = '';
            document.getElementById('password_confirmar').value = '';
            // Resetear barra de fuerza
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            strengthBar.style.width = '0%';
            strengthBar.style.background = '#ccc';
            strengthText.textContent = 'Introduce una contraseña';
            strengthText.style.color = '#6C757D';
        });
    }

    // Mostrar/Ocultar contraseñas
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
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
    const passwordInput = document.getElementById('password_nueva');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    let passwordStrengthValue = 0;

    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let strength = 0;

            // Criterios de fuerza
            if (val.length >= 8) strength += 1;
            if (val.match(/[a-z]+/)) strength += 1;
            if (val.match(/[A-Z]+/)) strength += 1;
            if (val.match(/[0-9]+/)) strength += 1;
            if (val.match(/[$@#&!%*?]/)) strength += 1;

            passwordStrengthValue = strength;

            // Colores y mensajes
            const colors = ['#e74c3c', '#e74c3c', '#f39c12', '#f1c40f', '#2ecc71', '#27ae60'];
            const messages = ['Muy débil', 'Muy débil', 'Débil', 'Moderada', 'Fuerte', 'Muy fuerte'];
            
            strengthBar.style.width = (strength * 20) + '%';
            strengthBar.style.background = colors[strength] || '#ccc';
            strengthText.textContent = val.length > 0 ? messages[strength] || 'Introduce una contraseña' : 'Introduce una contraseña';
            strengthText.style.color = colors[strength] || '#6C757D';
        });
    }

    // Validar antes de enviar
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', (e) => {
            if (passwordStrengthValue < 3) {
                e.preventDefault();
                alert('La contraseña es demasiado débil. Debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas, números y símbolos.');
                passwordInput.focus();
            }
        });
    }

    // Auto-ocultar mensaje de éxito después de 5 segundos
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.opacity = '0';
            successMessage.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                successMessage.remove();
            }, 500);
        }, 5000);
    }

    // ============================================
    // EDICIÓN DE PERFIL
    // ============================================
    function toggleEditMode() {
        const displays = document.querySelectorAll('.detail-display');
        const edits = document.querySelectorAll('.detail-edit');
        const actions = document.querySelector('.edit-actions');
        
        displays.forEach(d => {
            d.style.display = d.style.display === 'none' ? 'block' : 'none';
        });
        edits.forEach(e => {
            e.style.display = e.style.display === 'none' ? 'block' : 'none';
        });
        if (actions) {
            actions.style.display = actions.style.display === 'none' ? 'flex' : 'none';
        }
    }
    // Desvanecer mensaje después de 3 segundos
window.addEventListener('DOMContentLoaded', () => {
    const alertPerfil = document.getElementById('alertPerfil');
    const alertPassword = document.getElementById('alertPassword');
    
    if (alertPerfil) {
        setTimeout(() => {
            alertPerfil.classList.add('fade-out');
            setTimeout(() => {
                alertPerfil.style.display = 'none';
            }, 500); // Esperar a que termine la transición
        }, 3000); // 3 segundos antes de empezar a desvanecer
    }
    
    if (alertPassword) {
        setTimeout(() => {
            alertPassword.classList.add('fade-out');
            setTimeout(() => {
                alertPassword.style.display = 'none';
            }, 500);
        }, 3000);
    }
    
    // Resto del código de tabs...
});
    </script>
</body>
</html>
