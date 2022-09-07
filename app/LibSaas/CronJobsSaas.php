<?php
namespace App\LibSaas;

use App\Http\Middleware\Subscriber;
use App\Mail\InvoiceReminder;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CronJobsSaas
{


    public function siteCron(){
        set_time_limit(3600);
        $subscribers = Subscriber::where('expires','>',time())->get();

        foreach ($subscribers as $subscriber) {
            try{
                //$url = 'https://'.$subscriber->username.'.traineasy.net/cron';

                $hostname = $subscriber->website->hostnames()->first();
                if(!$hostname){
                    continue;
                }
                $protocol = ($hostname->force_https==1)? 'https://':'http://';
                $url = $protocol.$hostname->fqdn.'/cron';
                echo "fetching url: $url";
                getPageAsync($url);

            }
            catch(\Exception $ex){
                echo $ex->getTraceAsString().'<br/>';
            }

            // getPageAsync($url, [],'GET');
        }

        echo 'Cron complete';
    }

    public function notifyExpiringUsers(){
        $count = 0;
        //get all users expiring in 5 days
        $timeLimit = time() + (86400 * 5);
        $lowerLimit = time() - (86400 * 5);
        foreach(Subscriber::where('auto_renew',0)->where('expires','<=',$timeLimit)->where('expires','>',$lowerLimit)->cursor() as $subscriber){


            //check if user has any subscription invoice
            if($subscriber->user->invoices()->where('invoice_purpose_id',1)->where('paid',0)->count() ==0){
                //create invoice for user
                $itemId =  $subscriber->package_duration_id;
                $amount = $subscriber->packageDuration->price;

                if(empty($amount)){
                    continue;
                }

                //create invoice
                $hash = Hash::make($subscriber->user->id.$itemId.time());
                $hash = safeUrl($hash);
                $invoice= Invoice::create([
                    'user_id'=>$subscriber->user->id,
                    'invoice_purpose_id'=>1,
                    'amount'=>$amount,
                    'paid'=>0,
                    'item_id'=>$itemId,
                    'auto'=>0,
                    'hash'=>$hash,
                    'currency_id'=>$subscriber->currency_id
                ]);
            }
            else{

                $invoice = $subscriber->user->invoices()->where('paid',0)->where('invoice_purpose_id',1)->first();

            }


            //we now have the invoice. Send email to user
            $user= $subscriber->user;
            Mail::to($user)->send(new InvoiceReminder($invoice));

            //send to admins
            //   $this->notifyAdmins('User about to expire',"{$user->name}'s account expires soon. Follow up immediately.");

            $count++;

        }
        //    notifyAdmin('Subscription notices sent',"Subscription notice sent to {$count} users");
        echo "Subscription notice sent to {$count} users";

    }


}
