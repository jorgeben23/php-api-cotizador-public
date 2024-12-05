<?php
namespace Escalabs\Cotizador\Controllers;

use Escalabs\Cotizador\Config\Database;
use Escalabs\Cotizador\Exceptions\DatabaseException;
use PDO;
use Exception;

class CotizadorController {
    private $conexion;

    public function __construct() {
        try {
            $this->conexion = Database::conectar();
        } catch (DatabaseException $e) {
            $e->logError();
            throw $e;
        }
    }

    public function listarAnalisis($nombreAnalisis) {
        try {
            $query = $this->conexion->prepare("SELECT IdAnalisis, Nombre, PrecioBase FROM analisis WHERE Nombre LIKE :nombre");
            $query->execute(['nombre' => "%{$nombreAnalisis}%"]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new DatabaseException("Error al listar análisis: " . $e->getMessage());
        }
    }

    public function obtenerDetalleAnalisis($idAnalisis) {
        try {
            $sql = $this->conexion->prepare("SELECT a.nombre, a.condiciones, a.volumen, a.tiempoProceso, a.conservacion, m.Descripcion as muestra
                FROM analisis a
                LEFT JOIN AreaProcesoAnalisis apa ON a.IdAnalisis = apa.IdAnalisis
                LEFT JOIN Muestra m ON apa.IdMuestra = m.IdMuestra
                WHERE a.IdAnalisis = :id");
            $sql->execute(['id' => $idAnalisis]);
            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new DatabaseException("Error al obtener detalle de análisis: " . $e->getMessage());
        }
    }

    public function listarPaquetes($nombre) {
        try {
            $query = $this->conexion->prepare("SELECT idPaquete, nombre, descripcion, precioOferta, precioReal, fechaInicio, fechaVencimiento, publicado, condiciones, ruta_icono
                FROM cotizador_paquetes
                WHERE nombre LIKE :nombre");
            $query->execute(['nombre' => "%{$nombre}%"]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new DatabaseException("Error al listar paquetes: " . $e->getMessage());
        }
    }

    public function obtenerDetallePaquete($idPaquete) {
        try {
            $query = $this->conexion->prepare("SELECT idPaquete, nombre, descripcion, precioOferta, precioReal, fechaInicio, fechaVencimiento, publicado
                FROM cotizador_paquetes
                WHERE idPaquete = :id");
            $query->execute(['id' => $idPaquete]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new DatabaseException("Error al obtener detalle del paquete: " . $e->getMessage());
        }
    }

    public function insertarPaquete($data, $icono) {
        try {
            $ruta = $this->procesarIcono($icono);
            $query = $this->conexion->prepare("INSERT INTO cotizador_paquetes (nombre, descripcion, precioOferta, precioReal, fechaInicio, fechaVencimiento, condiciones, ruta_icono)
                VALUES (:nombre, :descripcion, :precioOferta, :precioReal, :fechaInicio, :fechaVencimiento, :condiciones, :ruta)");
            $query->execute([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'precioOferta' => $data['precioOferta'],
                'precioReal' => $data['precioReal'],
                'fechaInicio' => $data['fechaInicio'],
                'fechaVencimiento' => $data['fechaVencimiento'],
                'condiciones' => $data['condiciones'],
                'ruta' => $ruta,
            ]);
            return $this->conexion->lastInsertId();
        } catch (Exception $e) {
            throw new DatabaseException("Error al insertar paquete: " . $e->getMessage());
        }
    }

    public function actualizarPaquete($data, $icono = null) {
        try {
            $ruta = null;
            if ($icono) {
                $ruta = $this->procesarIcono($icono);
            }
            $query = $this->conexion->prepare("UPDATE cotizador_paquetes SET
                nombre = :nombre,
                descripcion = :descripcion,
                precioOferta = :precioOferta,
                precioReal = :precioReal,
                fechaInicio = :fechaInicio,
                fechaVencimiento = :fechaVencimiento,
                condiciones = :condiciones" . ($ruta ? ", ruta_icono = :ruta" : "") . "
                WHERE idPaquete = :id");
            $params = [
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'precioOferta' => $data['precioOferta'],
                'precioReal' => $data['precioReal'],
                'fechaInicio' => $data['fechaInicio'],
                'fechaVencimiento' => $data['fechaVencimiento'],
                'condiciones' => $data['condiciones'],
                'id' => $data['idPaquete'],
            ];
            if ($ruta) $params['ruta'] = $ruta;
            $query->execute($params);
            return $query->rowCount();
        } catch (Exception $e) {
            throw new DatabaseException("Error al actualizar paquete: " . $e->getMessage());
        }
    }

    public function eliminarPaquete($idPaquete) {
        try {
            $this->eliminarAnalisisPorPaquete($idPaquete);
            $query = $this->conexion->prepare("DELETE FROM cotizador_paquetes WHERE idPaquete = :id");
            $query->execute(['id' => $idPaquete]);
            return $query->rowCount();
        } catch (Exception $e) {
            throw new DatabaseException("Error al eliminar paquete: " . $e->getMessage());
        }
    }

    private function procesarIcono($icono) {
        try {
            $ruta = 'public/iconos/' . $icono['name']; 
            if (move_uploaded_file($icono['tmp_name'], $ruta)) {
                chmod($ruta, 0777);
                return '/iconos/' . $icono['name']; 
            } else {
                throw new Exception("Error al procesar el ícono.");
            }
        } catch (Exception $e) {
            throw new DatabaseException("Error al procesar ícono: " . $e->getMessage());
        }
    }

    private function eliminarAnalisisPorPaquete($idPaquete) {
        try {
            $query = $this->conexion->prepare("DELETE FROM cotizador_paquete_analisis WHERE idPaquete = :id");
            $query->execute(['id' => $idPaquete]);
        } catch (Exception $e) {
            throw new DatabaseException("Error al eliminar análisis del paquete: " . $e->getMessage());
        }
    }
}
