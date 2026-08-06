<?php

// config/csm.php
// Cada Anexo tiene el término (en años) impreso como texto fijo dentro del PDF,
// por eso la duración se resuelve eligiendo el archivo correcto, no rellenando un campo.

return [

    'plantillas' => [
        1 => 'Anexo_1.pdf',
        2 => 'Anexo_2.pdf',
        3 => 'Anexo_3.pdf',
        4 => 'Anexo_4.pdf',
        5 => 'Anexo_5.pdf',
        6 => 'Anexo_6.pdf',
        7 => 'Anexo_7.pdf',
        8 => 'Anexo_8.pdf',
        9 => 'Anexo_9.pdf',
        10 => 'Anexo_10.pdf',
        11 => 'Anexo_11.pdf',
        12 => 'Anexo_12.pdf',
    ],

    // Carpeta donde se guardan las plantillas originales (subilas ahí a medida que las tengas)
    'plantillas_path' => storage_path('app/csm-plantillas'),

    // Binario de pdftk. En Windows, típicamente algo como C:\Program Files (x86)\PDFtk Server\bin\pdftk.exe
    'pdftk_binary' => env('PDFTK_BINARY', 'pdftk'),

    // Lugar donde se suscribe el contrato (aparece en la página 3, "Se suscribe el presente en ___")
    'lugar_suscripcion' => 'Montevideo',

    // Los contratos C.S.M. solo aplican a usuarios de esta unidad (B.Com.Nº1)
    'unidad_id' => 1,
];