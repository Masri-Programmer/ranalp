<?php

return [
    'title' => 'Historial de transacciones',
    'description' => 'Ver tus compras, inversiones, donaciones y actividades de subasta.',
    'search_placeholder' => 'Buscar transacciones...',
    'item_unavailable' => 'Artículo no disponible',
    'empty_state' => 'No se encontraron transacciones.',
    'filters' => [
        'all' => 'Todos',
        'purchases' => 'Compras',
        'investments' => 'Inversiones',
        'donations' => 'Donaciones',
        'auctions' => 'Subastas',
    ],
    'columns' => [
        'date' => 'Fecha',
        'type' => 'Tipo',
        'item' => 'Artículo',
        'amount' => 'Cantidad',
        'status' => 'Estado',
        'action' => 'Acción',
    ],
    'types' => [
        'purchase' => 'Compra',
        'auction_purchase' => 'Subasta (Comprar ahora)',
        'investment' => 'Inversión',
        'donation' => 'Donación',
        'auction' => 'Subasta',
    ],
    'status' => [
        'completed' => 'Completado',
        'pending' => 'Pendiente',
        'failed' => 'Fallido',
        'cancelled' => 'Cancelado',
    ],
    'actions' => [
        'view' => 'Ver',
        'download' => 'Descargar',
    ],
];
