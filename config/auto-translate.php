<?php

return [
    /*
     * Here you can specify the source language code.
     */
    'source_language' => 'en',

    /*
     * Here you can specify the target language code(s). This can be a string or an array.
     */
    'target_language' => [
    'de','es','fr','pt','it','nl','pl','ru','ro','bg','ms','hr','cs','da','et','fi','zh'
    ],

    'dict' => [
        'en'=>'English',
        'de'=>'German',
        'es'=>'Spanish',
        'fr'=>'French',
        'pt'=>'Portuguese',
        'it'=>'Italian',
        'nl'=>'Dutch',
        'pl'=>'Polish',
        'ru'=>'Russian',
        'ro'=>'Romanian',
        'bg'=>'Bulgarian',
        'ms'=>'Malay',
        'hr'=>'Croatian',
        'cs'=>'Czech',
        'da'=>'Danish',
        'et'=>'Estonian',
        'fi'=>'Finnish',
        'zh'=>'Chinese'
    ],
    /*
     * Specify the path to the translation files.
     */
    'path' => realpath(base_path('resources/lang')),

    /*
     * This is the translator used to translate the source language files. You can also specify your own here if you wish. It has to implement \Ben182\AutoTranslate\Translators\TranslatorInterface.
     */
    'translator' => \Ben182\AutoTranslate\Translators\SimpleGoogleTranslator::class,

    'simple_google_translator' => [

        // The translator will wait between these numbers between each request.
        'sleep_between_requests' => [1, 3],

        // If you want to proxy the requests, you can specify a proxy server here.
        'proxy' => '',
    ],

    'deepl' => [

        // Your DeepL API Key. See https://www.deepl.com/pro.html#developer
        'api_key' => '',
    ],
];
