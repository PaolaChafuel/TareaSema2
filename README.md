# CRUD de Clientes con PHP y POO

Página web funcional en **HTML, CSS y PHP** que utiliza **programación orientada a objetos (POO)**.

## Ejecutar en Visual Studio Code (Windows)

1. Descomprime `CRUD_Clientes_POO.zip`.
2. En Visual Studio Code, selecciona **Archivo → Abrir carpeta** y elige `CRUD_Clientes_POO`.
3. Si tienes XAMPP en `C:\xampp`, abre **Terminal → Ejecutar tarea... → Iniciar CRUD PHP (XAMPP)**. No hace falta iniciar Apache ni MySQL.
4. Abre **http://localhost:8000** en el navegador.
5. Para detener el servidor, usa **Ctrl + C** en la terminal de VS Code.

**Alternativa:** Si `php` funciona en la terminal, elige la tarea `Iniciar CRUD PHP (PHP en PATH)`.
También puedes hacer doble clic en `INICIAR_XAMPP.bat` si usas Windows y XAMPP está en la ruta indicada.

**Atención:** no se ejecuta con Live Server de VS Code: PHP necesita su propio servidor.

## Estructura

```text
CRUD_Clientes_POO/
├── app/
│   ├── Cliente.php             ← Clase Cliente, atributos, constructor, getters y validación
│   └── ClienteRepository.php   ← Métodos CRUD y persistencia
├── public/
│   ├── index.php               ← Interfaz, formularios y acciones
│   └── assets/estilos.css      ← Diseño HTML/CSS adaptable
├── data/clientes.json         ← Datos guardados entre ejecuciones
├── .vscode/tasks.json         ← Tareas para ejecutar con PHP
├── INICIAR_XAMPP.bat
└── README.md
```

## ¿Cómo funciona la POO?

- `Cliente` es una **clase** con propiedades privadas: `id`, `nombre`, `apellido`, `correo`, `telefono`, `direccion`.
- `new Cliente(...)` crea un **objeto Cliente**.
- `ClienteRepository` tiene los **métodos CRUD**:
  - **CREATE**: `crear(Cliente $cliente)`.
  - **READ**: `listar()` y `buscarPorId(int $id)`.
  - **UPDATE**: `actualizar(Cliente $cliente)`.
  - **DELETE**: `eliminar(int $id)`.
- Los datos se guardan automáticamente en `data/clientes.json`; **no requiere base de datos ni dependencias externas**.

## Prueba rápida

1. Ingresa un nombre, apellido y correo y pulsa **Crear cliente**.
2. Comprueba que aparece en la tabla.
3. Usa **Editar** y cambia el teléfono.
4. Recarga la página: los cambios siguen ahí.
5. Usa **Eliminar** para borrar el cliente.
6. Prueba a registrar dos clientes con el mismo correo: se muestra un mensaje de validación.

## Requisitos

- PHP 8.0 o superior (XAMPP incluye PHP en Windows); se recomienda PHP 8.1+.
- Navegador web moderno.

## Nota académica

Se utiliza **almacenamiento JSON local**, ideal para ejecutar y presentar un CRUD sin instalar MySQL. Si la rúbrica exige explícitamente **MySQL**, habría que adaptar `ClienteRepository.php` con PDO y preparar la base de datos.
