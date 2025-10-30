<?php
/**
 * Proceso de compra - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que esté logueado
if (!is_logged_in()) {
    redirect('/views/login.php');
}

// Procesar datos del formulario de event-detail
$carrito = [];
$total = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entradas'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    foreach ($_POST['entradas'] as $idTipoEntrada => $cantidad) {
        if ($cantidad > 0) {
            // Obtener info de la entrada
            $query = "SELECT te.*, e.nombre as evento_nombre, e.fechaInicio, e.ubicacion 
                      FROM TipoEntrada te
                      INNER JOIN Evento e ON te.idEvento = e.idEvento
                      WHERE te.idTipoEntrada = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $idTipoEntrada);
            $stmt->execute();
            $entrada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entrada) {
                $entrada['cantidad'] = (int)$cantidad;
                $entrada['subtotal'] = $entrada['precio'] * $cantidad;
                $carrito[] = $entrada;
                $total += $entrada['subtotal'];
            }
        }
    }
}

if (empty($carrito)) {
    redirect('/views/search-events.php');
}

// Procesar compra
$compra_exitosa = false;
$error_compra = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_compra'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $db->beginTransaction();
        
        // Crear método de pago temporal
        $queryMetodo = "INSERT INTO MetodoPago (idUsuario, tipo, nombreTitular) 
                        VALUES (:idUsuario, :tipo, :titular)";
        $stmtMetodo = $db->prepare($queryMetodo);
        $stmtMetodo->execute([
            ':idUsuario' => $_SESSION['user_id'],
            ':tipo' => 'Tarjeta',
            ':titular' => $_POST['nombre_titular']
        ]);
        $idMetodoPago = $db->lastInsertId();
        
        // Crear compra
        $compraModel = new Compra($db);
        $compraModel->idUsuario = $_SESSION['user_id'];
        $compraModel->idMetodoPago = $idMetodoPago;
        $compraModel->total = $total;
        
        // Obtener estado "Pagado"
        $queryEstado = "SELECT idEstado FROM Estado WHERE nombre = 'Pagado' AND tipoEntidad = 'Compra' LIMIT 1";
        $stmtEstado = $db->prepare($queryEstado);
        $stmtEstado->execute();
        $compraModel->idEstadoCompra = $stmtEstado->fetch(PDO::FETCH_ASSOC)['idEstado'];
        
        if ($compraModel->crear()) {
            // Agregar detalles de compra
            foreach ($carrito as $item) {
                $compraModel->agregarDetalle($item['idTipoEntrada'], $item['cantidad'], $item['precio']);
            }
            
            $db->commit();
            $compra_exitosa = true;
            $_SESSION['ultima_compra'] = $compraModel->idCompra;
            
            // Redirigir a confirmación
            redirect('/views/confirmation.php?compra=' . $compraModel->idCompra);
        }
    } catch (Exception $e) {
        $db->rollBack();
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

    <main class="checkout-main">
        <div class="container">
            <h1>Finalizar compra</h1>
            
            <?php if (!empty($error_compra)): ?>
                <div class="alert alert-error"><?php echo $error_compra; ?></div>
            <?php endif; ?>

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
                                <input type="text" placeholder="1234 5678 9012 3456" maxlength="19" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Fecha de expiración</label>
                                    <input type="text" placeholder="MM/AA" maxlength="5" required>
                                </div>
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input type="text" placeholder="123" maxlength="3" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary btn-large">
                            Confirmar y pagar <?php echo format_price($total); ?>
                        </button>
                    </form>
                </div>

                <aside class="order-summary">
                    <h2>Resumen del pedido</h2>
                    
                    <?php foreach ($carrito as $item): ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <h3><?php echo htmlspecialchars($item['evento_nombre']); ?></h3>
                                <p><?php echo htmlspecialchars($item['nombre']); ?></p>
                                <p class="item-meta">
                                    <i class="fas fa-calendar"></i> <?php echo format_date($item['fechaInicio']); ?>
                                </p>
                                <p class="item-meta">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['ubicacion']); ?>
                                </p>
                            </div>
                            <div class="item-pricing">
                                <span class="quantity"><?php echo $item['cantidad']; ?>x <?php echo format_price($item['precio']); ?></span>
                                <strong><?php echo format_price($item['subtotal']); ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-total">
                        <span>Total</span>
                        <strong><?php echo format_price($total); ?></strong>
                    </div>

                    <div class="security-info">
                        <i class="fas fa-lock"></i>
                        <span>Pago seguro y encriptado</span>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
