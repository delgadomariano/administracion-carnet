<?php
// Consulta a la API de miembros y normalización de los datos del carnet.

function http_get($url, $timeout)
{
    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'ignore_errors' => false]]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? null : $body;
}

// Devuelve los datos listos para imprimir en el carnet.
function miembro_buscar($dni, array $config)
{
    $json = http_get($config['api_miembro'] . rawurlencode($dni), $config['api_timeout']);
    if ($json === null) {
        throw new RuntimeException('No se pudo consultar la base de datos de miembros.', 502);
    }
    $datos = json_decode($json);
    if (!is_array($datos) || empty($datos[0]) || empty($datos[0]->name)) {
        throw new RuntimeException('No se encontró ningún miembro con ese documento.', 404);
    }
    $m = $datos[0];

    if (isset($m->isBishop) && $m->isBishop === 'SI') {
        $cargo = 'Obispo';
    } elseif (empty($m->priestType)) {
        $cargo = 'Grey';
    } else {
        $cargo = $m->priestType === 'S' ? 'Sacerdote' : 'Isis';
    }

    return [
        'nombre'     => trim($m->name),
        'registro'   => isset($m->registration) ? $m->registration : '',
        'dni'        => $dni,
        'cargo'      => $cargo,
        'misionero'  => isset($m->isMissionary) && $m->isMissionary === 'SI',
        'foto'       => isset($m->img_imagen) ? $m->img_imagen : '',
    ];
}

// Guarda la foto (base64 o URL) en un archivo temporal y devuelve [ruta, tipo]
// para FPDF, o null si no hay foto utilizable. Quien llama debe borrar el archivo.
function miembro_foto_temporal($foto, $timeout)
{
    $foto = trim($foto);
    if ($foto === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $foto)) {
        $binario = http_get($foto, $timeout);
    } else {
        $binario = base64_decode(preg_replace('#^data:image/[a-z]+;base64,#i', '', $foto), true);
    }
    if (!$binario) {
        return null;
    }

    if (strncmp($binario, "\xFF\xD8", 2) === 0) {
        $tipo = 'JPG';
    } elseif (strncmp($binario, "\x89PNG", 4) === 0) {
        $tipo = 'PNG';
    } else {
        return null;
    }

    // Carpeta propia: en hostings compartidos el temp del sistema suele estar fuera de open_basedir.
    $dir = __DIR__ . '/../tmp';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new RuntimeException('No se pudo crear la carpeta tmp/ (revisar permisos).');
    }
    $ruta = $dir . '/foto-' . md5(uniqid(mt_rand(), true)) . '.' . strtolower($tipo);
    if (@file_put_contents($ruta, $binario) === false) {
        throw new RuntimeException('No se pudo escribir en la carpeta tmp/ (revisar permisos).');
    }
    return [$ruta, $tipo];
}
