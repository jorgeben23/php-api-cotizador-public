<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Escalabs\Cotizador\Controllers\AnalisisController;
use Escalabs\Cotizador\Exceptions\DatabaseException;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

header("Content-Type: application/json");

try {
    $controlador = new AnalisisController();

    $opcion = $_POST['funcion'] ?? null;
    $respuesta = [];

    switch ($opcion) {
        case 'listar':
            $analisis = $_POST['analisis'] ?? '';
            $respuesta = $controlador->listarAnalisis($analisis);
            break;

        case 'detalle':
            $idAnalisis = $_POST['idAnalisis'] ?? null;
            if ($idAnalisis) {
                $respuesta = $controlador->obtenerDetalleAnalisis($idAnalisis);
            } else {
                $respuesta = ['error' => 'no tiene el id del analisis'];
            }
            break;

        case 'listPaquetes':
            $nombrePaquete = $_POST['nombre'] ?? '';
            $respuesta = $controlador->listarPaquetes($nombrePaquete);
            break;

        case 'listOnePaquetes':
            $idPaquete = $_POST['idPaquete'] ?? null;
            if ($idPaquete) {
                $respuesta = $controlador->obtenerDetallePaquete($idPaquete);
            } else {
                $respuesta = ['error' => 'no tiene el id del paquete'];
            }
            break;

        case 'insertPaquete':
            $data = $_POST;  
            $icono = $_FILES['icono'] ?? null;
            $respuesta = $controlador->insertarPaquete($data, $icono);
            break;

        case 'updatePaquete':
            $data = $_POST;
            $icono = $_FILES['icono'] ?? null;
            $respuesta = $controlador->actualizarPaquete($data, $icono);
            break;

        case 'updatePaquetePublicado':
            $idPaquete = $_POST['idPaquete'] ?? null;
            if ($idPaquete) {
                $respuesta = $controlador->actualizarPaquetePublicado($idPaquete);
            } else {
                $respuesta = ['error' => 'no tiene el id del paquete'];
            }
            break;

        case 'updatePaquetePrecioReal':
            $idPaquete = $_POST['idPaquete'] ?? null;
            $precioReal = $_POST['precioReal'] ?? null;
            if ($idPaquete && $precioReal !== null) {
                $respuesta = $controlador->actualizarPaquetePrecioReal($idPaquete, $precioReal);
            } else {
                $respuesta = ['error' => 'Faltan parámetros'];
            }
            break;

        case 'insertAnalisisPaquete':
            $idAnalisis = $_POST['idAnalisis'] ?? null;
            $idPaquete = $_POST['idPaquete'] ?? null;
            $descAnalisis = $_POST['desc_analisis'] ?? '';
            $precioAnalisis = $_POST['precio_analisis'] ?? null;
            if ($idAnalisis && $idPaquete) {
                $respuesta = $controlador->insertarAnalisisPaquete($idAnalisis, $idPaquete, $descAnalisis, $precioAnalisis);
            } else {
                $respuesta = ['error' => 'Faltan parámetros'];
            }
            break;

        case 'listAnalisisByPaquete':
            $idPaquete = $_POST['idPaquete'] ?? null;
            if ($idPaquete) {
                $respuesta = $controlador->listarAnalisisPorPaquete($idPaquete);
            } else {
                $respuesta = ['error' => 'no tiene el id del paquete'];
            }
            break;

        case 'DeletePaquete':
            $idPaquete = $_POST['idPaquete'] ?? null;
            if ($idPaquete) {
                $respuesta = $controlador->eliminarPaquete($idPaquete);
            } else {
                $respuesta = ['error' => 'no tiene el id  del paquete'];
            }
            break;

        default:
            $respuesta = ['error' => 'Funcion no definida'];
            break;
    }

    echo json_encode($respuesta);

} catch (DatabaseException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la base de datos',
        'mensaje' => $e->getMessage()
    ]);
    $e->logError();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'mensaje' => $e->getMessage()
    ]);
}
