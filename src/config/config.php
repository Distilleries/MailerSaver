<?php

return [

    'mail'                => [
        'template' => 'mailersaver::admin.templates.mails.default',
        "actions"  => [
            'emails.auth.reminder'
        ],

        'override' => [
            'enabled' => false,
            'to'      => [''],
            'cc'      => [''],
            'bcc'     => ['']
        ]
    ],
];