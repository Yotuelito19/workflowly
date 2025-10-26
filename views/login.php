<?php
/**
 * Página de Login y Registro - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Si ya está logueado, redirigir al inicio
if (is_logged_in()) {
    redirect('/index.php');
}

// Procesar login
$login_error = '';
$register_error = '';
$register_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        // Proceso de LOGIN
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];

        if (!empty($email) && !empty($password)) {
            $database = new Database();
            $db = $database->getConnection();
            $usuario = new Usuario($db);
            
            $result = $usuario->login($email, $password);
            
            if ($result['success']) {
                redirect('/index.php');
            } else {
                $login_error = $result['message'];
            }
        } else {
            $login_error = 'Por favor, completa todos los campos';
        }
    } 
    elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Proceso de REGISTRO
        $nombre = sanitize_input($_POST['nombre']);
        $apellidos = sanitize_input($_POST['apellidos']);
        $email = sanitize_input($_POST['email']);
        $telefono = sanitize_input($_POST['telefono']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $tipoUsuario = isset($_POST['tipoUsuario']) ? sanitize_input($_POST['tipoUsuario']) : 'Comprador';

        // Validaciones
        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            $register_error = 'Todos los campos obligatorios deben estar completos';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $register_error = 'Email inválido';
        } elseif (strlen($password) < 8) {
            $register_error = 'La contraseña debe tener al menos 8 caracteres';
        } elseif ($password !== $password_confirm) {
            $register_error = 'Las contraseñas no coinciden';
        } else {
            $database = new Database();
            $db = $database->getConnection();
            $usuario = new Usuario($db);

            // Verificar si el email ya existe
            if ($usuario->emailExists($email)) {
                $register_error = 'El email ya está registrado';
            } else {
                // Obtener ID del estado "Activo"
                $queryEstado = "SELECT idEstado FROM Estado WHERE nombre = 'Activo' AND tipoEntidad = 'General' LIMIT 1";
                $stmtEstado = $db->prepare($queryEstado);
                $stmtEstado->execute();
                $idEstadoActivo = $stmtEstado->fetch(PDO::FETCH_ASSOC)['idEstado'];

                // Crear usuario
                $usuario->nombre = $nombre;
                $usuario->apellidos = $apellidos;
                $usuario->email = $email;
                $usuario->telefono = $telefono;
                $usuario->password = $password;  // CORREGIDO: era contraseña
                $usuario->tipoUsuario = $tipoUsuario;
                $usuario->idEstadoUsuario = $idEstadoActivo;

                if ($usuario->registrar()) {
                    $register_success = true;
                } else {
                    $register_error = 'Error al registrar el usuario';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - WorkFlowly</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #0a0;
            border: 1px solid #cfc;
        }
    </style>
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
            <div class="header-actions">
                <a href="../index.php" class="btn-link">Explorar eventos</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-layout">
                <!-- Left Side - Branding -->
                <div class="auth-branding">
                    <div class="branding-content">
                        <div class="brand-logo">
                            <div class="logo-circle large">
                                <span>W</span>
                            </div>
                            <h1>WorkFlowly</h1>
                        </div>
                        <h2>Tu plataforma de eventos de confianza</h2>
                        <p>Únete a miles de usuarios que ya disfrutan de eventos sin reventa abusiva y con total transparencia en precios.</p>
                        
                        <div class="features-list">
                            <div class="feature-item">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <strong>Sin reventa abusiva</strong>
                                    <span>Sistemas anti-bots garantizados</span>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-eye"></i>
                                <div>
                                    <strong>Precios transparentes</strong>
                                    <span>Sin costos ocultos</span>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-qrcode"></i>
                                <div>
                                    <strong>Entradas digitales</strong>
                                    <span>QR seguro directo a tu móvil</span>
                                </div>
                            </div>
                        </div>

                        <div class="trust-badges">
                            <div class="badges-content">
                                <div class="stat">
                                    <strong>50K+</strong>
                                    <span>usuarios activos</span>
                                </div>
                                <div class="stat">
                                    <strong>4.8★</strong>
                                    <span>valoración promedio</span>
                                </div>
                                <div class="stat">
                                    <strong>0%</strong>
                                    <span>costos ocultos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Auth Forms -->
                <div class="auth-forms">
                    <!-- Login Form -->
                    <div class="form-container" id="loginForm" <?php echo $register_success ? 'style="display:none;"' : ''; ?>>
                        <div class="form-header">
                            <h2>Bienvenido de vuelta</h2>
                            <p>Inicia sesión para acceder a tu cuenta</p>
                        </div>

                        <?php if (!empty($login_error)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $login_error; ?>
                            </div>
                        <?php endif; ?>

                        <form class="auth-form" method="POST" action="">
                            <input type="hidden" name="action" value="login">
                            
                            <div class="form-group">
                                <label for="loginEmail">Correo electrónico</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="loginEmail" name="email" placeholder="tu@email.com" required 
                                           value="<?php echo isset($_POST['email']) && $_POST['action'] === 'login' ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="loginPassword">Contraseña</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="loginPassword" name="password" placeholder="Tu contraseña" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('loginPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-options">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="remember">
                                    <span class="checkmark"></span>
                                    Recordarme
                                </label>
                                <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                            </div>

                            <button type="submit" class="btn-primary">
                                Iniciar Sesión
                                <i class="fas fa-arrow-right"></i>
                            </button>

                            <div class="divider">
                                <span>O continúa con</span>
                            </div>

                            <div class="social-login">
                                <button type="button" class="btn-social google" disabled>
                                    <i class="fab fa-google"></i>
                                    Google
                                </button>
                                <button type="button" class="btn-social facebook" disabled>
                                    <i class="fab fa-facebook-f"></i>
                                    Facebook
                                </button>
                            </div>

                            <p class="form-switch">
                                ¿No tienes cuenta? 
                                <a href="#" onclick="showRegisterForm(); return false;">Regístrate aquí</a>
                            </p>
                        </form>
                    </div>

                    <!-- Register Form -->
                    <div class="form-container <?php echo !$register_success ? 'hidden' : ''; ?>" id="registerForm">
                        <div class="form-header">
                            <h2>Crear cuenta nueva</h2>
                            <p>Regístrate y empieza a disfrutar eventos sin reventa</p>
                        </div>

                        <?php if (!empty($register_error)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $register_error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($register_success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> ¡Registro exitoso! Ya puedes iniciar sesión.
                            </div>
                        <?php endif; ?>

                        <form class="auth-form" method="POST" action="">
                            <input type="hidden" name="action" value="register">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">Nombre</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user"></i>
                                        <input type="text" id="firstName" name="nombre" placeholder="Tu nombre" required
                                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Apellidos</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user"></i>
                                        <input type="text" id="lastName" name="apellidos" placeholder="Tus apellidos" required
                                               value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="registerEmail">Correo electrónico</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="registerEmail" name="email" placeholder="tu@email.com" required
                                           value="<?php echo isset($_POST['email']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="phone" name="telefono" placeholder="+34 600 000 000" required
                                           value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="registerPassword">Contraseña</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="registerPassword" name="password" placeholder="Mínimo 8 caracteres" required minlength="8">
                                    <button type="button" class="toggle-password" onclick="togglePassword('registerPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword">Confirmar contraseña</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirmPassword" name="password_confirm" placeholder="Repite tu contraseña" required minlength="8">
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="userType">Tipo de usuario</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user-tag"></i>
                                    <select id="userType" name="tipoUsuario" required>
                                        <option value="Comprador" <?php echo (isset($_POST['tipoUsuario']) && $_POST['tipoUsuario'] === 'Comprador') ? 'selected' : ''; ?>>Comprador</option>
                                        <option value="Organizador" <?php echo (isset($_POST['tipoUsuario']) && $_POST['tipoUsuario'] === 'Organizador') ? 'selected' : ''; ?>>Organizador</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="terms" required>
                                    <span class="checkmark"></span>
                                    Acepto los <a href="#">términos y condiciones</a> y la <a href="#">política de privacidad</a>
                                </label>
                            </div>

                            <button type="submit" class="btn-primary">
                                Crear cuenta
                                <i class="fas fa-arrow-right"></i>
                            </button>

                            <p class="form-switch">
                                ¿Ya tienes cuenta? 
                                <a href="#" onclick="showLoginForm(); return false;">Inicia sesión</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="#">Términos de servicio</a>
                <a href="#">Política de privacidad</a>
                <a href="#">Ayuda</a>
            </div>
        </div>
    </footer>

    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.parentElement.querySelector('.toggle-password');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Show register form
        function showRegisterForm() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').classList.remove('hidden');
            document.getElementById('registerForm').style.display = 'block';
        }

        // Show login form
        function showLoginForm() {
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').classList.remove('hidden');
            document.getElementById('loginForm').style.display = 'block';
        }
    </script>
</body>
</html>
