<?php

return [

    'template' => 'mailersaver::default',
    'override' => [
        'enabled' => env('MAILERSAVER_ENABLED', false),
        'to' => env('MAILERSAVER_TO', 'default1@mailto.com,default2@mailto.com'),
        'cc' => env('MAILERSAVER_CC', ''),
        'bcc' => env('MAILERSAVER_BCC', ''),
    ],

];
