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
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <a href="../index.php" class="logo">
                    <div class="logo-circle"><span>W</span></div>
                    <span class="brand-name">WorkFlowly</span>
                </a>
            </div>
            <nav class="nav-menu">
                <a href="search-events.php">Eventos</a>
                <a href="../index.php">Inicio</a>
            </nav>
            <div class="nav-actions">
                <a href="../api/logout.php" class="btn-link">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <main class="account-main">
        <div class="container">
            <div class="account-layout">
                <!-- Sidebar -->
                <aside class="account-sidebar">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h3>
                        <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                        <span class="user-badge"><?php echo htmlspecialchars($usuario['tipoUsuario']); ?></span>
                    </div>

                    <nav class="account-nav">
                        <a href="#mis-entradas" class="active" onclick="showTab('mis-entradas')">
                            <i class="fas fa-ticket-alt"></i> Mis Entradas
                        </a>
                        <a href="#historial" onclick="showTab('historial')">
                            <i class="fas fa-history"></i> Historial de Compras
                        </a>
                        <a href="#perfil" onclick="showTab('perfil')">
                            <i class="fas fa-user"></i> Mi Perfil
                        </a>
                        <?php if (is_organizer()): ?>
                        <a href="#mis-eventos" onclick="showTab('mis-eventos')">
                            <i class="fas fa-calendar"></i> Mis Eventos
                        </a>
                        <?php endif; ?>
                    </nav>
                </aside>

                <!-- Main Content -->
                <div class="account-content">
                    <!-- Mis Entradas -->
                    <div class="tab-content active" id="mis-entradas">
                        <h1>Mis Entradas</h1>
                        
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
                                <i class="fas fa-ticket-alt"></i>
                                <h3>No tienes entradas aún</h3>
                                <p>Explora eventos y compra tus primeras entradas</p>
                                <a href="search-events.php" class="btn-primary">Buscar eventos</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Historial de Compras -->
                    <div class="tab-content" id="historial">
                        <h1>Historial de Compras</h1>
                        
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
                                <i class="fas fa-shopping-bag"></i>
                                <h3>No has realizado compras</h3>
                                <p>Tu historial de compras aparecerá aquí</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mi Perfil -->
                    <div class="tab-content" id="perfil">
                        <h1>Mi Perfil</h1>
                        
                        <?php if (!empty($mensaje_perfil)): ?>
                            <div class="alert alert-success"><?php echo $mensaje_perfil; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" class="profile-form">
                            <input type="hidden" name="actualizar_perfil" value="1">
                            
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Apellidos</label>
                                <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                                <small>El email no se puede cambiar</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="tel" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                            </div>
                            
                            <button type="submit" class="btn-primary">Guardar cambios</button>
                        </form>
                    </div>

                    <?php if (is_organizer()): ?>
                    <!-- Mis Eventos (solo organizadores) -->
                    <div class="tab-content" id="mis-eventos">
                        <h1>Mis Eventos</h1>
                        <p>Próximamente: Panel de gestión de eventos</p>
                        <a href="#" class="btn-primary">Crear nuevo evento</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabId) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Desactivar todos los links
            document.querySelectorAll('.account-nav a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Mostrar tab seleccionado
            document.getElementById(tabId).classList.add('active');
            
            // Activar link correspondiente
            event.target.closest('a').classList.add('active');
        }
    </script>
</body>
</html>
