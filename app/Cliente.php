<?php
declare(strict_types=1);

/** Entidad Cliente: representa un objeto del negocio. */
final class Cliente
{
    public function __construct(
        private ?int $id,
        private string $nombre,
        private string $apellido,
        private string $correo,
        private string $telefono,
        private string $direccion
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getCorreo(): string { return $this->correo; }
    public function getTelefono(): string { return $this->telefono; }
    public function getDireccion(): string { return $this->direccion; }
    public function getNombreCompleto(): string { return $this->nombre . ' ' . $this->apellido; }

    public function asignarId(int $id): void { $this->id = $id; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
        ];
    }

    public static function desdeArray(array $datos): self
    {
        return new self(
            isset($datos['id']) ? (int) $datos['id'] : null,
            trim((string) ($datos['nombre'] ?? '')),
            trim((string) ($datos['apellido'] ?? '')),
            trim((string) ($datos['correo'] ?? '')),
            trim((string) ($datos['telefono'] ?? '')),
            trim((string) ($datos['direccion'] ?? ''))
        );
    }

    /** Devuelve mensajes claros para datos incorrectos. */
    public function validar(): array
    {
        $errores = [];
        if ($this->nombre === '' || strlen($this->nombre) > 80) $errores[] = 'El nombre es obligatorio y debe tener máximo 80 caracteres.';
        if ($this->apellido === '' || strlen($this->apellido) > 80) $errores[] = 'El apellido es obligatorio y debe tener máximo 80 caracteres.';
        if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL) || strlen($this->correo) > 150) $errores[] = 'Ingresa un correo electrónico válido.';
        if ($this->telefono !== '' && (!preg_match('/^[+0-9()\-\s]{6,25}$/', $this->telefono))) $errores[] = 'El teléfono debe contener entre 6 y 25 caracteres válidos.';
        if (strlen($this->direccion) > 200) $errores[] = 'La dirección debe tener máximo 200 caracteres.';
        return $errores;
    }
}
