<?php
namespace App\Providers;


use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $env = app(Environment::class);

        if ($fqdn = optional($env->hostname())->fqdn) {

            // Using class based composers...
            View::composer(
                ['layouts.admin','layouts.member','layouts._member'], 'App\Http\View\Composers\AdminComposer'
            );

            View::composer(
                ['layouts.site','auth.login','auth.register'], 'App\Http\View\Composers\SiteComposer'
            );
            
            View::composer(
                ['layouts.site2','auth.login','auth.register'], 'App\Http\View\Composers\SiteComposer'
            );

        }



    }
}