<?php

return [
    // Shown in the "PENTING" box of the public e-warranty card (/warranty/{kode}).
    // Override in .env: WARRANTY_CS_WHATSAPP, WARRANTY_WEBSITE.
    'customer_service_whatsapp' => env('WARRANTY_CS_WHATSAPP', '0858 8889 9558'),
    'website' => env('WARRANTY_WEBSITE', 'www.lexentindonesia.com'),
    // Same box for GlossPro orders. Override in .env: WARRANTY_GLOSSPRO_CS_WHATSAPP, WARRANTY_GLOSSPRO_WEBSITE.
    'glosspro_customer_service_whatsapp' => env('WARRANTY_GLOSSPRO_CS_WHATSAPP', '0858 8889 9558'),
    'glosspro_website' => env('WARRANTY_GLOSSPRO_WEBSITE', 'www.glossproindonesia.com'),
];
