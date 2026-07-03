<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp admin (nomor tanpa +, contoh: 6281234567890)
    |--------------------------------------------------------------------------
    */
    'admin_whatsapp' => env('EQUAPP_ADMIN_WA', '6280000000000'),

    /*
    |--------------------------------------------------------------------------
    | URL prakiraan BMKG (DigitalForecast) � diambil server-side untuk hindari CORS
    |--------------------------------------------------------------------------
    */
    'bmkg_forecast_url' => env(
        'EQUAPP_BMKG_URL',
        'https://data.bmkg.go.id/DataMKG/MEWS/DigitalForecast/DigitalForecast-DI%20Yogyakarta.json'
    ),
];
