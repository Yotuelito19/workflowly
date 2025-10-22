<?php
/**
 * WorkFlowly - Mis Entradas
 */

// Iniciar sesión
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php?redirect=mis-entradas.php');
    exit;
}

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/EntradaController.php';

// Crear instancia del controlador
$entradaController = new EntradaController();

// Obtener filtro
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'proximas';

// Obtener entradas según filtro
switch ($filtro) {
    case 'pasadas':
        $entradas = $entradaController->getEntradasPasadas($_SESSION['usuario']['id']);
        break;
    case 'todas':
        $entradas = $entradaController->getTodasEntradas($_SESSION['usuario']['id']);
        break;
    case 'proximas':
    default:
        $entradas = $entradaController->getEntradasProximas($_SESSION['usuario']['id']);
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Entradas - WorkFlowly</title>
    
    <link rel="stylesheet" href="assets/css/account.css">
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
                <a href="../app/controllers/AuthController.php?action=logout" class="btn-primary">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Inicio</a> / 
            <a href="cuenta.php">Mi Cuenta</a> / 
            <span>Mis Entradas</span>
        </div>
    </div>

    <!-- Tickets Content -->
    <section class="tickets-section">
        <div class="container">
            <h1>Mis Entradas</h1>
            
            <!-- Filtros -->
            <div class="tickets-filters">
                <a href="?filtro=proximas" 
                   class="filter-btn <?php echo $filtro === 'proximas' ? 'active' : ''; ?>">
                    Próximas
                </a>
                <a href="?filtro=pasadas" 
                   class="filter-btn <?php echo $filtro === 'pasadas' ? 'active' : ''; ?>">
                    Pasadas
                </a>
                <a href="?filtro=todas" 
                   class="filter-btn <?php echo $filtro === 'todas' ? 'active' : ''; ?>">
                    Todas
                </a>
            </div>

            <!-- Listado de Entradas -->
            <?php if (!empty($entradas)): ?>
                <div class="tickets-grid">
                    <?php foreach ($entradas as $entrada): ?>
                        <div class="ticket-card">
                            <div class="ticket-header">
                                <div class="ticket-event">
                                    <h3><?php echo htmlspecialchars($entrada['evento']['nombre']); ?></h3>
                                    <p class="ticket-location">
                                        <?php echo htmlspecialchars($entrada['evento']['ubicacion']); ?>
                                    </p>
                                    <p class="ticket-date">
                                        <?php echo date('d/m/Y H:i', strtotime($entrada['evento']['fechaInicio'])); ?>
                                    </p>
                                </div>
                                <div class="ticket-status">
                                    <span class="status-badge status-<?php echo strtolower($entrada['estado']); ?>">
                                        <?php echo htmlspecialchars($entrada['estado']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="ticket-body">
                                <div class="ticket-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Tipo de entrada</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($entrada['tipoEntrada']); ?></span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Código</span>
                                        <span class="detail-value"><?php echo str_pad($entrada['idEntrada'], 8, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                    
                                    <?php if ($entrada['asiento']): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Asiento</span>
                                            <span class="detail-value">
                                                Zona <?php echo htmlspecialchars($entrada['zona']); ?> - 
                                                Fila <?php echo htmlspecialchars($entrada['fila']); ?> - 
                                                Asiento <?php echo htmlspecialchars($entrada['numero']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="ticket-qr">
                                    <?php if ($entrada['codigoQR']): ?>
                                        <img src="<?php echo htmlspecialchars($entrada['codigoQR']); ?>" 
                                             alt="Código QR">
                                    <?php else: ?>
                                        <div class="qr-placeholder">QR no disponible</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="ticket-footer">
                                <button onclick="descargarEntrada(<?php echo $entrada['idEntrada']; ?>)" 
                                        class="btn-secondary btn-small">
                                    Descargar PDF
                                </button>
                                <button onclick="enviarEmail(<?php echo $entrada['idEntrada']; ?>)" 
                                        class="btn-secondary btn-small">
                                    Enviar por Email
                                </button>
                                <a href="detalle-evento.php?id=<?php echo $entrada['evento']['idEvento']; ?>" 
                                   class="btn-primary btn-small">
                                    Ver Evento
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="100" height="100" viewBox="0 0 100 100">
                            <rect x="20" y="30" width="60" height="50" fill="#e0e0e0" rx="5"/>
                            <rect x="35" y="45" width="30" height="20" fill="#fff"/>
                        </svg>
                    </div>
                    <h3>No tienes entradas <?php echo $filtro; ?></h3>
                    <p>Cuando compres entradas, aparecerán aquí.</p>
                    <a href="eventos.php" class="btn-primary">Buscar Eventos</a>
                </div>
            <?php endif; ?>
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

    <script>
        function descargarEntrada(idEntrada) {
            window.location.href = '../app/controllers/EntradaController.php?action=descargar&id=' + idEntrada;
        }

        function enviarEmail(idEntrada) {
            if (confirm('¿Enviar esta entrada por email?')) {
                fetch('../app/controllers/EntradaController.php?action=enviar_email&id=' + idEntrada)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Entrada enviada por email correctamente');
                        } else {
                            alert('Error al enviar el email: ' + data.mensaje);
                        }
                    })
                    .catch(error => {
                        alert('Error al procesar la solicitud');
                    });
            }
        }
    </script>
</body>
</html>
