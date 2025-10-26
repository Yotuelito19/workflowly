<?php
/**
 * Página de confirmación de compra - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que esté logueado
if (!is_logged_in()) {
    redirect('/views/login.php');
}

// Obtener ID de compra
$idCompra = isset($_GET['compra']) ? (int)$_GET['compra'] : 0;

if ($idCompra === 0) {
    redirect('/views/account.php');
}

// Conectar BD y obtener detalles de la compra
$database = new Database();
$db = $database->getConnection();
$compraModel = new Compra($db);

// Verificar que la compra pertenece al usuario
$query = "SELECT * FROM Compra WHERE idCompra = :id AND idUsuario = :userId";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $idCompra, ':userId' => $_SESSION['user_id']]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    redirect('/views/account.php');
}

// Obtener detalles de la compra
$detalles = $compraModel->obtenerDetalles($idCompra);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Confirmada - WorkFlowly</title>
    <link rel="stylesheet" href="../assets/css/confirmation.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .confirmation-container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .order-number {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .order-details {
            background: #f5f5f5;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>¡Compra realizada con éxito!</h1>
        <p class="order-number">Número de pedido: <strong>#<?php echo $compra['idCompra']; ?></strong></p>
        
        <p>Hemos enviado un correo de confirmación a <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong> con todos los detalles de tu compra.</p>

        <div class="order-details">
            <h3>Resumen de la compra</h3>
            
            <?php foreach ($detalles as $detalle): ?>
                <div class="detail-row">
                    <div>
                        <strong><?php echo htmlspecialchars($detalle['evento_nombre']); ?></strong><br>
                        <small><?php echo htmlspecialchars($detalle['tipo_entrada_nombre']); ?> (x<?php echo $detalle['cantidad']; ?>)</small>
                    </div>
                    <span><?php echo format_price($detalle['precioUnitario'] * $detalle['cantidad']); ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="detail-row">
                <strong>Total pagado</strong>
                <strong><?php echo format_price($compra['total']); ?></strong>
            </div>
        </div>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <strong>Tus entradas están disponibles</strong><br>
            Puedes ver y descargar tus entradas desde tu cuenta en cualquier momento.
        </div>

        <div>
            <a href="account.php" class="btn btn-primary">
                <i class="fas fa-ticket-alt"></i> Ver mis entradas
            </a>
            <a href="search-events.php" class="btn btn-secondary">
                <i class="fas fa-search"></i> Buscar más eventos
            </a>
        </div>

        <p style="margin-top: 30px; color: #999; font-size: 14px;">
            Recibirás un código QR para cada entrada que podrás presentar en el evento.
        </p>
    </div>
</body>
</html>
