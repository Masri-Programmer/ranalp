<?php

return [
    'buttons' => [
        'submit' => 'Créer une annonce',
        'submitting' => 'Création en cours...',
        'preview' => 'Prévisualiser l\'annonce',
        'save_draft' => 'Enregistrer comme brouillon',
    ],
    'description' => 'Sélectionnez un type d\'annonce et remplissez les détails pour publier votre offre.',
    'fields' => [
        'buy_it_now_price' => [
            'label' => 'Prix d\'achat immédiat (€) (facultatif)',
            'placeholder' => 'Permet un achat immédiat',
        ],
        'category' => [
            'label' => 'Catégorie',
            'placeholder' => 'Sélectionnez une catégorie',
        ],
        'condition' => [
            'label' => 'État',
            'options' => [
                'new' => 'Neuf',
                'refurbished' => 'Reconditionné',
                'used' => 'Occasion',
            ],
        ],
        'description' => [
            'label' => 'Description',
            'placeholder' => 'Décrivez votre annonce en détail...',
            'hint' => 'Vous pouvez formater le texte ici.',
        ],
        'donation_goal' => [
            'label' => 'Objectif de don (€)',
            'placeholder' => 'ex. 5000',
        ],
        'ends_at' => [
            'label' => 'Fin de l\'enchère',
            'placeholder' => 'Sélectionnez une date de fin',
        ],
        'expires_at' => [
            'label' => 'Date d\'expiration (facultatif)',
            'placeholder' => 'Sélectionnez une date',
        ],
        'is_goal_flexible' => [
            'label' => 'Objectif flexible (conserver les fonds même s\'il n\'est pas atteint)',
        ],
        'location' => [
            'label' => 'Localisation',
            'placeholder' => 'ex. « Berlin, Allemagne »',
        ],
        'media' => [
            'description' => 'Télécharger ...',
            'documents' => 'Documents',
            'dropzone' => 'Déposez ou parcourez les fichiers ici',
            'images' => 'Photos',
            'label' => 'Fichier (photos, documents...)',
            'videos' => 'Vidéos',
            'video_embed' => 'Intégrer une vidéo (lien YouTube/Vimeo)',
            'attachments' => 'Médias et Pièces jointes',
            'dropzone_unified' => 'Glissez & déposez vos images, vidéos ou documents ici, ou parcourez',
        ],
        'price' => [
            'label' => 'Prix (€)',
            'placeholder' => 'ex. 499.99',
        ],
        'quantity' => [
            'label' => 'Quantité disponible',
        ],
        'reserve_price' => [
            'label' => 'Prix de réserve (€) (facultatif)',
            'placeholder' => 'Non affiché publiquement',
        ],
        'start_price' => [
            'label' => 'Enchère de départ (€)',
            'placeholder' => 'ex. 1.00',
        ],
        'starts_at' => [
            'label' => 'Début de l\'enchère (facultatif)',
            'placeholder' => 'Sélectionnez une date de début',
        ],
        'title' => [
            'label' => 'Titre',
            'placeholder' => 'ex. « Appartement moderne en ville »',
        ],
        'investment_goal' => 'Objectif d\'investissement',
        'minimum_investment' => 'Investissement minimum',
        'shares_offered' => [
            'label' => 'Parts Offertes',
        ],
        'share_price' => [
            'label' => 'Prix par Part',
        ],
        'images' => 'Images',
        'documents' => 'Documents',
        'videos' => 'Vidéos',
        'is_private' => [
            'label' => 'Privé (Lien d\'invitation seulement)',
            'help' => 'Si activée, la page ne sera pas affichée publiquement. Elle n\'est visible que par les personnes qui reçoivent le lien d\'invitation (WhatsApp, SMS, Email). Aucune inscription n\'est nécessaire pour les donateurs.',
        ],
        'association_check' => [
            'label' => 'J\'agis au nom d\'une association enregistrée (e.V.)',
            'help' => 'Les campagnes de dons ne peuvent être créées que par des associations enregistrées en Allemagne.',
        ],
        'association_proof' => [
            'label' => 'Preuve du statut caritatif / Extrait du registre des associations',
            'placeholder' => 'Télécharger un document',
        ],
        'tax_receipt_info' => [
            'label' => 'Note sur les reçus de dons',
            'text' => 'Nous sommes tenus d\'émettre un reçu de don ou une facture pour tout don supérieur à 300 euros. Veuillez confirmer que vous pouvez garantir cela.',
        ],
        'target' => [
            'label' => 'Objectif de Don (€)',
            'placeholder' => 'ex. 5000',
        ],
        'is_capped' => [
            'label' => 'Objectif Flexible (Conserver les fonds même s\'il n\'est pas atteint)',
        ],
    ],
    'notifications' => [
        'error' => 'Erreur lors de la création de l\'annonce. Veuillez vérifier vos saisies.',
        'success' => 'Annonce créée avec succès ! Elle est maintenant en cours de révision.',
        'preview_mode' => 'Vous êtes en mode aperçu. Cliquez sur "Créer la campagne" pour publier.',
    ],
    'sections' => [
        'core' => 'Détails essentiels',
        'type' => 'Type d\'annonce',
        'common' => 'Détails communs',
        'details' => 'Détails de l\'annonce',
        'media' => 'Média (Images, Documents, Vidéos)',
        'sales_details' => 'Informations sur la vente',
        'donation_details' => 'Objectifs de don',
        'mode_select' => 'Sélectionner le mode de vente',
        'design' => 'Design et Prévisualisation',
        'settings' => 'Paramètres et Confidentialité',
        'verification' => 'Vérification et Mentions Légales',
    ],
    'title' => 'Créer une nouvelle annonce',
    'types' => [
        'auction' => [
            'description' => 'Vendre un article au plus offrant.',
            'title' => 'Enchère',
        ],
        'purchase' => [
            'description' => 'Prix fixe pour des articles ou services.',
            'title' => 'Achat immédiat',
        ],
        'donation' => [
            'description' => 'Collecter des fonds pour un objectif spécifique.',
            'title' => 'Collecte de fonds',
        ],
        'investment' => 'Investissement',
        'private_occasion' => [
            'title' => 'Occasion Privée',
            'description' => 'Soutenez les anniversaires ou les événements spéciaux au sein du cercle de la famille et des amis – tout simplement par des cadeaux privés.',
        ],
        'founders_creatives' => [
            'title' => 'Fondateurs et Créatifs',
            'description' => 'Réalisez vos idées créatives : Présentez votre projet à notre communauté et collectez le soutien dont vous avez besoin pour sa mise en œuvre.',
        ],
        'donation_campaign' => [
            'title' => 'Campagne de Dons',
            'description' => 'Pour l\'engagement volontaire : Présentez des initiatives locales ou des projets d\'aide internationaux et recevez un soutien ciblé.',
        ],
        'charity_action' => [
            'title' => 'Action Caritative',
            'description' => 'Proposez des produits aux enchères ou à l\'achat direct et soutenez une bonne cause avec les bénéfices.',
        ],
    ],
    'placeholders' => [
        'title' => 'ex. « Veste en cuir vintage »',
        'description' => 'Décrivez votre article, projet ou objectif en détail...',
        'category' => 'Sélectionnez une catégorie',
        'location' => 'ex. « Berlin, Allemagne »',
        'price' => 'ex. 99.50',
        'start_price' => 'ex. 10.00',
        'reserve_price' => 'ex. 50.00',
        'buy_it_now_price' => 'ex. 150.00',
        'donation_goal' => 'ex. 5000',
        'investment_goal' => 'ex. 50000',
        'minimum_investment' => 'ex. 500',
        'shares_offered' => 'ex. 1000',
        'share_price' => 'ex. 50',
        'video_embed' => 'https://www.youtube.com/watch?v=...',
    ],
    'conditions' => [
        'new' => 'Neuf',
        'used' => 'Occasion',
        'refurbished' => 'Reconditionné',
    ],
    'tooltips' => [
        'is_goal_flexible' => 'Si coché, les dons peuvent continuer même après que l\'objectif soit atteint.',
        'preview' => 'Voyez ici à quoi ressemblera la page pour les visiteurs.',
        'invitation_link' => 'Vous pouvez copier ce lien après la création et l\'envoyer via WhatsApp/SMS.',
    ],
    'media' => [
        'dropzone' => 'Déposez les fichiers ici ou cliquez pour parcourir',
        'remove' => 'Supprimer',
        'existing' => 'Média existant',
    ],
    'listings' => [
        'edit' => [
            'title' => 'Modifier « {title} »',
            'description' => 'Mettez à jour les détails de votre annonce, les médias et les paramètres spécifiques ci-dessous.',
            'actions' => [
                'save' => 'Enregistrer les modifications',
                'saving' => 'Enregistrement en cours...',
            ],
        ],
    ],
    'edit' => [
        'notifications' => [
            'success' => 'Annonce mise à jour avec succès.',
        ],
    ],
    'modes' => [
        'auction' => 'Enchères',
        'purchase' => 'Acheter maintenant / Prix fixe',
    ],
    'preview' => [
        'mode' => 'Mode Aperçu',
        'notice' => 'Ceci est un aperçu de votre annonce. Certaines fonctionnalités (comme les enchères ou les likes) sont désactivées.',
    ],
    'validation' => [
        'association_required' => 'Une preuve d\'association enregistrée est requise pour les campagnes de dons.',
        'receipt_agreement' => 'Vous devez accepter l\'émission de reçus de dons à partir de 300€.',
    ],
    'terms' => [
        'title' => 'Conditions Générales',
        'description' => 'En créant une annonce, vous acceptez nos conditions générales.',
        'agree' => 'J\'accepte les',
        'link' => 'Conditions d\'Utilisation',
    ],
];
