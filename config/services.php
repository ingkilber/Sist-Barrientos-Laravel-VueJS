<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    'mailgun' => [
        'domain' => 'your-mailgun-domain',
        'secret' => 'your-mailgun-key',
    ],

    'ses' => [
        'key' => 'your-ses-key',
        'secret' => 'your-ses-secret',
        'region' => 'ses-region',  // e.g. us-east-1
    ],

    'sparkpost' => [
        'secret' => 'your-sparkpost-key',
    ],

    'mandrill' => [
        'secret' => 'your-mandrill-key',
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

];
