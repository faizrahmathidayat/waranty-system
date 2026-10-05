<?php

return [
    // Shown in the "PENTING" box of the public e-warranty card (/warranty/{kode}).
    // Override in .env: WARRANTY_CS_WHATSAPP, WARRANTY_WEBSITE.
    'customer_service_whatsapp' => env('WARRANTY_CS_WHATSAPP', '0815 1345 5525'),
    'website' => env('WARRANTY_WEBSITE', 'www.lexent.id'),
];
