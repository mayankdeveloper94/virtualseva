<?php return array (
  'anhskohbo/no-captcha' => 
  array (
    'providers' => 
    array (
      0 => 'Anhskohbo\\NoCaptcha\\NoCaptchaServiceProvider',
    ),
    'aliases' => 
    array (
      'NoCaptcha' => 'Anhskohbo\\NoCaptcha\\Facades\\NoCaptcha',
    ),
  ),
  'appzcoder/crud-generator' => 
  array (
    'providers' => 
    array (
      0 => 'Appzcoder\\CrudGenerator\\CrudGeneratorServiceProvider',
    ),
  ),
  'barryvdh/laravel-debugbar' => 
  array (
    'providers' => 
    array (
      0 => 'Barryvdh\\Debugbar\\ServiceProvider',
    ),
    'aliases' => 
    array (
      'Debugbar' => 'Barryvdh\\Debugbar\\Facades\\Debugbar',
    ),
  ),
  'barryvdh/laravel-dompdf' => 
  array (
    'providers' => 
    array (
      0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    ),
    'aliases' => 
    array (
      'PDF' => 'Barryvdh\\DomPDF\\Facade',
    ),
  ),
  'beyondcode/laravel-dump-server' => 
  array (
    'providers' => 
    array (
      0 => 'BeyondCode\\DumpServer\\DumpServerServiceProvider',
    ),
  ),
  'fideloper/proxy' => 
  array (
    'providers' => 
    array (
      0 => 'Fideloper\\Proxy\\TrustedProxyServiceProvider',
    ),
  ),
  'hyn/multi-tenant' => 
  array (
    'providers' => 
    array (
      0 => 'Hyn\\Tenancy\\Providers\\TenancyProvider',
      1 => 'Hyn\\Tenancy\\Providers\\WebserverProvider',
    ),
    'aliases' => 
    array (
      'Tenancy' => 'Hyn\\Tenancy\\Facades\\TenancyFacade',
    ),
  ),
  'intervention/image' => 
  array (
    'providers' => 
    array (
      0 => 'Intervention\\Image\\ImageServiceProvider',
    ),
    'aliases' => 
    array (
      'Image' => 'Intervention\\Image\\Facades\\Image',
    ),
  ),
  'laravel/socialite' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Socialite\\SocialiteServiceProvider',
    ),
    'aliases' => 
    array (
      'Socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'laravel/ui' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Ui\\UiServiceProvider',
    ),
  ),
  'laravelcollective/html' => 
  array (
    'providers' => 
    array (
      0 => 'Collective\\Html\\HtmlServiceProvider',
    ),
    'aliases' => 
    array (
      'Form' => 'Collective\\Html\\FormFacade',
      'Html' => 'Collective\\Html\\HtmlFacade',
    ),
  ),
  'mews/purifier' => 
  array (
    'providers' => 
    array (
      0 => 'Mews\\Purifier\\PurifierServiceProvider',
    ),
    'aliases' => 
    array (
      'Purifier' => 'Mews\\Purifier\\Facades\\Purifier',
    ),
  ),
  'nesbot/carbon' => 
  array (
    'providers' => 
    array (
      0 => 'Carbon\\Laravel\\ServiceProvider',
    ),
  ),
  'nunomaduro/collision' => 
  array (
    'providers' => 
    array (
      0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    ),
  ),
);