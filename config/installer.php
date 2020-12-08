<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel server requirements, you can add as many
    | as your application require, we check if the extension is enabled
    | by looping through the array and run "extension_loaded" on it.
    |
    */
   'core' => [
        'minPhpVersion' => '7.3.0',
    ],
    'requirements' => [
        'openssl',
        'pdo',
        'mbstring',
        'tokenizer',
        'JSON',
        'cURL',
        'bcmath',
       
    ],

    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        'storage/app/'             => '775',
        'storage/framework/'       => '775',
        'storage/logs/'            => '775',
        'storage/laravel-backups/' => '775',
        'bootstrap/cache/'         => '775',
        'public/images/'           => '775',
        'public/uploads/'          => '775',
        'vendor/h4cc/wkhtmltopdf-amd64' => '775',
    ]
];
