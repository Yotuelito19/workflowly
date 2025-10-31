<?php
/**
 * Modelo Compra
 * Gestiona las operaciones de compras y entradas
 */

class Compra {
    private $conn;
    private $table_name = "Compra";

    public $idCompra;
    public $idUsuario;
    public $fechaCompra;
    public $total;
    public $idMetodoPago;
    public $idEstadoCompra;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear una nueva compra
     */
    public function crear() {
        try {
            $this->conn->beginTransaction();

            // Insertar compra
            $query = "INSERT INTO " . $this->table_name . "
                      (idUsuario, fechaCompra, total, idMetodoPago, idEstadoCompra)
                      VALUES (:idUsuario, NOW(), :total, :idMetodoPago, :idEstadoCompra)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':idUsuario', $this->idUsuario);
            $stmt->bindParam(':total', $this->total);
            $stmt->bindParam(':idMetodoPago', $this->idMetodoPago);
            $stmt->bindParam(':idEstadoCompra', $this->idEstadoCompra);

            if (!$stmt->execute()) {
                throw new Exception("Error al crear la compra");
            }

            $this->idCompra = $this->conn->lastInsertId();
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error en Compra::crear(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Agregar detalle de compra (entradas)
     */
    public function agregarDetalle($idTipoEntrada, $cantidad, $precioUnitario) {
        $query = "INSERT INTO DetalleCompra (idCompra, idTipoEntrada, cantidad, precioUnitario)
                  VALUES (:idCompra, :idTipoEntrada, :cantidad, :precioUnitario)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idCompra', $this->idCompra);
        $stmt->bindParam(':idTipoEntrada', $idTipoEntrada);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':precioUnitario', $precioUnitario);

        if ($stmt->execute()) {
            $idDetalleCompra = $this->conn->lastInsertId();
            
            // Crear las entradas individuales
            $this->crearEntradas($idDetalleCompra, $cantidad);
            
            // Actualizar disponibilidad
            $this->actualizarDisponibilidad($idTipoEntrada, $cantidad);
            
            return $idDetalleCompra;
        }

        return false;
    }

    /**
     * Crear entradas individuales
     */
    private function crearEntradas($idDetalleCompra, $cantidad) {
        // Obtener estado "Activo" para las entradas
        $queryEstado = "SELECT idEstado FROM Estado WHERE nombre = 'Activo' AND tipoEntidad = 'General' LIMIT 1";
        $stmtEstado = $this->conn->prepare($queryEstado);
        $stmtEstado->execute();
        $idEstado = $stmtEstado->fetch(PDO::FETCH_ASSOC)['idEstado'];

        $query = "INSERT INTO Entrada (idDetalleCompra, codigoBarras, codigoQR, idEstadoEntrada)
                  VALUES (:idDetalleCompra, :codigoBarras, :codigoQR, :idEstado)";

        $stmt = $this->conn->prepare($query);

        for ($i = 0; $i < $cantidad; $i++) {
            $codigoBarras = generate_barcode();
            $codigoQR = generate_qr_code();

            $stmt->bindParam(':idDetalleCompra', $idDetalleCompra);
            $stmt->bindParam(':codigoBarras', $codigoBarras);
            $stmt->bindParam(':codigoQR', $codigoQR);
            $stmt->bindParam(':idEstado', $idEstado);

            $stmt->execute();
        }
    }

    /**
     * Actualizar disponibilidad de entradas
     */
    private function actualizarDisponibilidad($idTipoEntrada, $cantidad) {
        // Actualizar tipo de entrada
        $query = "UPDATE TipoEntrada 
                  SET cantidadDisponible = cantidadDisponible - :cantidad
                  WHERE idTipoEntrada = :idTipoEntrada";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':idTipoEntrada', $idTipoEntrada);
        $stmt->execute();

        // Actualizar evento
        $query2 = "UPDATE Evento e
                   INNER JOIN TipoEntrada te ON e.idEvento = te.idEvento
                   SET e.entradasDisponibles = e.entradasDisponibles - :cantidad
                   WHERE te.idTipoEntrada = :idTipoEntrada";

        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bindParam(':cantidad', $cantidad);
        $stmt2->bindParam(':idTipoEntrada', $idTipoEntrada);
        $stmt2->execute();
    }

    /**
     * Obtener compras de un usuario
     * Cambios realizados
     */
    public function obtenerComprasUsuario($idUsuario) {
    $query = "SELECT c.*, 
                     mp.tipo as metodo_pago, 
                     est.nombre as estado_compra,
                     COUNT(DISTINCT dc.idDetalleCompra) as num_detalles,
                     COUNT(DISTINCT e.idEntrada) as num_entradas
              FROM " . $this->table_name . " c
              INNER JOIN MetodoPago mp ON c.idMetodoPago = mp.idMetodoPago
              INNER JOIN Estado est ON c.idEstadoCompra = est.idEstado
              LEFT JOIN DetalleCompra dc ON c.idCompra = dc.idCompra
              LEFT JOIN Entrada e ON dc.idDetalleCompra = e.idDetalleCompra
              WHERE c.idUsuario = :idUsuario
              GROUP BY c.idCompra
              ORDER BY c.fechaCompra DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Obtener detalles de una compra
     * Cambios realizados
     */
    public function obtenerDetalles($idCompra) {
    $query = "SELECT dc.*, 
                     te.nombre as tipo_entrada_nombre, 
                     te.descripcion as tipo_entrada_desc,
                     ev.nombre as evento_nombre, 
                     ev.fechaInicio, 
                     ev.fechaFin, 
                     ev.ubicacion,
                     GROUP_CONCAT(DISTINCT z.nombre SEPARATOR ', ') as zona_nombre
              FROM DetalleCompra dc
              INNER JOIN TipoEntrada te ON dc.idTipoEntrada = te.idTipoEntrada
              INNER JOIN Evento ev ON te.idEvento = ev.idEvento
              LEFT JOIN TipoEntradaZona tez ON te.idTipoEntrada = tez.idTipoEntrada
              LEFT JOIN Zona z ON tez.idZona = z.idZona
              WHERE dc.idCompra = :idCompra
              GROUP BY dc.idDetalleCompra";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':idCompra', $idCompra);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Obtener entradas de un usuario
     * Cambios realizados
     */
    public function obtenerEntradasUsuario($idUsuario) {
    $query = "SELECT e.*, 
                     ev.nombre as evento_nombre, 
                     ev.fechaInicio, 
                     ev.fechaFin, 
                     ev.ubicacion, 
                     ev.imagenPrincipal,
                     te.nombre as tipo_entrada_nombre,
                     est.nombre as estado_entrada,
                     GROUP_CONCAT(DISTINCT z.nombre SEPARATOR ', ') as zona_nombre
              FROM Entrada e
              INNER JOIN DetalleCompra dc ON e.idDetalleCompra = dc.idDetalleCompra
              INNER JOIN Compra c ON dc.idCompra = c.idCompra
              INNER JOIN TipoEntrada te ON dc.idTipoEntrada = te.idTipoEntrada
              INNER JOIN Evento ev ON te.idEvento = ev.idEvento
              INNER JOIN Estado est ON e.idEstadoEntrada = est.idEstado
              LEFT JOIN TipoEntradaZona tez ON te.idTipoEntrada = tez.idTipoEntrada
              LEFT JOIN Zona z ON tez.idZona = z.idZona
              WHERE c.idUsuario = :idUsuario
              GROUP BY e.idEntrada
              ORDER BY ev.fechaInicio DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
