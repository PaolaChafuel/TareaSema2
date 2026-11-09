<?php
declare(strict_types=1);
session_start();
require_once dirname(__DIR__) . '/app/ClienteRepository.php';

$repo = new ClienteRepository(dirname(__DIR__) . '/data/clientes.json');
function e(?string $texto): string { return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function irInicio(): never { header('Location: /'); exit; }
function avisar(string $mensaje, string $tipo = 'ok'): void { $_SESSION['flash'] = compact('mensaje', 'tipo'); }
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$errores = [];
$valores = ['nombre' => '', 'apellido' => '', 'correo' => '', 'telefono' => '', 'direccion' => ''];
$modoEditar = false;
$idEdicion = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403); die('Solicitud inválida. Actualiza la página.');
    }
    $accion = (string) ($_POST['accion'] ?? '');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    try {
        if ($accion === 'eliminar') {
            if (!$id) throw new DomainException('Selecciona un cliente válido.');
            avisar($repo->eliminar($id) ? 'Cliente eliminado correctamente.' : 'No se encontró el cliente.', 'ok');
            irInicio();
        }
        if (!in_array($accion, ['crear', 'actualizar'], true)) throw new DomainException('Acción no permitida.');
        $modoEditar = ($accion === 'actualizar');
        $idEdicion = $modoEditar && $id ? $id : null;
        if ($modoEditar && !$idEdicion) throw new DomainException('Identificador inválido.');
        foreach (array_keys($valores) as $campo) $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
        $cliente = Cliente::desdeArray(
    array_merge(['id' => $idEdicion], $valores)
   );
        $errores = $cliente->validar();
        if (!$errores) {
            if ($modoEditar) {
                if (!$repo->actualizar($cliente)) throw new DomainException('El cliente ya no existe.');
                avisar('Cliente actualizado correctamente.');
            } else {
                $repo->crear($cliente);
                avisar('Cliente creado correctamente.');
            }
            irInicio();
        }
    } catch (DomainException $ex) {
        $errores[] = $ex->getMessage();
    }
} elseif (isset($_GET['editar'])) {
    $idEdicion = filter_var($_GET['editar'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $cliente = $idEdicion ? $repo->buscarPorId($idEdicion) : null;
    if ($cliente !== null) {
        $valores = $cliente->toArray();
        $modoEditar = true;
    } else {
        avisar('No se encontró el cliente.', 'error');
        irInicio();
    }
}

$clientes = $repo->listar();
$busqueda = trim((string) ($_GET['buscar'] ?? ''));
if ($busqueda !== '') {
    $clientes = array_values(array_filter($clientes, function (Cliente $c) use ($busqueda): bool {
        return stripos($c->getNombreCompleto() . ' ' . $c->getCorreo() . ' ' . $c->getTelefono(), $busqueda) !== false;
    }));
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Proyecto PHP de Programación Orientada a Objetos: CRUD de clientes">
  <title>CRUD de Clientes | PHP y POO</title>
  <link rel="stylesheet" href="/assets/estilos.css">
</head>
<body>
<header class="encabezado">
  <div class="contenedor barra"><div class="marca"><span class="logo">{ }</span><div>Mi proyecto <span>PHP + POO</span></div></div><span class="etiqueta">HTML · CSS · PHP</span></div>
</header>
<main class="contenedor">
  <section class="hero"><div class="eyebrow">TAREA DE APLICACIONES WEB</div><span> Crear</span><span> Consultar</span><span> Actualizar</span><span> Eliminar</span></div></section>
  <?php if ($flash): ?><div class="alerta <?= $flash['tipo'] === 'error' ? 'error' : 'ok' ?>" role="status"><?= e($flash['mensaje']) ?></div><?php endif; ?>
  <?php if ($errores): ?><div class="alerta error" role="alert"><strong>Revisa los siguientes datos:</strong><ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div class="columnas">
    <section class="panel formulario" id="formulario"><div class="panel-titulo"><span class="icono">✎</span><div><h2><?= $modoEditar ? 'Editar cliente' : 'Nuevo cliente' ?></h2><p><?= $modoEditar ? 'Modifica la información del registro.' : 'Completa los datos para agregar un registro.' ?></p></div></div>
      <form method="post" action="/#formulario">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="accion" value="<?= $modoEditar ? 'actualizar' : 'crear' ?>">
        <?php if ($modoEditar): ?><input type="hidden" name="id" value="<?= (int) $idEdicion ?>"><?php endif; ?>
        <div class="campo"><label for="nombre">Nombre <span>*</span></label><input id="nombre" name="nombre" type="text" value="<?= e($valores['nombre']) ?>" maxlength="80" required placeholder="Ej.: Ana"></div>
        <div class="campo"><label for="apellido">Apellido <span>*</span></label><input id="apellido" name="apellido" type="text" value="<?= e($valores['apellido']) ?>" maxlength="80" required placeholder="Ej.: Pérez"></div>
        <div class="campo"><label for="correo">Correo electrónico <span>*</span></label><input id="correo" name="correo" type="email" value="<?= e($valores['correo']) ?>" maxlength="150" required placeholder="ana@ejemplo.com"></div>
        <div class="campo"><label for="telefono">Teléfono</label><input id="telefono" name="telefono" type="tel" value="<?= e($valores['telefono']) ?>" maxlength="25" placeholder="0999999999"></div>
        <div class="campo"><label for="direccion">Dirección</label><textarea id="direccion" name="direccion" maxlength="200" rows="3" placeholder="Ciudad, dirección de domicilio"><?= e($valores['direccion']) ?></textarea></div>
        <button class="boton primario" type="submit"><?= $modoEditar ? 'Guardar cambios' : '+ Crear cliente' ?></button>
        <?php if ($modoEditar): ?><a class="boton secundario" href="/">Cancelar edición</a><?php endif; ?>
      </form>
    </section>
    <section class="panel listado"><div class="listado-titulo"><div><h2>Clientes registrados</h2><p>Consulta, modifica o elimina cada registro.</p></div><span class="contador"><?= count($clientes) ?> registros</span></div>
      <form class="buscar" method="get" action="/"><input name="buscar" value="<?= e($busqueda) ?>" aria-label="Buscar clientes" placeholder="Buscar por nombre, correo o teléfono..."><button class="boton secundario" type="submit">Buscar</button><?php if ($busqueda !== ''): ?><a class="limpiar" href="/">Limpiar</a><?php endif; ?></form>
      <div class="tabla-contenedor"><table><thead><tr><th>Cliente</th><th>Correo y teléfono</th><th>Acciones</th></tr></thead><tbody>
      <?php if (!$clientes): ?><tr><td colspan="3" class="sin-registros">No hay clientes <?= $busqueda !== '' ? 'que coincidan con la búsqueda' : 'registrados. Crea el primero usando el formulario' ?>.</td></tr><?php endif; ?>
      <?php foreach ($clientes as $c): ?><tr><td><div class="cliente-nombre"><?= e($c->getNombreCompleto()) ?></div><small>ID #<?= $c->getId() ?></small><?php if ($c->getDireccion() !== ''): ?><small class="direccion"><?= e($c->getDireccion()) ?></small><?php endif; ?></td><td><div><?= e($c->getCorreo()) ?></div><small><?= e($c->getTelefono() ?: 'Sin teléfono') ?></small></td><td class="acciones"><a class="boton editar" href="/?editar=<?= $c->getId() ?>#formulario">Editar</a><form method="post" action="/" class="inline" onsubmit="return confirm('¿Eliminar a este cliente? Esta acción no se puede deshacer.')"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= $c->getId() ?>"><button class="boton borrar" type="submit">Eliminar</button></form></td></tr><?php endforeach; ?>
      </tbody></table></div>
    </section>
  </div>
