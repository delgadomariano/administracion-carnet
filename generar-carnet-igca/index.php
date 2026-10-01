<?php
// Único punto de entrada. Solo responde a enlaces firmados por la página que llama:
//   index.php?dni=...&tipo=ungido|misionero&exp=...&sig=...
// Ver LEEME.md para cómo generar el enlace.

$config = require __DIR__ . '/app/config.php';
require __DIR__ . '/app/token.php';
require __DIR__ . '/app/miembro.php';
require __DIR__ . '/app/carnet.php';

ini_set('display_errors', '0');
date_default_timezone_set($config['zona_horaria']);
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

function responder_error($e, $depurar)
{
    $codigo = $e instanceof RuntimeException && $e->getCode() >= 400 ? $e->getCode() : 500;
    if ($codigo === 500) {
        error_log('generar-carnet: ' . $e);
        $mensaje = 'No se pudo generar el carnet.';
        if ($depurar) {
            $mensaje .= ' ' . get_class($e) . ': ' . $e->getMessage()
                . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
        }
    } else {
        $mensaje = $e->getMessage();
    }
    http_response_code($codigo);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Carnet IGCA</title></head>'
        . '<body style="font-family:sans-serif;background:#ecf0f3;color:#333;display:flex;'
        . 'align-items:center;justify-content:center;min-height:100vh;margin:0">'
        . '<p style="background:#fff;padding:24px 32px;border-radius:10px;max-width:420px;text-align:center">'
        . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
}

$foto = null;
try {
    $pedido = carnet_validar_pedido($_GET, $config);
    $miembro = miembro_buscar($pedido['dni'], $config);
    $foto = miembro_foto_temporal($miembro['foto'], $config['api_timeout']);
    $pdf = carnet_generar($miembro, $pedido['tipo'], $foto);
    $pdf->Output('I', 'carnet-' . $pedido['dni'] . '.pdf');
} catch (Exception $e) {   // PHP 5.6
    responder_error($e, !empty($config['depurar']));
} catch (Throwable $e) {   // PHP 7+
    responder_error($e, !empty($config['depurar']));
}

if ($foto) {
    @unlink($foto[0]);
}
