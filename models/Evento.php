<?php
/**
 * Modelo Evento
 * Gestiona las operaciones CRUD de eventos
 */

class Evento {
    private $conn;
    private $table_name = "Evento";

    public $idEvento;
    public $idUsuario;
    public $nombre;
    public $descripcion;
    public $tipo;
    public $fechaInicio;
    public $fechaFin;
    public $ubicacion;
    public $aforoTotal;
    public $entradasDisponibles;
    public $imagenPrincipal;
    public $idEstadoEvento;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los eventos disponibles
     */
    public function obtenerEventosDisponibles($limit = 20, $offset = 0) {
        $query = "SELECT e.*, u.nombre as organizador_nombre, u.apellidos as organizador_apellidos,
                         est.nombre as estado_nombre,
                         (SELECT MIN(precio) FROM TipoEntrada WHERE idEvento = e.idEvento) as precio_desde
                  FROM " . $this->table_name . " e
                  INNER JOIN Usuario u ON e.idUsuario = u.idUsuario
                  INNER JOIN Estado est ON e.idEstadoEvento = est.idEstado
                  WHERE est.nombre = 'Activo' 
                  AND e.fechaFin > NOW()
                  AND e.entradasDisponibles > 0
                  ORDER BY e.fechaInicio ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar eventos por filtros
     */
    public function buscarEventos($search = '', $tipo = '', $fecha_desde = '', $fecha_hasta = '') {
        $query = "SELECT e.*, u.nombre as organizador_nombre, u.apellidos as organizador_apellidos,
                         est.nombre as estado_nombre,
                         (SELECT MIN(precio) FROM TipoEntrada WHERE idEvento = e.idEvento) as precio_desde
                  FROM " . $this->table_name . " e
                  INNER JOIN Usuario u ON e.idUsuario = u.idUsuario
                  INNER JOIN Estado est ON e.idEstadoEvento = est.idEstado
                  WHERE est.nombre = 'Activo' 
                  AND e.fechaFin > NOW()
                  AND e.entradasDisponibles > 0";

        if (!empty($search)) {
            $query .= " AND (e.nombre LIKE :search OR e.descripcion LIKE :search OR e.ubicacion LIKE :search)";
        }

        if (!empty($tipo)) {
            $query .= " AND e.tipo = :tipo";
        }

        if (!empty($fecha_desde)) {
            $query .= " AND DATE(e.fechaInicio) >= :fecha_desde";
        }

        if (!empty($fecha_hasta)) {
            $query .= " AND DATE(e.fechaInicio) <= :fecha_hasta";
        }

        $query .= " ORDER BY e.fechaInicio ASC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_term = "%{$search}%";
            $stmt->bindParam(':search', $search_term);
        }

        if (!empty($tipo)) {
            $stmt->bindParam(':tipo', $tipo);
        }

        if (!empty($fecha_desde)) {
            $stmt->bindParam(':fecha_desde', $fecha_desde);
        }

        if (!empty($fecha_hasta)) {
            $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener evento por ID con detalles completos
     */
    public function obtenerPorId($id) {
        $query = "SELECT e.*, u.nombre as organizador_nombre, u.apellidos as organizador_apellidos, u.email as organizador_email,
                         est.nombre as estado_nombre
                  FROM " . $this->table_name . " e
                  INNER JOIN Usuario u ON e.idUsuario = u.idUsuario
                  INNER JOIN Estado est ON e.idEstadoEvento = est.idEstado
                  WHERE e.idEvento = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener tipos de entrada de un evento
     */
    public function obtenerTiposEntrada($idEvento) {
        $query = "SELECT te.idTipoEntrada, te.idEvento, te.nombre, te.descripcion, te.precio, te.cantidadDisponible,
                         tez.idZona, z.nombre as zona_nombre,
                         te.cantidadDisponible as disponibles
                  FROM TipoEntrada te
                  LEFT JOIN TipoEntradaZona tez ON te.idTipoEntrada = tez.idTipoEntrada
                  LEFT JOIN Zona z ON tez.idZona = z.idZona
                  WHERE te.idEvento = :idEvento
                  AND te.cantidadDisponible > 0
                  AND NOW() BETWEEN te.fechaInicioVenta AND te.fechaFinVenta
                  ORDER BY te.precio ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idEvento', $idEvento);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo evento
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . "
                  (idUsuario, nombre, descripcion, tipo, fechaInicio, fechaFin, ubicacion, aforoTotal, entradasDisponibles, imagenPrincipal, idEstadoEvento)
                  VALUES (:idUsuario, :nombre, :descripcion, :tipo, :fechaInicio, :fechaFin, :ubicacion, :aforoTotal, :entradasDisponibles, :imagenPrincipal, :idEstadoEvento)";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->ubicacion = htmlspecialchars(strip_tags($this->ubicacion));

        $stmt->bindParam(':idUsuario', $this->idUsuario);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':fechaInicio', $this->fechaInicio);
        $stmt->bindParam(':fechaFin', $this->fechaFin);
        $stmt->bindParam(':ubicacion', $this->ubicacion);
        $stmt->bindParam(':aforoTotal', $this->aforoTotal);
        $stmt->bindParam(':entradasDisponibles', $this->entradasDisponibles);
        $stmt->bindParam(':imagenPrincipal', $this->imagenPrincipal);
        $stmt->bindParam(':idEstadoEvento', $this->idEstadoEvento);

        if ($stmt->execute()) {
            $this->idEvento = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Obtener eventos del organizador
     */
    public function obtenerEventosOrganizador($idUsuario) {
        $query = "SELECT e.*, est.nombre as estado_nombre,
                         (SELECT COUNT(*) FROM TipoEntrada WHERE idEvento = e.idEvento) as tipos_entrada,
                         (SELECT IFNULL(SUM(dc.precioUnitario * dc.cantidad), 0) 
                          FROM DetalleCompra dc 
                          INNER JOIN TipoEntrada te ON dc.idTipoEntrada = te.idTipoEntrada 
                          WHERE te.idEvento = e.idEvento) as ingresos_totales
                  FROM " . $this->table_name . " e
                  INNER JOIN Estado est ON e.idEstadoEvento = est.idEstado
                  WHERE e.idUsuario = :idUsuario
                  ORDER BY e.fechaInicio DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>