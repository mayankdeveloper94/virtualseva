<?php
namespace App\Lib;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Mail\TransportManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;

class Helpers {

    static public function bootProviders(){

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

                $app = App::getInstance();

                /*     $app['swift.transport'] = $app->share(function ($app) {
                         return new TransportManager($app);
                     });*/

              /*  $app->singleton('swift.transport', function ($app) {
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
                Carbon::setLocale($language);
            }
        }
        catch(\Exexption $ex){

        }



                validateFolder(ATTACHMENTS);
                validateFolder(DEPARTMENTS);
                validateFolder(DOWNLOADS);
                validateFolder(FORUM);
                validateFolder(GALLERIES);
                validateFolder(IMAGES);
                validateFolder(MEMBERS);
                validateFolder(SETTINGS);

                if(!file_exists(TEMP_DIR)){
                    rmkdir(TEMP_DIR);
                }




    }


}
