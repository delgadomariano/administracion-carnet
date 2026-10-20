<?php
// Lanzador SOLO para pruebas locales con "php -S" (lo abre iniciar-local.bat).
// Arma el enlace firmado que normalmente genera la administración y abre el carnet.
// En el servidor real no funciona (responde 404) y el Implementador no lo sube por FTP.

$remoto = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
if (PHP_SAPI !== 'cli-server' || !in_array($remoto, ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

$config = require __DIR__ . '/app/config.php';
require __DIR__ . '/app/token.php';

// Sin Referer: index.php rechaza orígenes que no estén en origenes_permitidos.
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

$dni = isset($_POST['dni']) ? trim($_POST['dni']) : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'ungido';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('/^[A-Za-z0-9]{4,20}$/', $dni)) {
        $error = 'El DNI debe tener entre 4 y 20 letras o números, sin puntos ni espacios.';
    } elseif (!in_array($tipo, TIPOS_CARNET, true)) {
        $error = 'Tipo de carnet inválido.';
    } else {
        $ttl = min(300, (int)$config['ttl_maximo']);
        header('Location: index.php?' . carnet_query_firmada($dni, $tipo, $config['secreto'], $ttl));
        exit;
    }
}

$avisos = [];
if (strpos($config['api_miembro'], 'https:') === 0 && !extension_loaded('openssl')) {
    $avisos[] = 'PHP no tiene la extensión openssl: no va a poder consultar la API de miembros. Abrí el lanzador con iniciar-local.bat.';
}
if (empty($config['depurar'])) {
    $avisos[] = 'El modo depurar está apagado en app/config.php: si falla, el error se ve sin detalle (el detalle queda en esta ventana de PHP).';
}

function e($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es" translate="no" class="notranslate">
<head>
<meta charset="UTF-8">
<meta name="google" content="notranslate">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="no-referrer">
<title>Carnet IGCA · local</title>
<style>
  body { font-family: "Segoe UI", system-ui, sans-serif; background: #ecf0f3; color: #263238; margin: 0;
         min-height: 100vh; display: flex; align-items: center; justify-content: center; }
  .caja { background: #fff; border-radius: 12px; padding: 28px 32px; width: min(420px, 92vw); box-shadow: 0 4px 20px rgba(0,0,0,.08); }
  h1 { font-size: 20px; margin: 0 0 4px; }
  .sub { color: #78909c; font-size: 13px; margin: 0 0 20px; }
  label { display: block; font-size: 13px; color: #546e7a; margin: 14px 0 6px; }
  input[type=text] { width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 16px; border: 1px solid #cfd8dc; border-radius: 8px; }
  .tipos { display: flex; gap: 10px; }
  .tipos label { flex: 1; margin: 0; border: 1px solid #cfd8dc; border-radius: 8px; padding: 10px; color: #263238; cursor: pointer; font-size: 14px; }
  .tipos input { margin-right: 6px; }
  button { margin-top: 22px; width: 100%; padding: 12px; font-size: 15px; font-weight: 600; color: #fff; background: #1e63d6; border: 0; border-radius: 8px; cursor: pointer; }
  .error { background: #fde6e6; color: #b71c1c; padding: 10px 12px; border-radius: 8px; font-size: 14px; margin-bottom: 8px; }
  .aviso { background: #fff4e0; color: #8a5300; padding: 8px 12px; border-radius: 8px; font-size: 12.5px; margin-top: 8px; }
  .pie { color: #90a4ae; font-size: 12px; margin-top: 18px; }
</style>
</head>
<body>
<form class="caja" method="post" target="_blank" rel="noreferrer">
  <h1>Generar carnet IGCA</h1>
  <p class="sub">Prueba local · el enlace se firma acá y vence en 5 minutos.</p>
  <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <label for="dni">DNI del miembro</label>
  <input type="text" id="dni" name="dni" value="<?= e($dni) ?>" autofocus autocomplete="off" required>
  <label>Tipo de carnet</label>
  <div class="tipos">
    <label><input type="radio" name="tipo" value="ungido" <?= $tipo !== 'misionero' ? 'checked' : '' ?>>Ungido</label>
    <label><input type="radio" name="tipo" value="misionero" <?= $tipo === 'misionero' ? 'checked' : '' ?>>Misionero</label>
  </div>
  <button type="submit">Generar PDF</button>
  <?php foreach ($avisos as $a): ?><div class="aviso"><?= e($a) ?></div><?php endforeach; ?>
  <p class="pie">PHP <?= e(PHP_VERSION) ?> · el PDF se abre en otra pestaña.</p>
</form>
</body>
</html>
