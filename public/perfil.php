<?php
/**
 * WorkFlowly - Perfil Público de Usuario
 */

// Iniciar sesión
session_start();

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/UsuarioController.php';

// Obtener ID del usuario a mostrar
$idUsuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idUsuario <= 0) {
    header('Location: index.php');
    exit;
}

// Crear instancia del controlador
$usuarioController = new UsuarioController();

// Obtener datos del usuario
$usuario = $usuarioController->getUsuarioById($idUsuario);

if (!$usuario) {
    header('Location: index.php');
    exit;
}

// Si es organizador, obtener eventos
$eventos = [];
if ($usuario['tipoUsuario'] === 'Organizador') {
    $eventos = $usuarioController->getEventosOrganizador($idUsuario);
}

// Verificar si el usuario logueado es el mismo que el perfil
$esMiPerfil = isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] === $idUsuario;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?> - WorkFlowly</title>
    
    <link rel="stylesheet" href="assets/css/perfil.css">
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

    <!-- Profile Header -->
    <section class="profile-header">
        <div class="container">
            <div class="profile-info">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                </div>
                
                <div class="profile-details">
                    <h1><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h1>
                    <p class="profile-type"><?php echo htmlspecialchars($usuario['tipoUsuario']); ?></p>
                    
                    <?php if (!empty($usuario['biografia'])): ?>
                        <p class="profile-bio">
                            <?php echo htmlspecialchars($usuario['biografia']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="profile-meta">
                        <span>Miembro desde <?php echo date('M Y', strtotime($usuario['fechaRegistro'])); ?></span>
                    </div>
                </div>
                
                <?php if ($esMiPerfil): ?>
                    <div class="profile-actions">
                        <a href="cuenta.php" class="btn-primary">Editar Perfil</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="profile-content">
        <div class="container">
            <?php if ($usuario['tipoUsuario'] === 'Organizador'): ?>
                <!-- Eventos del Organizador -->
                <div class="profile-section">
                    <h2>Eventos Organizados</h2>
                    
                    <?php if (!empty($eventos)): ?>
                        <div class="events-grid">
                            <?php foreach ($eventos as $evento): ?>
                                <div class="event-card">
                                    <div class="event-image">
                                        <img src="<?php echo htmlspecialchars($evento['imagenPrincipal']); ?>" 
                                             alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
                                        <span class="event-badge"><?php echo htmlspecialchars($evento['tipo']); ?></span>
                                    </div>
                                    <div class="event-info">
                                        <h3><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                        <p class="event-location">
                                            <?php echo htmlspecialchars($evento['ubicacion']); ?>
                                        </p>
                                        <p class="event-date">
                                            <?php echo date('d/m/Y', strtotime($evento['fechaInicio'])); ?>
                                        </p>
                                        <div class="event-footer">
                                            <span class="event-price">
                                                Desde <strong><?php echo number_format($evento['precioMinimo'], 2); ?> EUR</strong>
                                            </span>
                                            <a href="detalle-evento.php?id=<?php echo $evento['idEvento']; ?>" 
                                               class="btn-primary">Ver detalles</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="empty-state">Este organizador aún no ha publicado eventos.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Perfil de Comprador -->
                <div class="profile-section">
                    <h2>Información Pública</h2>
                    <p>Este es un perfil de comprador. La información detallada es privada.</p>
                </div>
            <?php endif; ?>
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
</body>
</html>
