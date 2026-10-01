<?php
// Firma y verificación de los enlaces que habilitan generar un carnet.
//
// El enlace lleva: dni, tipo, exp (unix timestamp de vencimiento) y
// sig = HMAC-SHA256(secreto, "dni|tipo|exp") en hexadecimal.

const TIPOS_CARNET = ['ungido', 'misionero'];

function carnet_firmar($dni, $tipo, $exp, $secreto)
{
    return hash_hmac('sha256', $dni . '|' . $tipo . '|' . $exp, $secreto);
}

// Útil para quien llame desde PHP: devuelve la query string lista para usar.
function carnet_query_firmada($dni, $tipo, $secreto, $ttl = 300)
{
    $exp = time() + $ttl;
    return http_build_query([
        'dni'  => $dni,
        'tipo' => $tipo,
        'exp'  => $exp,
        'sig'  => carnet_firmar($dni, $tipo, $exp, $secreto),
    ]);
}

// Devuelve ['dni' => ..., 'tipo' => ...] o lanza RuntimeException con el código HTTP.
function carnet_validar_pedido(array $q, array $config)
{
    $dni  = isset($q['dni'])  ? trim($q['dni'])  : '';
    $tipo = isset($q['tipo']) ? trim($q['tipo']) : '';
    $exp  = isset($q['exp'])  ? trim($q['exp'])  : '';
    $sig  = isset($q['sig'])  ? strtolower(trim($q['sig'])) : '';

    if (!preg_match('/^[A-Za-z0-9]{4,20}$/', $dni)
        || !in_array($tipo, TIPOS_CARNET, true)
        || !ctype_digit($exp)
        || !preg_match('/^[0-9a-f]{64}$/', $sig)) {
        throw new RuntimeException('Enlace inválido.', 400);
    }

    if (!hash_equals(carnet_firmar($dni, $tipo, $exp, $config['secreto']), $sig)) {
        throw new RuntimeException('Enlace no autorizado.', 403);
    }

    $ahora = time();
    if ((int)$exp < $ahora) {
        throw new RuntimeException('El enlace venció. Volvé a generarlo desde la administración.', 403);
    }
    if ((int)$exp > $ahora + $config['ttl_maximo']) {
        throw new RuntimeException('Enlace no autorizado.', 403);
    }

    if (!empty($config['origenes_permitidos']) && !empty($_SERVER['HTTP_REFERER'])) {
        $host = strtolower((string)parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST));
        if (!in_array($host, $config['origenes_permitidos'], true)) {
            throw new RuntimeException('Origen no autorizado (' . ($host === '' ? 'desconocido' : $host) . ').', 403);
        }
    }

    return ['dni' => $dni, 'tipo' => $tipo];
}
