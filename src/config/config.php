<?php

return [

    'template' => 'mailersaver::default',
    'override' => [
        'enabled' => env('MAILERSAVER_ENABLED', false),
        'to' => env('MAILERSAVER_TO', ['default@mailto.com']),
        'cc' => env('MAILERSAVER_CC', ''),
        'bcc' => env('MAILERSAVER_BCC', ''),
    ],

];
