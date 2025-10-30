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

    // 👇 NUEVO
    public $idLugar;
    public $idOrganizador;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los eventos disponibles (página pública)
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
    public function buscarEventos($search = '', $tipo = '', $fecha_desde = '', $fecha_hasta = '', $ubicacion = '', $precio_min = 0, $precio_max = 0) {
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

        if (!empty($ubicacion)) {
            $query .= " AND e.ubicacion LIKE :ubicacion";
        }

        if (!empty($fecha_desde)) {
            $query .= " AND DATE(e.fechaInicio) >= :fecha_desde";
        }

        if (!empty($fecha_hasta)) {
            $query .= " AND DATE(e.fechaInicio) <= :fecha_hasta";
        }

        $hasPriceFilter = false;
        if ($precio_min > 0 || $precio_max > 0) {
            $query .= " HAVING 1=1";
            $hasPriceFilter = true;
            
            if ($precio_min > 0) {
                $query .= " AND precio_desde >= :precio_min";
            }
            
            if ($precio_max > 0) {
                $query .= " AND precio_desde <= :precio_max";
            }
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

        if (!empty($ubicacion)) {
            $ubicacion_term = "%{$ubicacion}%";
            $stmt->bindParam(':ubicacion', $ubicacion_term);
        }

        if (!empty($fecha_desde)) {
            $stmt->bindParam(':fecha_desde', $fecha_desde);
        }

        if (!empty($fecha_hasta)) {
            $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        }

        if ($precio_min > 0) {
            $stmt->bindParam(':precio_min', $precio_min, PDO::PARAM_INT);
        }

        if ($precio_max > 0) {
            $stmt->bindParam(':precio_max', $precio_max, PDO::PARAM_INT);
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
                  (idUsuario, nombre, descripcion, tipo, fechaInicio, fechaFin, ubicacion, aforoTotal, entradasDisponibles, imagenPrincipal, idEstadoEvento, idLugar, idOrganizador)
                  VALUES
                  (:idUsuario, :nombre, :descripcion, :tipo, :fechaInicio, :fechaFin, :ubicacion, :aforoTotal, :entradasDisponibles, :imagenPrincipal, :idEstadoEvento, :idLugar, :idOrganizador)";

        $stmt = $this->conn->prepare($query);

        // saneamos lo básico
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
        $stmt->bindParam(':aforoTotal', $this->aforoTotal, PDO::PARAM_INT);
        $stmt->bindParam(':entradasDisponibles', $this->entradasDisponibles, PDO::PARAM_INT);
        $stmt->bindParam(':imagenPrincipal', $this->imagenPrincipal);
        $stmt->bindParam(':idEstadoEvento', $this->idEstadoEvento, PDO::PARAM_INT);

        // 👇 nuevos, pueden ser null
        $stmt->bindValue(':idLugar', $this->idLugar ?: null, PDO::PARAM_INT);
        $stmt->bindValue(':idOrganizador', $this->idOrganizador ?: null, PDO::PARAM_INT);

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

    /**
     * Actualizar evento (versión tuya adaptada)
     */
    public function actualizar($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre,
                      descripcion = :descripcion,
                      tipo = :tipo,
                      fechaInicio = :fechaInicio,
                      fechaFin = :fechaFin,
                      ubicacion = :ubicacion,
                      aforoTotal = :aforoTotal,
                      entradasDisponibles = :entradasDisponibles,
                      imagenPrincipal = :imagenPrincipal,
                      idEstadoEvento = :idEstadoEvento,
                      idLugar = :idLugar,
                      idOrganizador = :idOrganizador
                  WHERE idEvento = :idEvento";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':fechaInicio', $data['fechaInicio']);
        $stmt->bindParam(':fechaFin', $data['fechaFin']);
        $stmt->bindParam(':ubicacion', $data['ubicacion']);
        $stmt->bindParam(':aforoTotal', $data['aforoTotal'], PDO::PARAM_INT);
        $stmt->bindParam(':entradasDisponibles', $data['entradasDisponibles'], PDO::PARAM_INT);
        $stmt->bindParam(':imagenPrincipal', $data['imagenPrincipal']);
        $stmt->bindParam(':idEstadoEvento', $data['idEstadoEvento'], PDO::PARAM_INT);

        // 👇 nuevos
        $idLugar = !empty($data['idLugar']) ? $data['idLugar'] : null;
        $idOrganizador = !empty($data['idOrganizador']) ? $data['idOrganizador'] : null;
        $stmt->bindValue(':idLugar', $idLugar, PDO::PARAM_INT);
        $stmt->bindValue(':idOrganizador', $idOrganizador, PDO::PARAM_INT);

        $stmt->bindParam(':idEvento', $data['idEvento'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Eliminar evento
     */
    public function eliminar(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM Evento WHERE idEvento = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Listado para admin (aquí es donde lo usa tu gestor) */
    public function listarTodos($limit = 50, $offset = 0) {
        $query = "SELECT 
                    e.*,
                    est.nombre AS estado_nombre,
                    l.idLugar,
                    l.nombre AS lugar_nombre,
                    o.idOrganizador,
                    uo.nombre AS organizador_nombre
                  FROM " . $this->table_name . " e
                  LEFT JOIN Estado est ON est.idEstado = e.idEstadoEvento
                  LEFT JOIN Lugar l ON l.idLugar = e.idLugar
                  LEFT JOIN Organizador o ON o.idOrganizador = e.idOrganizador
                  LEFT JOIN Usuario uo ON uo.idUsuario = o.idUsuario
                  ORDER BY e.fechaInicio DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
