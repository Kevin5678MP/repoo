<?php

class GestorDeArchivo {
    private $ubicacion;
    private $modoAcceso;
    private $tamano;

    public function __construct($ubicacion, $modoAcceso = 'r+b') {
        $this->ubicacion = $ubicacion;
        $this->modoAcceso = $modoAcceso;

        if (!file_exists($ubicacion) && strpos($modoAcceso, 'r') !== false) {
            throw new Exception("Archivo no encontrado: {$ubicacion}");
        }

        $this->actualizarTamano();
    }

    private function abrir() {
        $archivo = fopen($this->ubicacion, $this->modoAcceso);
        if (!$archivo) {
            throw new Exception("No se puede abrir el archivo: {$this->ubicacion}");
        }
        return $archivo;
    }

    private function cerrar($archivo) {
        fclose($archivo);
    }

    public function leer($bytes, $desde = null) {
        $archivo = $this->abrir();
        if ($desde !== null) {
            fseek($archivo, $desde);
        }
        $datos = fread($archivo, $bytes);
        $this->cerrar($archivo);

        return $datos;
    }

    public function escribir($contenido, $posicion = null, $sobrescribir = false) {
        $archivo = $this->abrir();

        if ($posicion !== null) {
            fseek($archivo, $posicion);
        } elseif (!$sobrescribir) {
            fseek($archivo, 0, SEEK_END);
        }

        fwrite($archivo, $contenido);
        $this->cerrar($archivo);
        $this->actualizarTamano();
    }

    public function insertar($contenido, $posicion) {
        $archivo = $this->abrir();
        fseek($archivo, $posicion);

        $contenidoRestante = stream_get_contents($archivo);
        ftruncate($archivo, $posicion);
        fseek($archivo, $posicion);

        fwrite($archivo, $contenido);
        fwrite($archivo, $contenidoRestante);

        $this->cerrar($archivo);
        $this->actualizarTamano();
    }

    public function obtenerTamano() {
        return $this->tamano;
    }

    private function actualizarTamano() {
        $this->tamano = file_exists($this->ubicacion) ? filesize($this->ubicacion) : 0;
    }
}

try {
    $nombreArchivo = 'archivoEjemplo.txt';
    $archivo = new GestorDeArchivo($nombreArchivo, file_exists($nombreArchivo) ? 'r+b' : 'w+b');
    echo "Contenido inicial: " . $archivo->leer(12) . "\n";
    $textoParaInsertar = " impresionante";
    $posicion = 5;
    $archivo->insertar($textoParaInsertar, $posicion);
    echo "Contenido actualizado: " . $archivo->leer(50) . "\n";
    echo "Tamaño del archivo: " . $archivo->obtenerTamano() . " bytes\n";
} catch (Exception $excepcion) {
    echo "Ocurrió un error: " . $excepcion->getMessage() . "\n";
}

?>
