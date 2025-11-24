<?php

return [
    'default' => 'default',

    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'API TRAZA EBA',
            ],

            'routes' => [
                // Ruta donde se ve la UI de Swagger
                'api'  => 'api/documentation',
                // Ruta interna donde se sirve el JSON/YAML
                'docs' => 'docs',
            ],

            'paths' => [
                // Carpeta donde se guarda api-docs.json
                'docs'      => storage_path('api-docs'),

                // Nombre del archivo JSON que ya se generó
                'docs_json' => 'api-docs.json',
                // (opcional) si algún día quieres YAML
                // 'docs_yaml' => 'api-docs.yaml',

                // muy importante: dónde buscar las anotaciones @OA\...
                'annotations' => [
                    base_path('app/Http/Controllers'),
                ],

                // prefijo de tus rutas; como usas /api, lo dejamos así
                'base' => '/',
            ],
        ],
    ],

    // Opcional: genera siempre al entrar a /api/documentation
    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
];
