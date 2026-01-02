<?php

return [
    'buttons' => [
        'submit' => 'Crear anuncio',
        'submitting' => 'Creando...',
        'preview' => 'Previsualizar Anuncio',
        'save_draft' => 'Guardar como Borrador',
    ],
    'description' => 'Selecciona un tipo de anuncio y completa los detalles para publicar tu oferta.',
    'fields' => [
        'buy_it_now_price' => [
            'label' => 'Precio de compra directa (€) (opcional)',
            'placeholder' => 'Permite la compra inmediata',
        ],
        'category' => [
            'label' => 'Categoría',
            'placeholder' => 'Seleccionar categoría',
        ],
        'condition' => [
            'label' => 'estado',
            'options' => [
                'new' => 'Nuevo',
                'refurbished' => 'reacondicionado',
                'used' => 'usado',
            ],
        ],
        'description' => [
            'label' => 'Descripción',
            'placeholder' => 'Describe tu anuncio en detalle...',
            'hint' => 'Puedes formatear texto aquí.',
        ],
        'donation_goal' => [
            'label' => 'Meta de donación (€)',
            'placeholder' => 'ej. 5000',
        ],
        'ends_at' => [
            'label' => 'fin de subasta',
            'placeholder' => 'Seleccionar fecha de fin',
        ],
        'expires_at' => [
            'label' => 'Fecha de caducidad (opcional)',
            'placeholder' => 'Seleccionar fecha',
        ],
        'is_goal_flexible' => [
            'label' => 'Meta flexible (conservar fondos incluso si no se alcanza)',
        ],
        'location' => [
            'label' => 'Ubicación',
            'placeholder' => 'ej. “Berlín, Alemania”',
        ],
        'media' => [
            'description' => 'Descargar ...',
            'documents' => 'Documentos',
            'dropzone' => 'Arrastra o busca archivos aquí',
            'images' => 'Fotos',
            'label' => 'Archivo (fotos, documentos...)',
            'videos' => 'vídeos',
            'video_embed' => 'Insertar Video (Enlace de YouTube/Vimeo)',
            'attachments' => 'Medios y Archivos Adjuntos',
            'dropzone_unified' => 'Arrastra y suelta tus imágenes, videos o documentos aquí, o navega',
        ],
        'price' => [
            'label' => 'Precio (€)',
            'placeholder' => 'ej. 499.99',
        ],
        'quantity' => [
            'label' => 'Cantidad disponible',
        ],
        'reserve_price' => [
            'label' => 'Precio mínimo (€) (opcional)',
            'placeholder' => 'No se muestra públicamente',
        ],
        'start_price' => [
            'label' => 'Puja inicial (€)',
            'placeholder' => 'ej. 1.00',
        ],
        'starts_at' => [
            'label' => 'Inicio de subasta (opcional)',
            'placeholder' => 'Seleccionar fecha de inicio',
        ],
        'title' => [
            'label' => 'Título',
            'placeholder' => 'ej. “Apartamento moderno en la ciudad”',
        ],
        'investment_goal' => 'Objetivo de Inversión',
        'minimum_investment' => 'Inversión Mínima',
        'shares_offered' => [
            'label' => 'Participaciones Ofrecidas',
        ],
        'share_price' => [
            'label' => 'Precio por Participación',
        ],
        'images' => 'Imágenes',
        'documents' => 'Documentos',
        'videos' => 'Vídeos',
        'is_private' => [
            'label' => 'Privado (Solo por enlace de invitación)',
            'help' => 'Si está habilitada, la página no se listará públicamente. Solo es visible para las personas que reciban el enlace de invitación (WhatsApp, SMS, correo electrónico). No es necesario registrarse para los donantes.',
        ],
        'association_check' => [
            'label' => 'Actúo en nombre de una asociación registrada (e.V.)',
            'help' => 'Las campañas de donación solo pueden ser creadas por asociaciones registradas en Alemania.',
        ],
        'association_proof' => [
            'label' => 'Prueba de Estatus Benéfico / Extracto del Registro de Asociaciones',
            'placeholder' => 'Subir documento',
        ],
        'tax_receipt_info' => [
            'label' => 'Nota sobre Recibos de Donación',
            'text' => 'Estamos obligados a emitir un recibo de donación o factura por cada donación superior a 300 euros. Por favor, confirme que puede garantizar esto.',
        ],
        'target' => [
            'label' => 'Meta de Donación (€)',
            'placeholder' => 'ej. 5000',
        ],
        'is_capped' => [
            'label' => 'Meta Flexible (Conservar los fondos aunque no se alcance)',
        ],
    ],
    'notifications' => [
        'error' => 'Error al crear el anuncio. Por favor, revisa tus datos.',
        'success' => '¡Anuncio creado con éxito! Ahora está siendo revisado.',
        'preview_mode' => 'Estás en modo de vista previa. Haz clic en "Crear Campaña" para publicar.',
    ],
    'sections' => [
        'core' => 'detalles principales',
        'type' => 'Tipo de Anuncio',
        'common' => 'Detalles Comunes',
        'details' => 'Detalles del Anuncio',
        'media' => 'Multimedia (Imágenes, Documentos, Vídeos)',
        'sales_details' => 'Información de Venta',
        'donation_details' => 'Objetivos de Donación',
        'mode_select' => 'Seleccionar Método de Venta',
        'design' => 'Diseño y Vista Previa',
        'settings' => 'Configuración y Privacidad',
        'verification' => 'Verificación y Legal',
    ],
    'title' => 'Crear nuevo anuncio',
    'types' => [
        'auction' => [
            'description' => 'Vende un artículo al mejor postor.',
            'title' => 'Subasta',
        ],
        'purchase' => [
            'description' => 'Precio fijo para artículos o servicios.',
            'title' => 'Compra Directa',
        ],
        'donation' => [
            'description' => 'Recauda fondos para un propósito específico.',
            'title' => 'Recaudación de Fondos',
        ],
        'investment' => 'Inversión',
        'private_occasion' => [
            'title' => 'Ocasión Privada',
            'description' => 'Apoya cumpleaños o eventos especiales dentro del círculo de familiares y amigos, de forma sencilla a través de regalos privados.',
        ],
        'founders_creatives' => [
            'title' => 'Fundadores y Creativos',
            'description' => 'Haz realidad tus ideas creativas: Presenta tu proyecto a nuestra comunidad y recolecta el apoyo que necesitas para su implementación.',
        ],
        'donation_campaign' => [
            'title' => 'Campaña de Donación',
            'description' => 'Para compromiso voluntario: Presenta iniciativas locales o proyectos de ayuda internacional y recibe apoyo específico.',
        ],
        'charity_action' => [
            'title' => 'Acción Benéfica',
            'description' => 'Ofrece productos para subasta o compra directa y apoya una buena causa con los ingresos.',
        ],
    ],
    'placeholders' => [
        'title' => 'ej., "Chaqueta de cuero vintage"',
        'description' => 'Describe tu artículo, proyecto u objetivo en detalle...',
        'category' => 'Selecciona una categoría',
        'location' => 'ej., "Berlín, Alemania"',
        'price' => 'ej., 99.50',
        'start_price' => 'ej., 10.00',
        'reserve_price' => 'ej., 50.00',
        'buy_it_now_price' => 'ej., 150.00',
        'donation_goal' => 'ej., 5000',
        'investment_goal' => 'ej., 50000',
        'minimum_investment' => 'ej., 500',
        'shares_offered' => 'ej., 1000',
        'share_price' => 'ej., 50',
        'video_embed' => 'https://www.youtube.com/watch?v=...',
    ],
    'conditions' => [
        'new' => 'Nuevo',
        'used' => 'Usado',
        'refurbished' => 'Reacondicionado',
    ],
    'tooltips' => [
        'is_goal_flexible' => 'Si está marcada, las donaciones pueden continuar incluso después de alcanzar la meta.',
        'preview' => 'Vea aquí cómo se verá la página para los visitantes.',
        'invitation_link' => 'Puedes copiar este enlace después de la creación y enviarlo por WhatsApp/SMS.',
    ],
    'media' => [
        'dropzone' => 'Arrastra archivos aquí o haz clic para buscar',
        'remove' => 'Eliminar',
        'existing' => 'Multimedia Existente',
    ],
    'listings' => [
        'edit' => [
            'title' => 'Editar "{title}"',
            'description' => 'Actualiza los detalles de tu anuncio, multimedia y configuraciones específicas a continuación.',
            'actions' => [
                'save' => 'Guardar Cambios',
                'saving' => 'Guardando...',
            ],
        ],
    ],
    'edit' => [
        'notifications' => [
            'success' => 'Anuncio actualizado con éxito.',
        ],
    ],
    'modes' => [
        'auction' => 'Subasta',
        'purchase' => 'Comprar Ahora / Precio Fijo',
    ],
    'preview' => [
        'mode' => 'Modo de Vista Previa',
        'notice' => 'Esta es una vista previa de tu anuncio. Algunas funciones (como pujar o dar \'me gusta\') están deshabilitadas.',
    ],
    'validation' => [
        'association_required' => 'Se requiere prueba de asociación registrada para las campañas de donación.',
        'receipt_agreement' => 'Debe aceptar la emisión de recibos de donación a partir de 300€.',
    ],
    'terms' => [
        'title' => 'Términos y Condiciones',
        'description' => 'Al crear un anuncio, acepta nuestros términos y condiciones.',
        'agree' => 'Acepto los',
        'link' => 'Términos de Servicio',
    ],
];
