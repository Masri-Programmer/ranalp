<?php

return [
    'title' => 'Historique des transactions',
    'description' => 'Consultez vos achats, investissements, dons et activités d\'enchères.',
    'search_placeholder' => 'Rechercher des transactions...',
    'item_unavailable' => 'Article non disponible',
    'empty_state' => 'Aucune transaction trouvée.',
    'filters' => [
        'all' => 'Tout',
        'donations' => 'Dons',
        'auctions' => 'Enchères',
        'purchases' => 'Achats',
    ],
    'columns' => [
        'date' => 'Date',
        'type' => 'Type',
        'item' => 'Article',
        'amount' => 'Montant',
        'status' => 'Statut',
        'action' => 'Action',
    ],
    'types' => [
        'auction_purchase' => 'Enchère (Achat immédiat)',
        'donation' => 'Don',
        'auction' => 'Enchère',
    ],
    'status' => [
        'completed' => 'Terminé',
        'pending' => 'En attente',
        'failed' => 'Échoué',
        'cancelled' => 'Annulé',
    ],
    'actions' => [
        'view' => 'Voir',
        'download' => 'Télécharger',
    ],
];
