<?php
// Configuración del generador de carnets.
// Este archivo NO es accesible desde la web (ver app/.htaccess).

return [
    // Clave compartida con la página que llama. Debe ser la misma en ambos lados.
    // Para cambiarla: php -r "echo bin2hex(random_bytes(32));"
    'secreto' => 'f0dc467d97daf189cbc77d0957e99abb0cb3c2d0e1cb1ee1e085db3a6baa95d9',

    // Vida máxima de un token, en segundos. El que llama suele usar 5 minutos.
    'ttl_maximo' => 900,

    // Si el navegador envía Referer, su dominio debe ser uno de estos (http o https).
    // Dejar vacío para no controlarlo.
    'origenes_permitidos' => [
        'igca.com.ar',
        'www.igca.com.ar',
        'localhost',   // ionic serve / pruebas locales
    ],

    // API de miembros: se le agrega el número de documento al final.
    'api_miembro' => 'https://ac.gnosis.is/api/GAPP/GETMIEMBRO/gapp/',
    'api_timeout' => 15,

    'zona_horaria' => 'America/Argentina/Buenos_Aires',

    // true = muestra el error técnico en pantalla. Solo para diagnosticar; dejar en false.
    'depurar' => false,
];
