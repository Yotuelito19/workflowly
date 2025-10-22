<?php
/**
 * WorkFlowly - Login y Registro
 * Conversión de login.html a PHP
 */

// Iniciar sesión
session_start();

// Si ya está logueado, redirigir
if (isset($_SESSION['usuario'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'cuenta.php';
    header('Location: ' . $redirect);
    exit;
}

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/AuthController.php';

// Crear instancia del controlador
$authController = new AuthController();

$errorLogin = '';
$errorRegistro = '';
$successRegistro = '';

// Procesar LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $resultado = $authController->login($email, $password);
    
    if ($resultado['success']) {
        $_SESSION['usuario'] = $resultado['usuario'];
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'cuenta.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        $errorLogin = $resultado['mensaje'];
    }
}

// Procesar REGISTRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email_registro']);
    $telefono = trim($_POST['telefono']);
    $password = $_POST['password_registro'];
    $passwordConfirm = $_POST['password_confirm'];
    $tipoUsuario = isset($_POST['tipo_usuario']) ? $_POST['tipo_usuario'] : 'Comprador';
    
    // Validaciones básicas
    if ($password !== $passwordConfirm) {
        $errorRegistro = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $errorRegistro = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        $resultado = $authController->registro($nombre, $apellidos, $email, $telefono, $password, $tipoUsuario);
        
        if ($resultado['success']) {
            $successRegistro = 'Registro exitoso. Ya puedes iniciar sesión.';
        } else {
            $errorRegistro = $resultado['mensaje'];
        }
    }
}

// Determinar qué vista mostrar
$mostrarRegistro = isset($_GET['registro']) || isset($_POST['registro']) || !empty($successRegistro);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mostrarRegistro ? 'Registro' : 'Iniciar Sesión'; ?> - WorkFlowly</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
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
            </div>
        </div>
    </header>

    <!-- Login/Registro Content -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <!-- Tabs -->
                <div class="auth-tabs">
                    <button class="auth-tab <?php echo !$mostrarRegistro ? 'active' : ''; ?>" 
                            onclick="showTab('login')">
                        Iniciar Sesión
                    </button>
                    <button class="auth-tab <?php echo $mostrarRegistro ? 'active' : ''; ?>" 
                            onclick="showTab('registro')">
                        Registro
                    </button>
                </div>

                <!-- LOGIN FORM -->
                <div id="login-form" class="auth-form <?php echo !$mostrarRegistro ? 'active' : ''; ?>">
                    <h2>Bienvenido de nuevo</h2>
                    <p class="auth-subtitle">Accede a tu cuenta para gestionar tus entradas</p>

                    <?php if (!empty($errorLogin)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($errorLogin); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($successRegistro)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($successRegistro); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="login" value="1">
                        <?php if (isset($_GET['redirect'])): ?>
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   placeholder="tu@email.com"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Tu contraseña"
                                   required>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember">
                                <span>Recordarme</span>
                            </label>
                            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                        </div>

                        <button type="submit" class="btn-primary btn-block">
                            Iniciar Sesión
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>¿No tienes cuenta? 
                            <a href="#" onclick="showTab('registro'); return false;">Regístrate aquí</a>
                        </p>
                    </div>
                </div>

                <!-- REGISTRO FORM -->
                <div id="registro-form" class="auth-form <?php echo $mostrarRegistro ? 'active' : ''; ?>">
                    <h2>Crear cuenta nueva</h2>
                    <p class="auth-subtitle">Únete a WorkFlowly y disfruta de eventos increíbles</p>

                    <?php if (!empty($errorRegistro)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($errorRegistro); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="registro" value="1">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" 
                                       id="nombre" 
                                       name="nombre" 
                                       placeholder="Juan"
                                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" 
                                       id="apellidos" 
                                       name="apellidos" 
                                       placeholder="Pérez García"
                                       value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>"
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email_registro">Email</label>
                            <input type="email" 
                                   id="email_registro" 
                                   name="email_registro" 
                                   placeholder="tu@email.com"
                                   value="<?php echo isset($_POST['email_registro']) ? htmlspecialchars($_POST['email_registro']) : ''; ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" 
                                   id="telefono" 
                                   name="telefono" 
                                   placeholder="+34 612 345 678"
                                   value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="password_registro">Contraseña</label>
                            <input type="password" 
                                   id="password_registro" 
                                   name="password_registro" 
                                   placeholder="Mínimo 6 caracteres"
                                   required>
                            <small class="form-hint">Mínimo 6 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirmar contraseña</label>
                            <input type="password" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   placeholder="Repite tu contraseña"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Tipo de cuenta</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="tipo_usuario" value="Comprador" checked>
                                    <span>Comprador (asistir a eventos)</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="tipo_usuario" value="Organizador">
                                    <span>Organizador (crear y gestionar eventos)</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="acepto_terminos" required>
                                <span>
                                    Acepto los 
                                    <a href="#" target="_blank">términos y condiciones</a> 
                                    y la 
                                    <a href="#" target="_blank">política de privacidad</a>
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="btn-primary btn-block">
                            Crear Cuenta
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>¿Ya tienes cuenta? 
                            <a href="#" onclick="showTab('login'); return false;">Inicia sesión aquí</a>
                        </p>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="benefits-section">
                    <h3>¿Por qué unirte a WorkFlowly?</h3>
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <span class="benefit-icon">🎫</span>
                            <div>
                                <strong>Entradas al instante</strong>
                                <p>Recibe tus entradas digitales inmediatamente</p>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-icon">🔒</span>
                            <div>
                                <strong>Compra segura</strong>
                                <p>Tus datos protegidos con la mejor tecnología</p>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-icon">💰</span>
                            <div>
                                <strong>Sin comisiones ocultas</strong>
                                <p>El precio que ves es el precio que pagas</p>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-icon">⚡</span>
                            <div>
                                <strong>Proceso rápido</strong>
                                <p>Compra en menos de 2 minutos</p>
                            </div>
                        </div>
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
    <script>
        function showTab(tab) {
            // Ocultar ambos formularios
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('registro-form').classList.remove('active');
            
            // Desactivar tabs
            const tabs = document.querySelectorAll('.auth-tab');
            tabs.forEach(t => t.classList.remove('active'));
            
            // Mostrar formulario seleccionado
            if (tab === 'login') {
                document.getElementById('login-form').classList.add('active');
                tabs[0].classList.add('active');
                // Actualizar URL
                window.history.replaceState({}, '', 'login.php');
            } else {
                document.getElementById('registro-form').classList.add('active');
                tabs[1].classList.add('active');
                // Actualizar URL
                window.history.replaceState({}, '', 'login.php?registro=1');
            }
        }

        // Validación de contraseñas en tiempo real
        document.getElementById('password_confirm')?.addEventListener('input', function() {
            const password = document.getElementById('password_registro').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
