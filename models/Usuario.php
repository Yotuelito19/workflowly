<?php
/**
 * Modelo Usuario
 * Gestiona las operaciones CRUD de usuarios
 */

class Usuario {
    private $conn;
    private $table_name = "Usuario";

    // Propiedades
    public $idUsuario;
    public $nombre;
    public $apellidos;
    public $email;
    public $password;
    public $telefono;
    public $fechaNacimiento;
    public $fechaRegistro;
    public $tipoUsuario;
    public $idEstadoUsuario;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registrar un nuevo usuario
     */
    public function registrar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, apellidos, email, password, telefono, fechaNacimiento, tipoUsuario, idEstadoUsuario) 
                  VALUES (:nombre, :apellidos, :email, :password, :telefono, :fechaNacimiento, :tipoUsuario, :idEstado)";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos de entrada
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellidos = htmlspecialchars(strip_tags($this->apellidos));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->fechaNacimiento = htmlspecialchars(strip_tags($this->fechaNacimiento));


        // Hash de contraseña
        $password_hash = password_hash($this->password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);

        // Asignar valores a la consulta
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellidos", $this->apellidos);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":fechaNacimiento", $this->fechaNacimiento);
        $stmt->bindParam(":tipoUsuario", $this->tipoUsuario);
        $stmt->bindParam(":idEstado", $this->idEstadoUsuario);

        if ($stmt->execute()) {
            $this->idUsuario = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Login de usuario
     */
    public function login($email, $password) {
        $query = "SELECT u.*, e.nombre as estado_nombre 
                  FROM " . $this->table_name . " u
                  INNER JOIN Estado e ON u.idEstadoUsuario = e.idEstado
                  WHERE u.email = :email 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                // Verificar que el usuario esté activo
                if ($row['estado_nombre'] !== 'Activo') {
                    return ['success' => false, 'message' => 'Usuario inactivo'];
                }

                // Guardar datos en sesión
                $_SESSION['user_id'] = $row['idUsuario'];
                $_SESSION['user_name'] = $row['nombre'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_type'] = $row['tipoUsuario'];
                $_SESSION['last_activity'] = time();

                return ['success' => true, 'user' => $row];
            } else {
                return ['success' => false, 'message' => 'Contraseña incorrecta'];
            }
        }

        return ['success' => false, 'message' => 'Usuario no encontrado'];
    }

    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email) {
        $query = "SELECT idUsuario FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT u.*, e.nombre as estado_nombre 
                  FROM " . $this->table_name . " u
                  INNER JOIN Estado e ON u.idEstadoUsuario = e.idEstado
                  WHERE u.idUsuario = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return null;
    }

    /**
     * Actualizar perfil de usuario
     */
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, 
                      apellidos = :apellidos, 
                      telefono = :telefono
                  WHERE idUsuario = :id";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellidos = htmlspecialchars(strip_tags($this->apellidos));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellidos", $this->apellidos);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":id", $this->idUsuario);

        return $stmt->execute();
    }

    /**
     * Cambiar contraseña
     */
    public function actualizarPassword() {
    $query = "UPDATE " . $this->table_name . "
              SET password = :password
              WHERE idUsuario = :idUsuario";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":password", $this->password);
    $stmt->bindParam(":idUsuario", $this->idUsuario);

    return $stmt->execute();
}
}
?>