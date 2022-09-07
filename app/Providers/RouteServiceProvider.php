<?php

namespace App\Providers;

use App\Template;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';
    protected $tenantNamespace = 'App\Http\Controllers\Tenant';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        if(isSaas()){
            $this->mapApiRoutes();

            $this->mapWebRoutes();

            $this->mapWebHookRoutes();
        }
        else{
            $this->mapApiRoutesTenant();
            $this->mapWebRoutesTenant();
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes_saas/web.php'));
    }


    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes_saas/api.php'));
    }


    protected function mapWebHookRoutes(){
        Route::namespace($this->namespace)
            ->group(base_path('routes_saas/webhooks.php'));
    }



    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutesTenant()
    {
        Route::middleware(['web','storage'])
            ->namespace($this->tenantNamespace)
            ->group(base_path('routes/web.php'));

    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutesTenant()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->tenantNamespace)
            ->group(base_path('routes/api.php'));
    }


}
