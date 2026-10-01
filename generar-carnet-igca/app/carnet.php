<?php
// Armado del PDF del carnet sobre las plantillas de frente y dorso.
// Las coordenadas (mm) están calibradas contra las plantillas: no cambiarlas
// sin volver a probar la impresión.

use setasign\Fpdi\Fpdi;

require_once __DIR__ . '/../lib/fpdf181/fpdf.php';
require_once __DIR__ . '/../lib/fpdi2/src/autoload.php';

const CARNET_X = 219;       // columna de texto y foto en el frente
const CARNET_Y = 9.8;       // borde superior de la foto
const CARNET_INTERLINEA = 3;

// FPDF con fuentes core trabaja en Windows-1252.
function pdf_texto($s)
{
    if (function_exists('iconv')) {
        $r = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
        if ($r !== false) {
            return $r;
        }
    }
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
    }
    return function_exists('utf8_decode') ? utf8_decode($s) : $s;
}

function carnet_generar(array $m, $tipo, $foto)
{
    $sufijo = $tipo === 'misionero' ? '-m' : '';
    $pdf = new Fpdi();
    $pdf->SetTitle(pdf_texto('Carnet ' . $m['nombre']));
    $pdf->SetTextColor(0, 0, 0);

    // Frente
    $pdf->AddPage('L', 'A4', 270);
    $pdf->setSourceFile(__DIR__ . "/../plantillas/carnet-adelante$sufijo.pdf");
    $pdf->useTemplate($pdf->importPage(1));

    $x = CARNET_X;
    $y = CARNET_Y;
    if ($foto) {
        try {
            $pdf->Image($foto[0], $x, $y, 26.1, 24.9, $foto[1]);
        } catch (Exception $e) {
            throw new RuntimeException('La foto del miembro no se pudo usar (' . $e->getMessage() . '). Volvé a cargarla en JPG.', 422);
        }
    }

    if ($tipo === 'misionero') {
        $cargo = $m['misionero'] ? 'Instructor Nacional' : 'Instructor Local';
    } else {
        $cargo = $m['cargo'];
    }

    $y += 25;
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetXY($x, $y);
    $pdf->Write(10, pdf_texto($m['nombre']));

    $pdf->SetFont('helvetica', '', 9);
    foreach (['ID:' . $m['registro'], 'DNI:' . $m['dni'], $cargo] as $linea) {
        $y += CARNET_INTERLINEA;
        $pdf->SetXY($x, $y);
        $pdf->Write(10, pdf_texto($linea));
    }

    // Dorso
    $pdf->AddPage('L', 'A4', 270);
    $pdf->setSourceFile(__DIR__ . "/../plantillas/carnet-atras$sufijo.pdf");
    $pdf->useTemplate($pdf->importPage(1));

    $y += CARNET_INTERLINEA;
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetXY($x + 7, $y);
    $pdf->Write(10, 'Fecha de emision ' . date('d-m-Y') . ' - Vence ' . date('d-m-Y', strtotime('+2 years')));

    return $pdf;
}
