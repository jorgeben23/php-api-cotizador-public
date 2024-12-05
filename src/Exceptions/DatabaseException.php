<?php
namespace Escalabs\Cotizador\Exceptions;

use Exception;
use Throwable;

class DatabaseException extends Exception {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function logError() {
        // errores unicamente de la base de datos
        error_log($this->__toString());
    }
}