<?php

namespace App\Providers;

use App\Models\Subscriber;
use App\Template;
use Hyn\Tenancy\Environment;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Schema::defaultStringLength(191);

        $env = app(Environment::class);

        if ($fqdn = optional($env->hostname())->fqdn) {
            if(!app()->runningInConsole()){
                require_once '../app/Lib/functions.php';

            }

        }
        else{
            define('SAAS_UPLOADS','saas/saas_uploads');

            if(!app()->runningInConsole()){
                $path1 = '../app/LibSaas/helpers.php';
                $path2 = 'app/LibSaas/helpers.php';
                if(file_exists($path1)){
                    require_once '../app/LibSaas/helpers.php';
                }
                elseif(file_exists($path2)){
                    require_once $path2;
                }
                else{
                    notifyAdmin('No helper','No helper file');
                    abort('404');
                }



            }

        }




        if ($fqdn = optional($env->hostname())->fqdn && !app()->runningInConsole()) {

            //set view paths

            $viewPath = resource_path('tenant-views');
            View::addLocation($viewPath);

          /*  $finder = new \Illuminate\View\FileViewFinder(app()['files'], array($viewPath));
            View::setFinder($finder);*/



            $websiteId= $env->hostname()->website->id;
            define('WID',$websiteId);

            //get subscriber
            $subscriber = Subscriber::where('website_id',$websiteId)->first();
            if(!$subscriber || $subscriber->user->enabled==0){
                $this->websiteUnavailable();
            }



            //get plan
            $package = $subscriber->packageDuration->package;
            if(empty($package->is_free) && $subscriber->expires < time()){
                $this->websiteUnavailable();
            }

            //define constants
            define('USER_LIMIT',$package->user_limit);
            define('DEPARTMENT_LIMIT',$package->department_limit);

            //calculate storage limit in bytes
            $multiplier = 1;
            switch($package->storage_unit){
                case 'mb':
                    $multiplier = 1048576;
                    break;
                case 'gb':
                    $multiplier = (1024*1024*1024);
                    break;
                case 'tb':
                    $multiplier = (1024*1024*1024*1024);
                    break;
            }

            $spaceLimit = $package->storage_space * $multiplier;
            define('STORAGE_SPACE',$spaceLimit);

            $userPath = "uploads/{$websiteId}";
            $spaceUsed=    filesize_r($userPath);
            define('STORAGE_USED',$spaceUsed);
            config(['database.default' => 'tenant']);
            //set user model
            $config = config(['auth.providers.users.model'=>\App\User::class]);


            $this->bootTenant($websiteId);


        }
        elseif(!app()->runningInConsole()){
            $viewPath = resource_path('views');
            View::addLocation($viewPath);

            try{

            $language = setting('config_language');
            if($language != 'en'){
                App::setLocale($language);

            }


                //setup email
                $protocol = setting('mail_protocol');
                if($protocol=='smtp'){
                    config([
                        'mail.driver' => 'smtp',
                        'mail.host' => setting('mail_smtp_host'),
                        'mail.port' => setting('mail_smtp_port'),
                        'mail.encryption' =>'tls',
                        'mail.username' => setting('mail_smtp_username'),
                        'mail.password' => setting('mail_smtp_password')
                    ]);

                    $app = App::getInstance();

                    /*     $app['swift.transport'] = $app->share(function ($app) {
                             return new TransportManager($app);
                         });*/

                    $app->singleton('swift.transport', function ($app) {
                        return new \Illuminate\Mail\TransportManager($app);
                    });

                    $mailer = new \Swift_Mailer($app['swift.transport']->driver());
                    Mail::setSwiftMailer($mailer);
                }


            }
            catch(\Exexption $ex){

            }


            //set user model
            $config = config(['auth.providers.users.model'=>\App\Models\User::class]);
        }
    }

    public function bootTenant($websiteId)
    {
        Schema::defaultStringLength(191);

        if(!Schema::hasTable('settings')){
            return true;
        }
        define('UPLOAD_PATH',config('app.upload_path'));
        define('ATTACHMENTS',$websiteId.'/attachments');
        define('DEPARTMENTS',$websiteId.'/departments');
        define('DOWNLOADS',$websiteId.'/downloads');
        define('FORUM',$websiteId.'/forum');
        define('GALLERIES',$websiteId.'/galleries');
        define('IMAGES',$websiteId.'/images');
        define('MEMBERS',$websiteId.'/members');
        define('SETTINGS',$websiteId.'/settings');
        define('TEMP_DIR','../storage/tmp/');
        
        define('ADS',$websiteId.'/ads');


        define('ATTACHMENT_PATH',"uploads/{$websiteId}/attachments");
        define('DOWNLOAD_PATH',"uploads/{$websiteId}/downloads");
        define('FORUM_PATH',"uploads/{$websiteId}/forum");
        define('MEMBER_PATH',"uploads/{$websiteId}/members");
        define('SETTINGS_PATH',"uploads/{$websiteId}/settings");




        Paginator::useBootstrapThree();

        try{
            //setup email
            $protocol = setting('mail_protocol');
            if($protocol=='smtp'){
                config([
                    'mail.driver' => 'smtp',
                    'mail.host' => setting('mail_smtp_host'),
                    'mail.port' => setting('mail_smtp_port'),
                    'mail.encryption' =>'tls',
                    'mail.username' => setting('mail_smtp_username'),
                    'mail.password' => setting('mail_smtp_password')
                ]);



                /*     $app['swift.transport'] = $app->share(function ($app) {
                         return new TransportManager($app);
                     });*/

/*                $app->singleton('swift.transport', function ($app) {
                    return new \Illuminate\Mail\TransportManager($app);
                });*/

                $transport = new \Swift_SmtpTransport(setting('mail_smtp_host'), setting('mail_smtp_port'));
                $transport->setUsername(setting('mail_smtp_username'));
                $transport->setPassword(setting('mail_smtp_password'));

                $mailer = new \Swift_Mailer($transport);
                Mail::setSwiftMailer($mailer);
            }

            //set language
            $language = setting('config_language');
            if($language != 'en'){
                App::setLocale($language);
            }
        }
        catch(\Exexption $ex){

        }









        if(class_exists('\App\Lib\Helpers') && method_exists(new \App\Lib\Helpers(),'bootProviders')){
            \App\Lib\Helpers::bootProviders();
        }
    }

    private function websiteUnavailable(){

        // $content = file_get_contents('expired.html');
        // $title = __('saas.website-expired');
        // $msg = __('saas.website-expired-msg');
        // $content = str_ireplace('[title]',$title,$content);
        // $content = str_ireplace('[message]',$msg,$content);
        $content = "Your website plan has been expired! Please renew your plan!";
        exit($content);
    }
}
