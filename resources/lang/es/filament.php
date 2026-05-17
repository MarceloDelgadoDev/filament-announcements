<?php

return [
    'navigation' => [
        'model' => 'Anuncio',
        'plural' => 'Anuncios',
    ],

    'form' => [
        'title' => [
            'label' => 'Título',
            'helper' => 'Encabezado corto que se muestra en el panel.',
        ],
        'body' => [
            'label' => 'Cuerpo',
            'helper' => 'Mensaje completo que los usuarios deben leer.',
        ],
        'type' => [
            'label' => 'Tipo',
            'helper' => 'La gravedad define el ícono y los colores en el panel.',
        ],
        'is_active' => [
            'label' => 'Activo',
            'helper' => 'Los anuncios inactivos están ocultos para todos.',
        ],
        'is_dismissible' => [
            'label' => 'Descartable',
            'helper' => 'Si está desactivado, los usuarios no pueden cerrar el banner.',
        ],
        'starts_at' => [
            'label' => 'Inicia el',
            'helper' => 'Opcional. Oculto hasta este momento.',
        ],
        'expires_at' => [
            'label' => 'Expira el',
            'helper' => 'Opcional. Se oculta automáticamente después de este momento.',
        ],
    ],

    'table' => [
        'created_at' => 'Creado el',
        'column_active' => 'Activo',
        'expired' => 'Expirado',
        'filter_inactive_manual' => 'Inactivo (manual)',
        'filter_expired_by_date' => 'Expirado por fecha',
        'filter_scheduled_future' => 'Programado (inicio futuro)',
        'bulk_activate' => 'Activar seleccionados',
        'bulk_deactivate' => 'Desactivar seleccionados',
    ],
];
