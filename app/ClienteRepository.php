<?php
declare(strict_types=1);
require_once __DIR__ . '/Cliente.php';

/** Clase CRUD. Persistencia local con JSON: no requiere MySQL. */
final class ClienteRepository
{
    public function __construct(private string $ruta)
    {
        if (!is_dir(dirname($ruta))) mkdir(dirname($ruta), 0775, true);
        if (!file_exists($ruta)) file_put_contents($ruta, "[]\n");
    }

    /** READ: retorna todos los clientes. @return Cliente[] */
    public function listar(): array
    {
        $datos = $this->leer();
        usort($datos, fn(array $a, array $b): int => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));
        return array_map(fn(array $fila): Cliente => Cliente::desdeArray($fila), $datos);
    }

    /** READ: busca un cliente por su identificador. */
    public function buscarPorId(int $id): ?Cliente
    {
        foreach ($this->leer() as $fila) {
            if ((int) $fila['id'] === $id) return Cliente::desdeArray($fila);
        }
        return null;
    }

    /** CREATE: guarda un objeto Cliente y devuelve su nuevo id. */
    public function crear(Cliente $cliente): int
    {
        return $this->modificarArchivo(function (array &$datos) use ($cliente): int {
            $this->verificarCorreo($datos, $cliente->getCorreo());
            $id = (int) max(array_column($datos, 'id') ?: [0]) + 1;
            $cliente->asignarId($id);
            $datos[] = $cliente->toArray();
            return $id;
        });
    }

    /** UPDATE: modifica un cliente existente. */
    public function actualizar(Cliente $cliente): bool
    {
        return $this->modificarArchivo(function (array &$datos) use ($cliente): bool {
            $id = $cliente->getId();
            $this->verificarCorreo($datos, $cliente->getCorreo(), $id);
            foreach ($datos as &$fila) {
                if ((int) $fila['id'] === $id) {
                    $fila = $cliente->toArray();
                    return true;
                }
            }
            return false;
        });
    }

    /** DELETE: elimina un cliente existente. */
    public function eliminar(int $id): bool
    {
        return $this->modificarArchivo(function (array &$datos) use ($id): bool {
            foreach ($datos as $indice => $fila) {
                if ((int) $fila['id'] === $id) {
                    array_splice($datos, $indice, 1);
                    return true;
                }
            }
            return false;
        });
    }

    private function leer(): array
    {
        $contenido = file_get_contents($this->ruta);
        $datos = json_decode($contenido === false ? '[]' : $contenido, true);
        if (!is_array($datos)) throw new RuntimeException('El archivo de datos no contiene JSON válido.');
        return $datos;
    }

    /** Bloqueo exclusivo para evitar sobrescrituras entre peticiones. */
    private function modificarArchivo(callable $operacion): mixed
    {
        $archivo = fopen($this->ruta, 'c+');
        if ($archivo === false) throw new RuntimeException('No se puede abrir el almacenamiento.');
        try {
            if (!flock($archivo, LOCK_EX)) throw new RuntimeException('No se pudo bloquear el archivo.');
            rewind($archivo);
            $contenido = stream_get_contents($archivo);
            $datos = json_decode($contenido ?: '[]', true);
            if (!is_array($datos)) throw new RuntimeException('El archivo de datos está dañado.');
            $resultado = $operacion($datos);
            rewind($archivo);
            if (!ftruncate($archivo, 0)) throw new RuntimeException('No se pudo escribir los datos.');
            fwrite($archivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            fflush($archivo);
            flock($archivo, LOCK_UN);
            return $resultado;
        } finally {
            fclose($archivo);
        }
    }

    private function verificarCorreo(array $datos, string $correo, ?int $exceptoId = null): void
    {
        foreach ($datos as $fila) {
            if (strcasecmp((string) $fila['correo'], $correo) === 0 && (int) $fila['id'] !== $exceptoId) {
                throw new DomainException('Ya existe un cliente registrado con ese correo electrónico.');
            }
        }
    }
}
