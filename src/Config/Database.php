<?php
namespace Escalabs\Cotizador\Config;

use PDO;
use PDOException;
use Escalabs\Cotizador\Exceptions\DatabaseException;

class Database {
    private static $conexion = null;

    public static function conectar() {
        if (self::$conexion === null) {
            try {
                $host = $_ENV['DB_HOST'];
                $dbname = $_ENV['DB_NAME'];
                $usuario = $_ENV['DB_USUARIO'];
                $password = $_ENV['DB_PASSWORD'];
                $puerto = $_ENV['DB_PUERTO'];

                $dsn = "mysql:host={$host};port={$puerto};dbname={$dbname};charset=utf8mb4";
                $opciones = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$conexion = new PDO($dsn, $usuario, $password, $opciones);
            } catch (PDOException $e) {
                throw new DatabaseException("Error de conexión: " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        return self::$conexion;
    }
}